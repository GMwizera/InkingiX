<?php
/**
 * EduBridge Rwanda - Student Dashboard Header
 * Sidebar navigation for logged-in students
 */
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
                <a href="index.php" class="logo" title="<?php echo SITE_NAME; ?>">
                    <div class="logo-icon">
                        <i class="fas fa-graduation-cap"></i>
                    </div>
                    <span class="logo-text">EduBridge</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <!-- My Journey / Dashboard -->
                <a href="dashboard.php" class="nav-item <?php echo $currentPage === 'dashboard' ? 'active' : ''; ?>" title="<?php echo __('nav_my_journey', 'My Journey'); ?>">
                    <i class="fas fa-road"></i>
                    <span class="nav-label"><?php echo __('nav_my_journey', 'My Journey'); ?></span>
                </a>

                <!-- Assessment -->
                <a href="assessment.php" class="nav-item <?php echo $currentPage === 'assessment' ? 'active' : ''; ?>" title="<?php echo __('nav_assessment', 'Assessment'); ?>">
                    <i class="fas fa-clipboard-list"></i>
                    <span class="nav-label"><?php echo __('nav_assessment', 'Assessment'); ?></span>
                </a>

                <!-- Careers -->
                <a href="careers.php" class="nav-item <?php echo $currentPage === 'careers' || $currentPage === 'career' || $currentPage === 'compare' ? 'active' : ''; ?>" title="<?php echo __('nav_careers', 'Careers'); ?>">
                    <i class="fas fa-briefcase"></i>
                    <span class="nav-label"><?php echo __('nav_careers', 'Careers'); ?></span>
                </a>

                <!-- Institutions -->
                <a href="institutions.php" class="nav-item <?php echo $currentPage === 'institutions' ? 'active' : ''; ?>" title="<?php echo __('nav_institutions', 'Institutions'); ?>">
                    <i class="fas fa-university"></i>
                    <span class="nav-label"><?php echo __('nav_institutions', 'Institutions'); ?></span>
                </a>

                <!-- Saved / Bookmarks -->
                <a href="bookmarks.php" class="nav-item <?php echo $currentPage === 'bookmarks' ? 'active' : ''; ?>" title="<?php echo __('nav_bookmarks', 'Saved'); ?>">
                    <i class="fas fa-bookmark"></i>
                    <span class="nav-label"><?php echo __('nav_bookmarks', 'Saved'); ?></span>
                </a>
            </nav>

            <div class="sidebar-footer">
                <!-- Profile -->
                <a href="profile.php" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" title="<?php echo __('nav_profile', 'Profile'); ?>">
                    <i class="fas fa-user"></i>
                    <span class="nav-label"><?php echo __('nav_profile', 'Profile'); ?></span>
                </a>

                <!-- Results -->
                <a href="results.php" class="nav-item <?php echo $currentPage === 'results' ? 'active' : ''; ?>" title="<?php echo __('nav_results', 'Results'); ?>">
                    <i class="fas fa-chart-pie"></i>
                    <span class="nav-label"><?php echo __('nav_results', 'Results'); ?></span>
                </a>

                <?php if (hasRole(['school_admin', 'system_admin'])): ?>
                <!-- Admin Panel -->
                <a href="admin/index.php" class="nav-item" title="<?php echo __('nav_admin', 'Admin'); ?>">
                    <i class="fas fa-shield-alt"></i>
                    <span class="nav-label"><?php echo __('nav_admin', 'Admin'); ?></span>
                </a>
                <?php endif; ?>

                <!-- User Avatar -->
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
                    <!-- Mobile menu toggle -->
                    <button class="mobile-menu-toggle d-lg-none me-3" type="button" onclick="toggleSidebar()">
                        <i class="fas fa-bars"></i>
                    </button>
                    <div>
                        <h1 class="page-title"><?php echo isset($pageGreeting) ? $pageGreeting : (isset($pageTitle) ? $pageTitle : __('nav_my_journey', 'My Journey')); ?></h1>
                        <p class="page-subtitle"><?php echo isset($pageSubtitle) ? $pageSubtitle : __('dashboard_subtitle', 'Explore your career discovery journey'); ?></p>
                    </div>
                </div>
                <div class="header-right">
                    <!-- Language Switcher -->
                    <div class="dropdown">
                        <button class="header-icon-btn" data-bs-toggle="dropdown" title="<?php echo __('language', 'Language'); ?>">
                            <span class="lang-badge"><?php echo $currentLang === 'en' ? 'EN' : 'RW'; ?></span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?php echo $currentLang === 'en' ? 'active' : ''; ?>" href="?lang=en">
                                <i class="fas fa-check me-2 <?php echo $currentLang === 'en' ? '' : 'invisible'; ?>"></i>English
                            </a></li>
                            <li><a class="dropdown-item <?php echo $currentLang === 'rw' ? 'active' : ''; ?>" href="?lang=rw">
                                <i class="fas fa-check me-2 <?php echo $currentLang === 'rw' ? '' : 'invisible'; ?>"></i>Kinyarwanda
                            </a></li>
                        </ul>
                    </div>

                    <!-- Profile Dropdown -->
                    <div class="dropdown">
                        <button class="profile-dropdown-btn" data-bs-toggle="dropdown">
                            <div class="user-avatar-header">
                                <?php echo $initials; ?>
                            </div>
                            <span class="d-none d-md-inline ms-2"><?php echo htmlspecialchars($currentUser['first_name']); ?></span>
                            <i class="fas fa-chevron-down ms-1 small"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="dropdown-header">
                                <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                                <br><small class="text-muted"><?php echo htmlspecialchars($currentUser['email']); ?></small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="profile.php">
                                    <i class="fas fa-user me-2"></i><?php echo __('nav_profile', 'My Profile'); ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item" href="results.php">
                                    <i class="fas fa-chart-bar me-2"></i><?php echo __('nav_results', 'My Results'); ?>
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item text-danger" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i><?php echo __('nav_logout', 'Logout'); ?>
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <!-- Dashboard Content -->
            <div class="dashboard-content">
                <?php displayFlashMessage(); ?>
