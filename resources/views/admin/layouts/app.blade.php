<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Custom Background Job Runner for Laravel')</title>
    <!-- Include Bootstrap CSS or any other CSS libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add additional CSS here if needed -->
    @stack('styles')
</head>

<body>
    <div id="app">
        <!-- Header Section -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{  route('admin.background-jobs.index') }}">Custom-Job-Runner</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link active" href="{{ route('admin.background-jobs.index') }}">Background Jobs</a>
                        </li>
                        <!-- Add more menu items here -->
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Main Content Section -->
        <div class="container mt-4">
            @yield('content') <!-- The page-specific content will be injected here -->
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="bg-dark text-white mt-4 py-3 text-center">
        <p>&copy; {{ date('Y') }} Laravel Application. All rights reserved.</p>
    </footer>

    <!-- Include Bootstrap JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add additional JS scripts here if needed -->
    @stack('scripts')
</body>

</html>