<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SLSS Student Management</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: "Lato", sans-serif;
        }
        .login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            padding: 3rem;
            max-width: 450px;
            width: 100%;
        }
        .login-logo {
            width: 80px;
            margin: 0 auto 1.5rem;
            display: block;
        }
        .btn-primary {
            background: #0b5fff;
            border: none;
            padding: 0.75rem;
            border-radius: 10px;
            font-weight: 600;
        }
        .btn-primary:hover {
            background: #0a53e8;
        }
        .form-control {
            border-radius: 10px;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
        }
        .form-control:focus {
            border-color: #0b5fff;
            box-shadow: 0 0 0 3px rgba(11,95,255,0.1);
        }
    </style>
</head>
<body>
    <div class="login-card">
        <img src="{{ asset('images/successlogo.png') }}" alt="SLSS Logo" class="login-logo">
        <h2 class="text-center mb-1">Welcome Back</h2>
        <p class="text-center text-muted mb-4">Success Laventille Secondary School<br>Student Management System</p>

        @if($errors->any())
            <div class="alert alert-danger">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label fw-bold">Email Address</label>
                <input type="email" class="form-control" id="email" name="email" value="{{ old('email') }}" required autofocus>
            </div>
            <div class="mb-3">
                <label for="password" class="form-label fw-bold">Password</label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
            <button type="submit" class="btn btn-primary w-100">Sign In</button>
        </form>
    </div>
</body>
</html>
