<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'SLSS Student Management')</title>
    <link rel="icon" type="image/png" href="{{ asset('images/successlogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --primary-light: #eef2ff;
            --success-green: #10b981;
            --danger-red: #ef4444;
            --warning-yellow: #f59e0b;
            --info-blue: #3b82f6;
            --bg-light: #f8fafc;
            --bg-sidebar: #1e293b;
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-color: #e2e8f0;
            --sidebar-width: 260px;
        }

        * {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background: var(--bg-light);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* Sidebar Navigation */
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            width: var(--sidebar-width);
            background: var(--bg-sidebar);
            padding: 0;
            z-index: 1000;
            box-shadow: 2px 0 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .sidebar-brand {
            padding: 1.5rem 1.25rem;
            background: rgba(0,0,0,0.2);
            border-bottom: 1px solid rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .sidebar-brand img {
            width: 42px;
            height: 42px;
            border-radius: 8px;
            background: white;
            padding: 4px;
        }

        .sidebar-brand-text {
            color: white;
            font-weight: 700;
            font-size: 1.1rem;
            line-height: 1.3;
        }

        .sidebar-menu {
            padding: 1rem 0;
        }

        .sidebar-menu-item {
            margin: 0.25rem 0.75rem;
        }

        .sidebar-menu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 500;
        }

        .sidebar-menu-link:hover {
            background: rgba(255,255,255,0.1);
            color: white;
        }

        .sidebar-menu-link.active {
            background: var(--primary-color);
            color: white;
        }

        .sidebar-menu-link i {
            width: 20px;
            text-align: center;
            font-size: 1.1rem;
        }

        .sidebar-user {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            padding: 1rem;
            background: rgba(0,0,0,0.2);
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        .sidebar-user-info {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem;
            background: rgba(255,255,255,0.05);
            border-radius: 8px;
        }

        .sidebar-user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: var(--primary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
        }

        .sidebar-user-details {
            flex: 1;
            min-width: 0;
        }

        .sidebar-user-name {
            color: white;
            font-weight: 600;
            font-size: 0.9rem;
            margin: 0;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .sidebar-user-role {
            color: rgba(255,255,255,0.6);
            font-size: 0.75rem;
            margin: 0;
        }

        .sidebar-logout {
            background: none;
            border: none;
            color: rgba(255,255,255,0.8);
            padding: 0.5rem;
            cursor: pointer;
            transition: color 0.2s;
        }

        .sidebar-logout:hover {
            color: var(--danger-red);
        }

        /* Mobile Toggle */
        .sidebar-toggle {
            display: none;
            position: fixed;
            top: 1rem;
            left: 1rem;
            z-index: 1001;
            background: var(--bg-sidebar);
            color: white;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
        }

        /* Top Header */
        .top-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: 70px;
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 0 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 999;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }

        .page-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .breadcrumb {
            background: none;
            padding: 0;
            margin: 0.25rem 0 0 0;
            font-size: 0.875rem;
        }

        .breadcrumb-item + .breadcrumb-item::before {
            content: "›";
            color: var(--text-muted);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            margin-top: 70px;
            padding: 2rem;
            min-height: calc(100vh - 70px);
        }

        /* Stats Cards */
        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            border: 1px solid var(--border-color);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 1rem;
        }

        .stat-icon.primary {
            background: var(--primary-light);
            color: var(--primary-color);
        }

        .stat-icon.success {
            background: #ecfdf5;
            color: var(--success-green);
        }

        .stat-icon.warning {
            background: #fef3c7;
            color: var(--warning-yellow);
        }

        .stat-icon.info {
            background: #dbeafe;
            color: var(--info-blue);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-dark);
            margin: 0;
        }

        .stat-label {
            color: var(--text-muted);
            font-size: 0.875rem;
            margin: 0;
        }

        /* Card Styles */
        .card {
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            overflow: hidden;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid var(--border-color);
            padding: 1.25rem 1.5rem;
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
        }

        .card-body {
            padding: 1.5rem;
        }

        /* Buttons */
        .btn {
            border-radius: 8px;
            padding: 0.625rem 1.25rem;
            font-weight: 600;
            transition: all 0.2s;
            border: none;
        }

        .btn-primary {
            background: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79,70,229,0.3);
        }

        .btn-success {
            background: var(--success-green);
            color: white;
        }

        .btn-success:hover {
            background: #059669;
            transform: translateY(-1px);
        }

        .btn-danger {
            background: var(--danger-red);
            color: white;
        }

        .btn-warning {
            background: var(--warning-yellow);
            color: white;
        }

        .btn-info {
            background: var(--info-blue);
            color: white;
        }

        /* Forms */
        .form-control, .form-select {
            border-radius: 8px;
            border: 1.5px solid var(--border-color);
            padding: 0.625rem 0.875rem;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        .form-label {
            font-weight: 600;
            color: var(--text-dark);
            margin-bottom: 0.5rem;
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 10px;
            padding: 1rem 1.25rem;
        }

        /* DataTables Custom Styling */
        .dataTables_wrapper .dataTables_filter input {
            border-radius: 8px;
            border: 1.5px solid var(--border-color);
            padding: 0.5rem 0.75rem;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1.5px solid var(--border-color);
            padding: 0.375rem 0.75rem;
        }

        table.dataTable thead th {
            font-weight: 700;
            color: var(--text-dark);
            background: var(--bg-light);
            border-bottom: 2px solid var(--border-color);
        }

        table.dataTable tbody tr:hover {
            background: var(--bg-light);
        }

        /* Responsive */
        @media (max-width: 992px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.show {
                transform: translateX(0);
            }

            .sidebar-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .top-header {
                left: 0;
                padding-left: 4rem;
            }

            .main-content {
                margin-left: 0;
            }
        }

        @media print {
            body {
                background: white;
            }
            .sidebar, .top-header, .no-print, .btn, .alert, .sidebar-toggle {
                display: none !important;
            }
            .main-content {
                margin: 0;
                padding: 0;
            }
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Mobile Toggle -->
    <button class="sidebar-toggle no-print" id="sidebarToggle">
        <i class="fas fa-bars"></i>
    </button>

    <!-- Sidebar -->
    <aside class="sidebar no-print" id="sidebar">
        <div class="sidebar-brand">
            <img src="{{ asset('images/successlogo.png') }}" alt="SLSS">
            <div class="sidebar-brand-text">
                Success<br>Student Management
            </div>
        </div>

        <nav class="sidebar-menu">
            <div class="sidebar-menu-item">
                <a href="{{ route('students.index') }}" class="sidebar-menu-link {{ request()->routeIs('students.*') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>
            <div class="sidebar-menu-item">
                <a href="{{ route('students.index') }}" class="sidebar-menu-link {{ request()->routeIs('students.index') ? 'active' : '' }}">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                </a>
            </div>
            @can('import-students')
            <div class="sidebar-menu-item">
                <a href="{{ route('import.index') }}" class="sidebar-menu-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
                    <i class="fas fa-file-import"></i>
                    <span>Import Data</span>
                </a>
            </div>
            @endcan
            @can('edit-students')
            <div class="sidebar-menu-item">
                <a href="{{ route('students.create') }}" class="sidebar-menu-link {{ request()->routeIs('students.create') ? 'active' : '' }}">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Student</span>
                </a>
            </div>
            @endcan
            @if(auth()->user()->role === 'admin')
            <div class="sidebar-menu-item">
                <a href="{{ route('users.index') }}" class="sidebar-menu-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                    <i class="fas fa-users-cog"></i>
                    <span>User Management</span>
                </a>
            </div>
            @endif
        </nav>

        <div class="sidebar-user">
            <div class="sidebar-user-info">
                <div class="sidebar-user-avatar">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </div>
                <div class="sidebar-user-details">
                    <p class="sidebar-user-name">{{ Auth::user()->name }}</p>
                    <p class="sidebar-user-role">{{ ucfirst(Auth::user()->role) }}</p>
                </div>
                <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                    @csrf
                    <button type="submit" class="sidebar-logout" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <!-- Top Header -->
    <header class="top-header no-print">
        <div>
            <h1 class="page-title">@yield('page-title', 'Dashboard')</h1>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    @yield('breadcrumbs')
                </ol>
            </nav>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main-content">
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible fade show no-print" role="alert">
                <strong>Please fix the following errors:</strong>
                <ul class="mb-0 mt-2">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @yield('content')
    </main>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        document.getElementById('sidebarToggle')?.addEventListener('click', function() {
            document.getElementById('sidebar').classList.toggle('show');
        });

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            const sidebar = document.getElementById('sidebar');
            const toggle = document.getElementById('sidebarToggle');
            if (window.innerWidth <= 992 && sidebar?.classList.contains('show')) {
                if (!sidebar.contains(event.target) && event.target !== toggle) {
                    sidebar.classList.remove('show');
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
