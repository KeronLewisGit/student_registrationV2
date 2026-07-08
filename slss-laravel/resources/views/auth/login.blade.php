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
</body>
</html>
