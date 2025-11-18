<?php
include 'config.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$event_id = $_GET['id'];

// Check if already registered
$stmt = $pdo->prepare("SELECT * FROM event_registrations WHERE user_id = ? AND event_id = ?");
$stmt->execute([$_SESSION['user_id'], $event_id]);
if($stmt->fetch()) {
    header('Location: index.php?msg=event_already_registered');
    exit;
}

$stmt = $pdo->prepare("INSERT INTO event_registrations (user_id, event_id) VALUES (?, ?)");
$stmt->execute([$_SESSION['user_id'], $event_id]);

header('Location: index.php?msg=event_registered');
?>
