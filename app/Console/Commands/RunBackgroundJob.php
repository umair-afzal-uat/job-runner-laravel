<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class RunBackgroundJob extends Command
{
    // Command signature with arguments for class, method, optional parameters, delay, priority, and retry attempts
    protected $signature = 'background:run {class} {method} {params?*} {--delay=0} {--priority=1} {--retries=3} {--retry-delay=5}';

    // A short description of what the command does
    protected $description = 'Run a background job with support for retries, delay, and priority tracking';

    /**
     * The main handle function that runs the background job.
     */
    public function handle()
    {
        $className = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params');
        $delay = (int) $this->option('delay');
        $priority = (int) $this->option('priority');
        $retryAttempts = (int) $this->option('retries');
        $retryDelay = (int) $this->option('retry-delay');

        try {
            // Parse the parameters into an associative array (e.g., "user_id=123" becomes ['user_id' => 123])
            $parsedParams = [];
            foreach ($params as $param) {
                [$key, $value] = explode('=', $param);
                $parsedParams[$key] = is_numeric($value) ? (int)$value : $value;
            }

            // Log the start of the job with details
            Log::channel('background_jobs')->info("Job started: {$className}@{$method}", [
                'params' => $parsedParams,
                'priority' => $priority,
                'status' => 'running',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Store initial job status in Cache for dashboard integration
            Cache::put("job_{$className}_{$method}", [
                'status' => 'running',
                'priority' => $priority,
                'params' => $parsedParams,
                'timestamp' => now()->toDateTimeString(),
            ], now()->addMinutes(30));

            // Delay execution if specified
            if ($delay > 0) {
                sleep($delay);
            }

            $retryCount = 0;
            $success = false;

            // Retry loop
            while ($retryCount < $retryAttempts && !$success) {
                try {
                    // Use reflection to instantiate the class and call the method
                    $reflection = new \ReflectionClass($className);
                    $constructor = $reflection->getConstructor();

                    $dependencies = [];
                    if ($constructor) {
                        foreach ($constructor->getParameters() as $param) {
                            $name = $param->getName();
                            $dependencies[] = $parsedParams[$name] ?? null;
                        }
                    }

                    $classInstance = $reflection->newInstanceArgs($dependencies);
                    $methodReflection = $reflection->getMethod($method);
                    $methodParams = [];

                    foreach ($methodReflection->getParameters() as $param) {
                        $name = $param->getName();
                        $methodParams[] = $parsedParams[$name] ?? null;
                    }

                    // Invoke the method
                    $result = $methodReflection->invokeArgs($classInstance, $methodParams);

                    // Log the success status of the job execution
                    Log::channel('background_jobs')->info("Job executed successfully: {$className}@{$method}", [
                        'status' => 'success',
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    Cache::put("job_{$className}_{$method}", [
                        'status' => 'completed',
                        'timestamp' => now()->toDateTimeString(),
                    ], now()->addMinutes(30));

                    $success = true;
                } catch (\Exception $e) {
                    // Log the error and increment retry count
                    Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", [
                        'error' => $e->getMessage(),
                        'retry_count' => $retryCount + 1,
                        'timestamp' => now()->toDateTimeString(),
                    ]);

                    $retryCount++;
                    if ($retryCount < $retryAttempts) {
                        Log::channel('background_jobs')->info("Retrying job: {$className}@{$method} - Attempt {$retryCount} of {$retryAttempts}", [
                            'params' => $parsedParams,
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
                    'params' => $parsedParams,
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ]);
                Cache::put("job_{$className}_{$method}", [
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ], now()->addMinutes(30));
            }
        } catch (\Exception $e) {
            // Log unexpected errors
            Log::channel('background_jobs')->error("Unexpected error in job execution: {$className}@{$method}", [
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
}
