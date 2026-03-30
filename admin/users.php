<?php
/**
 * EduBridge Rwanda - Admin Users Management
 */

$pageTitle = 'Manage Users';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);

    if ($action === 'toggle_status' && $userId > 0) {
        $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ?");
        $stmt->execute([$userId, $currentUser['id']]);
        setFlashMessage('success', 'User status updated.');
    }

    if ($action === 'change_role' && $userId > 0 && $currentUser['role'] === 'system_admin') {
        $newRole = sanitize($_POST['new_role'] ?? '');
        if (in_array($newRole, ['student', 'school_admin', 'system_admin'])) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            $stmt->execute([$newRole, $userId, $currentUser['id']]);
            setFlashMessage('success', 'User role updated.');
        }
    }

    header('Location: users.php');
    exit;
}

// Get filters
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($roleFilter)) {
    $sql .= " AND role = ?";
    $params[] = $roleFilter;
}

if (!empty($searchTerm)) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ? OR school_name LIKE ?)";
    $search = "%$searchTerm%";
    $params = array_merge($params, [$search, $search, $search, $search]);
}

$sql .= " ORDER BY created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i><?php echo SITE_NAME; ?> Admin
            </a>
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home me-1"></i>Main Site
                </a>
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="careers.php">
                                <i class="fas fa-briefcase"></i> Careers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="institutions.php">
                                <i class="fas fa-university"></i> Institutions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="questions.php">
                                <i class="fas fa-question-circle"></i> Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php displayFlashMessage(); ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-users me-2"></i>Manage Users</h2>
                    <span class="badge bg-primary"><?php echo count($users); ?> users</span>
                </div>

                <!-- Filters -->
                <div class="card mb-4">
                    <div class="card-body">
                        <form method="GET" class="row g-3">
                            <div class="col-md-5">
                                <input type="text" class="form-control" name="search"
                                       placeholder="Search by name, email, school..."
                                       value="<?php echo htmlspecialchars($searchTerm); ?>">
                            </div>
                            <div class="col-md-4">
                                <select class="form-select" name="role">
                                    <option value="">All Roles</option>
                                    <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>>Students</option>
                                    <option value="school_admin" <?php echo $roleFilter === 'school_admin' ? 'selected' : ''; ?>>School Admins</option>
                                    <option value="system_admin" <?php echo $roleFilter === 'system_admin' ? 'selected' : ''; ?>>System Admins</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search me-1"></i>Search
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Users Table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>School</th>
                                    <th>Grade</th>
                                    <th>Role</th>
                                    <th>Status</th>
                                    <th>Joined</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                                    </td>
                                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                                    <td><small><?php echo htmlspecialchars($user['school_name'] ?: '-'); ?></small></td>
                                    <td><?php echo $user['grade_level'] ?: '-'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['role'] === 'system_admin' ? 'danger' : ($user['role'] === 'school_admin' ? 'warning' : 'secondary'); ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $user['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $user['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td><small><?php echo date('M j, Y', strtotime($user['created_at'])); ?></small></td>
                                    <td>
                                        <?php if ($user['id'] !== $currentUser['id']): ?>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $user['is_active'] ? 'warning' : 'success'; ?>"
                                                    title="<?php echo $user['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                                <i class="fas fa-<?php echo $user['is_active'] ? 'ban' : 'check'; ?>"></i>
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
