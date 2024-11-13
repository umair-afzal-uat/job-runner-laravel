## **Documentation: Background Job Runner System in Laravel**

### **Overview**

The `runBackgroundJob` function is a PHP-based solution that allows you to run classes or methods asynchronously in the background, separate from the main Laravel application process. This function supports Unix-based and Windows-based systems, includes retry mechanisms, comprehensive logging, security validation, and an optional web-based dashboard for job monitoring.

---

### **1. Prerequisites**

Ensure the following setup:

-   The `runBackgroundJob` helper function is available in your Laravel project (typically in `app/Helpers`).
-   The `background_jobs` and `background_jobs_errors` log channels are configured in `config/logging.php`.
-   You have a list of pre-approved job classes defined for security purposes.

---

### **2. Global Helper Function: `runBackgroundJob`**

This function executes a specified class and method in the background, with support for configurable retries and delays.

**Syntax:**

```php
runBackgroundJob($className, $method, $params = [], $retryAttempts = 3, $retryDelay = 5, $priority = 'normal', $delay = 0);
```

-   **`$className`**: Fully qualified class name (e.g., `App\Jobs\SendWelcomeEmail`).
-   **`$method`**: Method to execute within the class (e.g., `handle`).
-   **`$params`**: Array of parameters for the method (e.g., `['user_id' => 123]`).
-   **`$retryAttempts`**: Number of retry attempts in case of failure (default: 3).
-   **`$retryDelay`**: Delay in seconds between retries (default: 5 seconds).
-   **`$priority`**: Job priority (`'high'`, `'normal'`, `'low'`). Higher priority jobs run before lower ones.
-   **`$delay`**: Delay in seconds before executing the job (default: 0).

---

### **3. Security Validation**

To prevent unauthorized execution, only pre-approved classes and methods are allowed.

**Allowed Jobs Configuration:**

```php
$allowedJobs = [
    'App\Jobs\SendWelcomeEmail' => ['handle'],
    'App\Jobs\ProcessUserReport' => ['generate', 'send'],
];
```

Attempting to execute a job or method not in this list will log an error and terminate the process.

---

### **4. Cross-Platform Support**

The function supports:

-   **Unix-based Systems**: Uses `nohup` for background execution.
-   **Windows-based Systems**: Uses `start /b` for background execution.

The function automatically detects the operating system and uses the appropriate command.

---

### **5. Error Handling and Logging**

Comprehensive error handling is implemented to capture and log exceptions:

-   **General Execution Log**: `background_jobs.log`
-   **Error Log**: `background_jobs_errors.log`

**Example Logs:**

-   Job Started:

```txt
[2024-11-12 10:00:00] local.INFO: Job started: App\Jobs\SendWelcomeEmail@handle {"params":{"user_id":123},"status":"running"}
```

-   Job Completed:

```txt
[2024-11-12 10:00:05] local.INFO: Job completed: App\Jobs\SendWelcomeEmail@handle {"params":{"user_id":123},"status":"completed"}
```

-   Job Failed:

```txt
[2024-11-12 10:00:10] local.ERROR: Job failed: App\Jobs\SendWelcomeEmail@handle {"error":"Some error message","params":{"user_id":123},"status":"failed"}
```

-   Unauthorized Execution Attempt:

```txt
[2024-11-12 10:00:15] local.ERROR: Unauthorized job execution attempt: App\Jobs\UnauthorizedJob@method {"status":"unauthorized"}
```

---

### **6. Retry Mechanism**

The function includes a built-in retry mechanism:

-   Configurable **retry attempts** and **retry delay**.
-   Logs each retry attempt with the error message.

**Example:**

```php
runBackgroundJob('App\Jobs\SendWelcomeEmail', 'handle', ['user_id' => 123], 5, 10);
```

This will retry the job up to 5 times with a 10-second delay between attempts.

---

### **7. Job Delays and Prioritization**

The function supports:

-   **Job Delays**: Allows specifying a delay before the job execution starts.
-   **Job Priority**: Supports three levels of priority (`high`, `normal`, `low`). Higher priority jobs are executed first.

**Example:**

```php
runBackgroundJob('App\Jobs\ProcessUserReport', 'generate', ['report_id' => 456], 3, 5, 'high', 15);
```

This runs the job with high priority and a 15-second delay before execution.

---

### **8. Web-Based Dashboard (Optional Feature)**

A web-based dashboard is available for admin users to monitor background jobs.

**Features:**

-   Display active jobs, their status (running, completed, failed), and retry count.
-   View detailed job logs, including execution time and parameters.
-   Cancel running jobs if needed.

**Access the Dashboard:**

Navigate to `http://127.0.0.1:8000/admin/background-jobs`.

---

### **9. Advanced Configuration**

The function supports additional customization:

-   **Timeout Configuration**: Set a custom timeout for job execution.
-   **Environment-Specific Logging**: Adjusts logging behavior based on the current environment (e.g., `local`, `production`).
-   **Enhanced Parameter Handling**: Accepts both indexed and associative arrays for method parameters.

**Example:**

```php
runBackgroundJob('App\Jobs\SendNotification', 'send', ['user_id' => 789, 'message' => 'Welcome!'], 2, 5, 'low', 0);
```

---

### **10. Artisan Command for Background Job Execution**

A Laravel Artisan command is available to manually trigger background jobs.

**Command Syntax:**

```bash
php artisan background:run {className} {method} {params?*} {--priority=} {--delay=}
```

**Example:**

```bash
php artisan background:run App\Jobs\SendWelcomeEmail handle user_id=123 --priority=high --delay=10
```

This command runs the job with high priority and a 10-second delay.

---

### **Conclusion**

The `runBackgroundJob` function provides a comprehensive solution for handling background job execution in Laravel. It supports cross-platform execution, robust error handling, secure job validation, and advanced features like prioritization and delays. The optional web-based dashboard offers enhanced visibility and control over background jobs.
