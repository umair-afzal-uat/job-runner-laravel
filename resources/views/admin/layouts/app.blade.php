<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Custom Background Job Runner for Laravel')</title>
    <!-- Include Bootstrap CSS or any other CSS libraries -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Add additional CSS here if needed -->
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEJQ3Lg7uLFF4A7l4LM49r6x7gb8r+3fmzR+aN1noJqZdt0eDtm90RYGVbW61" crossorigin="anonymous">

    <style>
        .navbar {
            background: #924a27;
        }

        .navbar .navbar-nav .nav-item .nav-link {
            background: #fff;
            color: #924a27;
            font-weight: bold;
            border: 0;
            border-radius: 3px;
            padding: 10px 24px;
        }

        table tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .container-wraper table th,
        .container-wraper table td {
            border: 0;
            padding: 10px;
            vertical-align: middle;
        }

        .container-wraper thead {
            background-color: #070707c9;
            color: white;
            text-align: center;
        }

        .container-wraper table {
            width: 100%;
            border-collapse: collapse;
            background-color: #f9f9f9;
        }

        .container-wraper h1 {
            font-size: 48px;
            font-weight: bold;
            background: linear-gradient(to right, #ff7e5f, #feb47b);
            -webkit-background-clip: text;
            color: transparent;
            text-shadow: 2px 2px 5px rgba(0, 0, 0, 0.2);
            display: block;
            padding: 10px;
            transition: all 0.3s ease-in-out;
            text-align: center;
            margin: 30px 0 30px
        }
    </style>
    @stack('styles')
</head>

<body>
    <div id="app">
        <!-- Header Section -->
        <nav class="navbar navbar-expand-lg navbar-dark  py-3 px-2">
            <div class="container">
                <a class="navbar-brand" href="{{  route('admin.background-jobs.index') }}">Custom-Job-Runner</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse justify-content-end" id="navbarNav">
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
        <div class="container-wraper" style="min-height: calc(100vh - 180px)">
            <div class="container mt-4">
                @yield('content') <!-- The page-specific content will be injected here -->
            </div>
        </div>
    </div>

    <!-- Footer Section -->
    <footer class="bg-dark text-white mt-4 py-3 text-center">
        <p class="mb-0">&copy; {{ date('Y') }} Laravel Application. All rights reserved.</p>
    </footer>

    <!-- Include Bootstrap JS and dependencies -->
    <!-- Bootstrap JS and Popper.js -->



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Add additional JS scripts here if needed -->
    @stack('scripts')
</body>

</html>