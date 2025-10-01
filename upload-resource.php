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

// Get teacher's workshops
$workshops_query = "SELECT id, title FROM workshops WHERE teacher_id = ? ORDER BY start_date DESC";
$stmt = $conn->prepare($workshops_query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $workshop_id = isset($_POST['workshop_id']) ? intval($_POST['workshop_id']) : 0;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $resource_type = trim($_POST['resource_type'] ?? 'document');
    $resource_url = trim($_POST['resource_url'] ?? '');
    
    if (empty($title) || $workshop_id <= 0) {
        $error = 'Please fill in all required fields.';
    } else {
        // Verify workshop belongs to this teacher
        $verify_query = "SELECT id FROM workshops WHERE id = ? AND teacher_id = ?";
        $stmt = $conn->prepare($verify_query);
        $stmt->bind_param("ii", $workshop_id, $user_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $error = 'Invalid workshop selected.';
        } else {
            // Insert resource
            $insert_query = "INSERT INTO resources (workshop_id, title, description, file_path, file_type, uploaded_by) 
                            VALUES (?, ?, ?, ?, ?, ?)";
            $stmt = $conn->prepare($insert_query);
            $stmt->bind_param("issssi", $workshop_id, $title, $description, $resource_url, $resource_type, $user_id);
            
            if ($stmt->execute()) {
                $success = 'Resource uploaded successfully!';
                $title = '';
                $description = '';
                $resource_url = '';
            } else {
                $error = 'Failed to upload resource. Please try again.';
            }
        }
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
    <title>Upload Resource | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-container {
            max-width: 800px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .upload-box {
            background: white;
            padding: 2rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-lg);
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

    <div class="upload-container">
        <div style="margin-bottom: 1.5rem;">
            <a href="teacher-dashboard.php" style="color: var(--primary); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="upload-box">
            <h1 style="color: var(--gray-900); margin-bottom: 0.5rem;">
                <i class="fas fa-upload"></i> Upload Learning Resource
            </h1>
            <p style="color: var(--gray-600); margin-bottom: 2rem;">
                Share materials with your students
            </p>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="upload-resource.php">
                <div class="form-group">
                    <label for="workshop_id"><i class="fas fa-chalkboard"></i> Select Workshop *</label>
                    <select id="workshop_id" name="workshop_id" class="form-control" required>
                        <option value="">-- Choose Workshop --</option>
                        <?php 
                        $workshops->data_seek(0);
                        while ($workshop = $workshops->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $workshop['id']; ?>" 
                                    <?php echo (isset($_POST['workshop_id']) && $_POST['workshop_id'] == $workshop['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($workshop['title']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Resource Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="e.g., Python Basics - Chapter 1" required
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="4" placeholder="Brief description of the resource..."><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-group">
                    <label for="resource_type"><i class="fas fa-file"></i> Resource Type</label>
                    <select id="resource_type" name="resource_type" class="form-control">
                        <option value="document">Document (PDF, DOC)</option>
                        <option value="presentation">Presentation (PPT)</option>
                        <option value="video">Video Link</option>
                        <option value="link">External Link</option>
                        <option value="other">Other</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="resource_url"><i class="fas fa-link"></i> Resource URL/Link *</label>
                    <input type="url" id="resource_url" name="resource_url" class="form-control" 
                           placeholder="https://example.com/resource.pdf" required
                           value="<?php echo isset($_POST['resource_url']) ? htmlspecialchars($_POST['resource_url']) : ''; ?>">
                    <small style="color: var(--gray-600); display: block; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Enter a direct link to your resource (Google Drive, Dropbox, etc.)
                    </small>
                </div>

                <button type="submit" class="btn btn-primary" style="width: 100%; margin-top: 1rem;">
                    <i class="fas fa-upload"></i> Upload Resource
                </button>
            </form>
        </div>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
