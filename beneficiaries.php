<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an NGO
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'ngo') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$selected_workshop = isset($_GET['workshop']) ? intval($_GET['workshop']) : 0;

$conn = getDBConnection();

// Get NGO's partnered workshops
$workshops_query = "SELECT w.id, w.title, w.start_date, w.mode, w.status,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as student_count
                    FROM workshops w 
                    WHERE w.ngo_id = ?
                    ORDER BY w.start_date DESC";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get beneficiaries data
$beneficiaries = null;
$workshop_title = '';
$workshop_info = null;

if ($selected_workshop > 0) {
    // Verify workshop belongs to this NGO
    $verify_query = "SELECT * FROM workshops WHERE id = ? AND ngo_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $selected_workshop, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $workshop_info = $result->fetch_assoc();
        $workshop_title = $workshop_info['title'];
        
        // Get beneficiaries
        $beneficiaries_query = "SELECT u.id, u.fullname, u.email, u.phone, 
                                e.enrollment_date, e.status, e.attendance_percentage,
                                e.certificate_issued
                                FROM enrollments e
                                JOIN users u ON e.student_id = u.id
                                WHERE e.workshop_id = ?
                                ORDER BY e.enrollment_date DESC";
        $stmt = $conn->prepare($beneficiaries_query);
        $stmt->bind_param("i", $selected_workshop);
        $stmt->execute();
        $beneficiaries = $stmt->get_result();
        $stmt->close();
    }
} else {
    // Get all beneficiaries across all partnered workshops
    $all_beneficiaries_query = "SELECT DISTINCT u.id, u.fullname, u.email, u.phone,
                                COUNT(e.id) as workshops_enrolled,
                                AVG(e.attendance_percentage) as avg_attendance,
                                SUM(e.certificate_issued) as certificates_earned
                                FROM enrollments e
                                JOIN users u ON e.student_id = u.id
                                JOIN workshops w ON e.workshop_id = w.id
                                WHERE w.ngo_id = ?
                                GROUP BY u.id
                                ORDER BY u.fullname ASC";
    $stmt = $conn->prepare($all_beneficiaries_query);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $beneficiaries = $stmt->get_result();
    $stmt->close();
}

// Get summary statistics
$total_beneficiaries_query = "SELECT COUNT(DISTINCT e.student_id) as total FROM enrollments e 
                               JOIN workshops w ON e.workshop_id = w.id 
                               WHERE w.ngo_id = ?";
$stmt = $conn->prepare($total_beneficiaries_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_beneficiaries = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Beneficiaries | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .page-header {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .workshop-selector {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            margin-bottom: 2rem;
            animation: fadeInUp 0.6s ease 0.2s both;
        }
        
        .workshop-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            border: 2px solid #e5e7eb;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .workshop-card:hover {
            border-color: #10b981;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.2);
        }
        
        .workshop-card.active {
            border-color: #10b981;
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
        }
        
        .beneficiaries-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            overflow: hidden;
            animation: fadeInUp 0.6s ease 0.4s both;
        }
        
        .beneficiaries-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .beneficiaries-table th {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .beneficiaries-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .beneficiaries-table tr:hover {
            background: #f9fafb;
        }
        
        .beneficiary-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 0.75rem;
        }
        
        .progress-bar {
            width: 100%;
            height: 8px;
            background: #e5e7eb;
            border-radius: 4px;
            overflow: hidden;
        }
        
        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #10b981, #059669);
            transition: width 0.3s ease;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
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
            <i class="fas fa-user-friends"></i>
        </div>
        <h1>View Beneficiaries</h1>
        <p>Track student progress and impact across your partnered workshops</p>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Summary Stats -->
        <div class="stats-grid" style="margin-bottom: 2rem;">
            <div class="stat-card">
                <div class="stat-value"><?php echo $total_beneficiaries; ?></div>
                <div class="stat-label"><i class="fas fa-users"></i> Total Beneficiaries</div>
            </div>
            <div class="stat-card">
                <div class="stat-value"><?php echo $workshops->num_rows; ?></div>
                <div class="stat-label"><i class="fas fa-handshake"></i> Partnered Workshops</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    if ($beneficiaries && $beneficiaries->num_rows > 0) {
                        $beneficiaries->data_seek(0);
                        $total_attendance = 0;
                        $count = 0;
                        while ($b = $beneficiaries->fetch_assoc()) {
                            if (isset($b['attendance_percentage'])) {
                                $total_attendance += $b['attendance_percentage'];
                                $count++;
                            } else if (isset($b['avg_attendance'])) {
                                $total_attendance += $b['avg_attendance'];
                                $count++;
                            }
                        }
                        echo $count > 0 ? number_format($total_attendance / $count, 0) : 0;
                        $beneficiaries->data_seek(0);
                    } else {
                        echo 0;
                    }
                    ?>%
                </div>
                <div class="stat-label"><i class="fas fa-chart-line"></i> Avg Attendance</div>
            </div>
            <div class="stat-card">
                <div class="stat-value">
                    <?php 
                    if ($beneficiaries && $beneficiaries->num_rows > 0) {
                        $beneficiaries->data_seek(0);
                        $total_certs = 0;
                        while ($b = $beneficiaries->fetch_assoc()) {
                            if (isset($b['certificate_issued'])) {
                                $total_certs += $b['certificate_issued'];
                            } else if (isset($b['certificates_earned'])) {
                                $total_certs += $b['certificates_earned'];
                            }
                        }
                        echo $total_certs;
                        $beneficiaries->data_seek(0);
                    } else {
                        echo 0;
                    }
                    ?>
                </div>
                <div class="stat-label"><i class="fas fa-certificate"></i> Certificates Issued</div>
            </div>
        </div>

        <!-- Workshop Selector -->
        <div class="workshop-selector">
            <h3 style="color: #1f2937; margin-bottom: 1rem;">
                <i class="fas fa-filter"></i> Filter by Workshop
            </h3>
            
            <?php if ($workshops->num_rows > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); gap: 1rem;">
                    <a href="beneficiaries.php" style="text-decoration: none; color: inherit;">
                        <div class="workshop-card <?php echo $selected_workshop === 0 ? 'active' : ''; ?>">
                            <strong style="color: #1f2937;">All Workshops</strong>
                            <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">
                                View all beneficiaries
                            </p>
                        </div>
                    </a>
                    
                    <?php 
                    $workshops->data_seek(0);
                    while ($workshop = $workshops->fetch_assoc()): 
                    ?>
                        <a href="beneficiaries.php?workshop=<?php echo $workshop['id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="workshop-card <?php echo $selected_workshop === $workshop['id'] ? 'active' : ''; ?>">
                                <strong style="color: #1f2937;"><?php echo htmlspecialchars($workshop['title']); ?></strong>
                                <p style="margin: 0.5rem 0 0 0; color: #6b7280; font-size: 0.875rem;">
                                    <i class="fas fa-users"></i> <?php echo $workshop['student_count']; ?> students
                                </p>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No partnered workshops found. <a href="partner-workshops.php">Partner with a workshop</a> to view beneficiaries.
                </div>
            <?php endif; ?>
        </div>

        <!-- Beneficiaries List -->
        <?php if ($beneficiaries && $beneficiaries->num_rows > 0): ?>
            <div style="background: white; padding: 1.5rem; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); margin-bottom: 2rem;">
                <h2 style="color: #1f2937; margin: 0;">
                    <i class="fas fa-users"></i> 
                    <?php echo $selected_workshop > 0 ? 'Workshop Beneficiaries' : 'All Beneficiaries'; ?>
                </h2>
                <?php if ($selected_workshop > 0 && $workshop_title): ?>
                    <p style="color: #6b7280; margin: 0.5rem 0 0 0;">
                        Workshop: <strong><?php echo htmlspecialchars($workshop_title); ?></strong>
                    </p>
                <?php endif; ?>
            </div>

            <div class="beneficiaries-table">
                <table>
                    <thead>
                        <tr>
                            <th>Student</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <?php if ($selected_workshop > 0): ?>
                                <th>Enrolled Date</th>
                                <th>Status</th>
                                <th>Attendance</th>
                                <th>Certificate</th>
                            <?php else: ?>
                                <th>Workshops</th>
                                <th>Avg Attendance</th>
                                <th>Certificates</th>
                            <?php endif; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $beneficiaries->data_seek(0);
                        while ($beneficiary = $beneficiaries->fetch_assoc()): 
                        ?>
                            <tr>
                                <td>
                                    <div style="display: flex; align-items: center;">
                                        <div class="beneficiary-avatar">
                                            <?php echo strtoupper(substr($beneficiary['fullname'], 0, 1)); ?>
                                        </div>
                                        <strong><?php echo htmlspecialchars($beneficiary['fullname']); ?></strong>
                                    </div>
                                </td>
                                <td>
                                    <i class="fas fa-envelope" style="color: #6b7280;"></i>
                                    <?php echo htmlspecialchars($beneficiary['email']); ?>
                                </td>
                                <td>
                                    <i class="fas fa-phone" style="color: #6b7280;"></i>
                                    <?php echo htmlspecialchars($beneficiary['phone'] ?? 'N/A'); ?>
                                </td>
                                <?php if ($selected_workshop > 0): ?>
                                    <td>
                                        <i class="fas fa-calendar-plus" style="color: #6b7280;"></i>
                                        <?php echo date('M d, Y', strtotime($beneficiary['enrollment_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $beneficiary['status'] === 'enrolled' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($beneficiary['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress-bar" style="flex: 1;">
                                                <div class="progress-fill" style="width: <?php echo $beneficiary['attendance_percentage']; ?>%;"></div>
                                            </div>
                                            <span style="font-weight: 600; color: #1f2937; min-width: 45px;">
                                                <?php echo number_format($beneficiary['attendance_percentage'], 0); ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($beneficiary['certificate_issued']): ?>
                                            <span class="badge badge-success">
                                                <i class="fas fa-certificate"></i> Issued
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background: #6b7280; color: white;">
                                                <i class="fas fa-clock"></i> Pending
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php else: ?>
                                    <td>
                                        <strong style="color: #10b981;">
                                            <?php echo $beneficiary['workshops_enrolled']; ?> workshops
                                        </strong>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div class="progress-bar" style="flex: 1;">
                                                <div class="progress-fill" style="width: <?php echo $beneficiary['avg_attendance']; ?>%;"></div>
                                            </div>
                                            <span style="font-weight: 600; color: #1f2937; min-width: 45px;">
                                                <?php echo number_format($beneficiary['avg_attendance'], 0); ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <strong style="color: #10b981;">
                                            <i class="fas fa-certificate"></i> <?php echo $beneficiary['certificates_earned']; ?>
                                        </strong>
                                    </td>
                                <?php endif; ?>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php elseif ($workshops->num_rows > 0): ?>
            <div class="alert alert-info" style="text-align: center; padding: 3rem;">
                <i class="fas fa-hand-pointer" style="font-size: 3rem; color: #10b981; margin-bottom: 1rem;"></i>
                <h3 style="color: #1f2937;">Select a Workshop</h3>
                <p style="color: #6b7280;">Click on any workshop above to view its beneficiaries</p>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No beneficiaries found. <a href="partner-workshops.php">Partner with workshops</a> to start tracking beneficiaries.
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
