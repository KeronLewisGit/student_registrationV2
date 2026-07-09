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

        .sidebar-menu-link .menu-arrow {
            margin-left: auto;
            transition: transform 0.2s;
        }

        .sidebar-menu-link.collapsed .menu-arrow {
            transform: rotate(-90deg);
        }

        .sidebar-submenu {
            display: none;
            padding-left: 1rem;
        }

        .sidebar-submenu.show {
            display: block;
        }

        .sidebar-submenu-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.625rem 1rem;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.2s;
            font-weight: 400;
            font-size: 0.9rem;
            margin: 0.125rem 0;
        }

        .sidebar-submenu-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }

        .sidebar-submenu-link.active {
            background: rgba(79,70,229,0.3);
            color: white;
        }

        .sidebar-submenu-link i {
            width: 16px;
            text-align: center;
            font-size: 0.9rem;
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

        /* Mobile Overlay */
        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 999;
            opacity: 0;
            transition: opacity 0.3s;
        }

        .mobile-overlay.show {
            display: block;
            opacity: 1;
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
            min-height: 44px;
        }

        .dataTables_wrapper .dataTables_length select {
            border-radius: 8px;
            border: 1.5px solid var(--border-color);
            padding: 0.375rem 0.75rem;
            min-height: 44px;
        }

        /* DataTables Pagination - Touch-Friendly */
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            min-width: 44px;
            min-height: 44px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.5rem 0.75rem;
            margin: 0 2px;
            border-radius: 6px;
            font-weight: 600;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--primary-color) !important;
            color: white !important;
            border-color: var(--primary-color) !important;
        }

        .dataTables_wrapper .dataTables_paginate .paginate_button:hover {
            background: var(--bg-light) !important;
            color: var(--text-dark) !important;
            border-color: var(--border-color) !important;
        }

        .dataTables_wrapper .dataTables_info {
            padding: 0.75rem 0;
            font-size: 0.9rem;
            color: var(--text-muted);
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

        /* Footer Styles */
        .app-footer {
            background: white;
            border-top: 1px solid var(--border-color);
            padding: 1.5rem 2rem;
            margin-left: var(--sidebar-width);
            margin-top: 3rem;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .footer-left,
        .footer-right {
            display: flex;
            flex-direction: column;
            gap: 0.25rem;
        }

        .footer-right {
            text-align: right;
        }

        .footer-copyright,
        .footer-version,
        .footer-developer,
        .footer-portfolio {
            margin: 0;
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .footer-developer {
            color: var(--text-dark);
        }

        .footer-link {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s;
        }

        .footer-link:hover {
            color: var(--primary-dark);
            text-decoration: underline;
        }

        /* Version History Modal Styles */
        .version-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
        }

        .version-item:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .version-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.75rem;
        }

        .version-badge {
            display: inline-block;
            padding: 0.375rem 0.75rem;
            background: var(--bg-light);
            color: var(--text-dark);
            border-radius: 6px;
            font-weight: 700;
            font-size: 0.875rem;
        }

        .version-badge.current {
            background: var(--primary-color);
            color: white;
        }

        .version-date {
            font-size: 0.875rem;
            color: var(--text-muted);
        }

        .version-features {
            margin: 0;
            padding-left: 1.5rem;
            font-size: 0.875rem;
            color: var(--text-dark);
        }

        .version-features li {
            margin-bottom: 0.375rem;
        }

        .version-features li:last-child {
            margin-bottom: 0;
        }

        /* Responsive */
        /* Tablet Responsive */
        @media (max-width: 992px) {
            .app-footer {
                margin-left: 0;
            }
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
                padding-right: 1rem;
                height: 60px;
            }

            .page-title {
                font-size: 1.25rem;
            }

            .main-content {
                margin-left: 0;
                margin-top: 60px;
                padding: 1.5rem;
                min-height: calc(100vh - 60px);
            }

            .stat-card {
                padding: 1.25rem;
            }

            .stat-value {
                font-size: 1.75rem;
            }
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .app-footer {
                padding: 1rem;
                margin-top: 2rem;
            }

            .footer-content {
                flex-direction: column;
                text-align: center;
                gap: 1rem;
            }

            .footer-right {
                text-align: center;
            }

            .top-header {
                padding-left: 3.5rem;
                padding-right: 0.75rem;
                height: 56px;
            }

            .page-title {
                font-size: 1.125rem;
            }

            .breadcrumb {
                display: none;
            }

            .main-content {
                margin-top: 56px;
                padding: 1rem;
                min-height: calc(100vh - 56px);
            }

            .stat-card {
                padding: 1rem;
            }

            .stat-value {
                font-size: 1.5rem;
            }

            .stat-label {
                font-size: 0.8rem;
            }

            .card-header {
                padding: 1rem;
                font-size: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            /* Make buttons more touch-friendly */
            .btn {
                padding: 0.75rem 1rem;
                font-size: 0.9rem;
                min-height: 44px;
            }

            .btn-sm {
                padding: 0.5rem 0.75rem;
                font-size: 0.85rem;
                min-height: 40px;
            }

            /* Improve table actions on mobile */
            .table-actions {
                flex-wrap: wrap;
            }

            /* Form improvements for mobile */
            .form-select,
            .form-control {
                font-size: 16px; /* Prevents zoom on iOS */
                min-height: 44px;
            }

            label.form-label {
                font-size: 0.9rem;
                margin-bottom: 0.375rem;
            }

            /* DataTables Mobile Optimizations */
            .dataTables_wrapper .dataTables_length,
            .dataTables_wrapper .dataTables_filter {
                text-align: center;
                margin-bottom: 1rem;
            }

            .dataTables_wrapper .dataTables_length label,
            .dataTables_wrapper .dataTables_filter label {
                display: flex;
                flex-direction: column;
                gap: 0.5rem;
                align-items: stretch;
            }

            .dataTables_wrapper .dataTables_filter input {
                width: 100%;
                font-size: 16px;
            }

            .dataTables_wrapper .dataTables_length select {
                width: 100%;
                font-size: 16px;
            }

            .dataTables_wrapper .dataTables_info {
                text-align: center;
                margin-top: 1rem;
                font-size: 0.85rem;
            }

            .dataTables_wrapper .dataTables_paginate {
                text-align: center;
                margin-top: 1rem;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                min-width: 44px;
                min-height: 44px;
                margin: 2px;
            }
        }

        /* Small Mobile Responsive */
        @media (max-width: 576px) {
            .sidebar-toggle {
                width: 44px;
                height: 44px;
                top: 0.5rem;
                left: 0.5rem;
            }

            .top-header {
                padding-left: 3.5rem;
                padding-right: 0.5rem;
            }

            .page-title {
                font-size: 1rem;
            }

            .main-content {
                padding: 0.75rem;
            }

            .stat-icon {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }

            .stat-value {
                font-size: 1.25rem;
            }

            /* Stack action buttons vertically on small screens */
            .action-buttons {
                flex-direction: column;
            }

            .btn-action {
                width: 100%;
                justify-content: center;
            }

            /* Form section headings */
            h4, h5 {
                font-size: 1rem;
            }

            /* Improve modal dialogs on small screens */
            .modal-dialog {
                margin: 0.5rem;
            }

            /* Better alert spacing */
            .alert {
                padding: 0.75rem;
                font-size: 0.9rem;
            }

            /* Optimize pagination for small screens */
            .pagination {
                font-size: 0.85rem;
            }

            .page-link {
                min-width: 44px;
                min-height: 44px;
                padding: 0.5rem 0.75rem;
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            /* DataTables pagination buttons for small screens */
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                font-size: 0.9rem;
                padding: 0.5rem 0.625rem;
            }

            /* Reduce spacing between pagination buttons on very small screens */
            .dataTables_wrapper .dataTables_paginate .paginate_button {
                margin: 1px;
            }
        }

        /* Extra Small Mobile - Additional optimizations */
        @media (max-width: 480px) {
            .dataTables_wrapper .dataTables_info {
                font-size: 0.8rem;
                padding: 0.5rem 0;
            }

            .dataTables_wrapper .dataTables_paginate .paginate_button {
                min-width: 40px;
                min-height: 40px;
                padding: 0.375rem 0.5rem;
                font-size: 0.85rem;
            }

            /* Hide ellipsis and some page numbers on very small screens for better fit */
            .dataTables_wrapper .dataTables_paginate .paginate_button.ellipsis {
                min-width: 30px;
                padding: 0.375rem 0.25rem;
            }

            /* Ensure Previous/Next buttons are still touch-friendly */
            .dataTables_wrapper .dataTables_paginate .paginate_button.previous,
            .dataTables_wrapper .dataTables_paginate .paginate_button.next {
                min-width: 44px;
                min-height: 44px;
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
    <!-- Mobile Overlay -->
    <div class="mobile-overlay no-print" id="mobileOverlay"></div>

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
                <a href="{{ route('students.index') }}" class="sidebar-menu-link {{ request()->routeIs('students.index') && !request()->routeIs('students.create') ? 'active' : '' }}">
                    <i class="fas fa-th-large"></i>
                    <span>Dashboard</span>
                </a>
            </div>

            <!-- Students Menu with Submenu -->
            <div class="sidebar-menu-item">
                <a href="#studentsSubmenu" class="sidebar-menu-link {{ request()->routeIs('students.*') && !request()->routeIs('students.index') ? 'active' : '' }} {{ request()->routeIs('students.*') && !request()->routeIs('students.index') ? '' : 'collapsed' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('students.*') && !request()->routeIs('students.index') ? 'true' : 'false' }}">
                    <i class="fas fa-users"></i>
                    <span>Students</span>
                    <i class="fas fa-chevron-down menu-arrow"></i>
                </a>
                <div class="sidebar-submenu collapse {{ request()->routeIs('students.*') && !request()->routeIs('students.index') ? 'show' : '' }}" id="studentsSubmenu">
                    @can('edit-students')
                    <a href="{{ route('students.create') }}" class="sidebar-submenu-link {{ request()->routeIs('students.create') ? 'active' : '' }}">
                        <i class="fas fa-plus-circle"></i>
                        <span>Add Student</span>
                    </a>
                    @endcan
                </div>
            </div>

            @can('import-students')
            <!-- Data Management Menu with Submenu -->
            <div class="sidebar-menu-item">
                <a href="#dataSubmenu" class="sidebar-menu-link {{ request()->routeIs('import.*') ? '' : 'collapsed' }}" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('import.*') ? 'true' : 'false' }}">
                    <i class="fas fa-database"></i>
                    <span>Data Management</span>
                    <i class="fas fa-chevron-down menu-arrow"></i>
                </a>
                <div class="sidebar-submenu collapse {{ request()->routeIs('import.*') ? 'show' : '' }}" id="dataSubmenu">
                    <a href="{{ route('import.index') }}" class="sidebar-submenu-link {{ request()->routeIs('import.*') ? 'active' : '' }}">
                        <i class="fas fa-file-import"></i>
                        <span>Import Data</span>
                    </a>
                </div>
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

    <!-- Footer -->
    <footer class="app-footer no-print">
        <div class="footer-content">
            <div class="footer-left">
                <p class="footer-copyright">
                    &copy; {{ date('Y') }} Success Laventille Secondary School. All rights reserved.
                </p>
                <p class="footer-version">
                    Version 1.0 | <a href="#" class="footer-link" data-bs-toggle="modal" data-bs-target="#versionHistoryModal">Version History</a>
                </p>
            </div>
            <div class="footer-right">
                <p class="footer-developer">
                    Designed &amp; Developed by <strong>Code Canvas Consultants LTD</strong>
                </p>
                <p class="footer-portfolio">
                    <a href="https://keronlewis.com" target="_blank" rel="noopener noreferrer" class="footer-link">
                        <i class="fas fa-user-tie me-1"></i>Developer Portfolio
                    </a>
                </p>
            </div>
        </div>
    </footer>

    <!-- Version History Modal -->
    <div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="versionHistoryModalLabel">
                        <i class="fas fa-code-branch me-2"></i>Version History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge current">v1.0</span>
                            <span class="version-date">{{ date('F Y') }} - Current</span>
                        </div>
                        <ul class="version-features">
                            <li>Complete mobile responsiveness across all devices</li>
                            <li>127-field comprehensive student profiles</li>
                            <li>Webhook integration with registration form</li>
                            <li>User management with role-based access control</li>
                            <li>PDF generation with official document watermarks</li>
                            <li>Advanced search and filtering capabilities</li>
                            <li>DataTables integration for efficient data management</li>
                        </ul>
                    </div>
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge">v0.9</span>
                            <span class="version-date">January 2026</span>
                        </div>
                        <ul class="version-features">
                            <li>Beta testing with form integration</li>
                            <li>PDF generation functionality</li>
                            <li>Enhanced security features</li>
                        </ul>
                    </div>
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge">v0.8</span>
                            <span class="version-date">December 2025</span>
                        </div>
                        <ul class="version-features">
                            <li>Initial development</li>
                            <li>Core student management features</li>
                            <li>Basic CRUD operations</li>
                        </ul>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.4/moment.min.js"></script>
    <script>
        // Sidebar toggle for mobile
        const sidebarToggle = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        const mobileOverlay = document.getElementById('mobileOverlay');

        function toggleSidebar() {
            sidebar.classList.toggle('show');
            mobileOverlay.classList.toggle('show');
            document.body.style.overflow = sidebar.classList.contains('show') ? 'hidden' : '';
        }

        function closeSidebar() {
            sidebar.classList.remove('show');
            mobileOverlay.classList.remove('show');
            document.body.style.overflow = '';
        }

        sidebarToggle?.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleSidebar();
        });

        // Close sidebar when clicking overlay
        mobileOverlay?.addEventListener('click', closeSidebar);

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(event) {
            if (window.innerWidth <= 992 && sidebar?.classList.contains('show')) {
                if (!sidebar.contains(event.target) && event.target !== sidebarToggle) {
                    closeSidebar();
                }
            }
        });

        // Close sidebar on window resize to desktop
        window.addEventListener('resize', function() {
            if (window.innerWidth > 992 && sidebar?.classList.contains('show')) {
                closeSidebar();
            }
        });

        // Handle submenu toggle arrow animation
        document.querySelectorAll('[data-bs-toggle="collapse"]').forEach(function(element) {
            const targetSelector = element.getAttribute('href');
            const target = document.querySelector(targetSelector);

            if (target) {
                // Listen to Bootstrap collapse events
                target.addEventListener('show.bs.collapse', function() {
                    element.classList.remove('collapsed');
                });

                target.addEventListener('hide.bs.collapse', function() {
                    element.classList.add('collapsed');
                });

                // Initialize arrow state on page load
                if (target.classList.contains('show')) {
                    element.classList.remove('collapsed');
                } else {
                    element.classList.add('collapsed');
                }
            }
        });
    </script>
    @stack('scripts')
</body>
</html>
