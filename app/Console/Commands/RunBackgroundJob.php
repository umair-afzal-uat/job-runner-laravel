<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class RunBackgroundJob extends Command
{
    protected $signature = 'background:run {class} {method} {params?*}';
    protected $description = 'Run a background job in the background';

    public function handle()
    {
        $className = $this->argument('class');
        $method = $this->argument('method');
        $params = $this->argument('params');

        try {
            // Parse the params into an associative array (e.g., "user_id=123" becomes ['user_id' => 123])
            $parsedParams = [];
            foreach ($params as $param) {
                [$key, $value] = explode('=', $param);
                $parsedParams[$key] = is_numeric($value) ? (int)$value : $value;
            }

            // Use reflection to resolve dependencies manually
            $reflection = new \ReflectionClass($className);
            $constructor = $reflection->getConstructor();

            $dependencies = [];
            if ($constructor) {
                foreach ($constructor->getParameters() as $param) {
                    $name = $param->getName();
                    $dependencies[] = $parsedParams[$name] ?? null;
                }
            }

            // Instantiate the class with constructor dependencies
            $classInstance = $reflection->newInstanceArgs($dependencies);

            // Prepare method parameters and execute the method
            $methodReflection = $reflection->getMethod($method);
            $methodParams = [];

            foreach ($methodReflection->getParameters() as $param) {
                $name = $param->getName();
                $methodParams[] = $parsedParams[$name] ?? null;
            }

            // Call the method with the parameters
            $result = $methodReflection->invokeArgs($classInstance, $methodParams);

            // Log the job execution status
            Log::channel('background_jobs')->info("Job executed: {$className}@{$method}", ['status' => 'success']);
        } catch (\Exception $e) {
            // Log the error in case of failure
            Log::channel('background_jobs')->error("Job failed: {$className}@{$method}", ['error' => $e->getMessage()]);
        }
    }
}
