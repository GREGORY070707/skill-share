<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($user_id <= 0) {
    $_SESSION['error'] = "Invalid user ID.";
    header("Location: manage-users.php");
    exit;
}

$conn = getDBConnection();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $role = $_POST['role'];
    $status = $_POST['status'];
    $bio = trim($_POST['bio']);
    
    // Validate inputs
    if (empty($fullname) || empty($email) || empty($role) || empty($status)) {
        $_SESSION['error'] = "Please fill in all required fields.";
    } else {
        // Check if email already exists for another user
        $check_query = "SELECT id FROM users WHERE email = ? AND id != ?";
        $stmt = $conn->prepare($check_query);
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $_SESSION['error'] = "Email already exists for another user.";
            $stmt->close();
        } else {
            $stmt->close();
            
            // Update user
            $update_query = "UPDATE users SET fullname = ?, email = ?, phone = ?, role = ?, status = ?, bio = ? WHERE id = ?";
            $stmt = $conn->prepare($update_query);
            $stmt->bind_param("ssssssi", $fullname, $email, $phone, $role, $status, $bio, $user_id);
            
            if ($stmt->execute()) {
                $_SESSION['success'] = "User updated successfully!";
                header("Location: manage-users.php");
                exit;
            } else {
                $_SESSION['error'] = "Failed to update user.";
            }
            $stmt->close();
        }
    }
}

// Get user data
$query = "SELECT * FROM users WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "User not found.";
    $stmt->close();
    $conn->close();
    header("Location: manage-users.php");
    exit;
}

$user_data = $result->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User | Admin</title>
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
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
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
            <h1 style="margin: 0; font-size: 2rem;"><i class="fas fa-user-edit"></i> Edit User</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">Update user information and settings</p>
        </div>
    </div>

    <div class="container">
        <div style="margin-bottom: 1.5rem;">
            <a href="manage-users.php" style="color: #667eea; text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Users
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
                    <label for="fullname">Full Name <span style="color: red;">*</span></label>
                    <input type="text" id="fullname" name="fullname" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['fullname']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span style="color: red;">*</span></label>
                    <input type="email" id="email" name="email" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Phone Number</label>
                    <input type="tel" id="phone" name="phone" class="form-control" 
                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="role">Role <span style="color: red;">*</span></label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="student" <?php echo $user_data['role'] === 'student' ? 'selected' : ''; ?>>Student</option>
                        <option value="teacher" <?php echo $user_data['role'] === 'teacher' ? 'selected' : ''; ?>>Teacher</option>
                        <option value="ngo" <?php echo $user_data['role'] === 'ngo' ? 'selected' : ''; ?>>NGO</option>
                        <option value="admin" <?php echo $user_data['role'] === 'admin' ? 'selected' : ''; ?>>Admin</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="status">Status <span style="color: red;">*</span></label>
                    <select id="status" name="status" class="form-control" required>
                        <option value="active" <?php echo $user_data['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                        <option value="inactive" <?php echo $user_data['status'] === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
                        <option value="pending" <?php echo $user_data['status'] === 'pending' ? 'selected' : ''; ?>>Pending</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="bio">Bio</label>
                    <textarea id="bio" name="bio" class="form-control" rows="4"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Update User
                    </button>
                    <a href="manage-users.php" class="btn btn-secondary">
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
