<?php 
include 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'club_admin') {
    header('Location: login.php');
    exit;
}

// Get club admin's club
$stmt = $pdo->prepare("SELECT club_id FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$admin_club_id = $stmt->fetchColumn();

if(isset($_POST['action']) && $_POST['action'] == 'add_event') {
    $stmt = $pdo->prepare("INSERT INTO events (club_id, title, description, event_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$admin_club_id, $_POST['title'], $_POST['description'], $_POST['event_date']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ? AND club_id = ?");
    $stmt->execute([$_POST['event_id'], $admin_club_id]);
}

if(isset($_POST['action']) && $_POST['action'] == 'edit_event') {
    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ? WHERE id = ? AND club_id = ?");
    $stmt->execute([$_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['event_id'], $admin_club_id]);
}

if(isset($_POST['action']) && $_POST['action'] == 'approve_registration') {
    $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ? AND club_id = ?");
    $stmt->execute([$_POST['status'], $_POST['reg_id'], $admin_club_id]);
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Club Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            padding: 40px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        .card-header {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
        }
        .btn-gradient {
            background: linear-gradient(45deg, #28a745, #20c997);
            border: none;
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-radius: 15px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-user-cog me-2"></i>Club Admin Panel</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3"><i class="fas fa-users me-3"></i>Club Dashboard</h1>
            <?php
            $stmt = $pdo->prepare("SELECT name FROM clubs WHERE id = ?");
            $stmt->execute([$admin_club_id]);
            $club_name = $stmt->fetchColumn();
            ?>
            <p class="lead">Managing: <?= htmlspecialchars($club_name) ?></p>
        </div>
    </div>

    <div class="container mt-4">
        <!-- Stats -->
        <div class="row mb-4">
            <?php
            $event_count = $pdo->prepare("SELECT COUNT(*) FROM events WHERE club_id = ?");
            $event_count->execute([$admin_club_id]);
            $events = $event_count->fetchColumn();
            
            $member_count = $pdo->prepare("SELECT COUNT(*) FROM registrations WHERE club_id = ? AND status = 'approved'");
            $member_count->execute([$admin_club_id]);
            $members = $member_count->fetchColumn();
            ?>
            <div class="col-md-6 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                    <h3><?= $events ?></h3>
                    <p class="mb-0">Club Events</p>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?= $members ?></h3>
                    <p class="mb-0">Club Members</p>
                </div>
            </div>
        </div>

        <!-- Add Event -->
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-plus me-2"></i>Add New Event
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_event">
                            <div class="mb-3">
                                <label class="form-label">Event Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="fas fa-plus me-2"></i>Add Event
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Event Registrations -->
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-2"></i>Event Registration Stats
                    </div>
                    <div class="card-body">
                        <?php
                        $stmt = $pdo->prepare("
                            SELECT e.title, COUNT(er.id) as registrations 
                            FROM events e 
                            LEFT JOIN event_registrations er ON e.id = er.event_id 
                            WHERE e.club_id = ? 
                            GROUP BY e.id 
                            ORDER BY registrations DESC
                        ");
                        $stmt->execute([$admin_club_id]);
                        ?>
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Event</th>
                                        <th>Registrations</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($event = $stmt->fetch()): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($event['title']) ?></td>
                                        <td><span class="badge bg-primary"><?= $event['registrations'] ?></span></td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Club Registrations -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-check me-2"></i>Club Registration Approvals
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Student</th>
                                        <th>Status</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $stmt = $pdo->prepare("
                                        SELECT r.id, r.status, u.username 
                                        FROM registrations r 
                                        JOIN users u ON r.user_id = u.id 
                                        WHERE r.club_id = ?
                                        ORDER BY r.status, r.id DESC
                                    ");
                                    $stmt->execute([$admin_club_id]);
                                    while($reg = $stmt->fetch()):
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($reg['username']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= $reg['status'] == 'approved' ? 'success' : ($reg['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                <?= ucfirst($reg['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($reg['status'] == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve_registration">
                                                <input type="hidden" name="reg_id" value="<?= $reg['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-success btn-sm me-1">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve_registration">
                                                <input type="hidden" name="reg_id" value="<?= $reg['id'] ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- My Events -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar me-2"></i>My Club Events
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th>Event Title</th>
                                        <th>Description</th>
                                        <th>Date</th>
                                        <th>Registrations</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $events = $pdo->prepare("SELECT * FROM events WHERE club_id = ? ORDER BY event_date DESC");
                                    $events->execute([$admin_club_id]);
                                    while($event = $events->fetch()):
                                        // Get registration count for this event
                                        $reg_count = $pdo->prepare("SELECT COUNT(*) FROM event_registrations WHERE event_id = ?");
                                        $reg_count->execute([$event['id']]);
                                        $count = $reg_count->fetchColumn();
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                                        <td><?= htmlspecialchars($event['description']) ?></td>
                                        <td><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
                                        <td>
                                            <button class="btn btn-info btn-sm" onclick="showRegistrations(<?= $event['id'] ?>, '<?= addslashes($event['title']) ?>')">
                                                <i class="fas fa-users"></i> <?= $count ?> Registered
                                            </button>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm me-1" onclick="editEvent(<?= $event['id'] ?>, '<?= addslashes($event['title']) ?>', '<?= addslashes($event['description']) ?>', '<?= $event['event_date'] ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this event?')">
                                                <input type="hidden" name="action" value="delete_event">
                                                <input type="hidden" name="event_id" value="<?= $event['id'] ?>">
                                                <button type="submit" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-trash"></i> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div class="modal fade" id="editEventModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Event</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_event">
                        <input type="hidden" name="event_id" id="edit_event_id">
                        <div class="mb-3">
                            <label class="form-label">Event Title</label>
                            <input type="text" name="title" id="edit_event_title" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_event_desc" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Event Date</label>
                            <input type="date" name="event_date" id="edit_event_date" class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Event</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Event Registrations Modal -->
    <div class="modal fade" id="registrationsModal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registrationsTitle">Event Registrations</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="registrationsList"></div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editEvent(id, title, desc, date) {
            document.getElementById('edit_event_id').value = id;
            document.getElementById('edit_event_title').value = title;
            document.getElementById('edit_event_desc').value = desc;
            document.getElementById('edit_event_date').value = date;
            new bootstrap.Modal(document.getElementById('editEventModal')).show();
        }
        
        function showRegistrations(eventId, eventTitle) {
            document.getElementById('registrationsTitle').textContent = 'Registrations for: ' + eventTitle;
            
            fetch('get_event_registrations.php?event_id=' + eventId)
                .then(response => response.text())
                .then(data => {
                    document.getElementById('registrationsList').innerHTML = data;
                    new bootstrap.Modal(document.getElementById('registrationsModal')).show();
                });
        }
    </script>
</body>
</html>
