<?php
/**
 * EduBridge Rwanda - Assessment Results
 */

$pageTitle = 'Assessment Results';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    $redirectUrl = 'results.php';
    if (isset($_GET['id'])) {
        $redirectUrl .= '?id=' . intval($_GET['id']);
    }
    header('Location: ' . $redirectUrl);
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get specific assessment or latest
if (isset($_GET['id'])) {
    $assessmentId = intval($_GET['id']);
    $stmt = $db->prepare("SELECT * FROM user_assessments WHERE id = ? AND user_id = ? AND is_completed = 1");
    $stmt->execute([$assessmentId, $currentUser['id']]);
    $assessment = $stmt->fetch();
} else {
    $assessment = getLatestAssessment($currentUser['id']);
    if ($assessment && !$assessment['is_completed']) {
        $assessment = null;
    }
}

if (!$assessment) {
    setFlashMessage('warning', 'No completed assessment found. Take an assessment first.');
    header('Location: assessment.php');
    exit;
}

// Get category results
$categoryResults = getAssessmentResults($assessment['id']);

// Get career matches
$careerMatches = getCareerMatches($assessment['id']);

// Get all user assessments for history
$stmt = $db->prepare("
    SELECT * FROM user_assessments
    WHERE user_id = ? AND is_completed = 1
    ORDER BY completed_at DESC
");
$stmt->execute([$currentUser['id']]);
$assessmentHistory = $stmt->fetchAll();

require_once 'includes/header.php';
?>

<div class="row">
    <!-- Main Results -->
    <div class="col-lg-8">
        <!-- Results Header -->
        <div class="card mb-4 bg-success text-white">
            <div class="card-body text-center py-4">
                <i class="fas fa-check-circle fa-3x mb-3"></i>
                <h2><?php echo __('results_title'); ?></h2>
                <p class="mb-0 opacity-75">
                    <i class="fas fa-calendar me-2"></i>
                    Completed on <?php echo date('F j, Y \a\t g:i A', strtotime($assessment['completed_at'])); ?>
                </p>
            </div>
        </div>

        <!-- Interest Profile -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-chart-bar me-2"></i><?php echo __('results_category_scores'); ?></h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">Your interest profile based on the RIASEC model. Higher scores indicate stronger alignment with that category.</p>

                <?php foreach ($categoryResults as $result): ?>
                <div class="result-category">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="category-name">
                            <span class="badge category-badge-<?php echo $result['code']; ?> me-2">
                                <i class="fas <?php echo $result['icon']; ?>"></i>
                            </span>
                            <?php echo getLocalizedField($result, 'name'); ?>
                        </div>
                        <span class="fw-bold"><?php echo number_format($result['percentage'], 0); ?>%</span>
                    </div>
                    <div class="progress mt-2" style="height: 20px;">
                        <div class="progress-bar category-<?php echo $result['code']; ?>"
                             role="progressbar"
                             style="width: <?php echo $result['percentage']; ?>%"
                             aria-valuenow="<?php echo $result['percentage']; ?>"
                             aria-valuemin="0"
                             aria-valuemax="100">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Top Career Matches -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-star me-2"></i><?php echo __('results_top_careers'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($careerMatches)): ?>
                <div class="row g-3">
                    <?php foreach ($careerMatches as $index => $match): ?>
                    <div class="col-md-6">
                        <div class="card h-100 career-card <?php echo $index === 0 ? 'border-primary' : ''; ?>">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <span class="badge bg-<?php echo $index === 0 ? 'primary' : 'secondary'; ?>">
                                        #<?php echo $index + 1; ?>
                                    </span>
                                    <span class="match-badge"><?php echo number_format($match['match_percentage'], 0); ?>%</span>
                                </div>
                                <h5 class="card-title"><?php echo getLocalizedField($match, 'title'); ?></h5>
                                <div class="mb-2">
                                    <?php echo getDemandBadge($match['demand_level'] ?? 'growing'); ?>
                                </div>
                                <p class="card-text small text-muted">
                                    <?php echo substr(getLocalizedField($match, 'description'), 0, 100); ?>...
                                </p>
                                <div class="salary-range small mb-3">
                                    <i class="fas fa-money-bill-wave me-1"></i>
                                    <?php echo formatCurrency($match['salary_range_min']); ?> - <?php echo formatCurrency($match['salary_range_max']); ?>/month
                                </div>
                                <a href="career.php?id=<?php echo $match['career_id']; ?>" class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-arrow-right me-1"></i><?php echo __('results_explore'); ?>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                <p class="text-muted">No career matches found. Please retake the assessment.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Actions -->
        <div class="d-flex gap-2 flex-wrap">
            <a href="assessment.php" class="btn btn-outline-primary">
                <i class="fas fa-redo me-2"></i><?php echo __('results_retake'); ?>
            </a>
            <a href="careers.php" class="btn btn-outline-secondary">
                <i class="fas fa-search me-2"></i>Browse All Careers
            </a>
            <button type="button" class="btn btn-outline-info" onclick="window.print()">
                <i class="fas fa-print me-2"></i>Print Results
            </button>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- RIASEC Explanation -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Understanding Your Results</h6>
            </div>
            <div class="card-body">
                <p class="small text-muted mb-3">Your results are based on the Holland Codes (RIASEC) model, which categorizes interests into six types:</p>
                <ul class="list-unstyled small">
                    <li class="mb-2"><span class="badge category-badge-R me-2">R</span><strong>Realistic:</strong> Hands-on, practical</li>
                    <li class="mb-2"><span class="badge category-badge-I me-2">I</span><strong>Investigative:</strong> Analytical, curious</li>
                    <li class="mb-2"><span class="badge category-badge-A me-2">A</span><strong>Artistic:</strong> Creative, expressive</li>
                    <li class="mb-2"><span class="badge category-badge-S me-2">S</span><strong>Social:</strong> Helping, teaching</li>
                    <li class="mb-2"><span class="badge category-badge-E me-2">E</span><strong>Enterprising:</strong> Leading, persuading</li>
                    <li><span class="badge category-badge-C me-2">C</span><strong>Conventional:</strong> Organized, detail-oriented</li>
                </ul>
            </div>
        </div>

        <!-- Assessment History -->
        <?php if (count($assessmentHistory) > 1): ?>
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-history me-2"></i>Assessment History</h6>
            </div>
            <div class="list-group list-group-flush">
                <?php foreach (array_slice($assessmentHistory, 0, 5) as $hist): ?>
                <a href="results.php?id=<?php echo $hist['id']; ?>"
                   class="list-group-item list-group-item-action <?php echo $hist['id'] == $assessment['id'] ? 'active' : ''; ?>">
                    <div class="d-flex justify-content-between">
                        <span><?php echo date('M j, Y', strtotime($hist['completed_at'])); ?></span>
                        <small><?php echo date('g:i A', strtotime($hist['completed_at'])); ?></small>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Next Steps -->
        <div class="card bg-light">
            <div class="card-body">
                <h6><i class="fas fa-lightbulb me-2 text-warning"></i>Next Steps</h6>
                <ol class="small mb-0 ps-3">
                    <li class="mb-2">Explore your top career matches in detail</li>
                    <li class="mb-2">Research education pathways and institutions</li>
                    <li class="mb-2">Talk to your school counselor about your results</li>
                    <li>Consider job shadowing or internships</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
