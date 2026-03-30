<?php
/**
 * EduBridge Rwanda - Career Detail Page
 */

require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: career.php?id=' . intval($_GET['id']));
    exit;
}

$db = getDBConnection();

// Get career ID
$careerId = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($careerId <= 0) {
    header('Location: careers.php');
    exit;
}

// Get career details
$stmt = $db->prepare("
    SELECT c.*, cc.name_en as category_name, cc.name_rw as category_name_rw, cc.code as category_code
    FROM careers c
    JOIN career_categories cc ON c.primary_category_id = cc.id
    WHERE c.id = ? AND c.is_active = 1
");
$stmt->execute([$careerId]);
$career = $stmt->fetch();

if (!$career) {
    header('Location: careers.php');
    exit;
}

$pageTitle = getLocalizedField($career, 'title');

// Get institutions offering this career
$stmt = $db->prepare("
    SELECT i.*, ci.program_name_en, ci.program_name_rw, ci.duration
    FROM institutions i
    JOIN career_institutions ci ON i.id = ci.institution_id
    WHERE ci.career_id = ? AND i.is_active = 1
    ORDER BY i.type, i.name_en
");
$stmt->execute([$careerId]);
$institutions = $stmt->fetchAll();

// Get related careers (same category)
$stmt = $db->prepare("
    SELECT c.id, c.title_en, c.title_rw, c.salary_range_min, c.salary_range_max, c.demand_level
    FROM careers c
    WHERE c.primary_category_id = ? AND c.id != ? AND c.is_active = 1
    LIMIT 4
");
$stmt->execute([$career['primary_category_id'], $careerId]);
$relatedCareers = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<!-- Breadcrumb -->
<nav aria-label="breadcrumb" class="mb-4">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="index.php">Home</a></li>
        <li class="breadcrumb-item"><a href="careers.php">Careers</a></li>
        <li class="breadcrumb-item active"><?php echo getLocalizedField($career, 'title'); ?></li>
    </ol>
</nav>

<div class="row">
    <!-- Main Content -->
    <div class="col-lg-8">
        <!-- Career Header -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-3">
                    <div class="d-flex gap-2 flex-wrap">
                        <span class="badge category-badge-<?php echo $career['category_code']; ?> fs-6">
                            <i class="fas fa-tag me-1"></i><?php echo getLocalizedField($career, 'category_name'); ?>
                        </span>
                        <?php echo getDemandBadge($career['demand_level'] ?? 'growing'); ?>
                    </div>
                </div>
                <h1 class="mb-3"><?php echo getLocalizedField($career, 'title'); ?></h1>
                <p class="lead text-muted"><?php echo getLocalizedField($career, 'description'); ?></p>
            </div>
        </div>

        <!-- Skills Required -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-tools me-2"></i><?php echo __('career_skills'); ?></h5>
            </div>
            <div class="card-body">
                <?php
                $skills = explode(',', getLocalizedField($career, 'required_skills'));
                ?>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($skills as $skill): ?>
                    <span class="badge bg-light text-dark border"><?php echo trim($skill); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Education Path -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i><?php echo __('career_education'); ?></h5>
            </div>
            <div class="card-body">
                <p><?php echo getLocalizedField($career, 'education_path'); ?></p>
            </div>
        </div>

        <!-- Where to Study -->
        <?php if (!empty($institutions)): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-university me-2"></i><?php echo __('career_institutions'); ?></h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Institution</th>
                                <th>Program</th>
                                <th>Duration</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($institutions as $inst): ?>
                            <tr>
                                <td>
                                    <strong><?php echo getLocalizedField($inst, 'name'); ?></strong>
                                    <?php if ($inst['website']): ?>
                                    <br><small><a href="<?php echo $inst['website']; ?>" target="_blank" class="text-muted">
                                        <i class="fas fa-external-link-alt me-1"></i>Visit Website
                                    </a></small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo getLocalizedField($inst, 'program_name'); ?></td>
                                <td><?php echo $inst['duration']; ?></td>
                                <td>
                                    <span class="badge bg-<?php echo $inst['type'] === 'university' ? 'primary' : ($inst['type'] === 'tvet' ? 'success' : 'info'); ?>">
                                        <?php echo ucfirst($inst['type']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Job Outlook -->
        <?php if (!empty($career['job_outlook_en'])): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-line me-2"></i><?php echo __('career_outlook'); ?></h5>
            </div>
            <div class="card-body">
                <p><?php echo getLocalizedField($career, 'job_outlook'); ?></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Salary Info -->
        <div class="card mb-4 bg-light">
            <div class="card-body text-center">
                <h6 class="text-muted mb-3"><?php echo __('career_salary'); ?> (Monthly)</h6>
                <div class="d-flex justify-content-center align-items-center gap-3">
                    <div>
                        <small class="text-muted d-block">Min</small>
                        <strong class="text-success"><?php echo formatCurrency($career['salary_range_min']); ?></strong>
                    </div>
                    <i class="fas fa-arrow-right text-muted"></i>
                    <div>
                        <small class="text-muted d-block">Max</small>
                        <strong class="text-success"><?php echo formatCurrency($career['salary_range_max']); ?></strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Take Assessment CTA -->
        <?php if (!isLoggedIn()): ?>
        <div class="card mb-4 bg-primary text-white">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-2x mb-3"></i>
                <h6>Is this career right for you?</h6>
                <p class="small opacity-75 mb-3">Take our free career assessment to find out!</p>
                <a href="register.php" class="btn btn-light btn-sm">
                    <i class="fas fa-user-plus me-1"></i>Get Started
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="fas fa-clipboard-list fa-2x text-primary mb-3"></i>
                <h6>Check Your Match</h6>
                <p class="small text-muted mb-3">See how well this career matches your interests!</p>
                <a href="assessment.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-play me-1"></i>Take Assessment
                </a>
            </div>
        </div>
        <?php endif; ?>

        <!-- Related Careers -->
        <?php if (!empty($relatedCareers)): ?>
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-link me-2"></i><?php echo __('career_related'); ?></h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach ($relatedCareers as $related): ?>
                <a href="career.php?id=<?php echo $related['id']; ?>" class="list-group-item list-group-item-action">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="d-block"><?php echo getLocalizedField($related, 'title'); ?></span>
                            <?php echo getDemandBadge($related['demand_level'] ?? 'growing', false); ?>
                        </div>
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Back Button -->
<div class="mt-4">
    <a href="careers.php" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back'); ?>
    </a>
</div>

<?php require_once 'includes/footer.php'; ?>
