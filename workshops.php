<?php
session_start();
require_once 'config/database.php';

$conn = getDBConnection();

// Get filter parameters
$category = isset($_GET['category']) ? $_GET['category'] : '';
$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Build query
$query = "SELECT w.*, u.fullname as teacher_name,
          (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
          FROM workshops w 
          LEFT JOIN users u ON w.teacher_id = u.id 
          WHERE w.status = 'approved' AND w.start_date > NOW()";

$params = [];
$types = '';

if (!empty($category)) {
    $query .= " AND w.category = ?";
    $params[] = $category;
    $types .= 's';
}

if (!empty($mode)) {
    $query .= " AND w.mode = ?";
    $params[] = $mode;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (w.title LIKE ? OR w.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY w.start_date ASC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get categories for filter
$categories_query = "SELECT DISTINCT category FROM workshops WHERE category IS NOT NULL AND category != '' ORDER BY category";
$categories = $conn->query($categories_query);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Browse Workshops | SkillShare</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 3rem 2rem;
            text-align: center;
        }
        
        .page-header h1 {
            font-size: 2.5rem;
            margin-bottom: 0.5rem;
        }
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-md);
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .workshop-card {
            background: white;
            border-radius: var(--radius-lg);
            padding: 1.5rem;
            box-shadow: var(--shadow-md);
            transition: var(--transition);
            border-left: 4px solid var(--primary);
        }
        
        .workshop-card:hover {
            transform: translateY(-5px);
            box-shadow: var(--shadow-xl);
        }
        
        .workshop-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
        }
        
        .workshop-meta {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
            flex-wrap: wrap;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--gray-600);
            font-size: 0.875rem;
        }
        
        .btn-sm {
            padding: 0.5rem 1rem;
            font-size: 0.875rem;
        }
        
        @media (max-width: 768px) {
            .filter-row {
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
                    <li><a href="workshops.php"><i class="fas fa-chalkboard-teacher"></i> Workshops</a></li>
                    <?php if (isset($_SESSION['user'])): ?>
                        <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                    <?php else: ?>
                        <li><a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a></li>
                        <li><a href="register.php" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">Get Started</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <h1><i class="fas fa-compass"></i> Explore Workshops</h1>
        <p>Discover amazing learning opportunities and enhance your skills</p>
    </div>

    <div class="container" style="margin-top: 2rem;">
        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="workshops.php">
                <div class="filter-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="search"><i class="fas fa-search"></i> Search</label>
                        <input type="text" id="search" name="search" class="form-control" 
                               placeholder="Search workshops..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="category"><i class="fas fa-tag"></i> Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo $category === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="mode"><i class="fas fa-laptop"></i> Mode</label>
                        <select id="mode" name="mode" class="form-control">
                            <option value="">All Modes</option>
                            <option value="online" <?php echo $mode === 'online' ? 'selected' : ''; ?>>Online</option>
                            <option value="offline" <?php echo $mode === 'offline' ? 'selected' : ''; ?>>Offline</option>
                            <option value="hybrid" <?php echo $mode === 'hybrid' ? 'selected' : ''; ?>>Hybrid</option>
                        </select>
                    </div>
                    
                    <div>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-filter"></i> Filter
                        </button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Workshop Results -->
        <div style="margin-bottom: 1.5rem;">
            <h2 style="color: var(--gray-700); font-size: 1.25rem;">
                <i class="fas fa-list"></i> 
                <?php echo $workshops->num_rows; ?> Workshop<?php echo $workshops->num_rows !== 1 ? 's' : ''; ?> Found
            </h2>
        </div>

        <?php if ($workshops->num_rows > 0): ?>
            <div class="card-grid">
                <?php while ($workshop = $workshops->fetch_assoc()): 
                    $is_enrolled = false;
                    if (isset($_SESSION['user_id'])) {
                        $user_id = $_SESSION['user_id'];
                        $conn_check = getDBConnection();
                        $check_stmt = $conn_check->prepare("SELECT id FROM enrollments WHERE student_id = ? AND workshop_id = ?");
                        $check_stmt->bind_param("ii", $user_id, $workshop['id']);
                        $check_stmt->execute();
                        $is_enrolled = $check_stmt->get_result()->num_rows > 0;
                        $check_stmt->close();
                        $conn_check->close();
                    }
                ?>
                    <div class="workshop-card">
                        <div class="workshop-header">
                            <div>
                                <h3 style="margin: 0 0 0.5rem 0; color: var(--gray-900);">
                                    <?php echo htmlspecialchars($workshop['title']); ?>
                                </h3>
                                <?php if ($workshop['category']): ?>
                                    <span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category']); ?></span>
                                <?php endif; ?>
                                <span class="badge" style="background: <?php 
                                    echo $workshop['mode'] === 'online' ? '#3b82f6' : ($workshop['mode'] === 'offline' ? '#10b981' : '#f59e0b'); 
                                ?>; color: white;">
                                    <?php echo ucfirst($workshop['mode']); ?>
                                </span>
                            </div>
                        </div>
                        
                        <p style="color: var(--gray-600); margin: 0.5rem 0;">
                            <?php echo htmlspecialchars(substr($workshop['description'], 0, 120)) . '...'; ?>
                        </p>
                        
                        <div class="workshop-meta">
                            <div class="meta-item">
                                <i class="fas fa-user-tie"></i>
                                <?php echo htmlspecialchars($workshop['teacher_name'] ?? 'TBA'); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-calendar"></i>
                                <?php echo date('M d, Y', strtotime($workshop['start_date'])); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-clock"></i>
                                <?php echo date('h:i A', strtotime($workshop['start_date'])); ?>
                            </div>
                            <div class="meta-item">
                                <i class="fas fa-users"></i>
                                <?php echo $workshop['enrolled_count']; ?>/<?php echo $workshop['max_participants']; ?>
                            </div>
                        </div>
                        
                        <div style="margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap;">
                            <?php if (isset($_SESSION['user']) && $_SESSION['user_role'] === 'student'): ?>
                                <?php if ($is_enrolled): ?>
                                    <button class="btn btn-secondary btn-sm" disabled>
                                        <i class="fas fa-check"></i> Already Enrolled
                                    </button>
                                <?php elseif ($workshop['enrolled_count'] >= $workshop['max_participants']): ?>
                                    <button class="btn btn-danger btn-sm" disabled>
                                        <i class="fas fa-times"></i> Full
                                    </button>
                                <?php else: ?>
                                    <a href="enroll.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus-circle"></i> Enroll Now
                                    </a>
                                <?php endif; ?>
                            <?php elseif (!isset($_SESSION['user'])): ?>
                                <a href="login.php" class="btn btn-primary btn-sm">
                                    <i class="fas fa-sign-in-alt"></i> Login to Enroll
                                </a>
                            <?php endif; ?>
                            
                            <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-outline btn-sm">
                                <i class="fas fa-info-circle"></i> View Details
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No workshops found matching your criteria. Try adjusting your filters.
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
