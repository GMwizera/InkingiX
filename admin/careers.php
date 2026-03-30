<?php

/**
 * InkingiX Rwanda - Admin Careers Management
 * System admin only - School admins don't manage careers
 */

require_once '../includes/functions.php';
requireRole(['system_admin']);

$pageTitle = __('nav_careers', 'Manage Careers');

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $careerId = intval($_POST['career_id'] ?? 0);

    if ($action === 'toggle_status' && $careerId > 0) {
        $stmt = $db->prepare("UPDATE careers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$careerId]);
        setFlashMessage('success', __('profile_updated', 'Career status updated.'));
    }

    if ($action === 'delete' && $careerId > 0) {
        $stmt = $db->prepare("DELETE FROM careers WHERE id = ?");
        $stmt->execute([$careerId]);
        setFlashMessage('success', __('delete', 'Career deleted.'));
    }

    header('Location: careers.php');
    exit;
}

// Get careers
$stmt = $db->query("
    SELECT c.*, cc.name_en as category_name, cc.code as category_code
    FROM careers c
    JOIN career_categories cc ON c.primary_category_id = cc.id
    ORDER BY c.title_en
");
$careers = $stmt->fetchAll();

$categories = getCareerCategories();

// Include the admin header
require_once 'includes/header-admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-briefcase me-2"></i><?php echo __('nav_careers'); ?></h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCareerModal">
        <i class="fas fa-plus me-1"></i><?php echo __('edit', 'Add'); ?> Career
    </button>
</div>

<!-- Careers Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th><?php echo __('salary_range'); ?></th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($careers)): ?>
                    <tr>
                        <td colspan="5" class="text-center text-muted py-4">
                            <?php echo __('admin_no_data_yet'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($careers as $career): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($career['title_en']); ?></strong>
                                <?php if ($career['title_rw']): ?>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($career['title_rw']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge category-badge-<?php echo $career['category_code']; ?>">
                                    <?php echo $career['category_name']; ?>
                                </span>
                            </td>
                            <td>
                                <?php echo formatCurrency($career['salary_range_min']); ?> -
                                <?php echo formatCurrency($career['salary_range_max']); ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $career['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $career['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <a href="career-edit.php?id=<?php echo $career['id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="career_id" value="<?php echo $career['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="btn btn-sm btn-outline-<?php echo $career['is_active'] ? 'warning' : 'success'; ?>">
                                        <i class="fas fa-<?php echo $career['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Career Modal -->
<div class="modal fade" id="addCareerModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form method="POST" action="career-add.php">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('edit', 'Add'); ?> Career</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Title (English)</label>
                            <input type="text" class="form-control" name="title_en" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Title (Kinyarwanda)</label>
                            <input type="text" class="form-control" name="title_rw">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('description'); ?> (English)</label>
                        <textarea class="form-control" name="description_en" rows="3" required></textarea>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Primary Category</label>
                            <select class="form-select" name="primary_category_id" required>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_en']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Secondary Category</label>
                            <select class="form-select" name="secondary_category_id">
                                <option value="">None</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_en']; ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Min Salary (RWF)</label>
                            <input type="number" class="form-control" name="salary_range_min" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Max Salary (RWF)</label>
                            <input type="number" class="form-control" name="salary_range_max" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('required_skills'); ?> (comma separated)</label>
                        <input type="text" class="form-control" name="required_skills_en" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('education_pathways'); ?></label>
                        <textarea class="form-control" name="education_path_en" rows="2" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo __('edit', 'Add'); ?> Career</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer-admin.php'; ?>