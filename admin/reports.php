<?php
/**
 * EduBridge Rwanda - Admin Reports
 * Available to both system_admin and school_admin
 */

$pageTitle = __('admin_reports', 'Reports');

require_once '../includes/functions.php';
requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Build school filter for school admins
$schoolWhere = '';
$schoolParams = [];
if ($currentUser['role'] === 'school_admin') {
    $schoolWhere = " AND u.school_name = ?";
    $schoolParams = [$currentUser['school_name']];
}

// Get statistics
// Students by school (system admin only sees all schools)
if ($currentUser['role'] === 'system_admin') {
    $stmt = $db->query("
        SELECT school_name, COUNT(*) as student_count
        FROM users
        WHERE role = 'student' AND is_active = 1
        GROUP BY school_name
        ORDER BY student_count DESC
    ");
    $studentsBySchool = $stmt->fetchAll();
} else {
    $studentsBySchool = [];
}

// Assessments over time (last 30 days)
if ($currentUser['role'] === 'school_admin') {
    $stmt = $db->prepare("
        SELECT DATE(ua.completed_at) as date, COUNT(*) as count
        FROM user_assessments ua
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1 AND ua.completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)" . $schoolWhere . "
        GROUP BY DATE(ua.completed_at)
        ORDER BY date
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT DATE(completed_at) as date, COUNT(*) as count
        FROM user_assessments
        WHERE is_completed = 1 AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
        GROUP BY DATE(completed_at)
        ORDER BY date
    ");
}
$assessmentsByDate = $stmt->fetchAll();

// Top career matches
if ($currentUser['role'] === 'school_admin') {
    $stmt = $db->prepare("
        SELECT c.title_en, COUNT(cm.id) as match_count
        FROM career_matches cm
        JOIN careers c ON cm.career_id = c.id
        JOIN user_assessments ua ON cm.assessment_id = ua.id
        JOIN users u ON ua.user_id = u.id
        WHERE cm.rank = 1" . $schoolWhere . "
        GROUP BY c.id
        ORDER BY match_count DESC
        LIMIT 10
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT c.title_en, COUNT(cm.id) as match_count
        FROM career_matches cm
        JOIN careers c ON cm.career_id = c.id
        WHERE cm.rank = 1
        GROUP BY c.id
        ORDER BY match_count DESC
        LIMIT 10
    ");
}
$topCareers = $stmt->fetchAll();

// Category distribution
if ($currentUser['role'] === 'school_admin') {
    $stmt = $db->prepare("
        SELECT cc.name_en, cc.code, AVG(ar.percentage) as avg_score
        FROM assessment_results ar
        JOIN career_categories cc ON ar.category_id = cc.id
        JOIN user_assessments ua ON ar.assessment_id = ua.id
        JOIN users u ON ua.user_id = u.id
        WHERE 1=1" . $schoolWhere . "
        GROUP BY cc.id
        ORDER BY avg_score DESC
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT cc.name_en, cc.code, AVG(ar.percentage) as avg_score
        FROM assessment_results ar
        JOIN career_categories cc ON ar.category_id = cc.id
        GROUP BY cc.id
        ORDER BY avg_score DESC
    ");
}
$categoryDistribution = $stmt->fetchAll();

// Student grade distribution
if ($currentUser['role'] === 'school_admin') {
    $stmt = $db->prepare("
        SELECT grade_level, COUNT(*) as count
        FROM users u
        WHERE role = 'student' AND grade_level IS NOT NULL" . $schoolWhere . "
        GROUP BY grade_level
        ORDER BY grade_level
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT grade_level, COUNT(*) as count
        FROM users
        WHERE role = 'student' AND grade_level IS NOT NULL
        GROUP BY grade_level
        ORDER BY grade_level
    ");
}
$gradeDistribution = $stmt->fetchAll();

// Include the admin header
require_once 'includes/header-admin.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2>
        <i class="fas fa-chart-bar me-2"></i><?php echo __('admin_reports'); ?>
        <?php if ($currentUser['role'] === 'school_admin'): ?>
        <small class="text-muted fs-6">- <?php echo htmlspecialchars($currentUser['school_name']); ?></small>
        <?php endif; ?>
    </h2>
    <button class="btn btn-outline-primary" onclick="window.print()">
        <i class="fas fa-print me-1"></i>Print
    </button>
</div>

<div class="row">
    <!-- Top Careers -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i><?php echo __('admin_top_careers_wanted'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($topCareers)): ?>
                <p class="text-muted"><?php echo __('admin_no_data_yet'); ?></p>
                <?php else: ?>
                <?php foreach ($topCareers as $index => $career): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>
                        <span class="badge bg-<?php echo $index < 3 ? 'primary' : 'secondary'; ?> me-2"><?php echo $index + 1; ?></span>
                        <?php echo htmlspecialchars($career['title_en']); ?>
                    </span>
                    <span class="badge bg-light text-dark"><?php echo $career['match_count']; ?></span>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Category Distribution -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i><?php echo __('admin_interest_clusters'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($categoryDistribution)): ?>
                <p class="text-muted"><?php echo __('admin_no_data_yet'); ?></p>
                <?php else: ?>
                <?php foreach ($categoryDistribution as $cat): ?>
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="badge category-badge-<?php echo $cat['code']; ?> me-2"><?php echo $cat['name_en']; ?></span>
                        <span><?php echo number_format($cat['avg_score'] ?? 0, 1); ?>%</span>
                    </div>
                    <div class="progress" style="height: 10px;">
                        <div class="progress-bar category-<?php echo $cat['code']; ?>" style="width: <?php echo $cat['avg_score'] ?? 0; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($currentUser['role'] === 'system_admin' && !empty($studentsBySchool)): ?>
    <!-- Students by School (System Admin only) -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-school me-2"></i>Students by School</h5>
            </div>
            <div class="card-body">
                <?php
                $maxStudents = !empty($studentsBySchool) ? max(array_column($studentsBySchool, 'student_count')) : 1;
                foreach (array_slice($studentsBySchool, 0, 8) as $school):
                ?>
                <div class="mb-2">
                    <div class="d-flex justify-content-between mb-1">
                        <small><?php echo htmlspecialchars($school['school_name'] ?: 'Unknown'); ?></small>
                        <small><?php echo $school['student_count']; ?></small>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" style="width: <?php echo ($school['student_count'] / $maxStudents) * 100; ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Grade Distribution -->
    <div class="col-md-<?php echo $currentUser['role'] === 'system_admin' ? '6' : '12'; ?> mb-4">
        <div class="card h-100">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i><?php echo __('admin_grade_distribution'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (empty($gradeDistribution)): ?>
                <p class="text-muted"><?php echo __('admin_no_data_yet'); ?></p>
                <?php else: ?>
                <div class="row text-center">
                    <?php foreach ($gradeDistribution as $grade): ?>
                    <div class="col-4 col-md-2 mb-3">
                        <div class="border rounded p-2">
                            <div class="h4 text-primary mb-0"><?php echo $grade['count']; ?></div>
                            <small class="text-muted"><?php echo $grade['grade_level']; ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Assessment Timeline -->
<div class="card mb-4">
    <div class="card-header">
        <h5 class="mb-0"><i class="fas fa-calendar me-2"></i>Assessments (Last 30 Days)</h5>
    </div>
    <div class="card-body">
        <?php if (empty($assessmentsByDate)): ?>
        <p class="text-muted"><?php echo __('admin_no_data_yet'); ?></p>
        <?php else: ?>
        <div class="table-responsive">
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th><?php echo __('admin_date'); ?></th>
                        <th><?php echo __('admin_assessments_completed'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($assessmentsByDate as $day): ?>
                    <tr>
                        <td><?php echo date('M j, Y', strtotime($day['date'])); ?></td>
                        <td>
                            <div class="progress" style="height: 20px; width: 200px;">
                                <div class="progress-bar" style="width: <?php echo min($day['count'] * 10, 100); ?>%">
                                    <?php echo $day['count']; ?>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer-admin.php'; ?>
