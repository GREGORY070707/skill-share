<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get teacher's workshops with student counts
$workshops_query = "SELECT w.id, w.title, w.start_date, w.mode, w.status,
                    (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as student_count
                    FROM workshops w 
                    WHERE w.teacher_id = ?
                    ORDER BY w.start_date DESC";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get selected workshop students
$selected_workshop = isset($_GET['workshop']) ? intval($_GET['workshop']) : 0;
$students = null;
$workshop_title = '';

if ($selected_workshop > 0) {
    // Verify workshop belongs to this teacher
    $verify_query = "SELECT title FROM workshops WHERE id = ? AND teacher_id = ?";
    $stmt = $conn->prepare($verify_query);
    $stmt->bind_param("ii", $selected_workshop, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $workshop_title = $result->fetch_assoc()['title'];
        
        // Get enrolled students
        $students_query = "SELECT u.id, u.fullname, u.email, u.phone, e.enrollment_date, e.status, e.attendance_percentage
                          FROM enrollments e
                          JOIN users u ON e.student_id = u.id
                          WHERE e.workshop_id = ?
                          ORDER BY e.enrollment_date DESC";
        $stmt = $conn->prepare($students_query);
        $stmt->bind_param("i", $selected_workshop);
        $stmt->execute();
        $students = $stmt->get_result();
        $stmt->close();
    }
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Students | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .students-container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .icon-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .icon-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 10px 30px rgba(16, 185, 129, 0.3);
        }
        
        .workshop-selector {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        
        .students-table {
            background: white;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            overflow: hidden;
        }
        
        .students-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .students-table th {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
        }
        
        .students-table td {
            padding: 1rem;
            border-bottom: 1px solid var(--gray-200);
        }
        
        .students-table tr:hover {
            background: var(--gray-50);
        }
        
        .students-table tr:last-child td {
            border-bottom: none;
        }
        
        .student-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-right: 0.75rem;
        }
        
        .workshop-card {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            cursor: pointer;
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .workshop-card:hover {
            transform: translateY(-3px);
            box-shadow: var(--shadow-lg);
        }
        
        .workshop-card.active {
            border-left-color: var(--secondary);
            background: linear-gradient(135deg, rgba(16, 185, 129, 0.05), rgba(5, 150, 105, 0.05));
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
                    <li><a href="teacher-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="students-container">
        <div style="margin-bottom: 1.5rem;">
            <a href="teacher-dashboard.php" style="color: var(--primary); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="icon-header">
            <div class="icon-circle">
                <i class="fas fa-user-graduate"></i>
            </div>
            <h1 style="color: var(--gray-900); margin-bottom: 0.5rem;">My Students</h1>
            <p style="color: var(--gray-600);">View and manage enrolled students across your workshops</p>
        </div>

        <!-- Workshop Selector -->
        <div class="workshop-selector">
            <h3 style="color: var(--gray-900); margin-bottom: 1rem;">
                <i class="fas fa-chalkboard"></i> Select Workshop
            </h3>
            
            <?php if ($workshops->num_rows > 0): ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 1rem;">
                    <?php while ($workshop = $workshops->fetch_assoc()): ?>
                        <a href="my-students.php?workshop=<?php echo $workshop['id']; ?>" style="text-decoration: none; color: inherit;">
                            <div class="workshop-card <?php echo $selected_workshop === $workshop['id'] ? 'active' : ''; ?>">
                                <div style="display: flex; justify-content: space-between; align-items: start;">
                                    <div style="flex: 1;">
                                        <h4 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                            <?php echo htmlspecialchars($workshop['title']); ?>
                                        </h4>
                                        <div style="display: flex; gap: 0.5rem; flex-wrap: wrap; margin-bottom: 0.5rem;">
                                            <span class="badge badge-<?php echo $workshop['status'] === 'approved' ? 'success' : 'warning'; ?>">
                                                <?php echo ucfirst($workshop['status']); ?>
                                            </span>
                                            <span class="badge badge-primary">
                                                <?php echo ucfirst($workshop['mode']); ?>
                                            </span>
                                        </div>
                                        <small style="color: var(--gray-600);">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                                        </small>
                                    </div>
                                    <div style="text-align: center; margin-left: 1rem;">
                                        <div style="font-size: 2rem; font-weight: 800; color: var(--primary);">
                                            <?php echo $workshop['student_count']; ?>
                                        </div>
                                        <small style="color: var(--gray-600);">Students</small>
                                    </div>
                                </div>
                            </div>
                        </a>
                    <?php endwhile; ?>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> You haven't created any workshops yet. 
                    <a href="create-workshop.php">Create your first workshop</a> to start teaching!
                </div>
            <?php endif; ?>
        </div>

        <!-- Students List -->
        <?php if ($selected_workshop > 0 && $workshop_title): ?>
            <div style="background: white; padding: 1.5rem; border-radius: var(--radius-lg); box-shadow: var(--shadow-md); margin-bottom: 2rem;">
                <h2 style="color: var(--gray-900); margin: 0;">
                    <i class="fas fa-users"></i> Enrolled Students
                </h2>
                <p style="color: var(--gray-600); margin: 0.5rem 0 0 0;">
                    Workshop: <strong><?php echo htmlspecialchars($workshop_title); ?></strong>
                </p>
            </div>

            <?php if ($students && $students->num_rows > 0): ?>
                <div class="students-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Student</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Enrolled Date</th>
                                <th>Status</th>
                                <th>Attendance</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($student = $students->fetch_assoc()): ?>
                                <tr>
                                    <td>
                                        <div style="display: flex; align-items: center;">
                                            <div class="student-avatar">
                                                <?php echo strtoupper(substr($student['fullname'], 0, 1)); ?>
                                            </div>
                                            <strong><?php echo htmlspecialchars($student['fullname']); ?></strong>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="fas fa-envelope" style="color: var(--gray-400);"></i>
                                        <?php echo htmlspecialchars($student['email']); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-phone" style="color: var(--gray-400);"></i>
                                        <?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?>
                                    </td>
                                    <td>
                                        <i class="fas fa-calendar-plus" style="color: var(--gray-400);"></i>
                                        <?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?>
                                    </td>
                                    <td>
                                        <span class="badge badge-<?php echo $student['status'] === 'enrolled' ? 'success' : 'warning'; ?>">
                                            <?php echo ucfirst($student['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                                            <div style="flex: 1; background: var(--gray-200); height: 8px; border-radius: 4px; overflow: hidden;">
                                                <div style="width: <?php echo $student['attendance_percentage']; ?>%; height: 100%; background: linear-gradient(90deg, var(--primary), var(--secondary));"></div>
                                            </div>
                                            <span style="font-weight: 600; color: var(--gray-700); min-width: 45px;">
                                                <?php echo number_format($student['attendance_percentage'], 0); ?>%
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($student['email']); ?>" 
                                           class="btn btn-primary btn-sm" style="padding: 0.5rem 0.75rem;">
                                            <i class="fas fa-envelope"></i> Email
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>

                <!-- Summary Stats -->
                <div style="margin-top: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div class="stat-card">
                        <div class="stat-value"><?php echo $students->num_rows; ?></div>
                        <div class="stat-label"><i class="fas fa-users"></i> Total Students</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php 
                            $students->data_seek(0);
                            $enrolled = 0;
                            while ($s = $students->fetch_assoc()) {
                                if ($s['status'] === 'enrolled') $enrolled++;
                            }
                            echo $enrolled;
                            ?>
                        </div>
                        <div class="stat-label"><i class="fas fa-check-circle"></i> Active</div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-value">
                            <?php 
                            $students->data_seek(0);
                            $total_attendance = 0;
                            $count = 0;
                            while ($s = $students->fetch_assoc()) {
                                $total_attendance += $s['attendance_percentage'];
                                $count++;
                            }
                            echo $count > 0 ? number_format($total_attendance / $count, 0) : 0;
                            ?>%
                        </div>
                        <div class="stat-label"><i class="fas fa-chart-line"></i> Avg Attendance</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No students have enrolled in this workshop yet.
                </div>
            <?php endif; ?>
        <?php elseif ($workshops->num_rows > 0): ?>
            <div class="alert alert-info" style="text-align: center; padding: 3rem;">
                <i class="fas fa-hand-pointer" style="font-size: 3rem; color: var(--primary); margin-bottom: 1rem;"></i>
                <h3 style="color: var(--gray-900);">Select a Workshop</h3>
                <p style="color: var(--gray-600);">Click on any workshop above to view its enrolled students</p>
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
