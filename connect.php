<?php
error_reporting(E_ALL);
ini_set('display_errors', 'On');

$servername = "localhost";
$username = "gkblvzmy_student-portal";
$password = "Success100%";
$dbname = "gkblvzmy_student-portal";

try {
    $pdo = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // set the PDO error mode to exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connected successfully";
    return $pdo;
  } catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
  }

?>