<?php
include 'config.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

$club_id = $_GET['id'];

try {
    // Check if already registered
    $stmt = $pdo->prepare("SELECT * FROM registrations WHERE user_id = ? AND club_id = ?");
    $stmt->execute([$_SESSION['user_id'], $club_id]);
    if($stmt->fetch()) {
        header('Location: index.php?msg=already_registered');
        exit;
    }

    // Check registration limit (2 clubs)
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE user_id = ? AND status = 'approved'");
    $stmt->execute([$_SESSION['user_id']]);
    if($stmt->fetchColumn() >= 2) {
        header('Location: index.php?msg=limit_reached');
        exit;
    }

    $stmt = $pdo->prepare("INSERT INTO registrations (user_id, club_id) VALUES (?, ?)");
    $stmt->execute([$_SESSION['user_id'], $club_id]);

    header('Location: index.php?msg=registration_pending');
} catch(PDOException $e) {
    header('Location: index.php?msg=registration_error');
}
?>
