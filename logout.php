<?php
// logout.php
require __DIR__ . '/auth_bootstrap.php';

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit;
}

// CSRF check
$posted = $_POST['csrf'] ?? '';
$valid  = isset($_SESSION['csrf_logout']) && hash_equals($_SESSION['csrf_logout'], $posted);
if (!$valid) {
    http_response_code(400);
    echo 'Invalid logout request';
    exit;
}

// Perform logout
try {
    $auth->logOut(); // Delight\Auth
} catch (\Delight\Auth\NotLoggedInException $e) {
    // ignore – user already logged out
}

// Destroy session completely
if (session_status() === PHP_SESSION_ACTIVE) {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    @session_destroy();
}

// Optional: clear the logout CSRF token
unset($_SESSION['csrf_logout']);

header('Location: login.php');
exit;
