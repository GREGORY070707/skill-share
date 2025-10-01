<?php
session_start();
require_once 'config/database.php';

$error = '';
$success = '';

// Check if already logged in
if (isset($_SESSION['user'])) {
    header("Location: dashboard.php");
    exit;
}

// Get role from URL parameter if present
$preselected_role = isset($_GET['role']) ? htmlspecialchars($_GET['role']) : '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmpassword = $_POST['confirmpassword'] ?? '';
    $role = trim($_POST['role'] ?? '');
    
    // Validation
    if (empty($fullname) || empty($email) || empty($password) || empty($confirmpassword) || empty($role)) {
        $error = 'All required fields must be filled.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirmpassword) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters long.';
    } else {
        $conn = getDBConnection();
        
        // Check if email already exists
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->store_result();
        
        if ($stmt->num_rows > 0) {
            $error = 'Email already registered. Please login or use a different email.';
            $stmt->close();
        } else {
            $stmt->close();
            
            // Hash password
            $hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insert user
            $ins = $conn->prepare('INSERT INTO users (fullname, email, phone, password, role, status) VALUES (?, ?, ?, ?, ?, ?)');
            $status = 'active';
            $ins->bind_param('ssssss', $fullname, $email, $phone, $hash, $role, $status);
            
            if ($ins->execute()) {
                $ins->close();
                $conn->close();
                header('Location: login.php?registered=1');
                exit;
            } else {
                $error = 'Registration failed. Please try again.';
                $ins->close();
            }
        }
        
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | Skill-Sharing Network</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        
        .register-container {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
        }
        
        .register-box {
            background: white;
            padding: 3rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
            max-width: 550px;
            width: 100%;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .register-header h1 {
            font-size: 2rem;
            font-weight: 800;
            color: var(--gray-900);
            margin-bottom: 0.5rem;
        }
        
        .register-header p {
            color: var(--gray-600);
        }
        
        .form-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
        }
        
        .btn-full {
            width: 100%;
            margin-top: 1rem;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
        }
        
        .divider {
            text-align: center;
            margin: 1.5rem 0;
            position: relative;
        }
        
        .divider::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            height: 1px;
            background: var(--gray-300);
        }
        
        .divider span {
            background: white;
            padding: 0 1rem;
            position: relative;
            color: var(--gray-500);
            font-size: 0.875rem;
        }
        
        .links {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .links a {
            color: var(--primary);
            text-decoration: none;
            font-weight: 600;
        }
        
        .links a:hover {
            text-decoration: underline;
        }
        
        @media (max-width: 600px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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
                    <li><a href="index.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="workshops.php"><i class="fas fa-chalkboard-teacher"></i> Workshops</a></li>
                    <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="register-container">
        <div class="register-box">
            <div class="form-icon">
                <i class="fas fa-user-plus"></i>
            </div>
            
            <div class="register-header">
                <h1>Join SkillShare</h1>
                <p>Create your account and start learning today</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="register.php">
                <div class="form-group">
                    <label for="fullname"><i class="fas fa-user"></i> Full Name</label>
                    <input type="text" id="fullname" name="fullname" class="form-control" 
                           placeholder="Enter your full name" required 
                           value="<?php echo isset($_POST['fullname']) ? htmlspecialchars($_POST['fullname']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="email"><i class="fas fa-envelope"></i> Email</label>
                        <input type="email" id="email" name="email" class="form-control" 
                               placeholder="your@email.com" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone"><i class="fas fa-phone"></i> Phone</label>
                        <input type="tel" id="phone" name="phone" class="form-control" 
                               placeholder="Phone number" 
                               value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" class="form-control" 
                               placeholder="Min. 6 characters" required>
                    </div>

                    <div class="form-group">
                        <label for="confirmpassword"><i class="fas fa-lock"></i> Confirm</label>
                        <input type="password" id="confirmpassword" name="confirmpassword" class="form-control" 
                               placeholder="Confirm password" required>
                    </div>
                </div>

                <div class="form-group">
                    <label for="role"><i class="fas fa-user-tag"></i> Select Role</label>
                    <select id="role" name="role" class="form-control" required>
                        <option value="">-- Choose Your Role --</option>
                        <option value="student" <?php echo ($preselected_role === 'student' || (isset($_POST['role']) && $_POST['role'] === 'student')) ? 'selected' : ''; ?>>Student - Learn New Skills</option>
                        <option value="teacher" <?php echo ($preselected_role === 'teacher' || (isset($_POST['role']) && $_POST['role'] === 'teacher')) ? 'selected' : ''; ?>>Teacher - Share Knowledge</option>
                        <option value="ngo" <?php echo ($preselected_role === 'ngo' || (isset($_POST['role']) && $_POST['role'] === 'ngo')) ? 'selected' : ''; ?>>NGO - Partner With Us</option>
                        <option value="admin" <?php echo ($preselected_role === 'admin' || (isset($_POST['role']) && $_POST['role'] === 'admin')) ? 'selected' : ''; ?>>Admin - Manage Platform</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-full">
                    <i class="fas fa-rocket"></i> Create Account
                </button>
            </form>

            <div class="divider">
                <span>OR</span>
            </div>

            <div class="links">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
