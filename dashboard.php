<?php 
include 'config.php';
if(!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

if($_SESSION['role'] == 'admin') {
    header('Location: admin.php');
    exit;
}

if($_SESSION['role'] == 'club_admin') {
    header('Location: club_admin.php');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Student Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Club Manager</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="logout.php">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="row">
            <div class="col-md-6">
                <h2>My Club Events</h2>
                <?php
                $stmt = $pdo->prepare("
                    SELECT e.*, c.name as club_name 
                    FROM events e 
                    JOIN clubs c ON e.club_id = c.id 
                    JOIN registrations r ON r.club_id = c.id 
                    WHERE r.user_id = ? AND r.status = 'approved'
                    ORDER BY e.event_date ASC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                
                if($stmt->rowCount() == 0) {
                    echo "<p class='alert alert-info'>You haven't joined any clubs yet. <a href='index.php'>Browse clubs</a> to get started!</p>";
                }
                ?>
                <div class="row">
                    <?php while($event = $stmt->fetch()): ?>
                    <div class="col-12 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <h5 class="card-title"><?= htmlspecialchars($event['title']) ?></h5>
                                        <p class="card-text"><?= htmlspecialchars($event['description']) ?></p>
                                        <small class="text-muted">Club: <?= htmlspecialchars($event['club_name']) ?></small><br>
                                        <small class="text-muted">Date: <?= date('M d, Y', strtotime($event['event_date'])) ?></small>
                                    </div>
                                    <a href="register_event.php?id=<?= $event['id'] ?>" class="btn btn-primary">Register for Event</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
            
            <div class="col-md-6">
                <h2>My Event Registrations</h2>
                <?php
                $stmt = $pdo->prepare("
                    SELECT e.title, e.event_date, c.name as club_name 
                    FROM event_registrations er
                    JOIN events e ON er.event_id = e.id
                    JOIN clubs c ON e.club_id = c.id
                    WHERE er.user_id = ?
                    ORDER BY e.event_date ASC
                ");
                $stmt->execute([$_SESSION['user_id']]);
                ?>
                <div class="row">
                    <?php while($event = $stmt->fetch()): ?>
                    <div class="col-12 mb-3">
                        <div class="card border-success">
                            <div class="card-body">
                                <h6 class="card-title"><?= htmlspecialchars($event['title']) ?></h6>
                                <small class="text-muted">Club: <?= htmlspecialchars($event['club_name']) ?></small><br>
                                <small class="text-muted">Date: <?= date('M d, Y', strtotime($event['event_date'])) ?></small>
                                <span class="badge bg-success ms-2">Registered</span>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
