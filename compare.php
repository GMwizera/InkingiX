<?php

/**
 * InkingiX Rwanda - Career Comparison Page
 * Side-by-side comparison of two careers
 */

$pageTitle = 'Compare Careers';
$pageSubtitle = 'Side-by-side comparison to help you decide';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    $params = $_GET;
    unset($params['lang']);
    setLanguage($_GET['lang']);
    $redirect = 'compare.php?' . http_build_query($params);
    header('Location: ' . $redirect);
    exit;
}

$db = getDBConnection();

// Get career IDs from URL
$careerIdA = isset($_GET['a']) ? intval($_GET['a']) : 0;
$careerIdB = isset($_GET['b']) ? intval($_GET['b']) : 0;

// Get optional match scores from URL
$scoreA = isset($_GET['score_a']) ? floatval($_GET['score_a']) : null;
$scoreB = isset($_GET['score_b']) ? floatval($_GET['score_b']) : null;

// Validate we have two careers to compare
if ($careerIdA <= 0 || $careerIdB <= 0) {
    setFlashMessage('error', __('compare_select_two', 'Please select two careers to compare.'));
    header('Location: careers.php');
    exit;
}

// Function to get career details with category
function getCareerDetails($db, $careerId)
{
    $stmt = $db->prepare("
        SELECT c.*, cc.name_en AS category_name_en, cc.name_rw AS category_name_rw, cc.code AS category_code
        FROM careers c
        JOIN career_categories cc ON c.primary_category_id = cc.id
        WHERE c.id = ? AND c.is_active = 1
    ");
    $stmt->execute([$careerId]);
    return $stmt->fetch();
}

// Function to get institutions for a career
function getCareerInstitutions($db, $careerId)
{
    $stmt = $db->prepare("
        SELECT i.*, ci.program_name_en, ci.program_name_rw, ci.duration
        FROM institutions i
        JOIN career_institutions ci ON i.id = ci.institution_id
        WHERE ci.career_id = ? AND i.is_active = 1
        ORDER BY i.type, i.name_en
    ");
    $stmt->execute([$careerId]);
    return $stmt->fetchAll();
}

// Get both careers
$careerA = getCareerDetails($db, $careerIdA);
$careerB = getCareerDetails($db, $careerIdB);

if (!$careerA || !$careerB) {
    setFlashMessage('error', __('compare_career_not_found', 'One or both careers not found.'));
    header('Location: careers.php');
    exit;
}

// Get institutions for both careers
$institutionsA = getCareerInstitutions($db, $careerIdA);
$institutionsB = getCareerInstitutions($db, $careerIdB);

// Check bookmark status
$isBookmarkedA = isCareerBookmarked($careerIdA);
$isBookmarkedB = isCareerBookmarked($careerIdB);

// Use sidebar for logged-in users, top navbar for guests
if (isLoggedIn()) {
    require_once 'includes/header-dashboard.php';
} else {
    require_once 'includes/header.php';
}
?>

<style>
    /* Compare page specific styles */
    .compare-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 1.5rem;
    }

    .compare-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 1.5rem;
    }

    @media (max-width: 768px) {
        .compare-grid {
            grid-template-columns: 1fr;
        }
    }

    .compare-card {
        border: 2px solid var(--border-light, #e5e7eb);
        border-radius: 12px;
        overflow: hidden;
        background: white;
    }

    .compare-card.career-a {
        border-color: var(--primary, #2E7D5A);
    }

    .compare-card.career-b {
        border-color: var(--blue-300, #1E5F8C);
    }

    .compare-card-header {
        padding: 1.25rem;
        background: #f8fafc;
        border-bottom: 1px solid var(--border-light, #e5e7eb);
    }

    .compare-card.career-a .compare-card-header {
        background: rgba(46, 125, 90, 0.05);
    }

    .compare-card.career-b .compare-card-header {
        background: rgba(30, 95, 140, 0.05);
    }

    .compare-card-body {
        padding: 1.25rem;
    }

    .compare-section {
        margin-bottom: 1.5rem;
    }

    .compare-section:last-child {
        margin-bottom: 0;
    }

    .compare-section-title {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #6b7280;
        margin-bottom: 0.75rem;
    }

    .compare-career-title {
        font-size: 1.25rem;
        font-weight: 700;
        margin-bottom: 0.75rem;
    }

    .compare-match-score {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: #dbeafe;
        border-radius: 8px;
        font-weight: 600;
        color: #1e40af;
        margin-bottom: 1rem;
    }

    .compare-match-score i {
        color: #3b82f6;
    }

    .skill-tags {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
    }

    .skill-tag {
        padding: 0.375rem 0.75rem;
        background: #f3f4f6;
        border-radius: 20px;
        font-size: 0.8rem;
        color: #374151;
    }

    .salary-display {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--primary, #2E7D5A);
    }

    .institution-item {
        padding: 0.75rem;
        background: #f9fafb;
        border-radius: 8px;
        margin-bottom: 0.5rem;
    }

    .institution-item:last-child {
        margin-bottom: 0;
    }

    .institution-name {
        font-weight: 600;
        margin-bottom: 0.25rem;
    }

    .institution-program {
        font-size: 0.875rem;
        color: #6b7280;
    }

    .institution-meta {
        display: flex;
        gap: 1rem;
        margin-top: 0.5rem;
        font-size: 0.75rem;
    }

    .institution-meta span {
        display: flex;
        align-items: center;
        gap: 0.25rem;
    }

    .compare-actions {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
        padding-top: 1rem;
        border-top: 1px solid #e5e7eb;
    }

    .no-institutions {
        color: #9ca3af;
        font-style: italic;
        font-size: 0.875rem;
    }
</style>

<!-- Header with back button -->
<div class="compare-header">
    <a href="<?php echo isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'results.php') !== false ? 'results.php' : 'careers.php'; ?>" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-2"></i><?php echo __('back_to_results', 'Back to Results'); ?>
    </a>
    <h2 class="mb-0"><i class="fas fa-balance-scale me-2"></i><?php echo __('compare_careers', 'Compare Careers'); ?></h2>
</div>

<!-- Comparison Grid -->
<div class="compare-grid">
    <!-- Career A -->
    <div class="compare-card career-a">
        <div class="compare-card-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge category-badge-<?php echo $careerA['category_code']; ?> mb-2">
                        <?php echo getLocalizedField($careerA, 'category_name'); ?>
                    </span>
                    <h3 class="compare-career-title"><?php echo getLocalizedField($careerA, 'title'); ?></h3>
                </div>
                <?php echo getDemandBadge($careerA['demand_level'] ?? 'growing'); ?>
            </div>

            <?php if ($scoreA !== null): ?>
                <div class="compare-match-score">
                    <i class="fas fa-chart-pie"></i>
                    <?php echo number_format($scoreA, 0); ?>% <?php echo __('match', 'Match'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="compare-card-body">
            <!-- Description -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('description', 'Description'); ?></div>
                <p class="mb-0"><?php echo getLocalizedField($careerA, 'description'); ?></p>
            </div>

            <!-- Skills -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('required_skills', 'Required Skills'); ?></div>
                <div class="skill-tags">
                    <?php
                    $skillsA = explode(',', getLocalizedField($careerA, 'required_skills'));
                    foreach ($skillsA as $skill):
                        $skill = trim($skill);
                        if (!empty($skill)):
                    ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- Salary -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('salary_range', 'Salary Range'); ?></div>
                <div class="salary-display">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    <?php echo formatCurrency($careerA['salary_range_min']); ?> - <?php echo formatCurrency($careerA['salary_range_max']); ?>
                    <small class="text-muted">/<?php echo __('month', 'month'); ?></small>
                </div>
            </div>

            <!-- Education Pathways -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('education_pathways', 'Education Pathways'); ?></div>
                <?php if (!empty($institutionsA)): ?>
                    <?php foreach ($institutionsA as $inst): ?>
                        <div class="institution-item">
                            <div class="institution-name"><?php echo htmlspecialchars(getLocalizedField($inst, 'name')); ?></div>
                            <div class="institution-program"><?php echo htmlspecialchars(getLocalizedField($inst, 'program_name')); ?></div>
                            <div class="institution-meta">
                                <span>
                                    <i class="fas fa-<?php echo $inst['type'] === 'university' ? 'graduation-cap' : 'tools'; ?>"></i>
                                    <?php echo ucfirst($inst['type']); ?>
                                </span>
                                <?php if (!empty($inst['location'])): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($inst['location']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($inst['duration'])): ?>
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($inst['duration']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-institutions"><?php echo __('no_institutions', 'No institutions listed yet.'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if (isLoggedIn()): ?>
                <div class="compare-actions">
                    <a href="career.php?id=<?php echo $careerIdA; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-info-circle me-1"></i><?php echo __('view_details', 'View Details'); ?>
                    </a>
                    <button type="button"
                        class="btn btn-sm bookmark-btn <?php echo $isBookmarkedA ? 'btn-warning' : 'btn-outline-secondary'; ?>"
                        data-career-id="<?php echo $careerIdA; ?>">
                        <i class="fas fa-bookmark me-1"></i>
                        <span class="bookmark-text"><?php echo $isBookmarkedA ? __('saved', 'Saved') : __('save', 'Save'); ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Career B -->
    <div class="compare-card career-b">
        <div class="compare-card-header">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <span class="badge category-badge-<?php echo $careerB['category_code']; ?> mb-2">
                        <?php echo getLocalizedField($careerB, 'category_name'); ?>
                    </span>
                    <h3 class="compare-career-title"><?php echo getLocalizedField($careerB, 'title'); ?></h3>
                </div>
                <?php echo getDemandBadge($careerB['demand_level'] ?? 'growing'); ?>
            </div>

            <?php if ($scoreB !== null): ?>
                <div class="compare-match-score">
                    <i class="fas fa-chart-pie"></i>
                    <?php echo number_format($scoreB, 0); ?>% <?php echo __('match', 'Match'); ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="compare-card-body">
            <!-- Description -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('description', 'Description'); ?></div>
                <p class="mb-0"><?php echo getLocalizedField($careerB, 'description'); ?></p>
            </div>

            <!-- Skills -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('required_skills', 'Required Skills'); ?></div>
                <div class="skill-tags">
                    <?php
                    $skillsB = explode(',', getLocalizedField($careerB, 'required_skills'));
                    foreach ($skillsB as $skill):
                        $skill = trim($skill);
                        if (!empty($skill)):
                    ?>
                            <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <!-- Salary -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('salary_range', 'Salary Range'); ?></div>
                <div class="salary-display">
                    <i class="fas fa-money-bill-wave me-2"></i>
                    <?php echo formatCurrency($careerB['salary_range_min']); ?> - <?php echo formatCurrency($careerB['salary_range_max']); ?>
                    <small class="text-muted">/<?php echo __('month', 'month'); ?></small>
                </div>
            </div>

            <!-- Education Pathways -->
            <div class="compare-section">
                <div class="compare-section-title"><?php echo __('education_pathways', 'Education Pathways'); ?></div>
                <?php if (!empty($institutionsB)): ?>
                    <?php foreach ($institutionsB as $inst): ?>
                        <div class="institution-item">
                            <div class="institution-name"><?php echo htmlspecialchars(getLocalizedField($inst, 'name')); ?></div>
                            <div class="institution-program"><?php echo htmlspecialchars(getLocalizedField($inst, 'program_name')); ?></div>
                            <div class="institution-meta">
                                <span>
                                    <i class="fas fa-<?php echo $inst['type'] === 'university' ? 'graduation-cap' : 'tools'; ?>"></i>
                                    <?php echo ucfirst($inst['type']); ?>
                                </span>
                                <?php if (!empty($inst['location'])): ?>
                                    <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($inst['location']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($inst['duration'])): ?>
                                    <span><i class="fas fa-clock"></i> <?php echo htmlspecialchars($inst['duration']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-institutions"><?php echo __('no_institutions', 'No institutions listed yet.'); ?></p>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <?php if (isLoggedIn()): ?>
                <div class="compare-actions">
                    <a href="career.php?id=<?php echo $careerIdB; ?>" class="btn btn-outline-primary btn-sm">
                        <i class="fas fa-info-circle me-1"></i><?php echo __('view_details', 'View Details'); ?>
                    </a>
                    <button type="button"
                        class="btn btn-sm bookmark-btn <?php echo $isBookmarkedB ? 'btn-warning' : 'btn-outline-secondary'; ?>"
                        data-career-id="<?php echo $careerIdB; ?>">
                        <i class="fas fa-bookmark me-1"></i>
                        <span class="bookmark-text"><?php echo $isBookmarkedB ? __('saved', 'Saved') : __('save', 'Save'); ?></span>
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Compare different careers -->
<div class="text-center mt-4">
    <a href="careers.php" class="btn btn-outline-primary">
        <i class="fas fa-search me-2"></i><?php echo __('compare_different', 'Compare Different Careers'); ?>
    </a>
</div>

<?php if (isLoggedIn()): ?>
    <script>
        // Bookmark toggle functionality
        document.querySelectorAll('.bookmark-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const careerId = this.dataset.careerId;
                const btn = this;
                const textSpan = btn.querySelector('.bookmark-text');

                try {
                    const response = await fetch('toggle_bookmark.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            career_id: parseInt(careerId)
                        })
                    });

                    const data = await response.json();

                    if (data.success) {
                        if (data.is_bookmarked) {
                            btn.classList.remove('btn-outline-secondary');
                            btn.classList.add('btn-warning');
                            textSpan.textContent = '<?php echo __('saved', 'Saved'); ?>';
                        } else {
                            btn.classList.remove('btn-warning');
                            btn.classList.add('btn-outline-secondary');
                            textSpan.textContent = '<?php echo __('save', 'Save'); ?>';
                        }
                    }
                } catch (error) {
                    console.error('Bookmark error:', error);
                }
            });
        });
    </script>
<?php endif; ?>

<?php
// Use matching footer for the header
if (isLoggedIn()) {
    require_once 'includes/footer-dashboard.php';
} else {
    require_once 'includes/footer.php';
}
?>