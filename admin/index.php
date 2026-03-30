<?php
/**
 * EduBridge Rwanda - Admin Dashboard
 */

$pageTitle = 'Admin Dashboard';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get statistics
$stats = [];

// Total students
$stmt = $db->query("SELECT COUNT(*) as count FROM users WHERE role = 'student' AND is_active = 1");
$stats['students'] = $stmt->fetch()['count'];

// Total assessments
$stmt = $db->query("SELECT COUNT(*) as count FROM user_assessments WHERE is_completed = 1");
$stats['assessments'] = $stmt->fetch()['count'];

// Total careers
$stmt = $db->query("SELECT COUNT(*) as count FROM careers WHERE is_active = 1");
$stats['careers'] = $stmt->fetch()['count'];

// Total institutions
$stmt = $db->query("SELECT COUNT(*) as count FROM institutions WHERE is_active = 1");
$stats['institutions'] = $stmt->fetch()['count'];

// Recent registrations
$stmt = $db->query("
    SELECT * FROM users
    WHERE role = 'student'
    ORDER BY created_at DESC
    LIMIT 5
");
$recentUsers = $stmt->fetchAll();

// Recent assessments
$stmt = $db->query("
    SELECT ua.*, u.first_name, u.last_name, u.school_name
    FROM user_assessments ua
    JOIN users u ON ua.user_id = u.id
    WHERE ua.is_completed = 1
    ORDER BY ua.completed_at DESC
    LIMIT 5
");
$recentAssessments = $stmt->fetchAll();

// Assessments by school
$stmt = $db->query("
    SELECT u.school_name, COUNT(ua.id) as assessment_count
    FROM users u
    JOIN user_assessments ua ON u.id = ua.user_id
    WHERE ua.is_completed = 1
    GROUP BY u.school_name
    ORDER BY assessment_count DESC
    LIMIT 5
");
$schoolStats = $stmt->fetchAll();
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
                <span class="text-light me-3">
                    <i class="fas fa-user me-1"></i><?php echo htmlspecialchars($currentUser['first_name']); ?>
                </span>
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
                            <a class="nav-link active" href="index.php">
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
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                        <?php if ($currentUser['role'] === 'system_admin'): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="fas fa-cog"></i> Settings
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Dashboard</h2>
                    <span class="badge bg-info"><?php echo ucfirst($currentUser['role']); ?></span>
                </div>

                <!-- Stats Cards -->
                <div class="row g-4 mb-4">
                    <div class="col-md-3">
                        <div class="card bg-primary text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['students']); ?></h3>
                                        <small>Total Students</small>
                                    </div>
                                    <i class="fas fa-users fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-success text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['assessments']); ?></h3>
                                        <small>Completed Assessments</small>
                                    </div>
                                    <i class="fas fa-clipboard-check fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-info text-white">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['careers']); ?></h3>
                                        <small>Career Profiles</small>
                                    </div>
                                    <i class="fas fa-briefcase fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="card bg-warning text-dark">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h3 class="mb-0"><?php echo number_format($stats['institutions']); ?></h3>
                                        <small>Institutions</small>
                                    </div>
                                    <i class="fas fa-university fa-2x opacity-50"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Recent Registrations -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Registrations</h5>
                                <a href="users.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>School</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentUsers as $user): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></td>
                                            <td><small><?php echo htmlspecialchars($user['school_name']); ?></small></td>
                                            <td><small><?php echo date('M j', strtotime($user['created_at'])); ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Recent Assessments -->
                    <div class="col-md-6 mb-4">
                        <div class="card">
                            <div class="card-header d-flex justify-content-between align-items-center">
                                <h5 class="mb-0">Recent Assessments</h5>
                                <a href="reports.php" class="btn btn-sm btn-outline-primary">View All</a>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Student</th>
                                            <th>School</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($recentAssessments as $assessment): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($assessment['first_name'] . ' ' . $assessment['last_name']); ?></td>
                                            <td><small><?php echo htmlspecialchars($assessment['school_name']); ?></small></td>
                                            <td><small><?php echo date('M j', strtotime($assessment['completed_at'])); ?></small></td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Top Schools -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Top Schools by Assessments</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach ($schoolStats as $school): ?>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span><?php echo htmlspecialchars($school['school_name']); ?></span>
                                <span class="badge bg-primary"><?php echo $school['assessment_count']; ?></span>
                            </div>
                            <div class="progress" style="height: 10px;">
                                <?php $percentage = ($school['assessment_count'] / max(array_column($schoolStats, 'assessment_count'))) * 100; ?>
                                <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
