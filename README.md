## **Documentation: Running Background Jobs in Laravel**

### **Overview**

The `runBackgroundJob` function allows you to run jobs asynchronously in the background from within your Laravel application. It provides functionality for retry logic, logging, job validation, and prioritization. It supports both Unix-based and Windows-based systems for executing jobs in the background.

---

### **1. Prerequisites**

Before using the `runBackgroundJob` function, ensure the following:

-   You have set up background job classes and methods that you want to run in the background.
-   You have configured logging for background jobs to track execution status.
-   The `runBackgroundJob` function has been added to your project (in the `app/Helpers` directory or similar location).
-   The `background_jobs` log channel is configured in `config/logging.php`.

---

### **2. Usage**

#### **Running a Job Using the `runBackgroundJob` Function**

The `runBackgroundJob` function is the main helper function to execute background jobs. It takes several parameters, including the class name, method, job parameters, retry attempts, and delay between retries.

Here is the syntax for running a background job:

```php
runBackgroundJob($className, $method, $params = [], $retryAttempts = 3, $retryDelay = 5);
```

-   **`$className`**: The fully qualified name of the job class (e.g., `App\Jobs\SendWelcomeEmail`).
-   **`$method`**: The method you wish to execute within the job class (e.g., `handle`).
-   **`$params`**: An array of parameters that will be passed to the job method (e.g., `['user_id' => 123]`).
-   **`$retryAttempts`**: (Optional) The number of times to retry the job if it fails (default is 3).
-   **`$retryDelay`**: (Optional) The delay in seconds between retries (default is 5 seconds).

#### **Example:**

To run a background job that sends a welcome email to a user with `user_id` of 123:

```php
runBackgroundJob('App\Jobs\SendWelcomeEmail', 'handle', ['user_id' => 123]);
```

This will run the `SendWelcomeEmail` job asynchronously in the background by calling the `handle` method and passing `user_id` as a parameter.

---

### **3. Retry Logic**

The retry mechanism allows jobs to be retried a configurable number of times in case of failure. If a job fails, it will be retried until the configured retry attempts are exhausted.

-   **`$retryAttempts`**: Specifies the number of retry attempts before marking the job as failed (default is 3).
-   **`$retryDelay`**: Specifies the delay (in seconds) between retry attempts (default is 5 seconds).

#### **Example:**

To configure retry attempts and delay, use the following:

```php
runBackgroundJob('App\Jobs\SendWelcomeEmail', 'handle', ['user_id' => 123], 5, 10);
```

This will retry the job 5 times with a 10-second delay between attempts.

---

### **4. Job Prioritization**

Currently, job prioritization is not explicitly built into this implementation. However, you can prioritize jobs by adjusting how they are dispatched and executed. For example:

-   **Low Priority**: Run low-priority jobs after a delay.
-   **High Priority**: Run high-priority jobs immediately, possibly bypassing the retry mechanism.

You can prioritize jobs by setting custom delays for retry attempts based on job types or method names. Additionally, you can use queue drivers like Redis or database queues for better prioritization.

---

### **5. Logging**

The `runBackgroundJob` function logs the status of the job execution, including:

-   **Running**: When the job starts.
-   **Completed**: When the job successfully completes.
-   **Failed**: If the job fails after retries.
-   **Retrying**: If the job is being retried.
-   **Unauthorized**: If an attempt is made to run an unauthorized job.

The logs are saved in the `background_jobs` log channel (as configured in `config/logging.php`).

#### **Example Log Entries:**

-   Job started:

```txt
[2024-11-12 15:45:00] local.INFO: Job started: App\Jobs\SendWelcomeEmail@handle {"params":{"user_id":123},"status":"running","timestamp":"2024-11-12 15:45:00"}
```

-   Job completed:

```txt
[2024-11-12 15:45:05] local.INFO: Job completed: App\Jobs\SendWelcomeEmail@handle {"params":{"user_id":123},"status":"completed","timestamp":"2024-11-12 15:45:05"}
```

-   Job failed (after retry attempts):

```txt
[2024-11-12 15:45:10] local.ERROR: Job failed: App\Jobs\SendWelcomeEmail@handle {"error":"Some error message","params":{"user_id":123},"status":"failed","timestamp":"2024-11-12 15:45:10"}
```

-   Unauthorized job attempt:

```txt
[2024-11-12 15:45:00] local.ERROR: Unauthorized job execution attempt: App\Jobs\SendWelcomeEmail@unauthorizedMethod {"params":{"user_id":123},"status":"unauthorized","timestamp":"2024-11-12 15:45:00"}
```

---

### **6. Security: Validating Allowed Jobs**

To enhance security, only jobs listed in the `allowedJobs` array can be run. Unauthorized jobs will be blocked and logged.

#### **Example of Approved Job List:**

```php
$allowedJobs = [
    'App\Jobs\SendWelcomeEmail' => ['handle'],
    'App\Jobs\SendPasswordResetEmail' => ['handle', 'sendResetLink'],
];
```

If a job and method combination is not in this array, the job will not be executed, and an error will be logged.

---

### **7. Running Background Jobs from the Command Line**

The `runBackgroundJob` function is designed to be invoked programmatically. However, you can also trigger the job execution from the command line using the following artisan command:

```bash
php artisan background:run {className} {method} {params?*}
```

For example, to run the `handle` method of the `SendWelcomeEmail` job:

```bash
php artisan background:run App\Jobs\SendWelcomeEmail handle user_id=123
```

This will trigger the job to execute in the background, just like the `runBackgroundJob` function.

---

### **8. Conclusion**

The `runBackgroundJob` function is a flexible, secure, and efficient way to run jobs in the background within your Laravel application. It supports retry logic, job validation, logging, and ensures only authorized jobs are executed. Additionally, it is cross-platform, supporting both Unix-based and Windows-based systems for background execution.

For more advanced features, such as prioritization and more robust job scheduling, consider integrating a Laravel queue system with queue workers and specific job drivers like Redis.

---

### **Example: Full Command Use**

Here’s a full example of using the `runBackgroundJob` function in code:

```php
// Run a background job with retry logic and a delay
runBackgroundJob('App\Jobs\SendWelcomeEmail', 'handle', ['user_id' => 123], 3, 10);
```

This example will:

1. Run the `handle` method of `SendWelcomeEmail`.
2. Retry the job up to 3 times if it fails, with a 10-second delay between attempts.
