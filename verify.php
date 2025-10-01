<?php
$conn = new mysqli("localhost", "root", "", "skillshare_db");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$email = $_GET['email'];
$token = $_GET['token'];

$sql = "SELECT * FROM users WHERE email='$email' AND verify_token='$token' AND is_verified=0";
$result = $conn->query($sql);

if ($result->num_rows == 1) {
    $update = "UPDATE users SET is_verified=1, verify_token=NULL WHERE email='$email'";
    if ($conn->query($update) === TRUE) {
        echo "<h2>✅ Email verified successfully!</h2>";
        echo "<p>You can now <a href='login.html'>login</a>.</p>";
    } else {
        echo "❌ Error updating verification.";
    }
} else {
    echo "❌ Invalid or expired verification link.";
}

$conn->close();
?>
