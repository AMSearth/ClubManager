<?php
$host = 'localhost';
$dbname = 'club_manager';
$username = 'yourUsername';
$password = 'password'; // Change this to your MySQL root password if you have one

try {
    // First try to connect without database to create it
    $pdo_temp = new PDO("mysql:host=$host", $username, $password);
    $pdo_temp->exec("CREATE DATABASE IF NOT EXISTS $dbname");
    
    // Now connect to the specific database
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

session_start();
?>
