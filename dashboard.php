<?php
/**
 * EduBridge Rwanda - Student Dashboard
 */

$pageTitle = 'Dashboard';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: dashboard.php');
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$latestAssessment = getLatestAssessment($currentUser['id']);
$careerMatches = [];

if ($latestAssessment && $latestAssessment['is_completed']) {
    $careerMatches = getCareerMatches($latestAssessment['id']);
}

// Get user's bookmarked careers
$bookmarks = getUserBookmarks($currentUser['id']);
$bookmarkCount = count($bookmarks);

// Set page greeting
$pageGreeting = __('dashboard_welcome', 'Hello') . ', ' . htmlspecialchars($currentUser['first_name']) . '!';
$pageSubtitle = __('dashboard_subtitle', 'Explore your career discovery journey');

require_once 'includes/header-dashboard.php';
?>

<!-- Quick Actions -->
<section class="quick-actions">
    <a href="assessment.php" class="action-btn primary">
        <i class="fas fa-clipboard-list"></i>
        <?php echo __('dashboard_take_assessment', 'Take Assessment'); ?>
    </a>
    <a href="careers.php" class="action-btn secondary">
        <i class="fas fa-compass"></i>
        <?php echo __('nav_careers', 'Explore Careers'); ?>
    </a>
    <?php if ($latestAssessment && $latestAssessment['is_completed']): ?>
    <a href="results.php" class="action-btn info">
        <i class="fas fa-chart-bar"></i>
        <?php echo __('dashboard_view_results', 'View Results'); ?>
    </a>
    <?php endif; ?>
    <?php if ($bookmarkCount > 0): ?>
    <a href="bookmarks.php" class="action-btn" style="background: #fef3c7; color: #d97706;">
        <i class="fas fa-bookmark"></i>
        <?php echo __('dashboard_my_bookmarks', 'My Bookmarks'); ?> (<?php echo $bookmarkCount; ?>)
    </a>
    <?php endif; ?>
</section>

<!-- Stats Cards -->
<section class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon assessment">
                <i class="fas fa-clipboard-check"></i>
            </div>
        </div>
        <p class="stat-label"><?php echo __('dashboard_assessments', 'Assessments'); ?></p>
        <p class="stat-value"><?php echo $latestAssessment ? '1' : '0'; ?></p>
        <div class="stat-change">
            <?php if ($latestAssessment && $latestAssessment['is_completed']): ?>
            <span class="stat-change-value positive"><i class="fas fa-check"></i></span>
            <span class="stat-change-text"><?php echo __('results_completed', 'Completed'); ?></span>
            <?php else: ?>
            <span class="stat-change-value"><i class="fas fa-clock"></i></span>
            <span class="stat-change-text"><?php echo __('dashboard_pending', 'Pending'); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon careers">
                <i class="fas fa-briefcase"></i>
            </div>
        </div>
        <p class="stat-label"><?php echo __('dashboard_career_matches', 'Career Matches'); ?></p>
        <p class="stat-value"><?php echo count($careerMatches); ?></p>
        <div class="stat-change">
            <?php if (!empty($careerMatches)): ?>
            <span class="stat-change-value positive"><?php echo number_format($careerMatches[0]['match_percentage'], 0); ?>%</span>
            <span class="stat-change-text"><?php echo __('dashboard_top_match', 'Top match'); ?></span>
            <?php else: ?>
            <span class="stat-change-text"><?php echo __('dashboard_take_to_see', 'Take assessment to see'); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon institutions">
                <i class="fas fa-university"></i>
            </div>
        </div>
        <p class="stat-label"><?php echo __('nav_institutions', 'Institutions'); ?></p>
        <p class="stat-value">10+</p>
        <div class="stat-change">
            <span class="stat-change-text"><?php echo __('dashboard_rwandan_institutions', 'Rwandan institutions'); ?></span>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-header">
            <div class="stat-icon progress" style="background: #fef3c7;">
                <i class="fas fa-bookmark" style="color: #d97706;"></i>
            </div>
        </div>
        <p class="stat-label"><?php echo __('dashboard_bookmarks', 'Saved Careers'); ?></p>
        <p class="stat-value"><?php echo $bookmarkCount; ?></p>
        <div class="stat-change">
            <?php if ($bookmarkCount > 0): ?>
            <a href="bookmarks.php" class="stat-change-text text-decoration-none"><?php echo __('view_all', 'View All'); ?> <i class="fas fa-arrow-right"></i></a>
            <?php else: ?>
            <span class="stat-change-text"><?php echo __('dashboard_no_bookmarks', 'None yet'); ?></span>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Main Grid -->
<div class="dashboard-grid">
    <!-- Recent Activity / Main Content -->
    <section class="activity-section">
        <div class="section-header">
            <h2 class="section-title"><i class="fas fa-history me-2"></i><?php echo __('dashboard_your_journey', 'Your Journey'); ?></h2>
        </div>

        <?php if (!$latestAssessment || !$latestAssessment['is_completed']): ?>
        <!-- No Assessment Taken -->
        <div class="text-center py-5">
            <div class="activity-icon assessment mx-auto mb-4" style="width: 80px; height: 80px;">
                <i class="fas fa-clipboard-list" style="font-size: 2rem;"></i>
            </div>
            <h4><?php echo __('dashboard_no_assessment', 'Start Your Career Discovery'); ?></h4>
            <p class="text-muted mb-4"><?php echo __('dashboard_no_assessment_desc', 'Take our career interest assessment to discover careers that match your personality and interests.'); ?></p>
            <a href="assessment.php" class="btn btn-primary btn-lg">
                <i class="fas fa-play me-2"></i><?php echo __('dashboard_take_assessment', 'Take Assessment'); ?>
            </a>
        </div>
        <?php else: ?>
        <!-- Activity Feed -->
        <div class="activity-feed">
            <div class="activity-item">
                <div class="activity-icon success">
                    <i class="fas fa-check"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text"><strong><?php echo __('dashboard_assessment_completed', 'Assessment Completed'); ?></strong></p>
                    <span class="activity-time"><?php echo date('F j, Y', strtotime($latestAssessment['completed_at'])); ?></span>
                </div>
                <span class="activity-badge completed"><?php echo __('results_completed', 'Completed'); ?></span>
            </div>

            <?php if (!empty($careerMatches)): ?>
            <?php foreach (array_slice($careerMatches, 0, 3) as $match): ?>
            <a href="career.php?id=<?php echo $match['career_id']; ?>" class="activity-item" style="text-decoration: none;">
                <div class="activity-icon career">
                    <i class="fas fa-briefcase"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text"><strong><?php echo getLocalizedField($match, 'title'); ?></strong></p>
                    <span class="activity-time"><?php echo __('dashboard_career_match', 'Career Match'); ?></span>
                </div>
                <span class="activity-badge new"><?php echo number_format($match['match_percentage'], 0); ?>%</span>
            </a>
            <?php endforeach; ?>
            <?php endif; ?>

            <div class="activity-item">
                <div class="activity-icon institution">
                    <i class="fas fa-user"></i>
                </div>
                <div class="activity-content">
                    <p class="activity-text"><strong><?php echo __('dashboard_profile_created', 'Profile Created'); ?></strong></p>
                    <span class="activity-time"><?php echo date('F j, Y', strtotime($currentUser['created_at'])); ?></span>
                </div>
                <span class="activity-badge completed"><?php echo __('results_completed', 'Done'); ?></span>
            </div>
        </div>

        <div class="mt-4 d-flex gap-2">
            <a href="results.php" class="btn btn-primary">
                <i class="fas fa-chart-bar me-2"></i><?php echo __('dashboard_view_results', 'View Full Results'); ?>
            </a>
            <a href="assessment.php" class="btn btn-outline">
                <i class="fas fa-redo me-2"></i><?php echo __('dashboard_retake', 'Retake Assessment'); ?>
            </a>
        </div>
        <?php endif; ?>
    </section>

    <!-- Sidebar Panels -->
    <aside class="dashboard-sidebar">
        <!-- Top Career Recommendations -->
        <?php if (!empty($careerMatches)): ?>
        <div class="sidebar-panel">
            <div class="section-header">
                <h3 class="section-title"><?php echo __('dashboard_top_careers', 'Top Careers'); ?></h3>
                <a href="results.php" class="view-all"><?php echo __('view_all', 'See All'); ?></a>
            </div>
            <div class="career-list">
                <?php foreach (array_slice($careerMatches, 0, 4) as $match): ?>
                <a href="career.php?id=<?php echo $match['career_id']; ?>" class="career-item" style="text-decoration: none; color: inherit;">
                    <div class="career-info">
                        <span class="career-name"><?php echo getLocalizedField($match, 'title'); ?></span>
                        <span class="career-category"><?php echo __('dashboard_match', 'Match'); ?></span>
                    </div>
                    <div class="career-match">
                        <span class="match-percentage"><?php echo number_format($match['match_percentage'], 0); ?>%</span>
                        <div class="progress-bar-container">
                            <div class="progress-fill" style="width: <?php echo $match['match_percentage']; ?>%"></div>
                        </div>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Saved Careers (Bookmarks) -->
        <?php if (!empty($bookmarks)): ?>
        <div class="sidebar-panel">
            <div class="section-header">
                <h3 class="section-title"><i class="fas fa-bookmark me-2" style="color: #d97706;"></i><?php echo __('dashboard_saved_careers', 'Saved Careers'); ?></h3>
                <a href="bookmarks.php" class="view-all"><?php echo __('view_all', 'See All'); ?></a>
            </div>
            <div class="career-list">
                <?php foreach (array_slice($bookmarks, 0, 3) as $bookmark): ?>
                <a href="career.php?id=<?php echo $bookmark['id']; ?>" class="career-item" style="text-decoration: none; color: inherit;">
                    <div class="career-info">
                        <span class="career-name"><?php echo getLocalizedField($bookmark, 'title'); ?></span>
                        <span class="career-category"><?php echo getDemandBadge($bookmark['demand_level'] ?? 'growing', false); ?></span>
                    </div>
                    <div class="career-match">
                        <i class="fas fa-chevron-right text-muted"></i>
                    </div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Profile Summary -->
        <div class="sidebar-panel">
            <div class="section-header">
                <h3 class="section-title"><?php echo __('dashboard_profile_summary', 'Profile Summary'); ?></h3>
                <a href="profile.php" class="view-all"><?php echo __('edit', 'Edit'); ?></a>
            </div>
            <div class="py-2">
                <div class="d-flex align-items-center gap-3 mb-3 pb-3" style="border-bottom: 1px solid var(--border-light);">
                    <div class="user-avatar-sidebar" style="width: 50px; height: 50px; font-size: 1rem;">
                        <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                        <p class="text-muted mb-0" style="font-size: var(--body-5);"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>
                </div>
                <ul class="list-unstyled mb-0" style="font-size: var(--body-4);">
                    <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid var(--border-light);">
                        <span class="text-muted"><?php echo __('profile_school', 'School'); ?></span>
                        <strong><?php echo htmlspecialchars($currentUser['school_name'] ?? '-'); ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2" style="border-bottom: 1px solid var(--border-light);">
                        <span class="text-muted"><?php echo __('profile_grade', 'Grade'); ?></span>
                        <strong><?php echo htmlspecialchars($currentUser['grade_level'] ?? '-'); ?></strong>
                    </li>
                    <li class="d-flex justify-content-between py-2">
                        <span class="text-muted"><?php echo __('profile_language', 'Language'); ?></span>
                        <strong><?php echo $currentUser['preferred_language'] === 'en' ? 'English' : 'Kinyarwanda'; ?></strong>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Help Card -->
        <div class="sidebar-panel" style="background: var(--soft-green-00);">
            <div class="d-flex align-items-start gap-3">
                <div class="stat-icon assessment" style="flex-shrink: 0;">
                    <i class="fas fa-question"></i>
                </div>
                <div>
                    <h6 class="mb-1"><?php echo __('dashboard_need_help', 'Need Help?'); ?></h6>
                    <p class="text-muted mb-2" style="font-size: var(--body-5);"><?php echo __('dashboard_help_desc', 'Check our FAQ or learn more about career paths.'); ?></p>
                    <a href="faq.php" class="btn btn-sm btn-outline">
                        <i class="fas fa-book me-1"></i><?php echo __('view_faq', 'View FAQ'); ?>
                    </a>
                </div>
            </div>
        </div>
    </aside>
</div>

<?php require_once 'includes/footer-dashboard.php'; ?>
