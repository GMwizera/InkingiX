<?php
/**
 * EduBridge Rwanda - Admin Header
 * Shared navigation for admin panel
 *
 * Note: functions.php should be included by the calling page before this header
 * to handle POST requests and role checking before output starts.
 */

// Ensure functions.php is loaded (in case this is called directly)
if (!function_exists('getCurrentUser')) {
    require_once dirname(__DIR__) . '/../includes/functions.php';
}

// Get current user - should already be set by calling page's requireRole()
if (!isset($currentUser)) {
    $currentUser = getCurrentUser();
}
$currentAdminPage = basename($_SERVER['PHP_SELF'], '.php');

// Get selected school for language switcher preservation
$selectedSchool = isset($_GET['school']) && $_GET['school'] !== 'all' ? $_GET['school'] : '';
?>
<!DOCTYPE html>
<html lang="<?php echo getCurrentLanguage(); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?> <?php echo __('nav_admin'); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
    <?php if (isset($extraStyles)) echo $extraStyles; ?>
</head>
<body>
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-shield-alt me-2"></i><?php echo SITE_NAME; ?> <?php echo __('nav_admin'); ?>
            </a>
            <div class="d-flex align-items-center gap-3">
                <!-- Language Switcher -->
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="fas fa-globe me-1"></i>
                        <?php echo getCurrentLanguage() === 'en' ? 'EN' : 'RW'; ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item <?php echo getCurrentLanguage() === 'en' ? 'active' : ''; ?>" href="?lang=en<?php echo $selectedSchool ? '&school=' . urlencode($selectedSchool) : ''; ?>">
                            <i class="fas fa-check me-2 <?php echo getCurrentLanguage() === 'en' ? '' : 'invisible'; ?>"></i>English
                        </a></li>
                        <li><a class="dropdown-item <?php echo getCurrentLanguage() === 'rw' ? 'active' : ''; ?>" href="?lang=rw<?php echo $selectedSchool ? '&school=' . urlencode($selectedSchool) : ''; ?>">
                            <i class="fas fa-check me-2 <?php echo getCurrentLanguage() === 'rw' ? '' : 'invisible'; ?>"></i>Kinyarwanda
                        </a></li>
                    </ul>
                </div>

                <!-- Admin Profile Dropdown -->
                <div class="dropdown">
                    <button class="btn btn-outline-light btn-sm dropdown-toggle d-flex align-items-center" type="button" data-bs-toggle="dropdown">
                        <div class="bg-light text-dark rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; font-size: 0.75rem; font-weight: 600;">
                            <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                        </div>
                        <?php echo htmlspecialchars($currentUser['first_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li class="dropdown-header">
                            <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                            <br><small class="text-muted"><?php echo $currentUser['role'] === 'system_admin' ? __('admin_system_admin') : __('admin_school_admin'); ?></small>
                            <?php if ($currentUser['role'] === 'school_admin' && !empty($currentUser['school_name'])): ?>
                            <br><small class="text-muted"><?php echo htmlspecialchars($currentUser['school_name']); ?></small>
                            <?php endif; ?>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item" href="../profile.php">
                                <i class="fas fa-user me-2"></i><?php echo __('nav_profile'); ?>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item" href="../index.php">
                                <i class="fas fa-home me-2"></i><?php echo __('nav_home'); ?>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item text-danger" href="../logout.php">
                                <i class="fas fa-sign-out-alt me-2"></i><?php echo __('nav_logout'); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <!-- Dashboard - Both roles -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'index' ? 'active' : ''; ?>" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> <?php echo __('admin_dashboard'); ?>
                            </a>
                        </li>

                        <?php if ($currentUser['role'] === 'system_admin'): ?>
                        <!-- System Admin Navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array($currentAdminPage, ['careers', 'career-add', 'career-edit']) ? 'active' : ''; ?>" href="careers.php">
                                <i class="fas fa-briefcase"></i> <?php echo __('nav_careers'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'reports' ? 'active' : ''; ?>" href="reports.php">
                                <i class="fas fa-chart-bar"></i> <?php echo __('admin_reports'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'users' ? 'active' : ''; ?>" href="users.php">
                                <i class="fas fa-users"></i> <?php echo __('admin_users'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo in_array($currentAdminPage, ['institutions', 'institution-add', 'institution-edit']) ? 'active' : ''; ?>" href="institutions.php">
                                <i class="fas fa-university"></i> <?php echo __('nav_institutions'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'questions' ? 'active' : ''; ?>" href="questions.php">
                                <i class="fas fa-question-circle"></i> <?php echo __('admin_questions'); ?>
                            </a>
                        </li>

                        <?php else: ?>
                        <!-- School Admin Navigation -->
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'reports' ? 'active' : ''; ?>" href="reports.php">
                                <i class="fas fa-chart-bar"></i> <?php echo __('admin_reports'); ?>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo $currentAdminPage === 'users' ? 'active' : ''; ?>" href="users.php">
                                <i class="fas fa-users"></i> <?php echo __('admin_students'); ?>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php displayFlashMessage(); ?>
