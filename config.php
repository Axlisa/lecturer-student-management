<?php
// Database configuration
$host = 'localhost';
$db = 'lecturer_student_management';
$user = 'root';
$pass = '';

// MySQLi Object-Oriented
$mysqli = new mysqli($host, $user, $pass, $db);

// Check MySQLi Object-Oriented connection
if ($mysqli->connect_error) {
    die("MySQLi Object-Oriented connection failed: " . $mysqli->connect_error);
}

// MySQLi Procedural
$mysqli_proc = mysqli_connect($host, $user, $pass, $db);

// Check MySQLi Procedural connection
if (!$mysqli_proc) {
    die("MySQLi Procedural connection failed: " . mysqli_connect_error());
}

// PDO
try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Could not connect to the database $db: " . $e->getMessage());
}
?>
