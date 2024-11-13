<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use App\Models\BackgroundJob;

class RunBackgroundJob extends Command
{
    // Command signature with priority option defined as string values (high, normal, low)
    protected $signature = 'background:run {class} {method} {params?*} {--delay=0} {--priority=normal} {--retries=3} {--retry-delay=5}';

    // Command description
    protected $description = 'Run a background job with support for retries, delay, and priority tracking';

    /**
     * Handle function to execute the background job.
     * This function handles the instantiation of the class, method invocation, error logging,
     * retry mechanism, and updates job status in the cache.
     */
    public function handle()
    {
        // Extract command arguments and options
        $className = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params');
        $delay = (int) $this->option('delay');
        $priority = $this->option('priority');
        $retryAttempts = (int) $this->option('retries');
        $retryDelay = (int) $this->option('retry-delay');

        // Map priority string to numeric value
        $priorityMap = [
            'high' => 3,
            'normal' => 2,
            'low' => 1,
        ];

        $priorityValue = $priorityMap[$priority] ?? 2; // Default to normal if invalid priority

        try {
            // Parse the input parameters into an associative array (e.g., "key=value" becomes ['key' => 'value'])
            $parsedParams = [];
            foreach ($params as $param) {
                [$key, $value] = explode('=', $param);
                $parsedParams[$key] = is_numeric($value) ? (int)$value : $value;
            }

            // Log the start of the job execution
            Log::channel('background_jobs')->info("Job started: {$className}@{$method}", [
                'params' => $parsedParams,
                'priority' => $priority,
                'status' => 'running',
                'timestamp' => now()->toDateTimeString(),
            ]);

            // Store initial job status in the cache for dashboard or monitoring purposes
            Cache::put("job_{$className}_{$method}", [
                'status' => 'running',
                'priority' => $priorityValue,
                'params' => $parsedParams,
                'timestamp' => now()->toDateTimeString(),
            ], now()->addMinutes(30));

            // Create the background job in the database with priority value
            $job = BackgroundJob::create([
                'class_name' => $className,
                'method' => $method,
                'params' => json_encode($params),
                'status' => 'running',
                'priority' => $priorityValue,
                'created_at' => now(),
            ]);

            // Apply delay before starting the job execution if specified
            if ($delay > 0) {
                sleep($delay);
            }

            $retryCount = 0;
            $success = false;

            // Retry loop to handle job execution and retries
            while ($retryCount < $retryAttempts && !$success) {
                try {
                    // Use reflection to dynamically instantiate the class and call the specified method
                    $reflection = new \ReflectionClass($className);
                    $constructor = $reflection->getConstructor();

                    // Resolve constructor dependencies if any
                    $dependencies = [];
                    if ($constructor) {
                        foreach ($constructor->getParameters() as $param) {
                            $name = $param->getName();
                            $dependencies[] = $parsedParams[$name] ?? null;
                        }
                    }

                    // Instantiate the class and prepare method parameters
                    $classInstance = $reflection->newInstanceArgs($dependencies);
                    $methodReflection = $reflection->getMethod($method);
                    $methodParams = [];

                    foreach ($methodReflection->getParameters() as $param) {
                        $name = $param->getName();
                        $methodParams[] = $parsedParams[$name] ?? null;
                    }

                    // Invoke the specified method with the parsed parameters
                    $result = $methodReflection->invokeArgs($classInstance, $methodParams);

                    // Log successful execution and update cache status
                    Log::channel('background_jobs')->info("Job executed successfully: {$className}@{$method}", [
                        'status' => 'success',
                        'timestamp' => now()->toDateTimeString(),
                    ]);
                    // Update the job status in the database
                    $job->update([
                        'status' => 'completed',
                        'updated_at' => now(),
                    ]);
                    Cache::put("job_{$className}_{$method}", [
                        'status' => 'completed',
                        'timestamp' => now()->toDateTimeString(),
                    ], now()->addMinutes(30));

                    $success = true;
                } catch (\Exception $e) {
                    // Log the error and retry count, then increment retry count
                    Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", [
                        'error' => $e->getMessage(),
                        'retry_count' => $retryCount + 1,
                        'timestamp' => now()->toDateTimeString(),
                    ]);

                    $retryCount++;

                    // Log retry attempt and apply delay before next retry if needed
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

            // Log failure after exhausting all retries and update cache status
            if (!$success) {
                Log::channel('background_jobs')->error("Job failed after {$retryAttempts} attempts: {$className}@{$method}", [
                    'params' => $parsedParams,
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ]);
                // Update the job status to 'failed' in the database
                $job->update([
                    'status' => 'failed',
                    'updated_at' => now(),
                ]);
                Cache::put("job_{$className}_{$method}", [
                    'status' => 'failed',
                    'timestamp' => now()->toDateTimeString(),
                ], now()->addMinutes(30));
            }
        } catch (\Exception $e) {
            // Log any unexpected errors that occur during the job handling process
            Log::channel('background_jobs')->error("Unexpected error in job execution: {$className}@{$method}", [
                'error' => $e->getMessage(),
                'timestamp' => now()->toDateTimeString(),
            ]);
        }
    }
}
