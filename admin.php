<?php 
include 'config.php';
if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header('Location: login.php');
    exit;
}

if(isset($_POST['action']) && $_POST['action'] == 'add_club') {
    $image = '';
    if($_FILES['image']['name']) {
        $image = 'uploads/' . time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }
    $stmt = $pdo->prepare("INSERT INTO clubs (name, description, image) VALUES (?, ?, ?)");
    $stmt->execute([$_POST['name'], $_POST['description'], $image]);
}

if(isset($_POST['action']) && $_POST['action'] == 'add_event') {
    $stmt = $pdo->prepare("INSERT INTO events (club_id, title, description, event_date) VALUES (?, ?, ?, ?)");
    $stmt->execute([$_POST['club_id'], $_POST['title'], $_POST['description'], $_POST['event_date']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'approve_registration') {
    $stmt = $pdo->prepare("UPDATE registrations SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['reg_id']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'approve_user') {
    $stmt = $pdo->prepare("UPDATE users SET status = ? WHERE id = ?");
    $stmt->execute([$_POST['status'], $_POST['user_id']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'edit_club') {
    $image = '';
    if($_FILES['image']['name']) {
        $image = 'uploads/' . time() . '_' . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
        $stmt = $pdo->prepare("UPDATE clubs SET name = ?, description = ?, image = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['description'], $image, $_POST['club_id']]);
    } else {
        $stmt = $pdo->prepare("UPDATE clubs SET name = ?, description = ? WHERE id = ?");
        $stmt->execute([$_POST['name'], $_POST['description'], $_POST['club_id']]);
    }
}

if(isset($_POST['action']) && $_POST['action'] == 'edit_user') {
    $stmt = $pdo->prepare("UPDATE users SET username = ? WHERE id = ?");
    $stmt->execute([$_POST['username'], $_POST['user_id']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'delete_user') {
    try {
        // Delete user registrations first
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE user_id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        // Delete user
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$_POST['user_id']]);
        
        $success_msg = "User deleted successfully!";
    } catch(PDOException $e) {
        $error_msg = "Error deleting user: " . $e->getMessage();
    }
}

if(isset($_POST['action']) && $_POST['action'] == 'edit_event') {
    $stmt = $pdo->prepare("UPDATE events SET title = ?, description = ?, event_date = ? WHERE id = ?");
    $stmt->execute([$_POST['title'], $_POST['description'], $_POST['event_date'], $_POST['event_id']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'add_club_admin') {
    // Add club_id column if it doesn't exist
    try {
        $pdo->exec("ALTER TABLE users MODIFY COLUMN role ENUM('student', 'admin', 'club_admin') DEFAULT 'student'");
        $pdo->exec("ALTER TABLE users ADD COLUMN club_id INT DEFAULT NULL");
    } catch(PDOException $e) {}
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, password, role, status, club_id) VALUES (?, ?, 'club_admin', 'approved', ?)");
        $stmt->execute([$_POST['username'], password_hash($_POST['password'], PASSWORD_DEFAULT), $_POST['club_id']]);
        $success_msg = "Club admin created successfully!";
    } catch(PDOException $e) {
        $error_msg = "Username already exists. Please choose a different username.";
    }
}

if(isset($_POST['action']) && $_POST['action'] == 'delete_event') {
    $stmt = $pdo->prepare("DELETE FROM events WHERE id = ?");
    $stmt->execute([$_POST['event_id']]);
}

if(isset($_POST['action']) && $_POST['action'] == 'delete_club') {
    try {
        // Get all events for this club first
        $stmt = $pdo->prepare("SELECT id FROM events WHERE club_id = ?");
        $stmt->execute([$_POST['club_id']]);
        $event_ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        // Delete event registrations for all events of this club
        if(!empty($event_ids)) {
            $placeholders = str_repeat('?,', count($event_ids) - 1) . '?';
            $stmt = $pdo->prepare("DELETE FROM event_registrations WHERE event_id IN ($placeholders)");
            $stmt->execute($event_ids);
        }
        
        // Delete club registrations
        $stmt = $pdo->prepare("DELETE FROM registrations WHERE club_id = ?");
        $stmt->execute([$_POST['club_id']]);
        
        // Delete events
        $stmt = $pdo->prepare("DELETE FROM events WHERE club_id = ?");
        $stmt->execute([$_POST['club_id']]);
        
        // Delete club admins
        $stmt = $pdo->prepare("DELETE FROM users WHERE club_id = ? AND role = 'club_admin'");
        $stmt->execute([$_POST['club_id']]);
        
        // Delete the club
        $stmt = $pdo->prepare("DELETE FROM clubs WHERE id = ?");
        $stmt->execute([$_POST['club_id']]);
        
        $success_msg = "Club deleted successfully!";
    } catch(PDOException $e) {
        $error_msg = "Error deleting club: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .admin-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 0;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .card-header {
            background: linear-gradient(45deg, #f8f9fa, #e9ecef);
            border-radius: 15px 15px 0 0 !important;
            border: none;
            font-weight: 600;
        }
        .btn-gradient {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
        }
        .btn-gradient:hover {
            background: linear-gradient(45deg, #764ba2, #667eea);
            color: white;
        }
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
        }
        .table th {
            border-top: none;
            font-weight: 600;
            color: #495057;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php"><i class="fas fa-users-cog me-2"></i>Club Manager - Admin</a>
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php"><i class="fas fa-home me-1"></i>Home</a>
                <a class="nav-link" href="logout.php"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="admin-header">
        <div class="container text-center">
            <h1 class="display-5 fw-bold mb-3"><i class="fas fa-tachometer-alt me-3"></i>Admin Dashboard</h1>
            <p class="lead">Manage clubs, events, and users</p>
        </div>
    </div>

    <div class="container mt-4">
        <?php if(isset($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?= $success_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if(isset($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?= $error_msg ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        
        <!-- Stats Cards -->
        <div class="row mb-4">
            <?php
            $club_count = $pdo->query("SELECT COUNT(*) FROM clubs")->fetchColumn();
            $user_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
            $event_count = $pdo->query("SELECT COUNT(*) FROM events")->fetchColumn();
            ?>
            <div class="col-md-4 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-users fa-2x mb-2"></i>
                    <h3><?= $club_count ?></h3>
                    <p class="mb-0">Total Clubs</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-user-graduate fa-2x mb-2"></i>
                    <h3><?= $user_count ?></h3>
                    <p class="mb-0">Students</p>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <div class="card stats-card text-center p-3">
                    <i class="fas fa-calendar-alt fa-2x mb-2"></i>
                    <h3><?= $event_count ?></h3>
                    <p class="mb-0">Events</p>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-plus-circle me-2"></i>Add New Club
                    </div>
                    <div class="card-body">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="action" value="add_club">
                            <div class="mb-3">
                                <label class="form-label">Club Name</label>
                                <input type="text" name="name" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="3"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Club Image</label>
                                <input type="file" name="image" class="form-control" accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="fas fa-plus me-2"></i>Add Club
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-plus me-2"></i>Add New Event
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_event">
                            <div class="mb-3">
                                <label class="form-label">Select Club</label>
                                <select name="club_id" class="form-select" required>
                                    <option value="">Choose Club...</option>
                                    <?php
                                    $clubs = $pdo->query("SELECT * FROM clubs");
                                    while($club = $clubs->fetch()):
                                    ?>
                                    <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Event Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Event Date</label>
                                <input type="date" name="event_date" class="form-control" required>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="fas fa-calendar-plus me-2"></i>Add Event
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-md-4 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-plus me-2"></i>Add Club Admin
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <input type="hidden" name="action" value="add_club_admin">
                            <div class="mb-3">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Password</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Assign to Club</label>
                                <select name="club_id" class="form-select" required>
                                    <option value="">Choose Club...</option>
                                    <?php
                                    $clubs = $pdo->query("SELECT * FROM clubs");
                                    while($club = $clubs->fetch()):
                                    ?>
                                    <option value="<?= $club['id'] ?>"><?= htmlspecialchars($club['name']) ?></option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-gradient w-100">
                                <i class="fas fa-user-plus me-2"></i>Add Club Admin
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mt-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-users me-2"></i>Manage Clubs
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-tag me-1"></i>Name</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Description</th>
                                        <th><i class="fas fa-image me-1"></i>Image</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $clubs = $pdo->query("SELECT * FROM clubs");
                                    while($club = $clubs->fetch()):
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($club['name']) ?></strong></td>
                                        <td><?= htmlspecialchars($club['description']) ?></td>
                                        <td>
                                            <?php if($club['image']): ?>
                                                <span class="badge bg-success"><i class="fas fa-check"></i> Yes</span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary"><i class="fas fa-times"></i> No</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <button class="btn btn-warning btn-sm me-1" onclick="editClub(<?= $club['id'] ?>, '<?= addslashes($club['name']) ?>', '<?= addslashes($club['description']) ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Are you sure you want to delete this club? This will also delete all related events, registrations, and club admins.')">>
                                                <input type="hidden" name="action" value="delete_club">
                                                <input type="hidden" name="club_id" value="<?= $club['id'] ?>">
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

        <div class="row mt-4">
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-user-check me-2"></i>User Approvals
                    </div>
                    <div class="card-body">
                        <?php
                        // Add status column if it doesn't exist
                        try {
                            $pdo->exec("ALTER TABLE users ADD COLUMN status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending'");
                            $pdo->exec("UPDATE users SET status = 'approved' WHERE role = 'admin'");
                        } catch(PDOException $e) {}
                        ?>
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-user me-1"></i>Username</th>
                                        <th><i class="fas fa-shield-alt me-1"></i>Status</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $users = $pdo->query("SELECT * FROM users WHERE role = 'student' AND status IN ('approved', 'pending') ORDER BY status, id DESC");
                                    while($user = $users->fetch()):
                                    ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($user['username']) ?></strong></td>
                                        <td>
                                            <span class="badge bg-<?= $user['status'] == 'approved' ? 'success' : ($user['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                <i class="fas fa-<?= $user['status'] == 'approved' ? 'check' : ($user['status'] == 'rejected' ? 'times' : 'clock') ?>"></i>
                                                <?= ucfirst($user['status']) ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if($user['status'] == 'pending'): ?>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve_user">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="status" value="approved">
                                                <button type="submit" class="btn btn-success btn-sm me-1">
                                                    <i class="fas fa-check"></i> Approve
                                                </button>
                                            </form>
                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="action" value="approve_user">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                <input type="hidden" name="status" value="rejected">
                                                <button type="submit" class="btn btn-danger btn-sm me-1">
                                                    <i class="fas fa-times"></i> Reject
                                                </button>
                                            </form>
                                            <?php endif; ?>
                                            <button class="btn btn-warning btn-sm" onclick="editUser(<?= $user['id'] ?>, '<?= addslashes($user['username']) ?>')">
                                                <i class="fas fa-edit"></i> Edit
                                            </button>
                                            <form method="POST" style="display:inline;" onsubmit="return confirm('Delete this user?')">
                                                <input type="hidden" name="action" value="delete_user">
                                                <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
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
            
            <div class="col-md-6 mb-4">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-calendar-check me-2"></i>All Events
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-users me-1"></i>Club</th>
                                        <th><i class="fas fa-calendar me-1"></i>Event</th>
                                        <th><i class="fas fa-clock me-1"></i>Date</th>
                                        <th><i class="fas fa-cogs me-1"></i>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $events = $pdo->query("SELECT e.*, c.name as club_name FROM events e JOIN clubs c ON e.club_id = c.id ORDER BY e.event_date DESC");
                                    while($event = $events->fetch()):
                                    ?>
                                    <tr>
                                        <td><span class="badge bg-primary"><?= htmlspecialchars($event['club_name']) ?></span></td>
                                        <td><strong><?= htmlspecialchars($event['title']) ?></strong></td>
                                        <td><?= date('M d, Y', strtotime($event['event_date'])) ?></td>
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

        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <i class="fas fa-clipboard-check me-2"></i>Registration Approvals
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th><i class="fas fa-user me-1"></i>Student</th>
                                        <th><i class="fas fa-users me-1"></i>Club</th>
                                        <th><i class="fas fa-info-circle me-1"></i>Status</th>
                                        <th><i class="fas fa-cogs me-1"></i>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                        <?php
                        $stmt = $pdo->query("
                            SELECT r.id, r.status, u.username, c.name as club_name 
                            FROM registrations r 
                            JOIN users u ON r.user_id = u.id 
                            JOIN clubs c ON r.club_id = c.id
                            ORDER BY r.status, r.id DESC
                        ");
                        while($reg = $stmt->fetch()):
                        ?>
                                        <tr>
                                            <td><strong><?= htmlspecialchars($reg['username']) ?></strong></td>
                                            <td><span class="badge bg-info"><?= htmlspecialchars($reg['club_name']) ?></span></td>
                                            <td>
                                                <span class="badge bg-<?= $reg['status'] == 'approved' ? 'success' : ($reg['status'] == 'rejected' ? 'danger' : 'warning') ?>">
                                                    <i class="fas fa-<?= $reg['status'] == 'approved' ? 'check' : ($reg['status'] == 'rejected' ? 'times' : 'clock') ?>"></i>
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
                                                <?php else: ?>
                                                <span class="text-muted">No action needed</span>
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
    </div>

    <!-- Edit Club Modal -->
    <div class="modal fade" id="editClubModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" enctype="multipart/form-data">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Club</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_club">
                        <input type="hidden" name="club_id" id="edit_club_id">
                        <div class="mb-3">
                            <label class="form-label">Club Name</label>
                            <input type="text" name="name" id="edit_club_name" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="edit_club_desc" class="form-control"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Change Image (optional)</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <small class="text-muted">Leave empty to keep current image</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update Club</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Edit User Modal -->
    <div class="modal fade" id="editUserModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit User</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="action" value="edit_user">
                        <input type="hidden" name="user_id" id="edit_user_id">
                        <div class="mb-3">
                            <input type="text" name="username" id="edit_username" class="form-control" placeholder="Username" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Update User</button>
                    </div>
                </form>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function editClub(id, name, desc) {
            document.getElementById('edit_club_id').value = id;
            document.getElementById('edit_club_name').value = name;
            document.getElementById('edit_club_desc').value = desc;
            new bootstrap.Modal(document.getElementById('editClubModal')).show();
        }
        
        function editUser(id, username) {
            document.getElementById('edit_user_id').value = id;
            document.getElementById('edit_username').value = username;
            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        }
        
        function editEvent(id, title, desc, date) {
            document.getElementById('edit_event_id').value = id;
            document.getElementById('edit_event_title').value = title;
            document.getElementById('edit_event_desc').value = desc;
            document.getElementById('edit_event_date').value = date;
            new bootstrap.Modal(document.getElementById('editEventModal')).show();
        }
    </script>
</body>
</html>
