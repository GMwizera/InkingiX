<?php

/**
 * InkingiX Rwanda - Admin Institutions Management
 * System admin only
 */

require_once '../includes/functions.php';
requireRole(['system_admin']);

$pageTitle = __('nav_institutions', 'Manage Institutions');

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $db->prepare("
            INSERT INTO institutions (name_en, name_rw, type, location, website, description_en, is_public)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            sanitize($_POST['name_en']),
            sanitize($_POST['name_rw'] ?? ''),
            sanitize($_POST['type']),
            sanitize($_POST['location']),
            sanitize($_POST['website'] ?? ''),
            sanitize($_POST['description_en'] ?? ''),
            isset($_POST['is_public']) ? 1 : 0
        ]);
        setFlashMessage('success', __('success', 'Institution added successfully.'));
    }

    if ($action === 'toggle_status') {
        $institutionId = intval($_POST['institution_id'] ?? 0);
        if ($institutionId > 0) {
            $stmt = $db->prepare("UPDATE institutions SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$institutionId]);
            setFlashMessage('success', __('profile_updated', 'Institution status updated.'));
        }
    }

    header('Location: institutions.php');
    exit;
}

// Get institutions
$stmt = $db->query("SELECT * FROM institutions ORDER BY type, name_en");
$institutions = $stmt->fetchAll();

// Include the admin header
require_once 'includes/header-admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-university me-2"></i><?php echo __('nav_institutions'); ?></h2>
    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
        <i class="fas fa-plus me-1"></i><?php echo __('edit', 'Add'); ?>
    </button>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th><?php echo __('admin_name'); ?></th>
                    <th>Type</th>
                    <th><?php echo __('institution_location'); ?></th>
                    <th>Public/Private</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($institutions)): ?>
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">
                            <?php echo __('admin_no_data_yet'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($institutions as $inst): ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($inst['name_en']); ?></strong>
                                <?php if ($inst['website']): ?>
                                    <br><small><a href="<?php echo $inst['website']; ?>" target="_blank" class="text-muted">
                                            <i class="fas fa-external-link-alt me-1"></i><?php echo __('institution_website'); ?>
                                        </a></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <span class="badge bg-<?php echo $inst['type'] === 'university' ? 'primary' : ($inst['type'] === 'tvet' ? 'success' : 'info'); ?>">
                                    <?php echo ucfirst($inst['type']); ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($inst['location']); ?></td>
                            <td><?php echo $inst['is_public'] ? 'Public' : 'Private'; ?></td>
                            <td>
                                <span class="badge bg-<?php echo $inst['is_active'] ? 'success' : 'secondary'; ?>">
                                    <?php echo $inst['is_active'] ? 'Active' : 'Inactive'; ?>
                                </span>
                            </td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="institution_id" value="<?php echo $inst['id']; ?>">
                                    <input type="hidden" name="action" value="toggle_status">
                                    <button type="submit" class="btn btn-sm btn-outline-<?php echo $inst['is_active'] ? 'warning' : 'success'; ?>">
                                        <i class="fas fa-<?php echo $inst['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
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

<!-- Add Modal -->
<div class="modal fade" id="addModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" name="action" value="add">
                <div class="modal-header">
                    <h5 class="modal-title"><?php echo __('edit', 'Add'); ?> Institution</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('admin_name'); ?> (English) *</label>
                        <input type="text" class="form-control" name="name_en" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('admin_name'); ?> (Kinyarwanda)</label>
                        <input type="text" class="form-control" name="name_rw">
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Type *</label>
                            <select class="form-select" name="type" required>
                                <option value="university"><?php echo __('institutions_universities'); ?></option>
                                <option value="tvet"><?php echo __('institutions_tvet'); ?></option>
                                <option value="college"><?php echo __('institutions_colleges'); ?></option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label"><?php echo __('institution_location'); ?> *</label>
                            <input type="text" class="form-control" name="location" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('institution_website'); ?></label>
                        <input type="url" class="form-control" name="website" placeholder="https://...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><?php echo __('description', 'Description'); ?></label>
                        <textarea class="form-control" name="description_en" rows="2"></textarea>
                    </div>
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" name="is_public" id="is_public" checked>
                        <label class="form-check-label" for="is_public">Public Institution</label>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo __('edit', 'Add'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once 'includes/footer-admin.php'; ?>