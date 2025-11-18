<?php
$host = 'localhost';
$username = 'aditya';
$password = '8767';

try {
    $pdo = new PDO("mysql:host=$host", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $pdo->exec("CREATE DATABASE IF NOT EXISTS club_manager");
    $pdo->exec("USE club_manager");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS clubs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        description TEXT,
        image VARCHAR(255) DEFAULT NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        role ENUM('student', 'admin', 'club_admin') DEFAULT 'student',
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        club_id INT DEFAULT NULL,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE SET NULL
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        club_id INT,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        event_date DATE,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        club_id INT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        UNIQUE KEY unique_registration (user_id, club_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (club_id) REFERENCES clubs(id) ON DELETE CASCADE
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS event_registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT,
        event_id INT,
        registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_event_registration (user_id, event_id),
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (event_id) REFERENCES events(id) ON DELETE CASCADE
    )");
    
    $stmt = $pdo->prepare("INSERT IGNORE INTO users (username, password, role, status) VALUES (?, ?, ?, ?)");
    $stmt->execute(['admin', password_hash('password', PASSWORD_DEFAULT), 'admin', 'approved']);
    
    echo "Database setup complete! Admin login: admin/password";
    
} catch(PDOException $e) {
    die("Setup failed: " . $e->getMessage());
}
?>
