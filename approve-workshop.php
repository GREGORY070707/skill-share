<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($workshop_id <= 0) {
    $_SESSION['error'] = "Invalid workshop ID.";
    header("Location: manage-workshops.php");
    exit;
}

$conn = getDBConnection();

// Update workshop status to approved
$update_query = "UPDATE workshops SET status = 'approved' WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("i", $workshop_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Workshop approved successfully!";
} else {
    $_SESSION['error'] = "Failed to approve workshop.";
}

$stmt->close();
$conn->close();

// Redirect back
$redirect = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : 'manage-workshops.php';
header("Location: $redirect");
exit;
?>
