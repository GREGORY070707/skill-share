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
$conn = getDBConnection();

// Get partnered workshops count
$partnered_query = "SELECT COUNT(*) as count FROM workshops WHERE ngo_id = ?";
$stmt = $conn->prepare($partnered_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$partnered_count = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get total beneficiaries
$beneficiaries_query = "SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e 
                        JOIN workshops w ON e.workshop_id = w.id 
                        WHERE w.ngo_id = ?";
$stmt = $conn->prepare($beneficiaries_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_beneficiaries = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Get partnered workshops
$workshops_query = "SELECT w.*, u.fullname as teacher_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
                    FROM workshops w 
                    LEFT JOIN users u ON w.teacher_id = u.id
                    WHERE w.ngo_id = ?
                    ORDER BY w.start_date DESC";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get available workshops to partner with
$available_query = "SELECT w.*, u.fullname as teacher_name,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
                    FROM workshops w 
                    LEFT JOIN users u ON w.teacher_id = u.id
                    WHERE w.ngo_id IS NULL AND w.status = 'approved' AND w.start_date > NOW()
                    ORDER BY w.start_date ASC LIMIT 6";
$available_workshops = $conn->query($available_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NGO Dashboard | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-nav {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
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
            color: #fa709a;
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
            border-left: 4px solid #fa709a;
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
                    <li><a href="ngo-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
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
                <p style="margin: 0; opacity: 0.9;">NGO Dashboard</p>
            </div>
        </div>
        <div>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                <i class="fas fa-hands-helping"></i> NGO Partner
            </span>
        </div>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $partnered_count; ?></div>
                <div class="stat-label"><i class="fas fa-handshake"></i> Partnered Workshops</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_beneficiaries; ?></div>
                <div class="stat-label"><i class="fas fa-users"></i> Total Beneficiaries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $available_workshops->num_rows; ?></div>
                <div class="stat-label"><i class="fas fa-calendar-plus"></i> Available to Partner</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $partnered_count > 0 ? number_format(($total_beneficiaries / $partnered_count), 0) : 0; ?></div>
                <div class="stat-label"><i class="fas fa-chart-line"></i> Avg per Workshop</div>
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
                <a href="partner-workshops.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <h3>Partner Workshops</h3>
                    <p>Collaborate with teachers</p>
                </a>
                
                <a href="beneficiaries.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--secondary); margin-bottom: 1rem;">
                        <i class="fas fa-user-friends"></i>
                    </div>
                    <h3>View Beneficiaries</h3>
                    <p>Track student progress</p>
                </a>
                
                <a href="impact-report.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>Impact Reports</h3>
                    <p>View analytics & insights</p>
                </a>
            </div>
        </section>

        <!-- Partnered Workshops -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: var(--gray-900);">
                <i class="fas fa-handshake"></i> Our Partnered Workshops
            </h2>
            
            <?php if ($workshops->num_rows > 0): ?>
                <div class="card-grid" style="margin-top: 1.5rem;">
                    <?php while ($workshop = $workshops->fetch_assoc()): ?>
                        <div class="workshop-card">
                            <div class="workshop-header">
                                <div>
                                    <h3 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                        <?php echo htmlspecialchars($workshop['title']); ?>
                                    </h3>
                                    <span class="badge badge-success">Partnered</span>
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
                                    <?php echo $workshop['enrolled_count']; ?> Beneficiaries
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-<?php echo $workshop['mode'] === 'online' ? 'laptop' : 'map-marker-alt'; ?>"></i>
                                    <?php echo ucfirst($workshop['mode']); ?>
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                                <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                    <i class="fas fa-eye"></i> View Details
                                </a>
                                <a href="workshop-beneficiaries.php?id=<?php echo $workshop['id']; ?>" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-users"></i> Beneficiaries
                                </a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info" style="margin-top: 1.5rem;">
                    <i class="fas fa-info-circle"></i> You haven't partnered with any workshops yet. Browse available workshops below!
                </div>
            <?php endif; ?>
        </section>

        <!-- Available Workshops to Partner -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: var(--gray-900);">
                <i class="fas fa-compass"></i> Available Workshops to Partner
            </h2>
            
            <?php if ($available_workshops->num_rows > 0): ?>
                <div class="card-grid" style="margin-top: 1.5rem;">
                    <?php while ($workshop = $available_workshops->fetch_assoc()): ?>
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
                                    <?php echo $workshop['enrolled_count']; ?> Students
                                </div>
                            </div>
                            <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
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
                <div class="alert alert-info" style="margin-top: 1.5rem;">
                    <i class="fas fa-info-circle"></i> No workshops available for partnership at the moment.
                </div>
            <?php endif; ?>
        </section>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
