<?php
/**
 * EduBridge Rwanda - Admin Reports
 */

$pageTitle = 'Reports';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get statistics
// Students by school
$stmt = $db->query("
    SELECT school_name, COUNT(*) as student_count
    FROM users
    WHERE role = 'student' AND is_active = 1
    GROUP BY school_name
    ORDER BY student_count DESC
");
$studentsBySchool = $stmt->fetchAll();

// Assessments over time (last 30 days)
$stmt = $db->query("
    SELECT DATE(completed_at) as date, COUNT(*) as count
    FROM user_assessments
    WHERE is_completed = 1 AND completed_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(completed_at)
    ORDER BY date
");
$assessmentsByDate = $stmt->fetchAll();

// Top career matches
$stmt = $db->query("
    SELECT c.title_en, COUNT(cm.id) as match_count
    FROM career_matches cm
    JOIN careers c ON cm.career_id = c.id
    WHERE cm.rank_order = 1
    GROUP BY c.id
    ORDER BY match_count DESC
    LIMIT 10
");
$topCareers = $stmt->fetchAll();

// Category distribution
$stmt = $db->query("
    SELECT cc.name_en, cc.code, AVG(ar.percentage) as avg_score
    FROM assessment_results ar
    JOIN career_categories cc ON ar.category_id = cc.id
    GROUP BY cc.id
    ORDER BY avg_score DESC
");
$categoryDistribution = $stmt->fetchAll();

// Student grade distribution
$stmt = $db->query("
    SELECT grade_level, COUNT(*) as count
    FROM users
    WHERE role = 'student' AND grade_level IS NOT NULL
    GROUP BY grade_level
    ORDER BY grade_level
");
$gradeDistribution = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i><?php echo SITE_NAME; ?> Admin
            </a>
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home me-1"></i>Main Site
                </a>
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
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
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="careers.php">
                                <i class="fas fa-briefcase"></i> Careers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="institutions.php">
                                <i class="fas fa-university"></i> Institutions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="questions.php">
                                <i class="fas fa-question-circle"></i> Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-chart-bar me-2"></i>Reports & Analytics</h2>
                    <button class="btn btn-outline-primary" onclick="window.print()">
                        <i class="fas fa-print me-1"></i>Print Report
                    </button>
                </div>

                <div class="row">
                    <!-- Top Careers -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-trophy me-2"></i>Top Career Matches</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($topCareers as $index => $career): ?>
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span>
                                        <span class="badge bg-<?php echo $index < 3 ? 'primary' : 'secondary'; ?> me-2"><?php echo $index + 1; ?></span>
                                        <?php echo htmlspecialchars($career['title_en']); ?>
                                    </span>
                                    <span class="badge bg-light text-dark"><?php echo $career['match_count']; ?></span>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Category Distribution -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-pie-chart me-2"></i>Interest Category Averages</h5>
                            </div>
                            <div class="card-body">
                                <?php foreach ($categoryDistribution as $cat): ?>
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="badge category-badge-<?php echo $cat['code']; ?> me-2"><?php echo $cat['name_en']; ?></span>
                                        <span><?php echo number_format($cat['avg_score'], 1); ?>%</span>
                                    </div>
                                    <div class="progress" style="height: 10px;">
                                        <div class="progress-bar category-<?php echo $cat['code']; ?>" style="width: <?php echo $cat['avg_score']; ?>%"></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Students by School -->
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

                    <!-- Grade Distribution -->
                    <div class="col-md-6 mb-4">
                        <div class="card h-100">
                            <div class="card-header">
                                <h5 class="mb-0"><i class="fas fa-graduation-cap me-2"></i>Students by Grade Level</h5>
                            </div>
                            <div class="card-body">
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
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Assessments Completed</th>
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
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
