<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class DeployController extends Controller
{
    public function showForm()
    {
        return $this->renderTokenForm();
    }

    public function deploy(Request $request)
    {
        // Security check - token validation
        $expectedToken = env('DEPLOY_TOKEN');

        // If no token is set in env, require setup
        if (empty($expectedToken)) {
            return $this->renderTokenForm('Please set DEPLOY_TOKEN in your .env file to enable deployments.', 'warning');
        }

        // Validate submitted token
        $submittedToken = $request->input('token');

        if ($submittedToken !== $expectedToken) {
            return $this->renderTokenForm('Invalid deployment token. Access denied.', 'error');
        }

        $output = [];
        $startTime = now();

        try {
            // Step 1: Git Pull
            $output[] = "=== PULLING LATEST CHANGES ===";
            $gitPull = Process::run('git pull origin master');
            $output[] = $gitPull->output();
            $output[] = $gitPull->errorOutput();

            // Step 2: Install/Update Dependencies (if composer.lock changed)
            $output[] = "\n=== UPDATING DEPENDENCIES ===";
            $composerInstall = Process::run('composer install --no-interaction --prefer-dist --optimize-autoloader');
            $output[] = $composerInstall->output();

            // Step 3: Run Migrations
            $output[] = "\n=== RUNNING MIGRATIONS ===";
            $migrate = Process::run('php artisan migrate --force');
            $output[] = $migrate->output();

            // Step 4: Ensure Storage Symlink
            $output[] = "\n=== CREATING STORAGE SYMLINK ===";

            // Check if symlink exists
            $publicStorage = public_path('storage');
            if (file_exists($publicStorage)) {
                if (is_link($publicStorage)) {
                    $output[] = "Storage symlink already exists at: {$publicStorage}";
                    $output[] = "Target: " . readlink($publicStorage);
                } else {
                    $output[] = "Warning: {$publicStorage} exists but is not a symlink!";
                }
            } else {
                $storageLink = Process::run('php artisan storage:link');
                $output[] = $storageLink->output();
                if ($storageLink->successful()) {
                    $output[] = "✓ Storage symlink created successfully";
                } else {
                    $output[] = "✗ Failed to create storage symlink: " . $storageLink->errorOutput();
                }
            }

            // Step 5: Clear Caches
            $output[] = "\n=== CLEARING CACHES ===";

            $configClear = Process::run('php artisan config:clear');
            $output[] = "Config cache cleared: " . ($configClear->successful() ? 'OK' : 'FAILED');

            $cacheClear = Process::run('php artisan cache:clear');
            $output[] = "Application cache cleared: " . ($cacheClear->successful() ? 'OK' : 'FAILED');

            $viewClear = Process::run('php artisan view:clear');
            $output[] = "View cache cleared: " . ($viewClear->successful() ? 'OK' : 'FAILED');

            $routeClear = Process::run('php artisan route:clear');
            $output[] = "Route cache cleared: " . ($routeClear->successful() ? 'OK' : 'FAILED');

            // Step 6: Optimize
            $output[] = "\n=== OPTIMIZING APPLICATION ===";
            $optimize = Process::run('php artisan optimize');
            $output[] = $optimize->output();

            $endTime = now();
            $duration = $endTime->diffInSeconds($startTime);

            $output[] = "\n=== DEPLOYMENT COMPLETED ===";
            $output[] = "Duration: {$duration} seconds";
            $output[] = "Timestamp: " . $endTime->format('Y-m-d H:i:s');

            $status = 'success';
            $message = 'Deployment completed successfully!';

        } catch (\Exception $e) {
            $output[] = "\n=== DEPLOYMENT FAILED ===";
            $output[] = "Error: " . $e->getMessage();
            $status = 'error';
            $message = 'Deployment failed: ' . $e->getMessage();
        }

        // Return formatted HTML output
        return $this->formatOutput($output, $status, $message);
    }

    private function formatOutput(array $output, string $status, string $message)
    {
        $statusColor = $status === 'success' ? '#10b981' : '#ef4444';
        $statusIcon = $status === 'success' ? '✓' : '✗';

        $outputText = implode("\n", $output);

        return response("
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Deployment - SLSS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'Courier New', monospace;
            background: #1a1a1a;
            color: #e0e0e0;
            padding: 2rem;
            line-height: 1.6;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        .header h1 {
            color: white;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .status {
            display: inline-block;
            padding: 0.5rem 1.5rem;
            background: {$statusColor};
            color: white;
            border-radius: 6px;
            font-weight: bold;
            font-size: 1.1rem;
            margin-top: 1rem;
        }
        .output {
            background: #2d2d2d;
            border: 1px solid #404040;
            border-radius: 8px;
            padding: 1.5rem;
            overflow-x: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
            font-size: 0.9rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.3);
        }
        .success-text {
            color: #10b981;
            font-weight: bold;
        }
        .error-text {
            color: #ef4444;
            font-weight: bold;
        }
        .info-text {
            color: #60a5fa;
            font-weight: bold;
        }
        .footer {
            text-align: center;
            margin-top: 2rem;
            color: #999;
            font-size: 0.875rem;
        }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: background 0.3s;
        }
        .back-link:hover {
            background: #764ba2;
        }
        @media (max-width: 768px) {
            body {
                padding: 1rem;
            }
            .header h1 {
                font-size: 1.5rem;
            }
            .output {
                font-size: 0.8rem;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h1>🚀 SLSS Deployment System</h1>
            <div class='status'>{$statusIcon} {$message}</div>
        </div>

        <div class='output'>{$outputText}</div>

        <div class='footer'>
            <a href='/' class='back-link'>← Back to Application</a>
            <p style='margin-top: 1rem;'>Success Laventille Secondary School - Student Management System</p>
        </div>
    </div>
</body>
</html>
        ")->header('Content-Type', 'text/html');
    }

    private function renderTokenForm(?string $message = null, string $messageType = 'info')
    {
        $alertColor = [
            'error' => '#ef4444',
            'warning' => '#f59e0b',
            'info' => '#3b82f6',
        ][$messageType] ?? '#3b82f6';

        $alertHtml = $message ? "
            <div class='alert' style='background: {$alertColor}15; border: 1px solid {$alertColor}; color: {$alertColor}; padding: 1rem; border-radius: 8px; margin-bottom: 2rem;'>
                <strong>⚠️ {$message}</strong>
            </div>
        " : '';

        return response("
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Deploy - SLSS</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .container {
            max-width: 500px;
            width: 100%;
        }
        .card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 2.5rem;
        }
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        .logo h1 {
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 0.5rem;
        }
        .logo p {
            color: #6b7280;
            font-size: 0.95rem;
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }
        .form-control {
            width: 100%;
            padding: 0.875rem 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.2s;
            font-family: 'Courier New', monospace;
            letter-spacing: 2px;
        }
        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        .btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1.05rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
        }
        .btn:active {
            transform: translateY(0);
        }
        .info-box {
            background: #f3f4f6;
            border-left: 4px solid #667eea;
            padding: 1rem;
            border-radius: 6px;
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #4b5563;
        }
        @media (max-width: 576px) {
            .card {
                padding: 1.5rem;
            }
            .logo h1 {
                font-size: 1.5rem;
            }
            .form-control,
            .btn {
                font-size: 16px; /* Prevents iOS zoom */
            }
        }
    </style>
</head>
<body>
    <div class='container'>
        <div class='card'>
            <div class='logo'>
                <h1>🚀 Deployment System</h1>
                <p>Success Laventille Secondary School</p>
            </div>

            {$alertHtml}

            <form method='POST' action='/deploy'>
                <div class='form-group'>
                    <label class='form-label' for='token'>Deployment Token</label>
                    <input
                        type='password'
                        id='token'
                        name='token'
                        class='form-control'
                        placeholder='Enter deployment token'
                        required
                        autofocus
                    >
                </div>

                <button type='submit' class='btn'>
                    🔒 Authenticate & Deploy
                </button>
            </form>

            <div class='info-box'>
                <strong>ℹ️ Security Notice:</strong> This endpoint executes git pull and system commands. Only authorized administrators should have access to the deployment token.
            </div>
        </div>
    </div>
</body>
</html>
        ")->header('Content-Type', 'text/html');
    }
}
