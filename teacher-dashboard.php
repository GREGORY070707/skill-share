<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Get success/error messages from session
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

$conn = getDBConnection();

// Get total workshops count
$total_query = "SELECT COUNT(*) as count FROM workshops WHERE teacher_id = ?";
$stmt = $conn->prepare($total_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_workshops = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get upcoming workshops count
$upcoming_query = "SELECT COUNT(*) as count FROM workshops WHERE teacher_id = ? AND start_date > NOW() AND status = 'approved'";
$stmt = $conn->prepare($upcoming_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$upcoming_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get total students enrolled
$students_query = "SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e 
                   JOIN workshops w ON e.workshop_id = w.id 
                   WHERE w.teacher_id = ?";
$stmt = $conn->prepare($students_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_students = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get my workshops
$workshops_query = "SELECT w.*, 
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
                    FROM workshops w 
                    WHERE w.teacher_id = ?
                    ORDER BY w.start_date DESC";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Teacher Dashboard | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-nav {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-lg);
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            color: #f5576c;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .workshop-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border-left: 4px solid #f5576c;
        }
        
        .workshop-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .workshop-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .workshop-meta {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        .status-pending {
            background: var(--warning);
            color: white;
        }
        
        .status-approved {
            background: var(--success);
            color: white;
        }
        
        .status-completed {
            background: var(--gray-500);
            color: white;
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
                    <li><a href="teacher-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="dashboard-nav">
        <div class="user-info">
            <div class="user-avatar">
                <?php echo strtoupper(substr($user['fullname'], 0, 1)); ?>
            </div>
            <div>
                <h2 style="margin: 0; font-size: 1.5rem;">Welcome, <?php echo htmlspecialchars($user['fullname']); ?>!</h2>
                <p style="margin: 0; opacity: 0.9;">Teacher Dashboard</p>
            </div>
        </div>
        <div>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                <i class="fas fa-chalkboard-teacher"></i> Teacher
            </span>
        </div>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_workshops; ?></div>
                <div class="stat-label"><i class="fas fa-chalkboard"></i> Total Workshops</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $upcoming_count; ?></div>
                <div class="stat-label"><i class="fas fa-calendar-check"></i> Upcoming</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_students; ?></div>
                <div class="stat-label"><i class="fas fa-users"></i> Total Students</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_workshops - $upcoming_count; ?></div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Completed</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <section style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="section-title" style="text-align: left; color: var(--gray-900); margin: 0;">
                    <i class="fas fa-bolt"></i> Quick Actions
                </h2>
            </div>
            
            <div class="card-grid" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <a href="create-workshop.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-plus-circle"></i>
                    </div>
                    <h3>Create Workshop</h3>
                    <p>Start a new workshop session</p>
                </a>
                
                <a href="my-students.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--secondary); margin-bottom: 1rem;">
                        <i class="fas fa-user-graduate"></i>
                    </div>
                    <h3>My Students</h3>
                    <p>View enrolled students</p>
                </a>
                
                <a href="upload-resource.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;">
                        <i class="fas fa-upload"></i>
                    </div>
                    <h3>Upload Resources</h3>
                    <p>Share materials with students</p>
                </a>
            </div>
        </section>

        <!-- My Workshops -->
        <section style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="section-title" style="text-align: left; color: var(--gray-900); margin: 0;">
                    <i class="fas fa-chalkboard"></i> My Workshops
                </h2>
                <a href="create-workshop.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Create New
                </a>
            </div>
            
            <?php if ($workshops->num_rows > 0): ?>
                <div class="card-grid">
                    <?php while ($workshop = $workshops->fetch_assoc()): ?>
                        <div class="workshop-card">
                            <div class="workshop-header">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
                                    <span class="badge status-<?php echo $workshop['status']; ?>">
                                        <?php echo ucfirst($workshop['status']); ?>
                                    </span>
                                    <?php if ($workshop['category']): ?>
                                        <span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <p style="color: var(--gray-600); margin: 0.5rem 0;">
                                <?php echo htmlspecialchars(substr($workshop['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="workshop-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($workshop['start_date'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <?php echo $workshop['enrolled_count']; ?> Students
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i>
                                    <?php echo ucfirst($workshop['mode']); ?>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="edit-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="workshop-students.php?id=<?php echo $workshop['id']; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-users"></i> Students (<?php echo $workshop['enrolled_count']; ?>)
                                </a>
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't created any workshops yet. Click "Create New" to get started!
                </div>
            <?php endif; ?>
        </section>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
