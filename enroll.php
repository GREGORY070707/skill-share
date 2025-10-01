<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a student
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'student') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($workshop_id <= 0) {
    header("Location: workshops.php");
    exit;
}

$conn = getDBConnection();

// Check if workshop exists and is available
$workshop_query = "SELECT * FROM workshops WHERE id = ? AND status = 'approved' AND start_date > NOW()";
$stmt = $conn->prepare($workshop_query);
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$workshop = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$workshop) {
    $_SESSION['error'] = 'Workshop not found or not available for enrollment.';
    header("Location: workshops.php");
    exit;
}

// Check if already enrolled
$check_query = "SELECT id FROM enrollments WHERE student_id = ? AND workshop_id = ?";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("ii", $user_id, $workshop_id);
$stmt->execute();
$already_enrolled = $stmt->get_result()->num_rows > 0;
$stmt->close();

if ($already_enrolled) {
    $_SESSION['error'] = 'You are already enrolled in this workshop.';
    header("Location: student-dashboard.php");
    exit;
}

// Check if workshop is full
if ($workshop['current_participants'] >= $workshop['max_participants']) {
    $_SESSION['error'] = 'This workshop is full.';
    header("Location: workshops.php");
    exit;
}

// Enroll the student
$enroll_query = "INSERT INTO enrollments (student_id, workshop_id, status) VALUES (?, ?, 'enrolled')";
$stmt = $conn->prepare($enroll_query);
$stmt->bind_param("ii", $user_id, $workshop_id);

if ($stmt->execute()) {
    // Update workshop participant count
    $update_query = "UPDATE workshops SET current_participants = current_participants + 1 WHERE id = ?";
    $update_stmt = $conn->prepare($update_query);
    $update_stmt->bind_param("i", $workshop_id);
    $update_stmt->execute();
    $update_stmt->close();
    
    $_SESSION['success'] = 'Successfully enrolled in the workshop!';
    header("Location: student-dashboard.php");
} else {
    $_SESSION['error'] = 'Failed to enroll. Please try again.';
    header("Location: workshops.php");
}

$stmt->close();
$conn->close();
exit;
?>
