<?php
/**
 * EduBridge Rwanda - Student Dashboard (My Journey)
 * Handles three states: no assessment, in-progress, completed
 */

$pageTitle = 'Dashboard';
require_once 'includes/functions.php';
require_once 'includes/matching_engine.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: dashboard.php');
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$db = getDBConnection();
$latestAssessment = getLatestAssessment($currentUser['id']);
$careerMatches = [];
$assessmentProgress = null;

// Determine assessment state: none, in_progress, completed
$assessmentState = 'none';
if ($latestAssessment) {
    if ($latestAssessment['is_completed']) {
        $assessmentState = 'completed';
        $careerMatches = getCareerMatches($latestAssessment['id']);
    } else {
        $assessmentState = 'in_progress';
        $assessmentProgress = getAssessmentProgress($db, $latestAssessment['id']);
    }
}

// Get user's bookmarked careers
$bookmarks = getUserBookmarks($currentUser['id']);
$bookmarkCount = count($bookmarks);

// Calculate journey progress milestones
$journeyMilestones = [
    'profile_created' => true,
    'assessment_started' => $latestAssessment !== null,
    'assessment_completed' => $assessmentState === 'completed',
    'careers_explored' => $bookmarkCount > 0 || count($careerMatches) > 0,
    'career_saved' => $bookmarkCount > 0,
];
$completedMilestones = array_sum($journeyMilestones);
$totalMilestones = count($journeyMilestones);
$journeyPercentage = round(($completedMilestones / $totalMilestones) * 100);

// Set page greeting
$pageGreeting = __('dashboard_welcome', 'Hello') . ', ' . htmlspecialchars($currentUser['first_name']) . '!';
$pageSubtitle = __('dashboard_subtitle', 'Explore your career discovery journey');

require_once 'includes/header-dashboard.php';
?>

<!-- Quick Actions -->
<section class="quick-actions">
    <?php if ($assessmentState === 'in_progress'): ?>
    <a href="assessment.php" class="action-btn primary">
        <i class="fas fa-play-circle"></i>
        <?php echo __('dashboard_continue_assessment', 'Continue Assessment'); ?>
    </a>
    <?php elseif ($assessmentState === 'completed'): ?>
    <a href="results.php" class="action-btn primary">
        <i class="fas fa-chart-bar"></i>
        <?php echo __('dashboard_view_results', 'View Results'); ?>
    </a>
    <a href="assessment.php" class="action-btn secondary">
        <i class="fas fa-redo"></i>
        <?php echo __('dashboard_retake', 'Retake Assessment'); ?>
    </a>
    <?php else: ?>
    <a href="assessment.php" class="action-btn primary">
        <i class="fas fa-clipboard-list"></i>
        <?php echo __('dashboard_take_assessment', 'Take Assessment'); ?>
    </a>
    <?php endif; ?>
    <a href="careers.php" class="action-btn secondary">
        <i class="fas fa-compass"></i>
        <?php echo __('nav_careers', 'Explore Careers'); ?>
    </a>
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
            <?php if ($assessmentState === 'completed'): ?>
            <span class="stat-change-value positive"><i class="fas fa-check"></i></span>
            <span class="stat-change-text"><?php echo __('results_completed', 'Completed'); ?></span>
            <?php elseif ($assessmentState === 'in_progress'): ?>
            <span class="stat-change-value" style="color: #d97706;"><i class="fas fa-spinner"></i></span>
            <span class="stat-change-text"><?php echo __('dashboard_in_progress', 'In Progress'); ?> (<?php echo $assessmentProgress['percentage'] ?? 0; ?>%)</span>
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
            <h2 class="section-title"><i class="fas fa-road me-2"></i><?php echo __('dashboard_your_journey', 'Your Journey'); ?></h2>
            <span class="badge bg-primary"><?php echo $journeyPercentage; ?>% <?php echo __('dashboard_complete', 'complete'); ?></span>
        </div>

        <!-- Journey Progress Bar -->
        <div class="journey-progress-container mb-4">
            <div class="journey-progress-bar">
                <div class="journey-progress-fill" style="width: <?php echo $journeyPercentage; ?>%"></div>
            </div>
            <div class="journey-milestones">
                <div class="milestone <?php echo $journeyMilestones['profile_created'] ? 'completed' : ''; ?>">
                    <div class="milestone-icon"><i class="fas fa-user"></i></div>
                    <span class="milestone-label"><?php echo __('dashboard_milestone_profile', 'Profile'); ?></span>
                </div>
                <div class="milestone <?php echo $journeyMilestones['assessment_started'] ? 'completed' : ''; ?>">
                    <div class="milestone-icon"><i class="fas fa-play"></i></div>
                    <span class="milestone-label"><?php echo __('dashboard_milestone_started', 'Started'); ?></span>
                </div>
                <div class="milestone <?php echo $journeyMilestones['assessment_completed'] ? 'completed' : ''; ?>">
                    <div class="milestone-icon"><i class="fas fa-check"></i></div>
                    <span class="milestone-label"><?php echo __('dashboard_milestone_completed', 'Completed'); ?></span>
                </div>
                <div class="milestone <?php echo $journeyMilestones['careers_explored'] ? 'completed' : ''; ?>">
                    <div class="milestone-icon"><i class="fas fa-search"></i></div>
                    <span class="milestone-label"><?php echo __('dashboard_milestone_explored', 'Explored'); ?></span>
                </div>
                <div class="milestone <?php echo $journeyMilestones['career_saved'] ? 'completed' : ''; ?>">
                    <div class="milestone-icon"><i class="fas fa-bookmark"></i></div>
                    <span class="milestone-label"><?php echo __('dashboard_milestone_saved', 'Saved'); ?></span>
                </div>
            </div>
        </div>

        <?php if ($assessmentState === 'none'): ?>
        <!-- No Assessment Taken -->
        <div class="journey-card text-center py-5">
            <div class="activity-icon assessment mx-auto mb-4" style="width: 80px; height: 80px;">
                <i class="fas fa-clipboard-list" style="font-size: 2rem;"></i>
            </div>
            <h4><?php echo __('dashboard_no_assessment', 'Start Your Career Discovery'); ?></h4>
            <p class="text-muted mb-4"><?php echo __('dashboard_no_assessment_desc', 'Take our career interest assessment to discover careers that match your personality and interests.'); ?></p>
            <a href="assessment.php" class="btn btn-primary btn-lg">
                <i class="fas fa-play me-2"></i><?php echo __('dashboard_take_assessment', 'Take Assessment'); ?>
            </a>
        </div>

        <?php elseif ($assessmentState === 'in_progress'): ?>
        <!-- Assessment In Progress -->
        <div class="journey-card">
            <div class="d-flex align-items-start gap-4">
                <div class="activity-icon assessment" style="width: 60px; height: 60px; flex-shrink: 0;">
                    <i class="fas fa-spinner fa-spin" style="font-size: 1.5rem;"></i>
                </div>
                <div class="flex-grow-1">
                    <h4 class="mb-2"><?php echo __('dashboard_assessment_in_progress', 'Assessment In Progress'); ?></h4>
                    <p class="text-muted mb-3"><?php echo __('dashboard_assessment_in_progress_desc', 'You have an unfinished assessment. Continue where you left off to see your career matches.'); ?></p>

                    <!-- Progress Indicator -->
                    <div class="assessment-progress-card mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="fw-semibold"><?php echo __('assessment_progress', 'Progress'); ?></span>
                            <span class="badge bg-warning text-dark">
                                <?php echo $assessmentProgress['answered_count'] ?? 0; ?> / <?php echo $assessmentProgress['total_questions'] ?? 30; ?> <?php echo __('dashboard_questions', 'questions'); ?>
                            </span>
                        </div>
                        <div class="progress" style="height: 12px;">
                            <div class="progress-bar bg-warning" role="progressbar" style="width: <?php echo $assessmentProgress['percentage'] ?? 0; ?>%"></div>
                        </div>
                        <?php if (isset($assessmentProgress['is_adaptive']) && $assessmentProgress['is_adaptive']): ?>
                        <p class="text-muted small mt-2 mb-0">
                            <i class="fas fa-magic me-1"></i>
                            <?php echo __('dashboard_adaptive_mode', 'Adaptive mode active - questions personalized to your interests'); ?>
                        </p>
                        <?php endif; ?>
                    </div>

                    <a href="assessment.php" class="btn btn-warning btn-lg">
                        <i class="fas fa-play-circle me-2"></i><?php echo __('dashboard_continue_assessment', 'Continue Assessment'); ?>
                    </a>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- Assessment Completed - Activity Feed -->
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

        <div class="mt-4 d-flex gap-2 flex-wrap">
            <a href="results.php" class="btn btn-primary">
                <i class="fas fa-chart-bar me-2"></i><?php echo __('dashboard_view_results', 'View Full Results'); ?>
            </a>
            <a href="assessment.php" class="btn btn-outline">
                <i class="fas fa-redo me-2"></i><?php echo __('dashboard_retake', 'Retake Assessment'); ?>
            </a>
            <?php if (count($careerMatches) >= 2): ?>
            <a href="compare.php?a=<?php echo $careerMatches[0]['career_id']; ?>&b=<?php echo $careerMatches[1]['career_id']; ?>" class="btn btn-outline-info">
                <i class="fas fa-balance-scale me-2"></i><?php echo __('compare_careers', 'Compare Careers'); ?>
            </a>
            <?php endif; ?>
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

        <!-- Next Steps Card -->
        <div class="sidebar-panel" style="background: #e8f5e9;">
            <h6 class="mb-3"><i class="fas fa-lightbulb me-2 text-warning"></i><?php echo __('dashboard_next_steps', 'Suggested Next Steps'); ?></h6>
            <ul class="list-unstyled mb-0 small">
                <?php if ($assessmentState === 'none'): ?>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-primary"></i><?php echo __('dashboard_step_take_assessment', 'Take the career interest assessment'); ?></li>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-muted"></i><?php echo __('dashboard_step_explore_careers', 'Explore career options'); ?></li>
                <?php elseif ($assessmentState === 'in_progress'): ?>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-warning"></i><?php echo __('dashboard_step_complete_assessment', 'Complete your assessment'); ?></li>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-muted"></i><?php echo __('dashboard_step_view_results', 'View your results'); ?></li>
                <?php else: ?>
                <li class="mb-2"><i class="fas fa-check me-2 text-success"></i><?php echo __('dashboard_step_explore_matches', 'Explore your top career matches'); ?></li>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-primary"></i><?php echo __('dashboard_step_research_institutions', 'Research education pathways'); ?></li>
                <li class="mb-2"><i class="fas fa-arrow-right me-2 text-muted"></i><?php echo __('dashboard_step_save_favorites', 'Save careers you like'); ?></li>
                <?php endif; ?>
            </ul>
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

<style>
/* Journey Progress Styles */
.journey-progress-container {
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

.journey-progress-bar {
    height: 8px;
    background: #e9ecef;
    border-radius: 4px;
    margin-bottom: 20px;
    overflow: hidden;
}

.journey-progress-fill {
    height: 100%;
    background: #2E7D5A;
    border-radius: 4px;
    transition: width 0.5s ease-out;
}

.journey-milestones {
    display: flex;
    justify-content: space-between;
    position: relative;
}

.journey-milestones::before {
    content: '';
    position: absolute;
    top: 15px;
    left: 15%;
    right: 15%;
    height: 2px;
    background: #e9ecef;
    z-index: 0;
}

.milestone {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 8px;
    position: relative;
    z-index: 1;
}

.milestone-icon {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #6c757d;
    font-size: 0.875rem;
    transition: all 0.3s ease;
}

.milestone.completed .milestone-icon {
    background: #2E7D5A;
    color: white;
}

.milestone-label {
    font-size: 0.75rem;
    color: #6c757d;
    text-align: center;
}

.milestone.completed .milestone-label {
    color: #2E7D5A;
    font-weight: 500;
}

/* Journey Card */
.journey-card {
    background: white;
    border-radius: 12px;
    padding: 24px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.05);
}

/* Assessment Progress Card */
.assessment-progress-card {
    background: #fffbeb;
    border-radius: 8px;
    padding: 16px;
}

@media (max-width: 768px) {
    .journey-milestones {
        flex-wrap: wrap;
        gap: 16px;
        justify-content: center;
    }

    .journey-milestones::before {
        display: none;
    }

    .milestone {
        width: 60px;
    }
}
</style>

<?php require_once 'includes/footer-dashboard.php'; ?>
