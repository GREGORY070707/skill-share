<?php
/**
 * Database Setup Script
 * Run this file once to create the database and tables
 */

// Database credentials
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP has no password for root
$dbname = 'skillshare_db';

// Create connection without database
$conn = new mysqli($host, $user, $pass);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Database Setup | SkillShare</title>
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
        }
        .btn:hover {
            background: #5568d3;
        }
    </style>
</head>
<body>
    <div class='container'>
        <h1>🚀 SkillShare Database Setup</h1>";

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
if ($conn->query($sql) === TRUE) {
    echo "<div class='success'>✓ Database '$dbname' created successfully or already exists.</div>";
} else {
    echo "<div class='error'>✗ Error creating database: " . $conn->error . "</div>";
}

// Select database
$conn->select_db($dbname);

// Read and execute schema file
$schema_file = __DIR__ . '/database/schema.sql';
if (file_exists($schema_file)) {
    $sql_content = file_get_contents($schema_file);
    
    // Split by semicolon and execute each statement
    $statements = array_filter(array_map('trim', explode(';', $sql_content)));
    
    $success_count = 0;
    $error_count = 0;
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^(CREATE DATABASE|USE)/i', $statement)) {
            if ($conn->query($statement) === TRUE) {
                $success_count++;
            } else {
                $error_count++;
                echo "<div class='error'>✗ Error executing statement: " . $conn->error . "</div>";
            }
        }
    }
    
    echo "<div class='success'>✓ Executed $success_count SQL statements successfully.</div>";
    if ($error_count > 0) {
        echo "<div class='error'>✗ $error_count statements failed.</div>";
    }
} else {
    echo "<div class='error'>✗ Schema file not found at: $schema_file</div>";
}

// Verify tables created
$tables_query = "SHOW TABLES";
$result = $conn->query($tables_query);

if ($result->num_rows > 0) {
    echo "<div class='success'><strong>✓ Tables Created:</strong><ul>";
    while ($row = $result->fetch_array()) {
        echo "<li>" . $row[0] . "</li>";
    }
    echo "</ul></div>";
} else {
    echo "<div class='error'>✗ No tables found in database.</div>";
}

// Check if admin user exists
$admin_check = $conn->query("SELECT * FROM users WHERE role = 'admin' LIMIT 1");
if ($admin_check && $admin_check->num_rows > 0) {
    $admin = $admin_check->fetch_assoc();
    echo "<div class='info'>
        <strong>ℹ Admin Account:</strong><br>
        Email: " . htmlspecialchars($admin['email']) . "<br>
        Password: admin123 (default - please change after login)
    </div>";
} else {
    echo "<div class='error'>✗ Admin user not created. Please check the schema file.</div>";
}

echo "<div class='success'>
    <strong>🎉 Setup Complete!</strong><br>
    Your database is ready to use. You can now:
    <ul>
        <li>Register new users</li>
        <li>Login with admin credentials</li>
        <li>Create workshops</li>
        <li>Start learning!</li>
    </ul>
</div>";

echo "<a href='index.php' class='btn'>Go to Home Page</a>
      <a href='login.php' class='btn' style='background: #10b981; margin-left: 1rem;'>Login</a>";

echo "</div></body></html>";

$conn->close();
?>
