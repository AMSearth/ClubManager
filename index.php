<?php include 'config.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>Club Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .club-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: none;
            border-radius: 15px;
        }
        .club-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        .club-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
            border-radius: 15px 0 0 15px;
        }
        .event-card {
            transition: transform 0.2s ease;
            border-radius: 10px;
        }
        .event-card:hover {
            transform: scale(1.02);
        }
        .hero-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 60px 0;
            margin-bottom: 40px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Event Manager</a>
            <div class="navbar-nav ms-auto">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <a class="nav-link" href="dashboard.php">Dashboard</a>
                    <a class="nav-link" href="logout.php">Logout</a>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Login</a>
                    <a class="nav-link" href="register.php">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="hero-section">
        <div class="container text-center">
            <h1 class="display-4 fw-bold mb-3">Event Management System</h1>
            <p class="lead">Discover clubs and exciting events happening around campus</p>
        </div>
    </div>

    <div class="container">
        <?php if(isset($_GET['msg'])): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?php 
                switch($_GET['msg']) {
                    case 'already_registered': echo 'You are already registered for this club.'; break;
                    case 'limit_reached': echo 'You can only register for 2 clubs maximum.'; break;
                    case 'registration_pending': echo 'Registration submitted! Waiting for admin approval.'; break;
                    case 'event_registered': echo 'Successfully registered for the event!'; break;
                    case 'event_already_registered': echo 'You are already registered for this event.'; break;
                    case 'registration_error': echo 'Registration failed. Please try again.'; break;
                }
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php
        $clubs_stmt = $pdo->query("SELECT * FROM clubs ORDER BY name");
        while($club = $clubs_stmt->fetch()):
        ?>
        <div class="card mb-4 club-card shadow-sm">
            <div class="row g-0">
                <?php if($club['image']): ?>
                <div class="col-md-4">
                    <img src="<?= htmlspecialchars($club['image']) ?>" class="club-image" alt="<?= htmlspecialchars($club['name']) ?>">
                </div>
                <div class="col-md-8">
                <?php else: ?>
                <div class="col-12">
                <?php endif; ?>
                    <div class="card-body p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="card-title text-primary mb-2"><?= htmlspecialchars($club['name']) ?></h3>
                                <p class="card-text text-muted"><?= htmlspecialchars($club['description']) ?></p>
                            </div>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <a href="register_club.php?id=<?= $club['id'] ?>" class="btn btn-primary btn-lg px-4">Join Club</a>
                            <?php endif; ?>
                        </div>
                        
                        <hr class="my-4">
                        <h5 class="text-secondary mb-3">🎉 Upcoming Events</h5>
                        <?php
                        $events_stmt = $pdo->prepare("SELECT * FROM events WHERE club_id = ? ORDER BY event_date ASC");
                        $events_stmt->execute([$club['id']]);
                        $events = $events_stmt->fetchAll();
                        
                        if(count($events) > 0):
                        ?>
                        <div class="row">
                            <?php foreach($events as $event): ?>
                            <div class="col-md-6 mb-3">
                                <div class="card border-0 shadow-sm event-card">
                                    <div class="card-body p-3">
                                        <h6 class="card-title text-dark fw-bold"><?= htmlspecialchars($event['title']) ?></h6>
                                        <p class="card-text small text-muted mb-2"><?= htmlspecialchars($event['description']) ?></p>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-primary fw-bold">📅 <?= date('M d, Y', strtotime($event['event_date'])) ?></small>
                                            <?php if(isset($_SESSION['user_id'])): ?>
                                                <a href="register_event.php?id=<?= $event['id'] ?>" class="btn btn-outline-primary btn-sm">Register</a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-light text-center">
                            <i class="text-muted">No upcoming events scheduled.</i>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
