<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($workshop_id <= 0) {
    $_SESSION['error'] = "Invalid workshop ID.";
    header("Location: manage-workshops.php");
    exit;
}

$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $category = trim($_POST['category']);
    $mode = $_POST['mode'];
    $location = trim($_POST['location']);
    $start_date = $_POST['start_date'];
    $start_time = $_POST['start_time'];
    $end_date = $_POST['end_date'];
    $end_time = $_POST['end_time'];
    $max_participants = intval($_POST['max_participants']);
    $status = $_POST['status'];
    
    // Combine date and time
    $start_datetime = $start_date . ' ' . $start_time;
    $end_datetime = $end_date . ' ' . $end_time;
    
    // Validate
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        // Update workshop
        $update_query = "UPDATE workshops SET title = ?, description = ?, category = ?, mode = ?, 
                        location = ?, start_date = ?, end_date = ?, max_participants = ?, status = ? 
                        WHERE id = ?";
        $stmt = $conn->prepare($update_query);
        $stmt->bind_param("sssssssssi", $title, $description, $category, $mode, $location, 
                         $start_datetime, $end_datetime, $max_participants, $status, $workshop_id);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Workshop updated successfully!";
            header("Location: manage-workshops.php");
            exit;
        } else {
            $_SESSION['error'] = "Failed to update workshop.";
        }
        $stmt->close();
    }
}

// Get workshop data
$query = "SELECT w.*, u.fullname as teacher_name FROM workshops w 
          LEFT JOIN users u ON w.teacher_id = u.id WHERE w.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Workshop not found.";
    $stmt->close();
    $conn->close();
    header("Location: manage-workshops.php");
    exit;
}

$workshop = $result->fetch_assoc();
$stmt->close();
$conn->close();

// Extract date and time
$start_date = date('Y-m-d', strtotime($workshop['start_date']));
$start_time = date('H:i', strtotime($workshop['start_date']));
$end_date = date('Y-m-d', strtotime($workshop['end_date']));
$end_time = date('H:i', strtotime($workshop['end_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Workshop | Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f9fafb;
        }
        
        .page-header {
            background: linear-gradient(135deg, #434343 0%, #000000 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .form-container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <i class="fas fa-graduation-cap"></i> SkillShare
            </div>
            <nav>
                <ul>
                    <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Users</a></li>
                    <li><a href="manage-workshops.php"><i class="fas fa-chalkboard"></i> Workshops</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1 style="margin: 0; font-size: 2rem;"><i class="fas fa-edit"></i> Edit Workshop</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Update workshop information and settings</p>
        </div>
    </div>

    <div class="container">
        <div style="margin-bottom: 1.5rem;">
            <a href="manage-workshops.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Workshops
            </a>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="title">Workshop Title <span style="color: red;">*</span></label>
                    <input type="text" id="title" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($workshop['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Description <span style="color: red;">*</span></label>
                    <textarea id="description" name="description" class="form-control" rows="4" required><?php echo htmlspecialchars($workshop['description']); ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category">Category</label>
                        <input type="text" id="category" name="category" class="form-control" 
                               value="<?php echo htmlspecialchars($workshop['category'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label for="mode">Mode <span style="color: red;">*</span></label>
                        <select id="mode" name="mode" class="form-control" required>
                            <option value="online" <?php echo $workshop['mode'] === 'online' ? 'selected' : ''; ?>>Online</option>
                            <option value="offline" <?php echo $workshop['mode'] === 'offline' ? 'selected' : ''; ?>>Offline</option>
                            <option value="hybrid" <?php echo $workshop['mode'] === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" class="form-control" 
                           value="<?php echo htmlspecialchars($workshop['location'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date">Start Date <span style="color: red;">*</span></label>
                        <input type="date" id="start_date" name="start_date" class="form-control" 
                               value="<?php echo $start_date; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="start_time">Start Time <span style="color: red;">*</span></label>
                        <input type="time" id="start_time" name="start_time" class="form-control" 
                               value="<?php echo $start_time; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="end_date">End Date <span style="color: red;">*</span></label>
                        <input type="date" id="end_date" name="end_date" class="form-control" 
                               value="<?php echo $end_date; ?>" required>
                    </div>

                    <div class="form-group">
                        <label for="end_time">End Time <span style="color: red;">*</span></label>
                        <input type="time" id="end_time" name="end_time" class="form-control" 
                               value="<?php echo $end_time; ?>" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="max_participants">Max Participants <span style="color: red;">*</span></label>
                        <input type="number" id="max_participants" name="max_participants" class="form-control" 
                               value="<?php echo $workshop['max_participants']; ?>" min="1" required>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span style="color: red;">*</span></label>
                        <select id="status" name="status" class="form-control" required>
                            <option value="pending" <?php echo $workshop['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $workshop['status'] === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="ongoing" <?php echo $workshop['status'] === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $workshop['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $workshop['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                </div>

                <div style="background: #f9fafb; padding: 1rem; border-radius: 8px; margin-top: 1rem;">
                    <strong>Teacher:</strong> <?php echo htmlspecialchars($workshop['teacher_name'] ?? 'N/A'); ?>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update Workshop
                    </button>
                    <a href="manage-workshops.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
