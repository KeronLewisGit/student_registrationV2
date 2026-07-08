<?php
/**
 * Webhook Debug Script
 * Upload this to: public_html/student_registrationV2/slss-laravel/public/
 * Access at: https://darkcyan-whale-509153.hostingersite.com/test-webhook.php
 */

// Log all incoming data
$logFile = __DIR__ . '/../storage/logs/webhook_debug.log';

// Capture all request data
$debugData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'headers' => getallheaders(),
    'get' => $_GET,
    'post' => $_POST,
    'raw_input' => file_get_contents('php://input'),
    'server' => [
        'REQUEST_URI' => $_SERVER['REQUEST_URI'] ?? '',
        'REMOTE_ADDR' => $_SERVER['REMOTE_ADDR'] ?? '',
        'HTTP_USER_AGENT' => $_SERVER['HTTP_USER_AGENT'] ?? '',
    ]
];

// Write to log file
file_put_contents(
    $logFile,
    "\n\n=== WEBHOOK DEBUG " . date('Y-m-d H:i:s') . " ===\n" .
    print_r($debugData, true) .
    "\n=== END DEBUG ===\n",
    FILE_APPEND
);

// Return success
header('Content-Type: application/json');
echo json_encode([
    'success' => true,
    'message' => 'Debug data logged',
    'log_file' => $logFile,
    'received_fields' => count($_POST['fields'] ?? [])
]);
