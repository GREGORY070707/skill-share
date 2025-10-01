<?php
session_start();
require_once 'config/database.php';

$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($workshop_id <= 0) {
    header("Location: workshops.php");
    exit;
}

$conn = getDBConnection();

// Get workshop details
$query = "SELECT w.*, u.fullname as teacher_name, u.email as teacher_email,
          (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
          FROM workshops w 
          LEFT JOIN users u ON w.teacher_id = u.id 
          WHERE w.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$workshop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$workshop) {
    header("Location: workshops.php");
    exit;
}

// Check if user is enrolled
$is_enrolled = false;
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
    $check_stmt = $conn->prepare("SELECT id FROM enrollments WHERE student_id = ? AND workshop_id = ?");
    $check_stmt->bind_param("ii", $user_id, $workshop_id);
    $check_stmt->execute();
    $is_enrolled = $check_stmt->get_result()->num_rows > 0;
    $check_stmt->close();
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($workshop['title']); ?> | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .workshop-hero {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.9), rgba(139, 92, 246, 0.9));
            color: white;
            padding: 3rem 2rem;
        }
        
        .workshop-content {
            max-width: 1200px;
            margin: 0 auto;
        }
        
        .workshop-grid {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .info-box {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 1.5rem;
        }
        
        .info-row {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.75rem 0;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .info-row:last-child {
            border-bottom: none;
        }
        
        .info-icon {
            width: 40px;
            height: 40px;
            background: var(--primary-light);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        @media (max-width: 768px) {
            .workshop-grid {
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
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Get Started</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="workshop-hero">
        <div class="workshop-content">
            <div style="margin-bottom: 1rem;">
                <a href="workshops.php" style="color: white; text-decoration: none; opacity: 0.9;">
                    <i class="fas fa-arrow-left"></i> Back to Workshops
                </a>
            </div>
            <h1 style="font-size: 2.5rem; margin-bottom: 1rem;"><?php echo htmlspecialchars($workshop['title']); ?></h1>
            <div style="display: flex; gap: 1rem; flex-wrap: wrap;">
                <?php if ($workshop['category']): ?>
                    <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem;">
                        <i class="fas fa-tag"></i> <?php echo htmlspecialchars($workshop['category']); ?>
                    </span>
                <?php endif; ?>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem;">
                    <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i> 
                    <?php echo ucfirst($workshop['mode']); ?>
                </span>
                <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem;">
                    <i class="fas fa-users"></i> 
                    <?php echo $workshop['enrolled_count']; ?>/<?php echo $workshop['max_participants']; ?> Enrolled
                </span>
            </div>
        </div>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <div class="workshop-grid">
            <!-- Main Content -->
            <div>
                <div class="info-box">
                    <h2 style="color: var(--gray-900); margin-bottom: 1rem;">
                        <i class="fas fa-info-circle"></i> About This Workshop
                    </h2>
                    <p style="color: var(--gray-700); line-height: 1.8;">
                        <?php echo nl2br(htmlspecialchars($workshop['description'])); ?>
                    </p>
                </div>

                <div class="info-box">
                    <h2 style="color: var(--gray-900); margin-bottom: 1rem;">
                        <i class="fas fa-user-tie"></i> Instructor
                    </h2>
                    <div style="display: flex; align-items: center; gap: 1rem;">
                        <div style="width: 60px; height: 60px; background: linear-gradient(135deg, var(--primary), var(--secondary)); 
                                    border-radius: 50%; display: flex; align-items: center; justify-content: center; 
                                    color: white; font-size: 1.5rem; font-weight: 700;">
                            <?php echo strtoupper(substr($workshop['teacher_name'] ?? 'T', 0, 1)); ?>
                        </div>
                        <div>
                            <h3 style="margin: 0; color: var(--gray-900);">
                                <?php echo htmlspecialchars($workshop['teacher_name'] ?? 'To Be Announced'); ?>
                            </h3>
                            <p style="margin: 0.25rem 0 0 0; color: var(--gray-600);">
                                <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($workshop['teacher_email'] ?? 'N/A'); ?>
                            </p>
                        </div>
                    </div>
                </div>

                <?php if ($workshop['location'] && $workshop['mode'] !== 'online'): ?>
                <div class="info-box">
                    <h2 style="color: var(--gray-900); margin-bottom: 1rem;">
                        <i class="fas fa-map-marker-alt"></i> Location
                    </h2>
                    <p style="color: var(--gray-700);">
                        <?php echo htmlspecialchars($workshop['location']); ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Sidebar -->
            <div>
                <div class="info-box">
                    <h3 style="color: var(--gray-900); margin-bottom: 1rem;">Workshop Details</h3>
                    
                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-calendar"></i>
                        </div>
                        <div>
                            <strong>Start Date</strong><br>
                            <span style="color: var(--gray-600);">
                                <?php echo date('F d, Y', strtotime($workshop['start_date'])); ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div>
                            <strong>Time</strong><br>
                            <span style="color: var(--gray-600);">
                                <?php echo date('h:i A', strtotime($workshop['start_date'])); ?> - 
                                <?php echo date('h:i A', strtotime($workshop['end_date'])); ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <strong>Participants</strong><br>
                            <span style="color: var(--gray-600);">
                                <?php echo $workshop['enrolled_count']; ?> / <?php echo $workshop['max_participants']; ?>
                            </span>
                        </div>
                    </div>

                    <div class="info-row">
                        <div class="info-icon">
                            <i class="fas fa-signal"></i>
                        </div>
                        <div>
                            <strong>Status</strong><br>
                            <span class="badge badge-success"><?php echo ucfirst($workshop['status']); ?></span>
                        </div>
                    </div>
                </div>

                <!-- Enrollment Action -->
                <div class="info-box" style="text-align: center;">
                    <?php if (isset($_SESSION['user']) && $_SESSION['user_role'] === 'student'): ?>
                        <?php if ($is_enrolled): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i> You are enrolled!
                            </div>
                            <a href="student-dashboard.php" class="btn btn-primary" style="width: 100%;">
                                <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                            </a>
                        <?php elseif ($workshop['enrolled_count'] >= $workshop['max_participants']): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i> Workshop is full
                            </div>
                        <?php else: ?>
                            <a href="enroll.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary" style="width: 100%; font-size: 1.1rem;">
                                <i class="fas fa-plus-circle"></i> Enroll Now
                            </a>
                            <p style="margin-top: 1rem; color: var(--gray-600); font-size: 0.875rem;">
                                <?php echo ($workshop['max_participants'] - $workshop['enrolled_count']); ?> spots remaining
                            </p>
                        <?php endif; ?>
                    <?php elseif (!isset($_SESSION['user'])): ?>
                        <p style="color: var(--gray-600); margin-bottom: 1rem;">
                            Please login to enroll in this workshop
                        </p>
                        <a href="login.php" class="btn btn-primary" style="width: 100%;">
                            <i class="fas fa-sign-in-alt"></i> Login to Enroll
                        </a>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Only students can enroll
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
