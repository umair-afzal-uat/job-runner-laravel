<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a job in the background with retry logic, delay, priority, validation, and logging.
     *
     * @param string $className
     * @param string $method
     * @param array $params
     * @param int $retryAttempts
     * @param int $retryDelay
     * @param int $jobDelay
     * @param int $priority
     * @return void
     */
    function runBackgroundJob($className, $method, $params = [], $retryAttempts = 3, $retryDelay = 5, $jobDelay = 0, $priority = 1)
    {
        // Pre-approved classes and methods
        $allowedJobs = [
            'App\Jobs\SendWelcomeEmail' => ['handle'],
            'App\Jobs\SendPasswordResetEmail' => ['handle', 'sendResetLink'],
            // Add more approved job classes and methods as needed
        ];

        // Validate if the class and method are allowed
        if (!isset($allowedJobs[$className]) || !in_array($method, $allowedJobs[$className])) {
            Log::channel('background_jobs')->error("Unauthorized job execution attempt: {$className}@{$method}", [
                'params' => $params,
                'status' => 'unauthorized',
                'timestamp' => now()->toDateTimeString(),
            ]);
            return;
        }

        // Escape class and method for safety
        $className = escapeshellarg($className);
        $method = escapeshellarg($method);
        $paramsString = http_build_query($params);

        // Prepare the command
        $command = "php artisan background:run {$className} {$method} {$paramsString}";

        // Check OS type and prepare command for background execution
        if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
            $command = 'start /B ' . $command;
        } else {
            $command = 'nohup ' . $command . ' > /dev/null 2>&1 &';
        }

        // Add job delay if specified
        if ($jobDelay > 0) {
            sleep($jobDelay);
        }

        // Initialize retry and success tracking
        $retryCount = 0;
        $success = false;

        // Log job start with timestamp and priority
        Log::channel('background_jobs')->info("Job started: {$className}@{$method}", [
            'params' => $params,
            'status' => 'running',
            'priority' => $priority,
            'timestamp' => now()->toDateTimeString(),
        ]);

        // Use Cache to manage job priority (optional for future dashboard integration)
        Cache::put("job_{$className}_{$method}", [
            'status' => 'running',
            'priority' => $priority,
            'timestamp' => now()->toDateTimeString(),
        ], now()->addMinutes(30));

        while ($retryCount < $retryAttempts && !$success) {
            try {
                // Execute the background command
                exec($command);

                // Log job completion and update cache
                Log::channel('background_jobs')->info("Job completed: {$className}@{$method}", [
                    'params' => $params,
                    'status' => 'completed',
                    'timestamp' => now()->toDateTimeString(),
                ]);
                Cache::put("job_{$className}_{$method}", [
                    'status' => 'completed',
                    'timestamp' => now()->toDateTimeString(),
                ], now()->addMinutes(30));

                $success = true;
            } catch (\Exception $e) {
                Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", [
                    'error' => $e->getMessage(),
                    'params' => $params,
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ]);

                // Retry logic with delay
                $retryCount++;
                if ($retryCount < $retryAttempts) {
                    Log::channel('background_jobs')->info("Retrying job: {$className}@{$method} - Attempt {$retryCount} of {$retryAttempts}", [
                        'params' => $params,
                        'status' => 'retrying',
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    sleep($retryDelay);
                }
            }
        }

        if (!$success) {
            // Log final failure and update cache
            Log::channel('background_jobs')->error("Job failed after {$retryAttempts} attempts: {$className}@{$method}", [
                'params' => $params,
                'status' => 'failed',
                'timestamp' => now()->toDateTimeString(),
            ]);
            Cache::put("job_{$className}_{$method}", [
                'status' => 'failed',
                'timestamp' => now()->toDateTimeString(),
            ], now()->addMinutes(30));
        }
    }
}
