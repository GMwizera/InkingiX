<?php
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();
$currentLang = getCurrentLanguage();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get user initials for avatar
$initials = strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1));
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo __('meta_description', 'EduBridge Rwanda - Career Discovery Platform for Rwandan Students'); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>

    <!-- Google Fonts - Roboto -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome Icons -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="app-container">
        <!-- Sidebar Navigation -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                </div>
            </div>

            <nav class="sidebar-nav">
                <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" title="<?php echo __('nav_dashboard', 'Dashboard'); ?>">
                    <i class="fas fa-th-large"></i>
                </a>
                <a href="assessment.php" class="nav-item <?php echo $currentPage === 'assessment' ? 'active' : ''; ?>" title="<?php echo __('nav_assessment', 'Assessment'); ?>">
                    <i class="fas fa-clipboard-list"></i>
                </a>
                <a href="results.php" class="nav-item <?php echo $currentPage === 'results' ? 'active' : ''; ?>" title="<?php echo __('nav_results', 'Results'); ?>">
                    <i class="fas fa-chart-pie"></i>
                </a>
                <a href="careers.php" class="nav-item <?php echo $currentPage === 'careers' || $currentPage === 'career' ? 'active' : ''; ?>" title="<?php echo __('nav_careers', 'Careers'); ?>">
                    <i class="fas fa-briefcase"></i>
                </a>
                <a href="institutions.php" class="nav-item <?php echo $currentPage === 'institutions' ? 'active' : ''; ?>" title="<?php echo __('nav_institutions', 'Institutions'); ?>">
                    <i class="fas fa-university"></i>
                </a>
                <a href="faq.php" class="nav-item <?php echo $currentPage === 'faq' ? 'active' : ''; ?>" title="FAQ">
                    <i class="fas fa-question-circle"></i>
                </a>
            </nav>

            <div class="sidebar-footer">
                <a href="profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" title="<?php echo __('nav_profile', 'Profile'); ?>">
                    <i class="fas fa-cog"></i>
                </a>
                <?php if (hasRole(['school_admin', 'system_admin'])): ?>
                <a href="admin/index.php" class="nav-item" title="<?php echo __('nav_admin', 'Admin'); ?>">
                    <i class="fas fa-shield-alt"></i>
                </a>
                <?php endif; ?>
                <a href="profile.php" class="user-avatar-sidebar" title="<?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>">
                    <?php echo $initials; ?>
                </a>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1 class="page-title"><?php echo isset($pageGreeting) ? $pageGreeting : (isset($pageTitle) ? $pageTitle : 'Dashboard'); ?></h1>
                    <p class="page-subtitle"><?php echo isset($pageSubtitle) ? $pageSubtitle : __('dashboard_subtitle', 'Explore your career discovery journey'); ?></p>
                </div>
                <div class="header-right">
                    <div class="header-search">
                        <input type="text" placeholder="<?php echo __('search_placeholder', 'Search careers...'); ?>" class="search-input">
                        <button class="search-btn">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <!-- Language Switcher -->
                    <div class="dropdown">
                        <button class="header-icon-btn" data-bs-toggle="dropdown" title="<?php echo __('language', 'Language'); ?>">
                            <i class="fas fa-globe"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?php echo $currentLang === 'en' ? 'active' : ''; ?>" href="?lang=en">English</a></li>
                            <li><a class="dropdown-item <?php echo $currentLang === 'rw' ? 'active' : ''; ?>" href="?lang=rw">Kinyarwanda</a></li>
                        </ul>
                    </div>

                    <!-- Notifications (placeholder) -->
                    <button class="header-icon-btn" title="<?php echo __('notifications', 'Notifications'); ?>">
                        <i class="fas fa-bell"></i>
                        <span class="notification-badge"></span>
                    </button>

                    <!-- Logout -->
                    <a href="logout.php" class="header-icon-btn" title="<?php echo __('nav_logout', 'Logout'); ?>">
                        <i class="fas fa-sign-out-alt"></i>
                    </a>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <?php displayFlashMessage(); ?>
