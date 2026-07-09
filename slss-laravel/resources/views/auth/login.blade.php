<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SLSS Student Management</title>
    <link rel="icon" type="image/png" href="{{ asset('images/successlogo.png') }}">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4f46e5;
            --primary-dark: #4338ca;
            --bg-gradient-1: #6366f1;
            --bg-gradient-2: #8b5cf6;
        }

        * {
            font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        body {
            background: linear-gradient(135deg, var(--bg-gradient-1) 0%, var(--bg-gradient-2) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }

        .login-container {
            display: flex;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            max-width: 900px;
            width: 100%;
            overflow: hidden;
        }

        .login-left {
            flex: 1;
            background: linear-gradient(135deg, var(--bg-gradient-1) 0%, var(--bg-gradient-2) 100%);
            padding: 3rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            text-align: center;
        }

        .login-left img {
            width: 120px;
            height: 120px;
            background: white;
            border-radius: 20px;
            padding: 1rem;
            margin-bottom: 2rem;
        }

        .login-left h1 {
            font-size: 1.75rem;
            font-weight: 700;
            margin-bottom: 1rem;
            line-height: 1.3;
        }

        .login-left p {
            opacity: 0.9;
            font-size: 1rem;
        }

        .login-right {
            flex: 1;
            padding: 3rem;
        }

        .login-header {
            margin-bottom: 2rem;
        }

        .login-header h2 {
            font-size: 1.75rem;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 0.5rem;
        }

        .login-header p {
            color: #64748b;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            color: #0f172a;
            margin-bottom: 0.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-label i {
            color: var(--primary-color);
        }

        .form-control {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 2px solid #e2e8f0;
            font-size: 0.95rem;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 3px rgba(79,70,229,0.1);
        }

        .btn-primary {
            background: var(--primary-color);
            border: none;
            padding: 0.875rem;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(79,70,229,0.4);
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .security-notice {
            margin-top: 2rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 10px;
            font-size: 0.85rem;
            color: #64748b;
            text-align: center;
        }

        .security-notice i {
            color: #10b981;
            margin-right: 0.5rem;
        }

        /* Login Footer */
        .login-footer {
            margin-top: 3rem;
            padding: 1.5rem 2rem;
            text-align: center;
            color: rgba(255, 255, 255, 0.9);
        }

        .login-footer-content {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.5rem;
        }

        .login-footer p {
            margin: 0;
            font-size: 0.875rem;
        }

        .login-footer-link {
            color: white;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.2s;
        }

        .login-footer-link:hover {
            text-decoration: underline;
            opacity: 0.8;
        }

        /* Version History Modal */
        .modal-content {
            border-radius: 15px;
            border: none;
        }

        .modal-header {
            background: var(--primary-color);
            color: white;
            border-radius: 15px 15px 0 0;
            border-bottom: none;
        }

        .modal-header .btn-close {
            filter: brightness(0) invert(1);
        }

        .version-item {
            margin-bottom: 1.5rem;
            padding-bottom: 1.5rem;
            border-bottom: 1px solid #e2e8f0;
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
            background: #f8fafc;
            color: #0f172a;
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
            color: #64748b;
        }

        .version-features {
            margin: 0;
            padding-left: 1.5rem;
            font-size: 0.875rem;
            color: #0f172a;
        }

        .version-features li {
            margin-bottom: 0.375rem;
        }

        .version-features li:last-child {
            margin-bottom: 0;
        }

        @media (max-width: 768px) {
            .login-container {
                flex-direction: column;
            }

            .login-left {
                padding: 2rem;
            }

            .login-left img {
                width: 80px;
                height: 80px;
            }

            .login-left h1 {
                font-size: 1.5rem;
            }

            .login-right {
                padding: 2rem;
            }

            .login-footer {
                margin-top: 2rem;
                padding: 1rem;
            }

            .login-footer p {
                font-size: 0.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <!-- Left Side - Branding -->
        <div class="login-left">
            <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo">
            <h1>Success Laventille Secondary School</h1>
            <p>Student Management System</p>
        </div>

        <!-- Right Side - Login Form -->
        <div class="login-right">
            <div class="login-header">
                <h2>Welcome Back</h2>
                <p>Please sign in to your account</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger mb-4">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i>
                        Email Address
                    </label>
                    <input type="email"
                           class="form-control"
                           id="email"
                           name="email"
                           value="{{ old('email') }}"
                           placeholder="Enter your email"
                           required
                           autofocus>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i>
                        Password
                    </label>
                    <input type="password"
                           class="form-control"
                           id="password"
                           name="password"
                           placeholder="Enter your password"
                           required>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox"
                               class="form-check-input"
                               id="remember"
                               name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me for 30 days
                        </label>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-sign-in-alt me-2"></i>
                    Sign In
                </button>

                <div class="security-notice">
                    <i class="fas fa-shield-alt"></i>
                    Your connection is secured with enterprise-grade encryption
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <div class="login-footer">
        <div class="login-footer-content">
            <p>&copy; {{ date('Y') }} Success Laventille Secondary School. All rights reserved.</p>
            <p>
                Version 1.0 |
                <a href="#" class="login-footer-link" data-bs-toggle="modal" data-bs-target="#versionHistoryModal">
                    Version History
                </a>
            </p>
            <p>Designed &amp; Developed by <strong>Code Canvas Consultants LTD</strong></p>
            <p>
                <a href="https://keronlewis.com" target="_blank" rel="noopener noreferrer" class="login-footer-link">
                    <i class="fas fa-user-tie me-1"></i>Developer Portfolio
                </a>
            </p>
        </div>
    </div>

    <!-- Version History Modal -->
    <div class="modal fade" id="versionHistoryModal" tabindex="-1" aria-labelledby="versionHistoryModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="versionHistoryModalLabel">
                        <i class="fas fa-code-branch me-2"></i>Version History
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <!-- Version 1.0 -->
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge current">v1.0</span>
                            <span class="version-date">{{ date('F Y') }} - Current</span>
                        </div>
                        <ul class="version-features">
                            <li>Complete mobile responsiveness across all devices</li>
                            <li>127-field comprehensive student profiles</li>
                            <li>Webhook integration with Elementor registration form</li>
                            <li>Automated student data import from public form</li>
                            <li>Role-based access control (Admin, Staff, Viewer)</li>
                            <li>User management system with safety checks</li>
                            <li>Advanced DataTables with search and pagination</li>
                            <li>Print and PDF export functionality</li>
                            <li>CSV bulk import with duplicate detection</li>
                            <li>Professional UI with Bootstrap 5.3.2</li>
                            <li>Touch-friendly interface meeting international standards</li>
                            <li>Secure authentication with Laravel Sanctum</li>
                        </ul>
                    </div>

                    <!-- Version 0.9 -->
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge">v0.9</span>
                            <span class="version-date">December 2024</span>
                        </div>
                        <ul class="version-features">
                            <li>Initial student management system</li>
                            <li>Basic CRUD operations for students</li>
                            <li>User authentication system</li>
                            <li>Student list view with basic filtering</li>
                            <li>Manual student creation forms</li>
                        </ul>
                    </div>

                    <!-- Version 0.8 -->
                    <div class="version-item">
                        <div class="version-header">
                            <span class="version-badge">v0.8</span>
                            <span class="version-date">November 2024</span>
                        </div>
                        <ul class="version-features">
                            <li>Project initialization</li>
                            <li>Database schema design</li>
                            <li>Laravel 11 setup</li>
                            <li>Basic authentication scaffolding</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
