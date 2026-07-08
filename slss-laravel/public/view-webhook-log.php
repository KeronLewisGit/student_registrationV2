<?php
/**
 * Webhook Log Viewer
 * Access at: https://darkcyan-whale-509153.hostingersite.com/view-webhook-log.php
 *
 * WARNING: DELETE THIS FILE AFTER DEBUGGING - IT EXPOSES SENSITIVE DATA
 */

$logFile = __DIR__ . '/../storage/logs/webhook_debug.log';

// Simple authentication
$password = 'slss2026';
if (!isset($_GET['key']) || $_GET['key'] !== $password) {
    die('Access denied. Add ?key=slss2026 to URL');
}

if (!file_exists($logFile)) {
    die('Log file not found: ' . $logFile);
}

// Read last 50000 characters (approximately last few entries)
$handle = fopen($logFile, 'r');
fseek($handle, max(0, filesize($logFile) - 50000));
$content = fread($handle, 50000);
fclose($handle);

header('Content-Type: text/plain; charset=utf-8');
echo "=== WEBHOOK DEBUG LOG (Last ~50KB) ===\n\n";
echo $content;
echo "\n\n=== END OF LOG ===\n";
echo "\nTo see the full log, SSH into server and run:\n";
echo "cat storage/logs/webhook_debug.log\n";
