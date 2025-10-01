<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is a teacher
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'teacher') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $mode = trim($_POST['mode'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $start_date = trim($_POST['start_date'] ?? '');
    $start_time = trim($_POST['start_time'] ?? '');
    $end_date = trim($_POST['end_date'] ?? '');
    $end_time = trim($_POST['end_time'] ?? '');
    $max_participants = intval($_POST['max_participants'] ?? 50);
    
    // Validation
    if (empty($title) || empty($description) || empty($mode) || empty($start_date) || empty($start_time)) {
        $error = 'Please fill in all required fields.';
    } else {
        // Combine date and time
        $start_datetime = $start_date . ' ' . $start_time;
        $end_datetime = !empty($end_date) && !empty($end_time) ? $end_date . ' ' . $end_time : $start_datetime;
        
        $conn = getDBConnection();
        
        // Insert workshop
        $query = "INSERT INTO workshops (title, description, category, teacher_id, mode, location, start_date, end_date, max_participants, status) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssissssi", $title, $description, $category, $user_id, $mode, $location, $start_datetime, $end_datetime, $max_participants);
        
        if ($stmt->execute()) {
            $_SESSION['success'] = 'Workshop created successfully! Waiting for admin approval.';
            header("Location: teacher-dashboard.php");
            exit;
        } else {
            $error = 'Failed to create workshop. Please try again.';
        }
        
        $stmt->close();
        $conn->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Workshop | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .create-container {
            max-width: 900px;
            margin: 2rem auto;
            padding: 2rem;
        }
        
        .create-box {
            background: white;
            padding: 2.5rem;
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow-xl);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1.5rem;
        }
        
        .icon-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .icon-circle {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: white;
            box-shadow: 0 10px 30px rgba(99, 102, 241, 0.3);
        }
        
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
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

    <div class="create-container">
        <div style="margin-bottom: 1.5rem;">
            <a href="teacher-dashboard.php" style="color: var(--primary); text-decoration: none;">
                <i class="fas fa-arrow-left"></i> Back to Dashboard
            </a>
        </div>

        <div class="create-box">
            <div class="icon-header">
                <div class="icon-circle">
                    <i class="fas fa-plus"></i>
                </div>
                <h1 style="color: var(--gray-900); margin-bottom: 0.5rem;">Create Workshop</h1>
                <p style="color: var(--gray-600);">Start a new workshop session and share your expertise</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="create-workshop.php">
                <div class="form-group">
                    <label for="title"><i class="fas fa-heading"></i> Workshop Title *</label>
                    <input type="text" id="title" name="title" class="form-control" 
                           placeholder="e.g., Introduction to Python Programming" required
                           value="<?php echo isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>">
                </div>

                <div class="form-group">
                    <label for="description"><i class="fas fa-align-left"></i> Description *</label>
                    <textarea id="description" name="description" class="form-control" 
                              rows="5" placeholder="Describe what students will learn in this workshop..." required><?php echo isset($_POST['description']) ? htmlspecialchars($_POST['description']) : ''; ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="category"><i class="fas fa-tag"></i> Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">-- Select Category --</option>
                            <option value="Technology" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Technology') ? 'selected' : ''; ?>>Technology</option>
                            <option value="Business" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Business') ? 'selected' : ''; ?>>Business</option>
                            <option value="Finance" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Finance') ? 'selected' : ''; ?>>Finance</option>
                            <option value="Marketing" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                            <option value="Design" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Design') ? 'selected' : ''; ?>>Design</option>
                            <option value="Arts" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Arts') ? 'selected' : ''; ?>>Arts & Crafts</option>
                            <option value="Health" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Health') ? 'selected' : ''; ?>>Health & Wellness</option>
                            <option value="Language" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Language') ? 'selected' : ''; ?>>Language</option>
                            <option value="Other" <?php echo (isset($_POST['category']) && $_POST['category'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="mode"><i class="fas fa-laptop"></i> Mode *</label>
                        <select id="mode" name="mode" class="form-control" required>
                            <option value="">-- Select Mode --</option>
                            <option value="online" <?php echo (isset($_POST['mode']) && $_POST['mode'] === 'online') ? 'selected' : ''; ?>>Online</option>
                            <option value="offline" <?php echo (isset($_POST['mode']) && $_POST['mode'] === 'offline') ? 'selected' : ''; ?>>Offline</option>
                            <option value="hybrid" <?php echo (isset($_POST['mode']) && $_POST['mode'] === 'hybrid') ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                </div>

                <div class="form-group" id="location-group">
                    <label for="location"><i class="fas fa-map-marker-alt"></i> Location</label>
                    <input type="text" id="location" name="location" class="form-control" 
                           placeholder="Enter venue address (for offline/hybrid mode)"
                           value="<?php echo isset($_POST['location']) ? htmlspecialchars($_POST['location']) : ''; ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="start_date"><i class="fas fa-calendar"></i> Start Date *</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" required
                               value="<?php echo isset($_POST['start_date']) ? $_POST['start_date'] : ''; ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="start_time"><i class="fas fa-clock"></i> Start Time *</label>
                        <input type="time" id="start_time" name="start_time" class="form-control" required
                               value="<?php echo isset($_POST['start_time']) ? $_POST['start_time'] : ''; ?>">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="end_date"><i class="fas fa-calendar-check"></i> End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control"
                               value="<?php echo isset($_POST['end_date']) ? $_POST['end_date'] : ''; ?>"
                               min="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="end_time"><i class="fas fa-clock"></i> End Time</label>
                        <input type="time" id="end_time" name="end_time" class="form-control"
                               value="<?php echo isset($_POST['end_time']) ? $_POST['end_time'] : ''; ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="max_participants"><i class="fas fa-users"></i> Maximum Participants</label>
                    <input type="number" id="max_participants" name="max_participants" class="form-control" 
                           min="1" max="500" value="<?php echo isset($_POST['max_participants']) ? $_POST['max_participants'] : '50'; ?>">
                    <small style="color: var(--gray-600); display: block; margin-top: 0.5rem;">
                        <i class="fas fa-info-circle"></i> Default: 50 participants
                    </small>
                </div>

                <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid var(--gray-200);">
                    <button type="submit" class="btn btn-primary" style="width: 100%; font-size: 1.1rem; padding: 1rem;">
                        <i class="fas fa-plus-circle"></i> Create Workshop
                    </button>
                    <p style="text-align: center; margin-top: 1rem; color: var(--gray-600); font-size: 0.875rem;">
                        <i class="fas fa-info-circle"></i> Your workshop will be submitted for admin approval
                    </p>
                </div>
            </form>
        </div>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>

    <script>
        // Show/hide location field based on mode
        document.getElementById('mode').addEventListener('change', function() {
            const locationGroup = document.getElementById('location-group');
            const locationInput = document.getElementById('location');
            
            if (this.value === 'online') {
                locationGroup.style.display = 'none';
                locationInput.required = false;
            } else {
                locationGroup.style.display = 'block';
                locationInput.required = this.value === 'offline';
            }
        });

        // Trigger on page load
        document.getElementById('mode').dispatchEvent(new Event('change'));

        // Set end date to match start date when start date changes
        document.getElementById('start_date').addEventListener('change', function() {
            const endDate = document.getElementById('end_date');
            if (!endDate.value || endDate.value < this.value) {
                endDate.value = this.value;
            }
            endDate.min = this.value;
        });
    </script>
</body>
</html>
