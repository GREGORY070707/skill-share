<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an NGO
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'ngo') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$conn = getDBConnection();

// Get comprehensive statistics
// Total partnered workshops
$total_workshops_query = "SELECT COUNT(*) as count FROM workshops WHERE ngo_id = ?";
$stmt = $conn->prepare($total_workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_workshops = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total beneficiaries
$total_beneficiaries_query = "SELECT COUNT(DISTINCT e.student_id) as count FROM enrollments e 
                               JOIN workshops w ON e.workshop_id = w.id 
                               WHERE w.ngo_id = ?";
$stmt = $conn->prepare($total_beneficiaries_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_beneficiaries = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Completed workshops
$completed_workshops_query = "SELECT COUNT(*) as count FROM workshops WHERE ngo_id = ? AND status = 'completed'";
$stmt = $conn->prepare($completed_workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$completed_workshops = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Total certificates issued
$certificates_query = "SELECT COUNT(*) as count FROM enrollments e 
                       JOIN workshops w ON e.workshop_id = w.id 
                       WHERE w.ngo_id = ? AND e.certificate_issued = 1";
$stmt = $conn->prepare($certificates_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$total_certificates = $stmt->get_result()->fetch_assoc()['count'];
$stmt->close();

// Average attendance
$avg_attendance_query = "SELECT AVG(e.attendance_percentage) as avg FROM enrollments e 
                         JOIN workshops w ON e.workshop_id = w.id 
                         WHERE w.ngo_id = ?";
$stmt = $conn->prepare($avg_attendance_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$avg_attendance = $stmt->get_result()->fetch_assoc()['avg'] ?? 0;
$stmt->close();

// Completion rate
$completion_rate_query = "SELECT 
                          (SELECT COUNT(*) FROM enrollments e JOIN workshops w ON e.workshop_id = w.id 
                           WHERE w.ngo_id = ? AND e.status = 'completed') as completed,
                          (SELECT COUNT(*) FROM enrollments e JOIN workshops w ON e.workshop_id = w.id 
                           WHERE w.ngo_id = ?) as total";
$stmt = $conn->prepare($completion_rate_query);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$completion_data = $stmt->get_result()->fetch_assoc();
$completion_rate = $completion_data['total'] > 0 ? ($completion_data['completed'] / $completion_data['total']) * 100 : 0;
$stmt->close();

// Category breakdown
$category_query = "SELECT w.category, COUNT(DISTINCT e.student_id) as beneficiaries 
                   FROM workshops w 
                   LEFT JOIN enrollments e ON w.id = e.workshop_id 
                   WHERE w.ngo_id = ? AND w.category IS NOT NULL 
                   GROUP BY w.category 
                   ORDER BY beneficiaries DESC";
$stmt = $conn->prepare($category_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$categories = $stmt->get_result();
$stmt->close();

// Workshop mode breakdown
$mode_query = "SELECT w.mode, COUNT(*) as count 
               FROM workshops w 
               WHERE w.ngo_id = ? 
               GROUP BY w.mode";
$stmt = $conn->prepare($mode_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$modes = $stmt->get_result();
$stmt->close();

// Monthly enrollment trend (last 6 months)
$trend_query = "SELECT DATE_FORMAT(e.enrollment_date, '%Y-%m') as month, COUNT(*) as enrollments
                FROM enrollments e
                JOIN workshops w ON e.workshop_id = w.id
                WHERE w.ngo_id = ? AND e.enrollment_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month
                ORDER BY month ASC";
$stmt = $conn->prepare($trend_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$trends = $stmt->get_result();
$stmt->close();

// Top performing workshops
$top_workshops_query = "SELECT w.title, w.category, 
                        COUNT(e.id) as total_enrollments,
                        AVG(e.attendance_percentage) as avg_attendance,
                        SUM(e.certificate_issued) as certificates
                        FROM workshops w
                        LEFT JOIN enrollments e ON w.id = e.workshop_id
                        WHERE w.ngo_id = ?
                        GROUP BY w.id
                        ORDER BY total_enrollments DESC
                        LIMIT 5";
$stmt = $conn->prepare($top_workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$top_workshops = $stmt->get_result();
$stmt->close();

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impact Reports | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes slideInLeft {
            from { opacity: 0; transform: translateX(-30px); }
            to { opacity: 1; transform: translateX(0); }
        }
        
        .page-header {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
            animation: fadeInUp 0.6s ease;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .report-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            animation: fadeInUp 0.6s ease;
            animation-fill-mode: both;
        }
        
        .report-card:nth-child(1) { animation-delay: 0.1s; }
        .report-card:nth-child(2) { animation-delay: 0.2s; }
        .report-card:nth-child(3) { animation-delay: 0.3s; }
        .report-card:nth-child(4) { animation-delay: 0.4s; }
        
        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 1rem;
        }
        
        .metric-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .metric-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.3);
        }
        
        .metric-value {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }
        
        .metric-label {
            font-size: 0.875rem;
            opacity: 0.9;
        }
        
        .progress-ring {
            width: 120px;
            height: 120px;
            margin: 0 auto;
        }
        
        .icon-circle {
            width: 80px;
            height: 80px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #f59e0b, #d97706);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            box-shadow: 0 10px 30px rgba(245, 158, 11, 0.3);
        }
        
        .top-workshop-item {
            padding: 1rem;
            border-left: 4px solid #f59e0b;
            background: #f9fafb;
            border-radius: 8px;
            margin-bottom: 1rem;
            transition: all 0.3s ease;
        }
        
        .top-workshop-item:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
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
            <i class="fas fa-chart-bar"></i>
        </div>
        <h1>Impact Reports</h1>
        <p>View analytics & insights on your social impact</p>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Key Metrics -->
        <section style="margin-bottom: 3rem;">
            <h2 style="color: #1f2937; font-weight: 800; font-size: 1.875rem; margin-bottom: 1.5rem;">
                <i class="fas fa-tachometer-alt" style="color: #f59e0b;"></i> Key Performance Indicators
            </h2>
            
            <div class="stats-grid">
                <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                    <div class="stat-value"><?php echo $total_workshops; ?></div>
                    <div class="stat-label"><i class="fas fa-handshake"></i> Total Workshops</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%);">
                    <div class="stat-value"><?php echo $total_beneficiaries; ?></div>
                    <div class="stat-label"><i class="fas fa-users"></i> Total Beneficiaries</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);">
                    <div class="stat-value"><?php echo $total_certificates; ?></div>
                    <div class="stat-label"><i class="fas fa-certificate"></i> Certificates Issued</div>
                </div>
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);">
                    <div class="stat-value"><?php echo number_format($avg_attendance, 1); ?>%</div>
                    <div class="stat-label"><i class="fas fa-chart-line"></i> Avg Attendance</div>
                </div>
            </div>
        </section>

        <!-- Charts Section -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <!-- Category Distribution -->
            <div class="report-card">
                <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-tags"></i> Beneficiaries by Category
                </h3>
                <div class="chart-container">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>

            <!-- Workshop Mode Distribution -->
            <div class="report-card">
                <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-laptop"></i> Workshop Mode Distribution
                </h3>
                <div class="chart-container">
                    <canvas id="modeChart"></canvas>
                </div>
            </div>
        </div>

        <!-- Enrollment Trend -->
        <div class="report-card" style="margin-bottom: 3rem;">
            <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                <i class="fas fa-chart-line"></i> Enrollment Trend (Last 6 Months)
            </h3>
            <div class="chart-container" style="height: 250px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- Additional Metrics -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 2rem; margin-bottom: 3rem;">
            <div class="report-card" style="text-align: center;">
                <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-check-circle"></i> Completion Rate
                </h3>
                <div style="position: relative; width: 150px; height: 150px; margin: 0 auto;">
                    <svg class="progress-ring" width="150" height="150">
                        <circle cx="75" cy="75" r="60" stroke="#e5e7eb" stroke-width="15" fill="none"/>
                        <circle cx="75" cy="75" r="60" stroke="#10b981" stroke-width="15" fill="none"
                                stroke-dasharray="<?php echo 2 * 3.14159 * 60; ?>"
                                stroke-dashoffset="<?php echo 2 * 3.14159 * 60 * (1 - $completion_rate / 100); ?>"
                                transform="rotate(-90 75 75)"
                                style="transition: stroke-dashoffset 1s ease;"/>
                    </svg>
                    <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%);">
                        <div style="font-size: 2rem; font-weight: 800; color: #10b981;">
                            <?php echo number_format($completion_rate, 1); ?>%
                        </div>
                    </div>
                </div>
                <p style="color: #6b7280; margin-top: 1rem;">
                    <?php echo $completion_data['completed']; ?> of <?php echo $completion_data['total']; ?> enrollments completed
                </p>
            </div>

            <div class="report-card" style="text-align: center;">
                <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-graduation-cap"></i> Completed Workshops
                </h3>
                <div style="font-size: 3rem; font-weight: 800; color: #667eea; margin: 2rem 0;">
                    <?php echo $completed_workshops; ?>
                </div>
                <p style="color: #6b7280;">
                    Out of <?php echo $total_workshops; ?> total workshops
                </p>
            </div>

            <div class="report-card" style="text-align: center;">
                <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1rem;">
                    <i class="fas fa-user-check"></i> Avg per Workshop
                </h3>
                <div style="font-size: 3rem; font-weight: 800; color: #f59e0b; margin: 2rem 0;">
                    <?php echo $total_workshops > 0 ? number_format($total_beneficiaries / $total_workshops, 1) : 0; ?>
                </div>
                <p style="color: #6b7280;">
                    Beneficiaries per workshop
                </p>
            </div>
        </div>

        <!-- Top Performing Workshops -->
        <div class="report-card">
            <h3 style="color: #1f2937; font-weight: 700; margin-bottom: 1.5rem;">
                <i class="fas fa-trophy"></i> Top Performing Workshops
            </h3>
            
            <?php if ($top_workshops->num_rows > 0): ?>
                <?php while ($workshop = $top_workshops->fetch_assoc()): ?>
                    <div class="top-workshop-item">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                            <div>
                                <strong style="color: #1f2937; font-size: 1.125rem;">
                                    <?php echo htmlspecialchars($workshop['title']); ?>
                                </strong>
                                <?php if ($workshop['category']): ?>
                                    <span class="badge badge-primary" style="margin-left: 0.5rem;">
                                        <?php echo htmlspecialchars($workshop['category']); ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; margin-top: 1rem;">
                            <div>
                                <small style="color: #6b7280; display: block;">Enrollments</small>
                                <strong style="color: #667eea; font-size: 1.25rem;">
                                    <i class="fas fa-users"></i> <?php echo $workshop['total_enrollments']; ?>
                                </strong>
                            </div>
                            <div>
                                <small style="color: #6b7280; display: block;">Avg Attendance</small>
                                <strong style="color: #10b981; font-size: 1.25rem;">
                                    <i class="fas fa-chart-line"></i> <?php echo number_format($workshop['avg_attendance'], 1); ?>%
                                </strong>
                            </div>
                            <div>
                                <small style="color: #6b7280; display: block;">Certificates</small>
                                <strong style="color: #f59e0b; font-size: 1.25rem;">
                                    <i class="fas fa-certificate"></i> <?php echo $workshop['certificates']; ?>
                                </strong>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> No workshop data available yet.
                </div>
            <?php endif; ?>
        </div>

        <!-- Export Options -->
        <div style="text-align: center; margin-top: 3rem; padding: 2rem; background: white; border-radius: 12px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);">
            <h3 style="color: #1f2937; margin-bottom: 1rem;">
                <i class="fas fa-download"></i> Export Report
            </h3>
            <p style="color: #6b7280; margin-bottom: 1.5rem;">
                Download your impact report in various formats
            </p>
            <div style="display: flex; gap: 1rem; justify-content: center; flex-wrap: wrap;">
                <button class="btn btn-primary" onclick="window.print()">
                    <i class="fas fa-print"></i> Print Report
                </button>
                <button class="btn btn-secondary" onclick="alert('PDF export feature coming soon!')">
                    <i class="fas fa-file-pdf"></i> Export as PDF
                </button>
                <button class="btn btn-outline" onclick="alert('Excel export feature coming soon!')">
                    <i class="fas fa-file-excel"></i> Export as Excel
                </button>
            </div>
        </div>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>

    <script>
        // Category Chart
        const categoryData = {
            labels: [
                <?php 
                $categories->data_seek(0);
                while ($cat = $categories->fetch_assoc()) {
                    echo "'" . htmlspecialchars($cat['category']) . "',";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    $categories->data_seek(0);
                    while ($cat = $categories->fetch_assoc()) {
                        echo $cat['beneficiaries'] . ",";
                    }
                    ?>
                ],
                backgroundColor: [
                    '#667eea', '#10b981', '#f59e0b', '#ef4444', '#3b82f6', '#8b5cf6'
                ]
            }]
        };

        new Chart(document.getElementById('categoryChart'), {
            type: 'doughnut',
            data: categoryData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Mode Chart
        const modeData = {
            labels: [
                <?php 
                $modes->data_seek(0);
                while ($mode = $modes->fetch_assoc()) {
                    echo "'" . ucfirst($mode['mode']) . "',";
                }
                ?>
            ],
            datasets: [{
                data: [
                    <?php 
                    $modes->data_seek(0);
                    while ($mode = $modes->fetch_assoc()) {
                        echo $mode['count'] . ",";
                    }
                    ?>
                ],
                backgroundColor: ['#3b82f6', '#10b981', '#f59e0b']
            }]
        };

        new Chart(document.getElementById('modeChart'), {
            type: 'pie',
            data: modeData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });

        // Trend Chart
        const trendData = {
            labels: [
                <?php 
                $trends->data_seek(0);
                while ($trend = $trends->fetch_assoc()) {
                    echo "'" . date('M Y', strtotime($trend['month'] . '-01')) . "',";
                }
                ?>
            ],
            datasets: [{
                label: 'Enrollments',
                data: [
                    <?php 
                    $trends->data_seek(0);
                    while ($trend = $trends->fetch_assoc()) {
                        echo $trend['enrollments'] . ",";
                    }
                    ?>
                ],
                borderColor: '#667eea',
                backgroundColor: 'rgba(102, 126, 234, 0.1)',
                tension: 0.4,
                fill: true
            }]
        };

        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: trendData,
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>
