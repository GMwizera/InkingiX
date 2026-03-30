<?php
/**
 * EduBridge Rwanda - Admin Questions Management
 * System admin only
 */

$pageTitle = __('admin_questions', 'Assessment Questions');

require_once '../includes/functions.php';
requireRole(['system_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get questions
$stmt = $db->query("
    SELECT q.*, cc.name_en as category_name, cc.code as category_code
    FROM assessment_questions q
    JOIN career_categories cc ON q.category_id = cc.id
    ORDER BY q.order_number
");
$questions = $stmt->fetchAll();

$categories = getCareerCategories();

// Include the admin header
require_once 'includes/header-admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-question-circle me-2"></i><?php echo __('admin_questions'); ?></h2>
    <span class="badge bg-primary"><?php echo count($questions); ?> <?php echo __('dashboard_questions'); ?></span>
</div>

<!-- Category Summary -->
<div class="row mb-4">
    <?php foreach ($categories as $cat): ?>
    <?php
    $catQuestions = array_filter($questions, fn($q) => $q['category_id'] == $cat['id']);
    ?>
    <div class="col-md-2 mb-2">
        <div class="card text-center">
            <div class="card-body py-2">
                <span class="badge category-badge-<?php echo $cat['code']; ?> mb-1"><?php echo $cat['code']; ?></span>
                <div class="h5 mb-0"><?php echo count($catQuestions); ?></div>
                <small class="text-muted"><?php echo $cat['name_en']; ?></small>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Questions Table -->
<div class="card">
    <div class="table-responsive">
        <table class="table table-hover mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Question (English)</th>
                    <th>Category</th>
                    <th>Weight</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($questions)): ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">
                        <?php echo __('admin_no_data_yet'); ?>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($questions as $q): ?>
                <tr>
                    <td><?php echo $q['order_number']; ?></td>
                    <td>
                        <?php echo htmlspecialchars($q['question_en']); ?>
                        <?php if ($q['question_rw']): ?>
                        <br><small class="text-muted"><?php echo htmlspecialchars($q['question_rw']); ?></small>
                        <?php endif; ?>
                    </td>
                    <td>
                        <span class="badge category-badge-<?php echo $q['category_code']; ?>">
                            <?php echo $q['category_name']; ?>
                        </span>
                    </td>
                    <td><?php echo $q['weight']; ?></td>
                    <td>
                        <span class="badge bg-<?php echo $q['is_active'] ? 'success' : 'secondary'; ?>">
                            <?php echo $q['is_active'] ? 'Active' : 'Inactive'; ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="alert alert-info mt-4">
    <i class="fas fa-info-circle me-2"></i>
    Assessment questions are based on the RIASEC (Holland Codes) model. Each category should have 5 questions for balanced assessment results.
</div>

<?php require_once 'includes/footer-admin.php'; ?>
