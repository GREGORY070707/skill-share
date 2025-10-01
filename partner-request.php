<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an NGO
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'ngo') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$workshop_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($workshop_id <= 0) {
    $_SESSION['error'] = "Invalid workshop ID.";
    header("Location: partner-workshops.php");
    exit;
}

$conn = getDBConnection();

// Check if workshop exists and is available
$check_query = "SELECT * FROM workshops WHERE id = ? AND ngo_id IS NULL AND status = 'approved'";
$stmt = $conn->prepare($check_query);
$stmt->bind_param("i", $workshop_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $_SESSION['error'] = "Workshop not available for partnership.";
    $stmt->close();
    $conn->close();
    header("Location: partner-workshops.php");
    exit;
}

// Update workshop with NGO partnership
$update_query = "UPDATE workshops SET ngo_id = ? WHERE id = ?";
$stmt = $conn->prepare($update_query);
$stmt->bind_param("ii", $user_id, $workshop_id);

if ($stmt->execute()) {
    $_SESSION['success'] = "Successfully partnered with the workshop!";
} else {
    $_SESSION['error'] = "Failed to partner with workshop. Please try again.";
}

$stmt->close();
$conn->close();

header("Location: partner-workshops.php");
exit;
?>
