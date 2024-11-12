<?php

use Illuminate\Support\Facades\Log;

if (!function_exists('runBackgroundJob')) {
    /**
     * Run a job in the background with retry logic, validation, and logging.
     *
     * @param string $className
     * @param string $method
     * @param array $params
     * @param int $retryAttempts
     * @param int $retryDelay
     * @return void
     */
    function runBackgroundJob($className, $method, $params = [], $retryAttempts = 3, $retryDelay = 5)
    {
        // Pre-approved classes and methods
        $allowedJobs = [
            'App\Jobs\SendWelcomeEmail' => ['handle'],
            'App\Jobs\SendPasswordResetEmail' => ['handle', 'sendResetLink'],
            // Add more approved job classes and methods as needed
        ];

        // Validate if the class and method are allowed
        if (!isset($allowedJobs[$className]) || !in_array($method, $allowedJobs[$className])) {
            // Log unauthorized job attempt
            Log::channel('background_jobs')->error("Unauthorized job execution attempt: {$className}@{$method}", [
                'params' => $params,
                'status' => 'unauthorized',
                'timestamp' => now()->toDateTimeString(),
            ]);
            return;
        }

        $paramsString = http_build_query($params);

        // Escape class and method for safety
        $className = escapeshellarg($className);
        $method = escapeshellarg($method);

        // Prepare the command
        $command = "php artisan background:run {$className} {$method} {$paramsString}";

        $retryCount = 0;
        $success = false;

        // Log job start with timestamp
        Log::channel('background_jobs')->info("Job started: {$className}@{$method}", [
            'params' => $params,
            'status' => 'running',
            'timestamp' => now()->toDateTimeString(),
        ]);

        while ($retryCount < $retryAttempts && !$success) {
            try {
                // Check OS type (Unix or Windows)
                if (strncasecmp(PHP_OS, 'WIN', 3) === 0) {
                    // Windows (use start for background execution)
                    $command = 'start /B ' . $command;
                } else {
                    // Unix/Linux (use nohup to run in the background)
                    $command = 'nohup ' . $command . ' > /dev/null 2>&1 &';
                }

                // Execute the background command
                exec($command);

                // Log job completion with timestamp
                Log::channel('background_jobs')->info("Job completed: {$className}@{$method}", [
                    'params' => $params,
                    'status' => 'completed',
                    'timestamp' => now()->toDateTimeString(),
                ]);

                $success = true;
            } catch (\Exception $e) {
                // Log the error and retry attempt
                Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", [
                    'error' => $e->getMessage(),
                    'params' => $params,
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ]);

                // Retry logic
                $retryCount++;
                if ($retryCount < $retryAttempts) {
                    Log::channel('background_jobs')->info("Retrying job: {$className}@{$method} - Attempt {$retryCount} of {$retryAttempts}", [
                        'params' => $params,
                        'status' => 'retrying',
                        'timestamp' => now()->toDateTimeString(),
                    ]);

                    sleep($retryDelay);  // Delay between retries (in seconds)
                }
            }
        }

        if (!$success) {
            // Log failure after all retry attempts are exhausted
            Log::channel('background_jobs')->error("Job failed after {$retryAttempts} attempts: {$className}@{$method}", [
                'params' => $params,
                'status' => 'failed',
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
}
