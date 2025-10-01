<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an NGO
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'ngo') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$user_id = $user['id'];

// Get success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

$conn = getDBConnection();

// Get partnered workshops
$partnered_query = "SELECT w.*, u.fullname as teacher_name, u.email as teacher_email,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
                    FROM workshops w 
                    LEFT JOIN users u ON w.teacher_id = u.id
                    WHERE w.ngo_id = ?
                    ORDER BY w.start_date DESC";
$stmt = $conn->prepare($partnered_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$partnered_workshops = $stmt->get_result();
$stmt->close();

// Get available workshops to partner with
$available_query = "SELECT w.*, u.fullname as teacher_name, u.email as teacher_email,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
                    FROM workshops w 
                    LEFT JOIN users u ON w.teacher_id = u.id
                    WHERE w.ngo_id IS NULL AND w.status = 'approved' AND w.start_date > NOW()
                    ORDER BY w.start_date ASC";
$available_workshops = $conn->query($available_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Partner Workshops | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .page-header {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .workshop-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            transition: all 0.4s ease;
            border-left: 4px solid #fa709a;
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }
        
        .workshop-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 12px 35px rgba(250, 112, 154, 0.25);
        }
        
        .workshop-card:nth-child(1) { animation-delay: 0.1s; }
        .workshop-card:nth-child(2) { animation-delay: 0.2s; }
        .workshop-card:nth-child(3) { animation-delay: 0.3s; }
        .workshop-card:nth-child(4) { animation-delay: 0.4s; }
        
        .section-divider {
            margin: 3rem 0;
            border-top: 2px solid #e5e7eb;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
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
                    <li><a href="ngo-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="icon-circle">
            <i class="fas fa-handshake"></i>
        </div>
        <h1>Partner Workshops</h1>
        <p>Collaborate with teachers to bring quality education to communities</p>
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

        <!-- Partnered Workshops -->
        <section>
            <h2 style="color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 1.5rem;">
                <i class="fas fa-check-circle" style="color: #10b981;"></i> Your Partnered Workshops
            </h2>
            
            <?php if ($partnered_workshops->num_rows > 0): ?>
                <div class="card-grid">
                    <?php while ($workshop = $partnered_workshops->fetch_assoc()): ?>
                        <div class="workshop-card">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-weight: 700;">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <span class="badge badge-success">Partnered</span>
                                        <?php if ($workshop['category']): ?>
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category']); ?></span>
                                        <?php endif; ?>
                                        <span class="badge" style="background: <?php 
                                            echo $workshop['status'] === 'approved' ? '#10b981' : 
                                                ($workshop['status'] === 'ongoing' ? '#3b82f6' : 
                                                ($workshop['status'] === 'completed' ? '#6b7280' : '#f59e0b')); 
                                        ?>; color: white;">
                                            <?php echo ucfirst($workshop['status']); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            
                            <p style="color: #6b7280; margin: 1rem 0;">
                                <?php echo htmlspecialchars(substr($workshop['description'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin: 1rem 0; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                <div>
                                    <small style="color: #6b7280; display: block;">Teacher</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($workshop['teacher_name']); ?>
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Start Date</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Beneficiaries</small>
                                    <strong style="color: #10b981;">
                                        <i class="fas fa-users"></i> <?php echo $workshop['enrolled_count']; ?> Students
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Mode</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i> 
                                        <?php echo ucfirst($workshop['mode']); ?>
                                    </strong>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="beneficiaries.php?workshop=<?php echo $workshop['id']; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-users"></i> View Beneficiaries
                                </a>
                                <a href="mailto:<?php echo htmlspecialchars($workshop['teacher_email']); ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-envelope"></i> Contact Teacher
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't partnered with any workshops yet. Browse available workshops below to get started!
                </div>
            <?php endif; ?>
        </section>

        <div class="section-divider"></div>

        <!-- Available Workshops -->
        <section>
            <h2 style="color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 1.5rem;">
                <i class="fas fa-compass" style="color: #667eea;"></i> Available Workshops to Partner
            </h2>
            
            <?php if ($available_workshops->num_rows > 0): ?>
                <div class="card-grid">
                    <?php while ($workshop = $available_workshops->fetch_assoc()): ?>
                        <div class="workshop-card" style="border-left-color: #667eea;">
                            <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 1rem;">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: #1f2937; font-weight: 700;">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
                                    <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                        <span class="badge badge-warning">Available</span>
                                        <?php if ($workshop['category']): ?>
                                            <span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <p style="color: #6b7280; margin: 1rem 0;">
                                <?php echo htmlspecialchars(substr($workshop['description'], 0, 120)) . '...'; ?>
                            </p>
                            
                            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 0.75rem; margin: 1rem 0; padding: 1rem; background: #f9fafb; border-radius: 8px;">
                                <div>
                                    <small style="color: #6b7280; display: block;">Teacher</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-user-tie"></i> <?php echo htmlspecialchars($workshop['teacher_name']); ?>
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Start Date</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Current Students</small>
                                    <strong style="color: #667eea;">
                                        <i class="fas fa-users"></i> <?php echo $workshop['enrolled_count']; ?> / <?php echo $workshop['max_participants']; ?>
                                    </strong>
                                </div>
                                <div>
                                    <small style="color: #6b7280; display: block;">Mode</small>
                                    <strong style="color: #1f2937;">
                                        <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i> 
                                        <?php echo ucfirst($workshop['mode']); ?>
                                    </strong>
                                </div>
                            </div>
                            
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <a href="partner-request.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-handshake"></i> Partner Now
                                </a>
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-outline btn-sm">
                                    <i class="fas fa-info-circle"></i> Details
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No workshops available for partnership at the moment. Check back soon!
                </div>
            <?php endif; ?>
        </section>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
