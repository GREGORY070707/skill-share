<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in
if (!isset($_SESSION['user']) || !isset($_SESSION['user_role'])) {
    header("Location: login.php");
    exit;
}

$user = $_SESSION['user'];
$role = $_SESSION['user_role'];

// Redirect to appropriate dashboard based on role
switch ($role) {
    case 'student':
        header("Location: student-dashboard.php");
        break;
    case 'teacher':
        header("Location: teacher-dashboard.php");
        break;
    case 'ngo':
        header("Location: ngo-dashboard.php");
        break;
    case 'admin':
        header("Location: admin-dashboard.php");
        break;
    default:
        session_destroy();
        header("Location: login.php");
        break;
}
exit;
?>
