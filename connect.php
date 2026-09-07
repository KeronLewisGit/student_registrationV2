<?php
error_reporting(E_ALL);
ini_set('display_errors', 'Off');
ini_set('log_errors', 'On');

// Prefer credentials from an untracked file OUTSIDE version control.
// Create db_credentials.php on the server (and never commit it) returning:
//   <?php return ['host' => 'localhost', 'username' => '...', 'password' => '...', 'database' => '...'];
$credentialsFile = __DIR__ . '/db_credentials.php';
if (is_file($credentialsFile)) {
    $creds = require $credentialsFile;
    $servername = $creds['host'];
    $username   = $creds['username'];
    $password   = $creds['password'];
    $dbname     = $creds['database'];
} else {
    // No credentials file: refuse to start rather than fall back to values that
    // were once committed to a public repository. Create db_credentials.php on
    // the server (see comment above); it is git-ignored.
    error_log('connect.php: db_credentials.php is missing; database credentials are not configured.');
    http_response_code(503);
    exit('The site is temporarily unavailable (database configuration missing).');
}

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $pdo;
  } catch(PDOException $e) {
    error_log('Database connection failed: ' . $e->getMessage());
    http_response_code(500);
    exit('A server error occurred. Please try again later.');
  }

?>