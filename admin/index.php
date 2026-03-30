<?php
/**
 * EduBridge Rwanda - Admin Dashboard
 * School-scoped for school_admin, global view with filter for system_admin
 */

$pageTitle = 'Admin Dashboard';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Determine school filter
$schoolFilter = null;
$selectedSchool = null;

if ($currentUser['role'] === 'school_admin') {
    // School admins can only see their own school
    $schoolFilter = $currentUser['school_name'];
    $selectedSchool = $schoolFilter;
} elseif ($currentUser['role'] === 'system_admin') {
    // System admins can filter by school or see all
    if (isset($_GET['school']) && $_GET['school'] !== 'all') {
        $schoolFilter = $_GET['school'];
        $selectedSchool = $schoolFilter;
    }
}

// Get list of all schools for dropdown (system_admin only)
$allSchools = [];
if ($currentUser['role'] === 'system_admin') {
    $stmt = $db->query("
        SELECT DISTINCT school_name
        FROM users
        WHERE school_name IS NOT NULL AND school_name != ''
        ORDER BY school_name
    ");
    $allSchools = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// Build WHERE clause for school filtering
$schoolWhere = '';
$schoolParams = [];
if ($schoolFilter) {
    $schoolWhere = " AND u.school_name = ?";
    $schoolParams = [$schoolFilter];
}

// Get statistics
$stats = [];

// Total students (filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM users u WHERE role = 'student' AND is_active = 1" . $schoolWhere);
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND is_active = 1");
}
$stats['students'] = $stmt->fetch()['count'];

// Total completed assessments (filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM user_assessments ua
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1" . $schoolWhere
    );
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("SELECT COUNT(*) as count FROM user_assessments WHERE is_completed = 1");
}
$stats['assessments'] = $stmt->fetch()['count'];

// Total careers (global - not school-specific)
$stmt = $db->query("SELECT COUNT(*) as count FROM careers WHERE is_active = 1");
$stats['careers'] = $stmt->fetch()['count'];

// Total institutions (global - not school-specific)
$stmt = $db->query("SELECT COUNT(*) as count FROM institutions WHERE is_active = 1");
$stats['institutions'] = $stmt->fetch()['count'];

// Assessments this month (filtered by school)
$currentMonth = date('Y-m-01');
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT COUNT(*) as count
        FROM user_assessments ua
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1
        AND ua.completed_at >= ?" . $schoolWhere
    );
    $stmt->execute(array_merge([$currentMonth], $schoolParams));
} else {
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM user_assessments WHERE is_completed = 1 AND completed_at >= ?");
    $stmt->execute([$currentMonth]);
}
$stats['assessments_this_month'] = $stmt->fetch()['count'];

// Top 5 careers students want (rank 1 matches, filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT c.id, c.title_en, c.title_rw, c.demand_level, COUNT(*) AS interest_count
        FROM career_matches cm
        JOIN careers c ON cm.career_id = c.id
        JOIN user_assessments ua ON cm.assessment_id = ua.id
        JOIN users u ON ua.user_id = u.id
        WHERE cm.rank = 1 AND u.school_name = ?
        GROUP BY c.id
        ORDER BY interest_count DESC
        LIMIT 5
    ");
    $stmt->execute([$schoolFilter]);
} else {
    $stmt = $db->query("
        SELECT c.id, c.title_en, c.title_rw, c.demand_level, COUNT(*) AS interest_count
        FROM career_matches cm
        JOIN careers c ON cm.career_id = c.id
        WHERE cm.rank = 1
        GROUP BY c.id
        ORDER BY interest_count DESC
        LIMIT 5
    ");
}
$topCareers = $stmt->fetchAll();
$maxCareerCount = !empty($topCareers) ? max(array_column($topCareers, 'interest_count')) : 1;

// Grade distribution (S1-S6, filtered by school)
$gradeDistribution = [];
$grades = ['S1', 'S2', 'S3', 'S4', 'S5', 'S6'];
foreach ($grades as $grade) {
    if ($schoolFilter) {
        $stmt = $db->prepare("
            SELECT COUNT(*) as count
            FROM users u
            WHERE grade_level = ? AND role = 'student' AND is_active = 1" . $schoolWhere
        );
        $stmt->execute(array_merge([$grade], $schoolParams));
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) as count FROM users WHERE grade_level = ? AND role = 'student' AND is_active = 1");
        $stmt->execute([$grade]);
    }
    $gradeDistribution[$grade] = $stmt->fetch()['count'];
}
$maxGradeCount = max($gradeDistribution) ?: 1;

// Interest clusters (RIASEC category percentages, filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT cc.code, cc.name_en, cc.name_rw, cc.icon,
               AVG(ar.score) as avg_score,
               COUNT(ar.id) as response_count
        FROM assessment_results ar
        JOIN career_categories cc ON ar.category_id = cc.id
        JOIN user_assessments ua ON ar.assessment_id = ua.id
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1" . $schoolWhere . "
        GROUP BY cc.id
        ORDER BY avg_score DESC
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT cc.code, cc.name_en, cc.name_rw, cc.icon,
               AVG(ar.score) as avg_score,
               COUNT(ar.id) as response_count
        FROM assessment_results ar
        JOIN career_categories cc ON ar.category_id = cc.id
        JOIN user_assessments ua ON ar.assessment_id = ua.id
        WHERE ua.is_completed = 1
        GROUP BY cc.id
        ORDER BY avg_score DESC
    ");
}
$interestClusters = $stmt->fetchAll();
$totalAvgScore = array_sum(array_column($interestClusters, 'avg_score')) ?: 1;

// Recent registrations (filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT * FROM users u
        WHERE role = 'student'" . $schoolWhere . "
        ORDER BY created_at DESC
        LIMIT 5
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT * FROM users
        WHERE role = 'student'
        ORDER BY created_at DESC
        LIMIT 5
    ");
}
$recentUsers = $stmt->fetchAll();

// Recent assessments (filtered by school)
if ($schoolFilter) {
    $stmt = $db->prepare("
        SELECT ua.*, u.first_name, u.last_name, u.school_name
        FROM user_assessments ua
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1" . $schoolWhere . "
        ORDER BY ua.completed_at DESC
        LIMIT 5
    ");
    $stmt->execute($schoolParams);
} else {
    $stmt = $db->query("
        SELECT ua.*, u.first_name, u.last_name, u.school_name
        FROM user_assessments ua
        JOIN users u ON ua.user_id = u.id
        WHERE ua.is_completed = 1
        ORDER BY ua.completed_at DESC
        LIMIT 5
    ");
}
$recentAssessments = $stmt->fetchAll();

// RIASEC category colors
$categoryColors = [
    'R' => '#dc3545', // Red - Realistic
    'I' => '#6f42c1', // Purple - Investigative
    'A' => '#fd7e14', // Orange - Artistic
    'S' => '#20c997', // Teal - Social
    'E' => '#0d6efd', // Blue - Enterprising
    'C' => '#6c757d', // Gray - Conventional
];
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <style>
        /* CSS-only bar chart styles */
        .css-chart {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }
        .css-chart-bar {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .css-chart-label {
            min-width: 80px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .css-chart-bar-container {
            flex: 1;
            height: 24px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
            position: relative;
        }
        .css-chart-bar-fill {
            height: 100%;
            border-radius: 4px;
            transition: width 0.5s ease-out;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 8px;
            min-width: 30px;
        }
        .css-chart-bar-value {
            font-size: 0.75rem;
            font-weight: 600;
            color: white;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }
        .css-chart-bar-value-outside {
            position: absolute;
            right: -35px;
            font-size: 0.75rem;
            font-weight: 600;
            color: #495057;
        }

        /* Grade distribution colors */
        .grade-s1 { background: linear-gradient(90deg, #667eea 0%, #764ba2 100%); }
        .grade-s2 { background: linear-gradient(90deg, #f093fb 0%, #f5576c 100%); }
        .grade-s3 { background: linear-gradient(90deg, #4facfe 0%, #00f2fe 100%); }
        .grade-s4 { background: linear-gradient(90deg, #43e97b 0%, #38f9d7 100%); }
        .grade-s5 { background: linear-gradient(90deg, #fa709a 0%, #fee140 100%); }
        .grade-s6 { background: linear-gradient(90deg, #a18cd1 0%, #fbc2eb 100%); }

        /* Interest cluster donut-style display */
        .interest-cluster-item {
            display: flex;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }
        .interest-cluster-item:last-child {
            border-bottom: none;
        }
        .interest-cluster-icon {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            margin-right: 12px;
            font-size: 0.875rem;
        }
        .interest-cluster-info {
            flex: 1;
        }
        .interest-cluster-name {
            font-weight: 500;
            font-size: 0.875rem;
        }
        .interest-cluster-bar {
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 4px;
            overflow: hidden;
        }
        .interest-cluster-fill {
            height: 100%;
            border-radius: 3px;
            transition: width 0.5s ease-out;
        }
        .interest-cluster-percentage {
            font-weight: 600;
            font-size: 0.875rem;
            min-width: 45px;
            text-align: right;
        }

        /* Monthly stat highlight */
        .stat-highlight {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }
        .stat-highlight-number {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1;
        }
        .stat-highlight-label {
            font-size: 0.875rem;
            opacity: 0.9;
            margin-top: 8px;
        }

        /* School filter dropdown */
        .school-filter-form {
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        .school-filter-form select {
            min-width: 200px;
        }
    </style>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i><?php echo SITE_NAME; ?> <?php echo __('nav_admin'); ?>
            </a>
            <div class="d-flex align-items-center">
                <span class="text-light me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($currentUser['first_name']); ?>
                    <?php if ($currentUser['role'] === 'school_admin'): ?>
                    <small class="opacity-75">(<?php echo htmlspecialchars($currentUser['school_name']); ?>)</small>
                    <?php endif; ?>
                </span>
                <a href="../index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home me-1"></i><?php echo __('nav_home'); ?>
                </a>
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i><?php echo __('nav_logout'); ?>
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> <?php echo __('admin_dashboard'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> <?php echo __('admin_users'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="careers.php">
                                <i class="fas fa-briefcase"></i> <?php echo __('nav_careers'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="institutions.php">
                                <i class="fas fa-university"></i> <?php echo __('nav_institutions'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="questions.php">
                                <i class="fas fa-question-circle"></i> <?php echo __('admin_questions'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> <?php echo __('admin_reports'); ?>
                            </a>
                        </li>
                        <?php if ($currentUser['role'] === 'system_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> <?php echo __('admin_settings'); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
                    <div>
                        <h2 class="mb-1"><?php echo __('admin_dashboard'); ?></h2>
                        <?php if ($selectedSchool): ?>
                        <p class="text-muted mb-0">
                            <i class="fas fa-school me-1"></i>
                            <?php echo htmlspecialchars($selectedSchool); ?>
                        </p>
                        <?php endif; ?>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <?php if ($currentUser['role'] === 'system_admin' && !empty($allSchools)): ?>
                        <!-- School Filter Dropdown -->
                        <form method="GET" class="school-filter-form">
                            <label for="school" class="form-label mb-0 text-muted small">
                                <i class="fas fa-filter me-1"></i><?php echo __('admin_filter_school'); ?>:
                            </label>
                            <select name="school" id="school" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="all"><?php echo __('admin_all_schools'); ?></option>
                                <?php foreach ($allSchools as $school): ?>
                                <option value="<?php echo htmlspecialchars($school); ?>" <?php echo $selectedSchool === $school ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($school); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </form>
                        <?php endif; ?>
                        <span class="badge bg-info"><?php echo $currentUser['role'] === 'system_admin' ? __('admin_system_admin') : __('admin_school_admin'); ?></span>
                    </div>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['students']); ?></h3>
                                        <small><?php echo __('admin_total_students'); ?></small>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['assessments']); ?></h3>
                                        <small><?php echo __('admin_completed_assessments'); ?></small>
                                    </div>
                                    <i class="fas fa-clipboard-check fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['careers']); ?></h3>
                                        <small><?php echo __('admin_career_profiles'); ?></small>
                                    </div>
                                    <i class="fas fa-briefcase fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark h-100">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['institutions']); ?></h3>
                                        <small><?php echo __('nav_institutions'); ?></small>
                                    </div>
                                    <i class="fas fa-university fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- New Data Panels Row -->
                <div class="row g-4 mb-4">
                    <!-- Panel 1: Top 5 Careers (Rank 1) -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-trophy me-2 text-warning"></i>
                                    <?php echo __('admin_top_careers_wanted'); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($topCareers)): ?>
                                <p class="text-muted small mb-3"><?php echo __('admin_top_careers_desc'); ?></p>
                                <div class="css-chart">
                                    <?php foreach ($topCareers as $index => $career):
                                        $percentage = ($career['interest_count'] / $maxCareerCount) * 100;
                                    ?>
                                    <div class="css-chart-bar">
                                        <span class="css-chart-label">
                                            <span class="badge bg-secondary me-1">#<?php echo $index + 1; ?></span>
                                            <?php echo htmlspecialchars(getLocalizedField($career, 'title')); ?>
                                        </span>
                                    </div>
                                    <div class="d-flex align-items-center gap-2 ps-3 mb-2">
                                        <div class="css-chart-bar-container" style="max-width: 300px;">
                                            <div class="css-chart-bar-fill bg-primary" style="width: <?php echo $percentage; ?>%">
                                                <span class="css-chart-bar-value"><?php echo $career['interest_count']; ?></span>
                                            </div>
                                        </div>
                                        <?php echo getDemandBadge($career['demand_level'] ?? 'growing'); ?>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                                <?php else: ?>
                                <p class="text-muted mb-0"><?php echo __('admin_no_data_yet'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 2: Assessments This Month -->
                    <div class="col-lg-3">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-calendar-check me-2 text-success"></i>
                                    <?php echo __('admin_this_month'); ?>
                                </h5>
                            </div>
                            <div class="card-body d-flex flex-column justify-content-center">
                                <div class="stat-highlight">
                                    <div class="stat-highlight-number"><?php echo number_format($stats['assessments_this_month']); ?></div>
                                    <div class="stat-highlight-label">
                                        <?php echo __('admin_assessments_completed'); ?>
                                        <br><small><?php echo date('F Y'); ?></small>
                                    </div>
                                </div>
                                <div class="text-center mt-3">
                                    <small class="text-muted">
                                        <?php
                                        $avgDaily = $stats['assessments_this_month'] > 0
                                            ? round($stats['assessments_this_month'] / date('j'), 1)
                                            : 0;
                                        echo sprintf(__('admin_avg_daily'), $avgDaily);
                                        ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Panel 3: Grade Distribution -->
                    <div class="col-lg-3">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-graduation-cap me-2 text-info"></i>
                                    <?php echo __('admin_grade_distribution'); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <div class="css-chart">
                                    <?php foreach ($gradeDistribution as $grade => $count):
                                        $percentage = $maxGradeCount > 0 ? ($count / $maxGradeCount) * 100 : 0;
                                        $gradeClass = 'grade-' . strtolower($grade);
                                    ?>
                                    <div class="css-chart-bar">
                                        <span class="css-chart-label" style="min-width: 35px;"><?php echo $grade; ?></span>
                                        <div class="css-chart-bar-container" style="position: relative;">
                                            <div class="css-chart-bar-fill <?php echo $gradeClass; ?>" style="width: <?php echo max($percentage, 5); ?>%">
                                                <?php if ($count > 0): ?>
                                                <span class="css-chart-bar-value"><?php echo $count; ?></span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Interest Clusters Row -->
                <div class="row g-4 mb-4">
                    <!-- Panel 4: Interest Clusters (RIASEC) -->
                    <div class="col-lg-6">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-chart-pie me-2 text-purple"></i>
                                    <?php echo __('admin_interest_clusters'); ?>
                                </h5>
                            </div>
                            <div class="card-body">
                                <?php if (!empty($interestClusters)): ?>
                                <p class="text-muted small mb-3"><?php echo __('admin_riasec_desc'); ?></p>
                                <?php foreach ($interestClusters as $cluster):
                                    $percentage = $totalAvgScore > 0 ? ($cluster['avg_score'] / $totalAvgScore) * 100 : 0;
                                    $color = $categoryColors[$cluster['code']] ?? '#6c757d';
                                ?>
                                <div class="interest-cluster-item">
                                    <div class="interest-cluster-icon" style="background: <?php echo $color; ?>">
                                        <i class="fas <?php echo $cluster['icon'] ?? 'fa-circle'; ?>"></i>
                                    </div>
                                    <div class="interest-cluster-info">
                                        <div class="interest-cluster-name">
                                            <?php echo htmlspecialchars(getLocalizedField($cluster, 'name')); ?>
                                            <span class="badge bg-light text-dark ms-1"><?php echo $cluster['code']; ?></span>
                                        </div>
                                        <div class="interest-cluster-bar">
                                            <div class="interest-cluster-fill" style="width: <?php echo $percentage; ?>%; background: <?php echo $color; ?>;"></div>
                                        </div>
                                    </div>
                                    <span class="interest-cluster-percentage"><?php echo number_format($percentage, 1); ?>%</span>
                                </div>
                                <?php endforeach; ?>
                                <?php else: ?>
                                <p class="text-muted mb-0"><?php echo __('admin_no_data_yet'); ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Activity -->
                    <div class="col-lg-6">
                        <div class="row g-4">
                            <!-- Recent Registrations -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-user-plus me-2"></i>
                                            <?php echo __('admin_recent_registrations'); ?>
                                        </h5>
                                        <a href="users.php" class="btn btn-sm btn-outline-primary"><?php echo __('view_all'); ?></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th><?php echo __('admin_name'); ?></th>
                                                    <th><?php echo __('profile_school'); ?></th>
                                                    <th><?php echo __('admin_date'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($recentUsers)): ?>
                                                <?php foreach ($recentUsers as $user): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                                    <td><small><?php echo htmlspecialchars($user['school_name'] ?? '-'); ?></small></td>
                                                    <td><small><?php echo date('M j', strtotime($user['created_at'])); ?></small></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                <tr><td colspan="3" class="text-muted text-center"><?php echo __('admin_no_data_yet'); ?></td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            <!-- Recent Assessments -->
                            <div class="col-12">
                                <div class="card">
                                    <div class="card-header d-flex justify-content-between align-items-center">
                                        <h5 class="mb-0">
                                            <i class="fas fa-clipboard-check me-2"></i>
                                            <?php echo __('admin_recent_assessments'); ?>
                                        </h5>
                                        <a href="reports.php" class="btn btn-sm btn-outline-primary"><?php echo __('view_all'); ?></a>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-hover mb-0">
                                            <thead>
                                                <tr>
                                                    <th><?php echo __('admin_student'); ?></th>
                                                    <th><?php echo __('profile_school'); ?></th>
                                                    <th><?php echo __('admin_date'); ?></th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php if (!empty($recentAssessments)): ?>
                                                <?php foreach ($recentAssessments as $assessment): ?>
                                                <tr>
                                                    <td><?php echo htmlspecialchars($assessment['first_name'] . ' ' . $assessment['last_name']); ?></td>
                                                    <td><small><?php echo htmlspecialchars($assessment['school_name'] ?? '-'); ?></small></td>
                                                    <td><small><?php echo date('M j', strtotime($assessment['completed_at'])); ?></small></td>
                                                </tr>
                                                <?php endforeach; ?>
                                                <?php else: ?>
                                                <tr><td colspan="3" class="text-muted text-center"><?php echo __('admin_no_data_yet'); ?></td></tr>
                                                <?php endif; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
