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
    // Legacy fallback — these credentials were committed to a public repository
    // and MUST be rotated; once rotated, move the new values to db_credentials.php.
    $servername = "localhost";
    $username = "gkblvzmy_student-portal";
    $password = "N3tsniper!23";
    $dbname = "gkblvzmy_student-portal";
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