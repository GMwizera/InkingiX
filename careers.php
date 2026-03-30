<?php
/**
 * EduBridge Rwanda - Careers List
 */

$pageTitle = 'Explore Careers';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: careers.php');
    exit;
}

$db = getDBConnection();

// Get filter parameters
$categoryFilter = isset($_GET['category']) ? intval($_GET['category']) : 0;
$searchTerm = isset($_GET['search']) ? sanitize($_GET['search']) : '';

// Build query
$sql = "SELECT c.*, cc.name_en as category_name, cc.code as category_code, c.demand_level
        FROM careers c
        JOIN career_categories cc ON c.primary_category_id = cc.id
        WHERE c.is_active = 1";
$params = [];

if ($categoryFilter > 0) {
    $sql .= " AND (c.primary_category_id = ? OR c.secondary_category_id = ?)";
    $params[] = $categoryFilter;
    $params[] = $categoryFilter;
}

if (!empty($searchTerm)) {
    $sql .= " AND (c.title_en LIKE ? OR c.title_rw LIKE ? OR c.description_en LIKE ?)";
    $searchWildcard = "%$searchTerm%";
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
    $params[] = $searchWildcard;
}

$sql .= " ORDER BY c.title_en ASC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$careers = $stmt->fetchAll();

// Get categories for filter
$categories = getCareerCategories();

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-briefcase me-2"></i><?php echo __('careers_title'); ?></h2>
    <span class="badge bg-primary"><?php echo count($careers); ?> careers</span>
</div>

<!-- Search and Filter -->
<div class="card mb-4">
    <div class="card-body">
        <form method="GET" class="row g-3">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                    <input type="text" class="form-control" name="search"
                           placeholder="<?php echo __('careers_search'); ?>"
                           value="<?php echo htmlspecialchars($searchTerm); ?>">
                </div>
            </div>
            <div class="col-md-4">
                <select class="form-select" name="category">
                    <option value="0"><?php echo __('careers_all'); ?></option>
                    <?php foreach ($categories as $cat): ?>
                    <option value="<?php echo $cat['id']; ?>" <?php echo $categoryFilter == $cat['id'] ? 'selected' : ''; ?>>
                        <?php echo getLocalizedField($cat, 'name'); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-filter me-1"></i><?php echo __('filter'); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Career Categories Quick Filter -->
<div class="mb-4">
    <div class="d-flex flex-wrap gap-2">
        <a href="careers.php" class="btn btn-sm <?php echo $categoryFilter == 0 ? 'btn-primary' : 'btn-outline-primary'; ?>">
            All
        </a>
        <?php foreach ($categories as $cat): ?>
        <a href="careers.php?category=<?php echo $cat['id']; ?>"
           class="btn btn-sm <?php echo $categoryFilter == $cat['id'] ? 'btn-primary' : 'btn-outline-primary'; ?>">
            <i class="fas <?php echo $cat['icon']; ?> me-1"></i><?php echo getLocalizedField($cat, 'name'); ?>
        </a>
        <?php endforeach; ?>
    </div>
</div>

<!-- Careers Grid -->
<?php if (!empty($careers)): ?>
<div class="row g-4">
    <?php foreach ($careers as $career): ?>
    <div class="col-md-6 col-lg-4">
        <div class="card h-100 career-card" data-category="<?php echo $career['primary_category_id']; ?>">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge category-badge-<?php echo $career['category_code']; ?>">
                        <?php echo $career['category_name']; ?>
                    </span>
                    <?php echo getDemandBadge($career['demand_level'] ?? 'growing'); ?>
                </div>
                <h5 class="card-title"><?php echo getLocalizedField($career, 'title'); ?></h5>
                <p class="card-text text-muted small">
                    <?php echo substr(getLocalizedField($career, 'description'), 0, 120); ?>...
                </p>
                <div class="salary-range small mb-3">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    <?php echo formatCurrency($career['salary_range_min']); ?> - <?php echo formatCurrency($career['salary_range_max']); ?>/month
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="career.php?id=<?php echo $career['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-arrow-right me-1"></i>View Details
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="alert alert-info">
    <i class="fas fa-info-circle me-2"></i><?php echo __('careers_no_results'); ?>
</div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
