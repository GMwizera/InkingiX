<?php

/**
 * InkingiX Rwanda - Admin Users Management
 */

require_once '../includes/functions.php';
requireRole(['system_admin', 'school_admin']);

$pageTitle = __('admin_users', 'Manage Users');

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = intval($_POST['user_id'] ?? 0);

    if ($action === 'toggle_status' && $userId > 0) {
        // School admins can only manage students in their school
        if ($currentUser['role'] === 'school_admin') {
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ? AND role = 'student' AND school_name = ?");
            $stmt->execute([$userId, $currentUser['id'], $currentUser['school_name']]);
        } else {
            $stmt = $db->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND id != ?");
            $stmt->execute([$userId, $currentUser['id']]);
        }
        setFlashMessage('success', __('profile_updated', 'User status updated.'));
    }

    if ($action === 'change_role' && $userId > 0 && $currentUser['role'] === 'system_admin') {
        $newRole = sanitize($_POST['new_role'] ?? '');
        if (in_array($newRole, ['student', 'school_admin', 'system_admin'])) {
            $stmt = $db->prepare("UPDATE users SET role = ? WHERE id = ? AND id != ?");
            $stmt->execute([$newRole, $userId, $currentUser['id']]);
            setFlashMessage('success', __('profile_updated', 'User role updated.'));
        }
    }

    header('Location: users.php');
    exit;
}

// Get filters
$roleFilter = isset($_GET['role']) ? sanitize($_GET['role']) : '';
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query - school admins only see their school's students
$sql = "SELECT * FROM users WHERE 1=1";
$params = [];

if ($currentUser['role'] === 'school_admin') {
    $sql .= " AND school_name = ? AND role = 'student'";
    $params[] = $currentUser['school_name'];
}

if (!empty($roleFilter) && $currentUser['role'] === 'system_admin') {
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

// Include the admin header
require_once 'includes/header-admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-users me-2"></i>
        <?php echo $currentUser['role'] === 'school_admin' ? __('admin_students') : __('admin_users'); ?>
    </h2>
    <span class="badge bg-primary"><?php echo count($users); ?> <?php echo $currentUser['role'] === 'school_admin' ? __('admin_students') : __('admin_users'); ?></span>
</div>

<!-- Filters -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-<?php echo $currentUser['role'] === 'system_admin' ? '5' : '9'; ?>">
                <input type="text" class="form-control" name="search"
                    placeholder="<?php echo __('search', 'Search'); ?>..."
                    value="<?php echo htmlspecialchars($searchTerm); ?>">
            </div>
            <?php if ($currentUser['role'] === 'system_admin'): ?>
                <div class="col-md-4">
                    <select class="form-select" name="role">
                        <option value=""><?php echo __('admin_all_schools', 'All Roles'); ?></option>
                        <option value="student" <?php echo $roleFilter === 'student' ? 'selected' : ''; ?>><?php echo __('admin_students'); ?></option>
                        <option value="school_admin" <?php echo $roleFilter === 'school_admin' ? 'selected' : ''; ?>><?php echo __('admin_school_admin'); ?></option>
                        <option value="system_admin" <?php echo $roleFilter === 'system_admin' ? 'selected' : ''; ?>><?php echo __('admin_system_admin'); ?></option>
                    </select>
                </div>
            <?php endif; ?>
            <div class="col-md-3">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-search me-1"></i><?php echo __('search'); ?>
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
                    <th><?php echo __('admin_name'); ?></th>
                    <th>Email</th>
                    <?php if ($currentUser['role'] === 'system_admin'): ?>
                        <th><?php echo __('profile_school'); ?></th>
                    <?php endif; ?>
                    <th><?php echo __('profile_grade', 'Grade'); ?></th>
                    <?php if ($currentUser['role'] === 'system_admin'): ?>
                        <th>Role</th>
                    <?php endif; ?>
                    <th>Status</th>
                    <th><?php echo __('admin_date'); ?></th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="<?php echo $currentUser['role'] === 'system_admin' ? '8' : '5'; ?>" class="text-center text-muted py-4">
                            <?php echo __('admin_no_data_yet'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></strong>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <?php if ($currentUser['role'] === 'system_admin'): ?>
                                <td><small><?php echo htmlspecialchars($user['school_name'] ?: '-'); ?></small></td>
                            <?php endif; ?>
                            <td><?php echo $user['grade_level'] ?: '-'; ?></td>
                            <?php if ($currentUser['role'] === 'system_admin'): ?>
                                <td>
                                    <span class="badge bg-<?php echo $user['role'] === 'system_admin' ? 'danger' : ($user['role'] === 'school_admin' ? 'warning' : 'secondary'); ?>">
                                        <?php echo ucfirst(str_replace('_', ' ', $user['role'])); ?>
                                    </span>
                                </td>
                            <?php endif; ?>
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
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once 'includes/footer-admin.php'; ?>