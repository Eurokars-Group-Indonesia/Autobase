<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'AutoBase')</title>

    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('favicon-16x16.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('favicon-32x32.png') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('site.webmanifest') }}">

    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --sidebar-width: 0px;
            --navbar-height: 60px;
            --bg-body: #f8f9fa;
            --bg-card: #ffffff;
            --text-primary: #212529;
            --text-secondary: #6c757d;
            --border-color: #dee2e6;
            --navbar-bg: #002856;
            --card-header-bg: #002856;

            /* Override Bootstrap blue color */
            --bs-blue: #002856;
            --bs-primary: #002856;
            --bs-primary-rgb: 0, 40, 86;
            --bs-link-color: #002856;
            --bs-link-hover-color: #001a3d;

            /* Pagination colors */
            --bs-pagination-active-bg: #002856;
            --bs-pagination-active-border-color: #002856;
        }

        [data-theme="dark"] {
            --bg-body: #1a1d20;
            --bg-card: #2b3035;
            --text-primary: #e9ecef;
            --text-secondary: #adb5bd;
            --border-color: #495057;
            --navbar-bg: #1a1d20;
            --card-header-bg: #2b3035;
        }

        body {
            font-family: 'Poppins', system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial, "Noto Sans", sans-serif;
            background-color: var(--bg-body);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
            font-size: 13px;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-custom {
            background: var(--navbar-bg);
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
            height: var(--navbar-height);
            transition: background-color 0.3s ease;
        }

        [data-theme="dark"] .navbar-custom {
            border-bottom: 1px solid var(--border-color);
        }
        .navbar-custom .navbar-brand {
            color: #FA891A;
            font-weight: 600;
            font-size: 1.5rem;
        }
        .navbar-custom .nav-link {
            color: rgba(255,255,255,0.9);
            font-weight: 500;
            padding: 0.5rem 1rem;
            transition: all 0.3s;
        }
        .navbar-custom .nav-link:hover {
            color: white;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .navbar-custom .nav-link:focus,
        .navbar-custom .nav-link:active,
        .navbar-custom .nav-link.show {
            color: white !important;
        }
        .navbar-custom .dropdown-toggle::after {
            color: white;
        }
        .navbar-custom .dropdown-toggle.show {
            color: white !important;
            background-color: rgba(255,255,255,0.1);
            border-radius: 5px;
        }
        .navbar-custom .dropdown-menu {
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,.1);
        }

        [data-theme="dark"] .navbar-custom .dropdown-menu {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
        }

        [data-theme="dark"] .navbar-custom .dropdown-item {
            color: var(--text-primary);
        }

        [data-theme="dark"] .navbar-custom .dropdown-item:hover,
        [data-theme="dark"] .navbar-custom .dropdown-item:focus {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }

        /* Mobile Menu Styling */
        .navbar-collapse {
            background-color: var(--navbar-bg);
        }

        @media (max-width: 991.98px) {
            .navbar-collapse {
                background-color: white;
                margin-top: 1rem;
                padding: 1rem;
                border-radius: 10px;
                box-shadow: 0 4px 6px rgba(0,0,0,.1);
            }

            [data-theme="dark"] .navbar-collapse {
                background-color: var(--bg-card);
            }

            .navbar-nav .nav-link {
                color: #212529 !important;
                padding: 0.75rem 1rem;
                border-radius: 5px;
                margin-bottom: 0.25rem;
            }

            [data-theme="dark"] .navbar-nav .nav-link {
                color: var(--text-primary) !important;
            }

            .navbar-nav .nav-link:hover {
                background-color: #f8f9fa;
                color: #002856 !important;
            }

            [data-theme="dark"] .navbar-nav .nav-link:hover {
                background-color: var(--bg-body);
                color: #6ea8fe !important;
            }

            .navbar-nav .dropdown-menu {
                background-color: #f8f9fa;
                border: none;
                box-shadow: none;
                margin-left: 1rem;
            }

            [data-theme="dark"] .navbar-nav .dropdown-menu {
                background-color: var(--bg-body);
            }

            .navbar-nav .dropdown-item {
                color: #212529;
                padding: 0.5rem 1rem;
                border-radius: 5px;
            }

            [data-theme="dark"] .navbar-nav .dropdown-item {
                color: var(--text-primary);
            }

            .navbar-nav .dropdown-item:hover {
                background-color: white;
                color: #002856;
            }

            [data-theme="dark"] .navbar-nav .dropdown-item:hover {
                background-color: var(--bg-card);
                color: #6ea8fe;
            }

            .theme-toggle {
                background: #002856 !important;
                color: white !important;
                margin-bottom: 0.5rem;
                width: 100%;
                height: 45px;
                display: flex;
                justify-content: center;
                align-items: center;
                font-size: 1.2rem;
                border-radius: 5px;
            }

            .theme-toggle:hover,
            .theme-toggle:focus,
            .theme-toggle:active {
                background: #001a3d !important;
                color: white !important;
            }

            [data-theme="dark"] .theme-toggle {
                background: #495057 !important;
                color: white !important;
            }

            [data-theme="dark"] .theme-toggle:hover,
            [data-theme="dark"] .theme-toggle:focus,
            [data-theme="dark"] .theme-toggle:active {
                background: #5a6268 !important;
                color: white !important;
            }

            .user-avatar {
                display: inline-flex;
                margin-right: 0.5rem;
            }

            .navbar-nav .dropdown-toggle {
                color: #212529 !important;
            }

            [data-theme="dark"] .navbar-nav .dropdown-toggle {
                color: var(--text-primary) !important;
            }
        }

        .navbar-toggler {
            border-color: rgba(255,255,255,0.5);
        }

        .navbar-toggler-icon {
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 30 30'%3e%3cpath stroke='rgba%28255, 255, 255, 1%29' stroke-linecap='round' stroke-miterlimit='10' stroke-width='2' d='M4 7h22M4 15h22M4 23h22'/%3e%3c/svg%3e");
        }
        .main-content {
            margin-top: 40px;
            margin-bottom: 40px;
            padding-top: 20px;
            flex: 1;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            margin-bottom: 20px;
            background-color: var(--bg-card);
            transition: background-color 0.3s ease;
        }
        .card-header {
            background: var(--card-header-bg);
            color: white;
            border-radius: 10px 10px 0 0 !important;
            padding: 1rem 1.5rem;
            font-weight: 600;
        }
        .card-header h5 {
            color: white !important;
            margin: 0;
        }

        [data-theme="dark"] .card-header {
            border-bottom: 1px solid var(--border-color);
        }
        .breadcrumb {
            background-color: var(--bg-card);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,.08);
            margin-bottom: 20px;
            transition: background-color 0.3s ease;
        }
        .breadcrumb-item.active {
            color: black;
        }
        [data-theme="dark"] .breadcrumb-item.active {
            color: white;
        }
        .breadcrumb-item a {
            color: #002856;
            text-decoration: none;
        }
        .breadcrumb-item a:hover {
            color: #002856;
        }
        [data-theme="dark"] .breadcrumb-item a:hover {
            color: white;
        }
        .btn-primary {
            background: #002856;
            border: none;
        }
        .btn-primary:hover {
            background: #001a3d;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,.2);
        }
        .btn-info {
            background: #002856;
            color: #ffffff;
            border: #002856;
        }
        .btn-info:hover {
            background: #FA891A;
            color: #ffffff;
            border: #002856;
        }
        .table {
            background-color: var(--bg-card);
            color: var(--text-primary);
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .table thead th {
            background-color: #002856;
            border-bottom: 2px solid #002856;
            color: white;
            font-weight: 600;
            padding: 0.5rem;
        }
        .table tbody tr {
            border-color: var(--border-color);
        }
        .table tbody tr:hover {
            background-color: var(--bg-body);
        }
        .table tbody td {
            padding: 0.4rem 0.5rem;
        }
        .table-sm tbody td,
        .table-sm thead th {
            padding: 0.3rem 0.5rem;
        }
        .badge {
            padding: 0.5em 0.8em;
        }
        .modal-body {
            font-size: 13px;
        }
        .modal-body .table {
            font-size: 13px;
        }
        .modal-body .table thead th,
        .modal-body .table tbody td {
            font-size: 13px;
        }
        .user-avatar {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #FA891A;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .theme-toggle {
            background: rgba(255,255,255,0.1);
            border: none;
            color: white;
            padding: 0.5rem 0.75rem;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            height: 38px;
            width: 38px;
        }
        .theme-toggle:hover {
            background: #002856;
        }
        .theme-toggle i {
            font-size: 1.1rem;
        }

        .form-control, .form-select {
            background-color: var(--bg-card);
            color: var(--text-primary);
            border-color: var(--border-color);
            transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        .form-control:focus, .form-select:focus {
            background-color: var(--bg-card);
            color: var(--text-primary);
            border-color: #002856;
        }

        .btn {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }

        [data-theme="dark"] .form-control::placeholder {
            color: var(--text-secondary);
        }

        .form-label, label {
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .form-check-label {
            color: var(--text-primary);
            transition: color 0.3s ease;
        }

        .text-muted, small.text-muted {
            color: var(--text-secondary) !important;
        }

        .card-body {
            color: var(--text-primary);
        }

        .invalid-feedback {
            color: #dc3545;
        }

        .text-danger {
            color: #dc3545 !important;
        }

        [data-theme="dark"] .dropdown-menu {
            background-color: var(--bg-card);
            border-color: var(--border-color);
        }

        [data-theme="dark"] .dropdown-item {
            color: var(--text-primary);
        }

        [data-theme="dark"] .dropdown-item:hover {
            background-color: var(--bg-body);
            color: var(--text-primary);
        }

        [data-theme="dark"] .dropdown-divider {
            border-color: var(--border-color);
        }

        [data-theme="dark"] .alert {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        [data-theme="dark"] .btn-light {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        [data-theme="dark"] .btn-light:hover {
            background-color: var(--bg-body);
            color: var(--text-primary);
        }

        [data-theme="dark"] .btn-secondary {
            background-color: #495057;
            border-color: #495057;
        }

        [data-theme="dark"] .btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }

        [data-theme="dark"] code {
            background-color: var(--bg-body);
            color: #f8f9fa;
        }

        .fw-bold {
            color: var(--text-primary);
        }

        [data-theme="dark"] .pagination .page-link {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-primary);
        }

        [data-theme="dark"] .pagination .page-link:hover {
            background-color: var(--bg-body);
            color: var(--text-primary);
        }

        [data-theme="dark"] .pagination .page-item.active .page-link {
            background-color: #FA891A;
            border-color: #FA891A;
            color: black;
        }

        [data-theme="dark"] .pagination .page-item.disabled .page-link {
            background-color: var(--bg-card);
            border-color: var(--border-color);
            color: var(--text-secondary);
        }

        .pagination .page-link svg {
            width: 0.875rem;
            height: 0.875rem;
            vertical-align: middle;
        }

        .pagination {
            margin-bottom: 0;
        }

        .pagination .page-link {
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
            min-width: 40px;
            text-align: center;
        }

        .pagination .page-item:first-child .page-link,
        .pagination .page-item:last-child .page-link {
            min-width: 60px;
            font-weight: 500;
        }

        .pagination .page-item.active .page-link {
            font-weight: 600;
            background-color: #002856;
            border-color: #002856;
            color: white;
        }

        h1, h2, h3, h4, h5, h6 {
            color: var(--text-primary);
        }

        p {
            color: var(--text-primary);
        }

        a {
            color: #002856;
        }

        [data-theme="dark"] a:not(.btn):not(.nav-link):not(.dropdown-item):not(.page-link) {
            color: #FA891A;
        }

        .input-group .btn {
            border-radius: 0 5px 5px 0;
        }

        .input-group .form-control {
            border-radius: 5px 0 0 5px;
        }

        .input-group .btn:not(:last-child) {
            border-radius: 0;
        }

        .breadcrumb-item+.breadcrumb-item::before {
            color: var(--bs-breadcrumb-divider-color);
        }

        [data-theme="dark"] .breadcrumb-item+.breadcrumb-item::before {
            color: white;
        }

        /* Footer */
        footer {
            background-color: var(--bg-card);
            color: var(--text-secondary);
            transition: background-color 0.3s ease, color 0.3s ease;
            margin-top: auto;
        }

        footer .border-top {
            border-color: var(--border-color) !important;
        }

        /* Prevent horizontal scroll on mobile */
        @media (max-width: 767.98px) {
            html, body {
                overflow-x: hidden;
                max-width: 100vw;
            }
            .container-fluid {
                padding-left: 10px;
                padding-right: 10px;
            }
            .main-content {
                padding-left: 0;
                padding-right: 0;
            }
            .card {
                border-radius: 5px;
            }
            .breadcrumb {
                border-radius: 5px;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-custom fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                <i class="bi bi-car-front-fill"></i> AutoBase
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('dashboard') }}">
                            <i class="bi bi-speedometer2"></i> Dashboard
                        </a>
                    </li>
                    @foreach(auth()->user()->getMenus() as $menu)
                        @if($menu->children->count() > 0)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                    <i class="bi {{ $menu->menu_icon ?? 'bi-folder' }}"></i> {{ $menu->menu_name }}
                                </a>
                                <ul class="dropdown-menu">
                                    @foreach($menu->children as $child)
                                        <li><a class="dropdown-item" href="{{ $child->menu_url }}">
                                            <i class="bi {{ $child->menu_icon ?? 'bi-circle' }}"></i> {{ $child->menu_name }}
                                        </a></li>
                                    @endforeach
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link" href="{{ $menu->menu_url }}">
                                    <i class="bi {{ $menu->menu_icon ?? 'bi-circle' }}"></i> {{ $menu->menu_name }}
                                </a>
                            </li>
                        @endif
                    @endforeach
                </ul>
                <ul class="navbar-nav align-items-center">
                    <li class="nav-item me-2">
                        <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                            <i class="bi bi-moon-stars" id="themeIcon"></i>
                        </button>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" data-bs-toggle="dropdown">
                            <div class="user-avatar me-2">
                                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                            </div>
                            {{ auth()->user()->name }}
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <form action="{{ route('logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item">
                                        <i class="bi bi-box-arrow-right"></i> Logout
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container-fluid main-content" style="margin-top: var(--navbar-height);">
        @if(isset($breadcrumbs))
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                    @foreach($breadcrumbs as $breadcrumb)
                        @if($loop->last)
                            <li class="breadcrumb-item active">{{ $breadcrumb['title'] }}</li>
                        @else
                            <li class="breadcrumb-item"><a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['title'] }}</a></li>
                        @endif
                    @endforeach
                </ol>
            </nav>
        @endif

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </div>

    <!-- Footer -->
    <footer class="mt-5 py-3 border-top">
        <div class="container-fluid">
            <div class="text-center text-muted">
                <small>&copy; 2026 IT Team Eurokars Group Indonesia. All rights reserved.</small>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script>
        // Dark Mode Toggle
        const themeToggle = document.getElementById('themeToggle');
        const themeIcon = document.getElementById('themeIcon');
        const htmlElement = document.documentElement;

        // Check for saved theme preference or default to 'light'
        const currentTheme = localStorage.getItem('theme') || 'light';
        htmlElement.setAttribute('data-theme', currentTheme);
        updateThemeIcon(currentTheme);

        themeToggle.addEventListener('click', function() {
            const currentTheme = htmlElement.getAttribute('data-theme');
            const newTheme = currentTheme === 'light' ? 'dark' : 'light';

            htmlElement.setAttribute('data-theme', newTheme);
            localStorage.setItem('theme', newTheme);
            updateThemeIcon(newTheme);
        });

        function updateThemeIcon(theme) {
            if (theme === 'dark') {
                themeIcon.className = 'bi bi-sun';
            } else {
                themeIcon.className = 'bi bi-moon-stars';
            }
        }
    </script>
    @stack('scripts')
</body>
</html>
