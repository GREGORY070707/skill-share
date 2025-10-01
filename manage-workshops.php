<?php
session_start();
require_once 'config/database.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user']) || $_SESSION['user_role'] !== 'admin') {
    header("Location: login.php");
    exit;
}

// Get filter parameters
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$category_filter = isset($_GET['category']) ? $_GET['category'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Get success/error messages
$success = isset($_SESSION['success']) ? $_SESSION['success'] : '';
$error = isset($_SESSION['error']) ? $_SESSION['error'] : '';
unset($_SESSION['success'], $_SESSION['error']);

$conn = getDBConnection();

// Build query
$query = "SELECT w.*, u.fullname as teacher_name, 
          (SELECT COUNT(*) FROM enrollments e WHERE e.workshop_id = w.id) as enrolled_count
          FROM workshops w 
          LEFT JOIN users u ON w.teacher_id = u.id 
          WHERE 1=1";
$params = [];
$types = '';

if (!empty($status_filter)) {
    $query .= " AND w.status = ?";
    $params[] = $status_filter;
    $types .= 's';
}

if (!empty($category_filter)) {
    $query .= " AND w.category = ?";
    $params[] = $category_filter;
    $types .= 's';
}

if (!empty($search)) {
    $query .= " AND (w.title LIKE ? OR w.description LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'ss';
}

$query .= " ORDER BY w.created_at DESC";

$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$workshops = $stmt->get_result();
$stmt->close();

// Get categories for filter
$categories = $conn->query("SELECT DISTINCT category FROM workshops WHERE category IS NOT NULL ORDER BY category");

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Workshops | Admin</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            background: #f9fafb;
        }
        
        .page-header {
            background: linear-gradient(135deg, #434343 0%, #000000 100%);
            color: white;
            padding: 2rem;
            margin-bottom: 2rem;
        }
        
        .filters {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }
        
        .filter-row {
            display: grid;
            grid-template-columns: 2fr 1fr 1fr auto;
            gap: 1rem;
            align-items: end;
        }
        
        .data-table {
            background: white;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th {
            background: #f9fafb;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
            border-bottom: 2px solid #e5e7eb;
        }
        
        .data-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            color: #6b7280;
        }
        
        .data-table tr:hover {
            background: #f9fafb;
        }
        
        .action-btn {
            padding: 0.25rem 0.75rem;
            font-size: 0.875rem;
            margin: 0 0.25rem;
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
                    <li><a href="admin-dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li><a href="manage-users.php"><i class="fas fa-users-cog"></i> Users</a></li>
                    <li><a href="manage-workshops.php"><i class="fas fa-chalkboard"></i> Workshops</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <div class="page-header">
        <div class="container">
            <h1 style="margin: 0; font-size: 2rem;"><i class="fas fa-chalkboard-teacher"></i> Manage Workshops</h1>
            <p style="margin: 0.5rem 0 0 0; opacity: 0.9;">View, approve, and manage all workshops</p>
        </div>
    </div>

    <div class="container">
        <!-- Alert Messages -->
        <?php if ($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Filters -->
        <div class="filters">
            <form method="GET" action="manage-workshops.php">
                <div class="filter-row">
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="search"><i class="fas fa-search"></i> Search</label>
                        <input type="text" id="search" name="search" class="form-control" 
                               placeholder="Search workshops..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="status"><i class="fas fa-toggle-on"></i> Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>Pending</option>
                            <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approved</option>
                            <option value="ongoing" <?php echo $status_filter === 'ongoing' ? 'selected' : ''; ?>>Ongoing</option>
                            <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Completed</option>
                            <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                        </select>
                    </div>
                    
                    <div class="form-group" style="margin-bottom: 0;">
                        <label for="category"><i class="fas fa-tag"></i> Category</label>
                        <select id="category" name="category" class="form-control">
                            <option value="">All Categories</option>
                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                <option value="<?php echo htmlspecialchars($cat['category']); ?>" 
                                        <?php echo $category_filter === $cat['category'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['category']); ?>
                                </option>
                            <?php endwhile; ?>
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

        <!-- Results -->
        <div style="margin-bottom: 1rem;">
            <h2 style="color: #1f2937; font-size: 1.25rem; margin: 0;">
                <i class="fas fa-list"></i> <?php echo $workshops->num_rows; ?> Workshop<?php echo $workshops->num_rows !== 1 ? 's' : ''; ?> Found
            </h2>
        </div>

        <?php if ($workshops->num_rows > 0): ?>
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Title</th>
                            <th>Teacher</th>
                            <th>Category</th>
                            <th>Start Date</th>
                            <th>Enrolled</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($workshop = $workshops->fetch_assoc()): ?>
                            <tr>
                                <td><strong>#<?php echo $workshop['id']; ?></strong></td>
                                <td>
                                    <strong style="color: #1f2937;"><?php echo htmlspecialchars($workshop['title']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($workshop['teacher_name'] ?? 'N/A'); ?></td>
                                <td>
                                    <span class="badge badge-primary"><?php echo htmlspecialchars($workshop['category'] ?? 'General'); ?></span>
                                </td>
                                <td><?php echo date('M d, Y', strtotime($workshop['start_date'])); ?></td>
                                <td><?php echo $workshop['enrolled_count']; ?>/<?php echo $workshop['max_participants']; ?></td>
                                <td>
                                    <span class="badge badge-<?php 
                                        echo $workshop['status'] === 'approved' ? 'success' : 
                                            ($workshop['status'] === 'pending' ? 'warning' : 
                                            ($workshop['status'] === 'ongoing' ? 'primary' : 
                                            ($workshop['status'] === 'completed' ? 'secondary' : 'danger'))); 
                                    ?>">
                                        <?php echo ucfirst($workshop['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($workshop['status'] === 'pending'): ?>
                                        <a href="approve-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-success action-btn">
                                            <i class="fas fa-check"></i>
                                        </a>
                                        <a href="reject-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-danger action-btn">
                                            <i class="fas fa-times"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="edit-workshop.php?id=<?php echo $workshop['id']; ?>" class="btn btn-primary action-btn">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="workshop-details.php?id=<?php echo $workshop['id']; ?>" class="btn btn-secondary action-btn">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="delete-workshop.php?id=<?php echo $workshop['id']; ?>" 
                                       class="btn btn-danger action-btn"
                                       onclick="return confirm('Are you sure you want to delete this workshop?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> No workshops found matching your criteria.
            </div>
        <?php endif; ?>
    </div>

    <footer style="margin-top: 4rem;">
        <p>&copy; <?php echo date('Y'); ?> Skill-Sharing Network | Empowering Through Education</p>
    </footer>
</body>
</html>
