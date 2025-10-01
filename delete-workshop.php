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

// Delete workshop (will cascade to enrollments, resources, feedback)
$delete_query = "DELETE FROM workshops WHERE id = ?";
$stmt = $conn->prepare($delete_query);
$stmt->bind_param("i", $workshop_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Workshop deleted successfully!";
} else {
    $_SESSION['error'] = "Failed to delete workshop.";
}

$stmt->close();
$conn->close();

header("Location: manage-workshops.php");
exit;
?>
