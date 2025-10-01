<?php
/**
 * Demo Data Cleanup Script
 * Run this file once to remove all demo workshops and start fresh
 * Only real teacher-created workshops will be shown after this
 */

require_once 'config/database.php';

// Security: Only allow running this script once or with a confirmation
$confirm = isset($_GET['confirm']) && $_GET['confirm'] === 'yes';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cleanup Demo Data | SkillShare</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 2rem;
            margin: 0;
        }
        .container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        h1 {
            color: #667eea;
            border-bottom: 3px solid #667eea;
            padding-bottom: 1rem;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #ffc107;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #dc3545;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            padding: 1rem;
            border-radius: 5px;
            margin: 1rem 0;
            border-left: 4px solid #17a2b8;
        }
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 1rem;
            border: none;
            cursor: pointer;
            font-size: 1rem;
        }
        .btn:hover {
            background: #5568d3;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-danger:hover {
            background: #c82333;
        }
        .btn-secondary {
            background: #6c757d;
        }
        .btn-secondary:hover {
            background: #5a6268;
        }
        ul {
            margin: 1rem 0;
            padding-left: 2rem;
        }
        li {
            margin: 0.5rem 0;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🧹 Cleanup Demo Data</h1>

<?php
if (!$confirm) {
    // Show confirmation page
    $conn = getDBConnection();
    
    // Get current data counts
    $workshops_count = $conn->query("SELECT COUNT(*) as count FROM workshops")->fetch_assoc()['count'];
    $enrollments_count = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];
    $resources_count = $conn->query("SELECT COUNT(*) as count FROM resources")->fetch_assoc()['count'];
    $feedback_count = $conn->query("SELECT COUNT(*) as count FROM feedback")->fetch_assoc()['count'];
    
    $conn->close();
    
    echo "<div class='warning'>
        <strong>⚠️ WARNING: This action cannot be undone!</strong><br><br>
        This script will permanently delete all demo/test data from your database:
        <ul>
            <li><strong>$workshops_count</strong> workshops will be deleted</li>
            <li><strong>$enrollments_count</strong> enrollments will be deleted</li>
            <li><strong>$resources_count</strong> resources will be deleted</li>
            <li><strong>$feedback_count</strong> feedback entries will be deleted</li>
        </ul>
        <strong>User accounts will NOT be deleted.</strong>
    </div>";
    
    echo "<div class='info'>
        <strong>ℹ️ What happens after cleanup?</strong><br><br>
        After running this script:
        <ul>
            <li>All demo workshops will be removed</li>
            <li>Only workshops created by teachers will appear</li>
            <li>The system will start fresh with real data only</li>
            <li>Teachers can create new workshops immediately</li>
        </ul>
    </div>";
    
    echo "<div style='margin-top: 2rem;'>
        <a href='cleanup-demo-data.php?confirm=yes' class='btn btn-danger' onclick='return confirm(\"Are you absolutely sure you want to delete all demo data? This cannot be undone!\")'>
            ✓ Yes, Delete All Demo Data
        </a>
        <a href='index.php' class='btn btn-secondary' style='margin-left: 1rem;'>
            ✗ Cancel
        </a>
    </div>";
    
} else {
    // Execute cleanup
    $conn = getDBConnection();
    
    try {
        // Disable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
        
        // Delete all workshops (cascades to related tables)
        $workshops_deleted = $conn->query("DELETE FROM workshops");
        $workshops_affected = $conn->affected_rows;
        
        // Reset auto-increment
        $conn->query("ALTER TABLE workshops AUTO_INCREMENT = 1");
        
        // Delete enrollments
        $enrollments_deleted = $conn->query("DELETE FROM enrollments");
        $enrollments_affected = $conn->affected_rows;
        $conn->query("ALTER TABLE enrollments AUTO_INCREMENT = 1");
        
        // Delete resources
        $resources_deleted = $conn->query("DELETE FROM resources");
        $resources_affected = $conn->affected_rows;
        $conn->query("ALTER TABLE resources AUTO_INCREMENT = 1");
        
        // Delete feedback
        $feedback_deleted = $conn->query("DELETE FROM feedback");
        $feedback_affected = $conn->affected_rows;
        $conn->query("ALTER TABLE feedback AUTO_INCREMENT = 1");
        
        // Delete notifications
        $notifications_deleted = $conn->query("DELETE FROM notifications");
        $notifications_affected = $conn->affected_rows;
        $conn->query("ALTER TABLE notifications AUTO_INCREMENT = 1");
        
        // Re-enable foreign key checks
        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
        
        echo "<div class='success'>
            <strong>✓ Cleanup Completed Successfully!</strong><br><br>
            The following data has been removed:
            <ul>
                <li><strong>$workshops_affected</strong> workshops deleted</li>
                <li><strong>$enrollments_affected</strong> enrollments deleted</li>
                <li><strong>$resources_affected</strong> resources deleted</li>
                <li><strong>$feedback_affected</strong> feedback entries deleted</li>
                <li><strong>$notifications_affected</strong> notifications deleted</li>
            </ul>
        </div>";
        
        // Verify cleanup
        $verify_workshops = $conn->query("SELECT COUNT(*) as count FROM workshops")->fetch_assoc()['count'];
        $verify_enrollments = $conn->query("SELECT COUNT(*) as count FROM enrollments")->fetch_assoc()['count'];
        
        echo "<div class='info'>
            <strong>✓ Verification:</strong><br>
            <ul>
                <li>Workshops remaining: <strong>$verify_workshops</strong></li>
                <li>Enrollments remaining: <strong>$verify_enrollments</strong></li>
            </ul>
        </div>";
        
        echo "<div class='success'>
            <strong>🎉 Your database is now clean!</strong><br><br>
            What's next?
            <ul>
                <li>Teachers can now create real workshops</li>
                <li>Only teacher-created workshops will be displayed</li>
                <li>Students can enroll in real workshops</li>
                <li>All demo data has been removed</li>
            </ul>
        </div>";
        
        echo "<a href='index.php' class='btn'>Go to Home Page</a>
              <a href='teacher-dashboard.php' class='btn' style='background: #10b981; margin-left: 1rem;'>Teacher Dashboard</a>";
        
    } catch (Exception $e) {
        echo "<div class='error'>
            <strong>✗ Error during cleanup:</strong><br>
            " . htmlspecialchars($e->getMessage()) . "
        </div>";
        
        echo "<a href='cleanup-demo-data.php' class='btn btn-secondary'>Try Again</a>";
    }
    
    $conn->close();
}
?>

    </div>
</body>
</html>
