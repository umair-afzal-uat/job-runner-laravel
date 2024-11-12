<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunBackgroundJob extends Command
{
    // Command signature with arguments for class, method, and optional parameters
    protected $signature = 'background:run {class} {method} {params?*}';

    // A short description of what the command does
    protected $description = 'Run a background job in the background';

    /**
     * The main handle function that runs the background job.
     * This function:
     * 1. Accepts class name, method name, and optional parameters.
     * 2. Parses the parameters into an associative array.
     * 3. Uses reflection to instantiate the class and resolve constructor dependencies.
     * 4. Calls the specified method with the parsed parameters.
     * 5. Logs the execution status (success or failure) with timestamped entries.
     */
    public function handle()
    {
        // Retrieve the class name, method, and optional parameters from the command arguments
        $className = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params');

        try {
            // Parse the parameters into an associative array (e.g., "user_id=123" becomes ['user_id' => 123])
            $parsedParams = [];
            foreach ($params as $param) {
                // Split each parameter by '=' to create key-value pairs
                [$key, $value] = explode('=', $param);
                $parsedParams[$key] = is_numeric($value) ? (int)$value : $value;
            }

            // Use reflection to get class constructor and resolve dependencies
            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            // Initialize an array to hold constructor dependencies
            $dependencies = [];
            if ($constructor) {
                // Resolve constructor dependencies from parsed parameters
                foreach ($constructor->getParameters() as $param) {
                    $name = $param->getName();
                    // Add parameter value if it exists in parsed parameters, otherwise set to null
                    $dependencies[] = $parsedParams[$name] ?? null;
                }
            }

            // Instantiate the class with the resolved dependencies
            $classInstance = $reflection->newInstanceArgs($dependencies);

            // Reflect on the method to be called and prepare its parameters
            $methodReflection = $reflection->getMethod($method);
            $methodParams = [];

            // Prepare the method parameters by matching them with parsed parameters
            foreach ($methodReflection->getParameters() as $param) {
                $name = $param->getName();
                $methodParams[] = $parsedParams[$name] ?? null;
            }

            // Call the method with the prepared parameters
            $result = $methodReflection->invokeArgs($classInstance, $methodParams);

            // Log the success status of the job execution
            Log::channel('background_jobs')->info("Job executed: {$className}@{$method}", ['status' => 'success']);
        } catch (\Exception $e) {
            // Catch any exceptions and log the error with the failure status
            Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", ['error' => $e->getMessage()]);
        }
    }
}
