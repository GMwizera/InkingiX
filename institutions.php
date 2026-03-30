<?php
/**
 * EduBridge Rwanda - Institutions List
 */

$pageTitle = 'Educational Institutions';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: institutions.php');
    exit;
}

$db = getDBConnection();

// Get filter
$typeFilter = isset($_GET['type']) ? sanitize($_GET['type']) : '';

// Get institutions
$sql = "SELECT * FROM institutions WHERE is_active = 1";
$params = [];

if (!empty($typeFilter)) {
    $sql .= " AND type = ?";
    $params[] = $typeFilter;
}

$sql .= " ORDER BY type, name_en";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$institutions = $stmt->fetchAll();

// Group by type
$grouped = [
    'university' => [],
    'tvet' => [],
    'college' => []
];

foreach ($institutions as $inst) {
    $grouped[$inst['type']][] = $inst;
}

// Use sidebar for logged-in users, top navbar for guests
if (isLoggedIn()) {
    require_once 'includes/header-dashboard.php';
} else {
    require_once 'includes/header.php';
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-university me-2"></i><?php echo __('institutions_title'); ?></h2>
    <span class="badge bg-primary"><?php echo count($institutions); ?> institutions</span>
</div>

<!-- Type Filter -->
<div class="mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="institutions.php" class="btn btn-sm <?php echo empty($typeFilter) ? 'btn-primary' : 'btn-outline-primary'; ?>">
            All
        </a>
        <a href="institutions.php?type=university" class="btn btn-sm <?php echo $typeFilter === 'university' ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <i class="fas fa-university me-1"></i><?php echo __('institutions_universities'); ?>
        </a>
        <a href="institutions.php?type=tvet" class="btn btn-sm <?php echo $typeFilter === 'tvet' ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <i class="fas fa-tools me-1"></i><?php echo __('institutions_tvet'); ?>
        </a>
        <a href="institutions.php?type=college" class="btn btn-sm <?php echo $typeFilter === 'college' ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <i class="fas fa-school me-1"></i><?php echo __('institutions_colleges'); ?>
        </a>
    </div>
</div>

<?php foreach ($grouped as $type => $typeInstitutions): ?>
<?php if (!empty($typeInstitutions) && (empty($typeFilter) || $typeFilter === $type)): ?>
<div class="mb-5">
    <h4 class="mb-3">
        <?php
        $icons = ['university' => 'fa-university', 'tvet' => 'fa-tools', 'college' => 'fa-school'];
        $labels = ['university' => __('institutions_universities'), 'tvet' => __('institutions_tvet'), 'college' => __('institutions_colleges')];
        ?>
        <i class="fas <?php echo $icons[$type]; ?> me-2 text-primary"></i>
        <?php echo $labels[$type]; ?>
        <span class="badge bg-secondary"><?php echo count($typeInstitutions); ?></span>
    </h4>

    <div class="row g-4">
        <?php foreach ($typeInstitutions as $inst): ?>
        <div class="col-md-6 col-lg-4">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-<?php echo $inst['is_public'] ? 'success' : 'info'; ?>">
                            <?php echo $inst['is_public'] ? 'Public' : 'Private'; ?>
                        </span>
                    </div>
                    <h5 class="card-title"><?php echo getLocalizedField($inst, 'name'); ?></h5>
                    <p class="card-text small text-muted">
                        <?php echo getLocalizedField($inst, 'description') ?: 'Educational institution in Rwanda.'; ?>
                    </p>
                    <p class="small mb-2">
                        <i class="fas fa-map-marker-alt me-2 text-muted"></i>
                        <?php echo $inst['location']; ?>
                    </p>
                </div>
                <?php if ($inst['website']): ?>
                <div class="card-footer bg-transparent">
                    <a href="<?php echo $inst['website']; ?>" target="_blank" class="btn btn-outline-primary btn-sm w-100">
                        <i class="fas fa-external-link-alt me-1"></i><?php echo __('institution_website'); ?>
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>
<?php endforeach; ?>

<?php
// Use matching footer for the header
if (isLoggedIn()) {
    require_once 'includes/footer-dashboard.php';
} else {
    require_once 'includes/footer.php';
}
?>
