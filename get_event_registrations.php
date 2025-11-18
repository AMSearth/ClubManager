<?php
include 'config.php';

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'club_admin') {
    exit('Unauthorized');
}

$event_id = $_GET['event_id'];

// Get registrations for this event
$stmt = $pdo->prepare("
    SELECT u.username, u.id as user_id, er.registration_date
    FROM event_registrations er
    JOIN users u ON er.user_id = u.id
    JOIN events e ON er.event_id = e.id
    JOIN users ca ON ca.id = ?
    WHERE er.event_id = ? AND e.club_id = ca.club_id
    ORDER BY er.registration_date DESC
");
$stmt->execute([$_SESSION['user_id'], $event_id]);

if($stmt->rowCount() > 0) {
    echo '<div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th><i class="fas fa-user me-1"></i>Student Name</th>
                        <th><i class="fas fa-calendar me-1"></i>Registration Date</th>
                    </tr>
                </thead>
                <tbody>';
    
    while($reg = $stmt->fetch()) {
        echo '<tr>
                <td><strong>' . htmlspecialchars($reg['username']) . '</strong></td>
                <td>' . date('M d, Y H:i', strtotime($reg['registration_date'])) . '</td>
              </tr>';
    }
    
    echo '</tbody></table></div>';
    echo '<div class="mt-3 text-muted">
            <strong>Total Registrations: ' . $stmt->rowCount() . '</strong>
          </div>';
} else {
    echo '<div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-2"></i>No students have registered for this event yet.
          </div>';
}
?>
