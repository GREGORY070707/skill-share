<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'student') {
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

// Get enrolled workshops count
$enrolled_query = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND status = 'enrolled'";
$stmt = $conn->prepare($enrolled_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$enrolled_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get completed workshops count
$completed_query = "SELECT COUNT(*) as count FROM enrollments WHERE student_id = ? AND status = 'completed'";
$stmt = $conn->prepare($completed_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get available workshops
$workshops_query = "SELECT w.*, u.fullname as teacher_name, 
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id AND e.student_id = ?) as is_enrolled
                    FROM workshops w 
                    LEFT JOIN users u ON w.teacher_id = u.id 
                    WHERE w.status = 'approved' AND w.start_date > NOW()
                    ORDER BY w.start_date ASC LIMIT 6";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get enrolled workshops
$my_workshops_query = "SELECT w.*, u.fullname as teacher_name, e.enrollment_date
                       FROM enrollments e
                       JOIN workshops w ON e.workshop_id = w.id
                       LEFT JOIN users u ON w.teacher_id = u.id
                       WHERE e.student_id = ? AND e.status = 'enrolled'
                       ORDER BY w.start_date ASC";
$stmt = $conn->prepare($my_workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$my_workshops = $stmt->get_result();
$stmt->close();

// Get resources for ONLY enrolled workshops
$resources_query = "SELECT r.*, w.title as workshop_title
                    FROM resources r
                    JOIN workshops w ON r.workshop_id = w.id
                    JOIN enrollments e ON e.workshop_id = w.id
                    WHERE e.student_id = ? AND e.status = 'enrolled'
                    ORDER BY r.uploaded_at DESC LIMIT 10";
$stmt = $conn->prepare($resources_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$resources = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }
        
        @keyframes pulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.05);
            }
        }
        
        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }
        
        .dashboard-nav {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(102, 126, 234, 0.4);
            animation: fadeInUp 0.6s ease;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 1rem;
            animation: slideInLeft 0.8s ease;
        }
        
        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            color: var(--primary);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }
        
        .user-avatar:hover {
            transform: rotate(360deg) scale(1.1);
        }
        
        .workshop-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            border-left: 4px solid var(--primary);
            position: relative;
            overflow: hidden;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }
        
        .workshop-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s ease;
        }
        
        .workshop-card:hover::before {
            left: 100%;
        }
        
        .workshop-card:hover {
            transform: translateY(-8px) scale(1.02);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.25);
            border-left-width: 6px;
        }
        
        .workshop-card:nth-child(1) { animation-delay: 0.1s; }
        .workshop-card:nth-child(2) { animation-delay: 0.2s; }
        .workshop-card:nth-child(3) { animation-delay: 0.3s; }
        .workshop-card:nth-child(4) { animation-delay: 0.4s; }
        .workshop-card:nth-child(5) { animation-delay: 0.5s; }
        .workshop-card:nth-child(6) { animation-delay: 0.6s; }
        
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
            transition: all 0.3s ease;
        }
        
        .meta-item:hover {
            color: var(--primary);
            transform: translateX(3px);
        }
        
        .meta-item i {
            transition: transform 0.3s ease;
        }
        
        .meta-item:hover i {
            transform: scale(1.2) rotate(5deg);
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .btn-sm::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }
        
        .btn-sm:hover::before {
            width: 300px;
            height: 300px;
        }
        
        .section-title {
            position: relative;
            display: inline-block;
            animation: fadeInUp 0.8s ease;
        }
        
        .section-title::after {
            content: '';
            position: absolute;
            bottom: -8px;
            left: 0;
            width: 60px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 2px;
            animation: slideInLeft 1s ease;
        }
        
        .stat-card {
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
            transition: all 0.3s ease;
        }
        
        .stat-card:nth-child(1) { animation-delay: 0.1s; }
        .stat-card:nth-child(2) { animation-delay: 0.2s; }
        .stat-card:nth-child(3) { animation-delay: 0.3s; }
        .stat-card:nth-child(4) { animation-delay: 0.4s; }
        
        .stat-card:hover {
            transform: translateY(-5px) scale(1.05);
        }
        
        .stat-value {
            animation: pulse 2s ease-in-out infinite;
        }
        
        .alert {
            animation: fadeInUp 0.6s ease;
            border-left: 4px solid;
            padding: 1rem 1.5rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }
        
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border-left-color: #10b981;
        }
        
        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border-left-color: #ef4444;
        }
        
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border-left-color: #3b82f6;
        }
        
        .badge {
            transition: all 0.3s ease;
        }
        
        .badge:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        
        body {
            scroll-behavior: smooth;
            background: #f9fafb;
        }
        
        .container {
            position: relative;
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        
        .card-grid {
            animation: fadeInUp 0.8s ease;
        }
        
        section {
            opacity: 0;
            animation: fadeInUp 0.8s ease forwards;
        }
        
        section:nth-of-type(1) { animation-delay: 0.2s; }
        section:nth-of-type(2) { animation-delay: 0.3s; }
        section:nth-of-type(3) { animation-delay: 0.4s; }
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
                    <li><a href="student-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                <p style="margin: 0; opacity: 0.9;">Student Dashboard</p>
            </div>
        </div>
        <div>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                <i class="fas fa-user-graduate"></i> Student
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
                <div class="stat-value"><?php echo $enrolled_count; ?></div>
                <div class="stat-label"><i class="fas fa-book-open"></i> Enrolled Workshops</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completed_count; ?></div>
                <div class="stat-label"><i class="fas fa-check-circle"></i> Completed</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $workshops->num_rows; ?></div>
                <div class="stat-label"><i class="fas fa-calendar-alt"></i> Available</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $completed_count; ?></div>
                <div class="stat-label"><i class="fas fa-certificate"></i> Certificates</div>
            </div>
        </div>

        <!-- My Enrolled Workshops -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 0.5rem;">
                <i class="fas fa-book-reader" style="color: var(--primary);"></i> My Enrolled Workshops
            </h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 1rem;">
                Your currently enrolled workshops and learning progress
            </p>
            
            <?php if ($my_workshops->num_rows > 0): ?>
                <div class="card-grid" style="margin-top: 1.5rem;">
                    <?php while ($workshop = $my_workshops->fetch_assoc()): ?>
                        <div class="workshop-card">
                            <div class="workshop-header">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
                                    <span class="badge badge-success">Enrolled</span>
                                </div>
                            </div>
                            <p style="color: var(--gray-600); margin: 0.5rem 0;">
                                <?php echo htmlspecialchars(substr($workshop['description'], 0, 100)) . '...'; ?>
                            </p>
                            <div class="workshop-meta">
                                <div class="meta-item">
                                    <i class="fas fa-user-tie"></i>
                                    <?php echo htmlspecialchars($workshop['teacher_name'] ?? 'TBA'); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('h:i A', strtotime($workshop['start_date'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i>
                                    <?php echo ucfirst($workshop['mode']); ?>
                                </div>
                            </div>
                            <div style="margin-top: 1rem;">
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-top: 1.5rem; background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 8px;">
                    <i class="fas fa-info-circle"></i> You haven't enrolled in any workshops yet. Browse available workshops below!
                </div>
            <?php endif; ?>
        </section>

        <!-- Available Workshops -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 0.5rem;">
                <i class="fas fa-compass" style="color: var(--secondary);"></i> Available Workshops
            </h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 1rem;">
                Discover new workshops and expand your skills
            </p>
            
            <?php if ($workshops->num_rows > 0): ?>
                <div class="card-grid" style="margin-top: 1.5rem;">
                    <?php while ($workshop = $workshops->fetch_assoc()): ?>
                        <div class="workshop-card">
                            <div class="workshop-header">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
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
                                    <i class="fas fa-user-tie"></i>
                                    <?php echo htmlspecialchars($workshop['teacher_name'] ?? 'TBA'); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <?php echo $workshop['current_participants']; ?>/<?php echo $workshop['max_participants']; ?>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                <?php if ($workshop['is_enrolled'] > 0): ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-check"></i> Already Enrolled
                                    </button>
                                <?php else: ?>
                                    <a href="enroll.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Enroll Now
                                    </a>
                                <?php endif; ?>
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
                
                <div style="text-align: center; margin-top: 2rem;">
                    <a href="workshops.php" class="btn btn-primary">
                        <i class="fas fa-th"></i> View All Workshops
                    </a>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-top: 1.5rem; background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 8px;">
                    <i class="fas fa-info-circle"></i> No workshops available at the moment. Check back soon!
                </div>
            <?php endif; ?>
        </section>

        <!-- Learning Resources Section - ONLY for enrolled workshops -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 1rem;">
                <i class="fas fa-book" style="color: #10b981;"></i> My Learning Resources
            </h2>
            <p style="color: #6b7280; margin-bottom: 1.5rem; font-size: 1rem;">
                Resources from your enrolled workshops only
            </p>
            
            <?php if ($resources->num_rows > 0): ?>
                <div class="card-grid">
                    <?php while ($resource = $resources->fetch_assoc()): ?>
                        <div class="card workshop-card" style="border-left: 4px solid var(--secondary);">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-weight: 700;">
                                        <?php echo htmlspecialchars($resource['title']); ?>
                                    </h3>
                                    <span class="badge badge-primary">
                                        <?php echo htmlspecialchars($resource['workshop_title']); ?>
                                    </span>
                                </div>
                                <div style="font-size: 2rem; color: var(--secondary); transition: transform 0.3s ease;">
                                    <i class="fas fa-<?php 
                                        echo $resource['file_type'] === 'video' ? 'video' : 
                                            ($resource['file_type'] === 'presentation' ? 'file-powerpoint' : 'file-pdf'); 
                                    ?>"></i>
                                </div>
                            </div>
                            
                            <?php if ($resource['description']): ?>
                                <p style="color: var(--gray-600); margin: 0.5rem 0;">
                                    <?php echo htmlspecialchars($resource['description']); ?>
                                </p>
                            <?php endif; ?>
                            
                            <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--gray-200);">
                                <div style="display: flex; justify-content: space-between; align-items: center;">
                                    <small style="color: var(--gray-500);">
                                        <i class="fas fa-clock"></i> 
                                        <?php echo date('M d, Y', strtotime($resource['uploaded_at'])); ?>
                                    </small>
                                    <a href="<?php echo htmlspecialchars($resource['file_path']); ?>" 
                                       target="_blank" class="btn btn-primary btn-sm">
                                        <i class="fas fa-download"></i> Access Resource
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="background: #dbeafe; color: #1e40af; border-left: 4px solid #3b82f6; padding: 1rem; border-radius: 8px;">
                    <i class="fas fa-info-circle"></i> No resources available yet. Your teachers will upload materials for your enrolled workshops.
                </div>
            <?php endif; ?>
        </section>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
