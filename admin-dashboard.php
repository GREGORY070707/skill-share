<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$conn = getDBConnection();

// Get statistics
$total_users_query = "SELECT COUNT(*) as count FROM users";
$total_users = $conn->query($total_users_query)->fetch_assoc()['count'];

$total_workshops_query = "SELECT COUNT(*) as count FROM workshops";
$total_workshops = $conn->query($total_workshops_query)->fetch_assoc()['count'];

$pending_workshops_query = "SELECT COUNT(*) as count FROM workshops WHERE status = 'pending'";
$pending_workshops = $conn->query($pending_workshops_query)->fetch_assoc()['count'];

$total_enrollments_query = "SELECT COUNT(*) as count FROM enrollments";
$total_enrollments = $conn->query($total_enrollments_query)->fetch_assoc()['count'];

// Get user breakdown
$students_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'student'")->fetch_assoc()['count'];
$teachers_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'teacher'")->fetch_assoc()['count'];
$ngos_count = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'ngo'")->fetch_assoc()['count'];

// Get recent users
$recent_users_query = "SELECT * FROM users ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($recent_users_query);

// Get pending workshops
$pending_workshops_query = "SELECT w.*, u.fullname as teacher_name 
                            FROM workshops w 
                            LEFT JOIN users u ON w.teacher_id = u.id 
                            WHERE w.status = 'pending' 
                            ORDER BY w.created_at DESC LIMIT 10";
$pending_workshops_list = $conn->query($pending_workshops_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .dashboard-nav {
            background: linear-gradient(135deg, #434343 0%, #000000 100%);
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
            color: #000;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            font-weight: 700;
        }
        
        .data-table {
            width: 100%;
            background: white;
            border-radius: var(--radius-lg);
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: var(--gray-100);
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: var(--gray-700);
            border-bottom: 2px solid var(--gray-200);
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
            color: var(--gray-700);
        }
        
        .data-table tr:hover {
            background: var(--gray-50);
        }
        
        .action-btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            margin: 0 0.25rem;
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
                    <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Users</a></li>
                    <li><a href="manage-workshops.php"><i class="fas fa-chalkboard"></i> Workshops</a></li>
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
                <p style="margin: 0; opacity: 0.9;">Administrator Dashboard</p>
            </div>
        </div>
        <div>
            <span class="badge" style="background: rgba(255,255,255,0.3); color: white; font-size: 1rem; padding: 0.5rem 1rem;">
                <i class="fas fa-user-shield"></i> Admin
            </span>
        </div>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Stats Section -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_users; ?></div>
                <div class="stat-label"><i class="fas fa-users"></i> Total Users</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_workshops; ?></div>
                <div class="stat-label"><i class="fas fa-chalkboard"></i> Total Workshops</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $pending_workshops; ?></div>
                <div class="stat-label"><i class="fas fa-clock"></i> Pending Approvals</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_enrollments; ?></div>
                <div class="stat-label"><i class="fas fa-user-check"></i> Total Enrollments</div>
            </div>
        </div>

        <!-- User Breakdown -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: var(--gray-900);">
                <i class="fas fa-chart-pie"></i> User Distribution
            </h2>
            
            <div class="card-grid" style="margin-top: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <div class="card" style="text-align: center; border-left: 4px solid var(--primary);">
                    <div style="font-size: 2.5rem; color: var(--primary); margin-bottom: 0.5rem;">
                        <?php echo $students_count; ?>
                    </div>
                    <h3 style="margin: 0.5rem 0;">Students</h3>
                    <a href="manage-users.php?role=student" class="btn btn-primary btn-sm" style="margin-top: 1rem;">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                
                <div class="card" style="text-align: center; border-left: 4px solid var(--secondary);">
                    <div style="font-size: 2.5rem; color: var(--secondary); margin-bottom: 0.5rem;">
                        <?php echo $teachers_count; ?>
                    </div>
                    <h3 style="margin: 0.5rem 0;">Teachers</h3>
                    <a href="manage-users.php?role=teacher" class="btn btn-secondary btn-sm" style="margin-top: 1rem;">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
                
                <div class="card" style="text-align: center; border-left: 4px solid var(--accent);">
                    <div style="font-size: 2.5rem; color: var(--accent); margin-bottom: 0.5rem;">
                        <?php echo $ngos_count; ?>
                    </div>
                    <h3 style="margin: 0.5rem 0;">NGO Partners</h3>
                    <a href="manage-users.php?role=ngo" class="btn btn-sm" style="background: var(--accent); color: white; margin-top: 1rem;">
                        <i class="fas fa-eye"></i> View All
                    </a>
                </div>
            </div>
        </section>

        <!-- Quick Actions -->
        <section style="margin-top: 3rem;">
            <h2 class="section-title" style="text-align: left; color: var(--gray-900);">
                <i class="fas fa-bolt"></i> Quick Actions
            </h2>
            
            <div class="card-grid" style="margin-top: 1.5rem; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
                <a href="manage-users.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;">
                        <i class="fas fa-users-cog"></i>
                    </div>
                    <h3>Manage Users</h3>
                    <p>View, edit, or remove users</p>
                </a>
                
                <a href="manage-workshops.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--secondary); margin-bottom: 1rem;">
                        <i class="fas fa-chalkboard-teacher"></i>
                    </div>
                    <h3>Manage Workshops</h3>
                    <p>Approve or manage workshops</p>
                </a>
                
                <a href="reports.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--accent); margin-bottom: 1rem;">
                        <i class="fas fa-chart-bar"></i>
                    </div>
                    <h3>View Reports</h3>
                    <p>Analytics and insights</p>
                </a>
                
                <a href="settings.php" class="card" style="text-decoration: none; color: inherit; text-align: center;">
                    <div style="font-size: 3rem; color: var(--danger); margin-bottom: 1rem;">
                        <i class="fas fa-cog"></i>
                    </div>
                    <h3>Settings</h3>
                    <p>Platform configuration</p>
                </a>
            </div>
        </section>

        <!-- Pending Workshop Approvals -->
        <section style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="section-title" style="text-align: left; color: var(--gray-900); margin: 0;">
                    <i class="fas fa-clock"></i> Pending Workshop Approvals
                </h2>
                <a href="manage-workshops.php?status=pending" class="btn btn-primary btn-sm">
                    <i class="fas fa-list"></i> View All
                </a>
            </div>
            
            <?php if ($pending_workshops_list->num_rows > 0): ?>
                <div class="data-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Workshop Title</th>
                                <th>Teacher</th>
                                <th>Category</th>
                                <th>Start Date</th>
                                <th>Mode</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($workshop = $pending_workshops_list->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($workshop['title']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($workshop['teacher_name'] ?? 'N/A'); ?></td>
                                    <td><span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category'] ?? 'General'); ?></span></td>
                                    <td><?php echo date('M d, Y', strtotime($workshop['start_date'])); ?></td>
                                    <td><?php echo ucfirst($workshop['mode']); ?></td>
                                    <td>
                                        <a href="approve-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-success action-btn">
                                            <i class="fas fa-check"></i> Approve
                                        </a>
                                        <a href="reject-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-danger action-btn">
                                            <i class="fas fa-times"></i> Reject
                                        </a>
                                        <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-outline action-btn">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> No pending workshop approvals. All caught up!
                </div>
            <?php endif; ?>
        </section>

        <!-- Recent Users -->
        <section style="margin-top: 3rem;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 class="section-title" style="text-align: left; color: var(--gray-900); margin: 0;">
                    <i class="fas fa-user-plus"></i> Recent Registrations
                </h2>
                <a href="manage-users.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-list"></i> View All Users
                </a>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($recent_user = $recent_users->fetch_assoc()): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($recent_user['fullname']); ?></strong></td>
                                <td><?php echo htmlspecialchars($recent_user['email']); ?></td>
                                <td><span class="badge badge-primary"><?php echo ucfirst($recent_user['role']); ?></span></td>
                                <td>
                                    <?php if ($recent_user['status'] === 'active'): ?>
                                        <span class="badge badge-success">Active</span>
                                    <?php else: ?>
                                        <span class="badge badge-warning"><?php echo ucfirst($recent_user['status']); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($recent_user['created_at'])); ?></td>
                                <td>
                                    <a href="edit-user.php?id=<?php echo $recent_user['id']; ?>" class="btn btn-primary action-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <a href="view-user.php?id=<?php echo $recent_user['id']; ?>" class="btn btn-outline action-btn">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
