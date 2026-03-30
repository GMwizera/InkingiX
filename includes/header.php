<?php

/**
 * InkingiX Rwanda - Public Header
 * Navigation for public/non-logged-in users
 */
require_once __DIR__ . '/functions.php';
$currentUser = getCurrentUser();
$currentLang = getCurrentLanguage();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo __('meta_description', 'InkingiX Rwanda - Career Discovery Platform for Rwandan Students'); ?>">
    <title><?php echo isset($pageTitle) ? $pageTitle . ' - ' : ''; ?><?php echo SITE_NAME; ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="assets/images/favicon.svg">

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
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i>InkingiX Rwanda
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'index' ? 'active' : ''; ?>" href="index.php">
                            <i class="fas fa-home me-1"></i><?php echo __('nav_home', 'Home'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'careers' || $currentPage === 'career' ? 'active' : ''; ?>" href="careers.php">
                            <i class="fas fa-briefcase me-1"></i><?php echo __('nav_careers', 'Careers'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'institutions' ? 'active' : ''; ?>" href="institutions.php">
                            <i class="fas fa-university me-1"></i><?php echo __('nav_institutions', 'Institutions'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'about' ? 'active' : ''; ?>" href="about.php">
                            <i class="fas fa-info-circle me-1"></i><?php echo __('nav_about', 'About'); ?>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $currentPage === 'faq' ? 'active' : ''; ?>" href="faq.php">
                            <i class="fas fa-question-circle me-1"></i><?php echo __('nav_faq', 'FAQ'); ?>
                        </a>
                    </li>
                </ul>

                <ul class="navbar-nav align-items-center">
                    <!-- Language Switcher -->
                    <li class="nav-item dropdown me-2">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-globe me-1"></i>
                            <?php echo $currentLang === 'en' ? 'EN' : 'RW'; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item <?php echo $currentLang === 'en' ? 'active' : ''; ?>" href="?lang=en">English</a></li>
                            <li><a class="dropdown-item <?php echo $currentLang === 'rw' ? 'active' : ''; ?>" href="?lang=rw">Kinyarwanda</a></li>
                        </ul>
                    </li>

                    <?php if (isLoggedIn()): ?>
                        <!-- Logged In - Profile Dropdown -->
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" data-bs-toggle="dropdown">
                                <div class="user-avatar-nav me-2">
                                    <?php echo strtoupper(substr($currentUser['first_name'], 0, 1) . substr($currentUser['last_name'], 0, 1)); ?>
                                </div>
                                <?php echo htmlspecialchars($currentUser['first_name']); ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li class="dropdown-header">
                                    <strong><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></strong>
                                    <br><small class="text-muted"><?php echo htmlspecialchars($currentUser['email']); ?></small>
                                </li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item" href="dashboard.php">
                                        <i class="fas fa-road me-2"></i><?php echo __('nav_my_journey', 'My Journey'); ?>
                                    </a>
                                </li>
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
                                <li>
                                    <a class="dropdown-item" href="bookmarks.php">
                                        <i class="fas fa-bookmark me-2"></i><?php echo __('nav_bookmarks', 'Saved Careers'); ?>
                                    </a>
                                </li>
                                <?php if (hasRole(['school_admin', 'system_admin'])): ?>
                                    <li>
                                        <hr class="dropdown-divider">
                                    </li>
                                    <li>
                                        <a class="dropdown-item" href="admin/index.php">
                                            <i class="fas fa-cog me-2"></i><?php echo __('nav_admin', 'Admin Panel'); ?>
                                        </a>
                                    </li>
                                <?php endif; ?>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <a class="dropdown-item text-danger" href="logout.php">
                                        <i class="fas fa-sign-out-alt me-2"></i><?php echo __('nav_logout', 'Logout'); ?>
                                    </a>
                                </li>
                            </ul>
                        </li>
                    <?php else: ?>
                        <!-- Not Logged In - Login/Register -->
                        <li class="nav-item">
                            <a class="nav-link" href="login.php">
                                <i class="fas fa-sign-in-alt me-1"></i><?php echo __('nav_login', 'Login'); ?>
                            </a>
                        </li>
                        <li class="nav-item ms-2">
                            <a class="btn btn-primary" href="register.php">
                                <i class="fas fa-user-plus me-1"></i><?php echo __('nav_register', 'Register'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main>
        <div class="container py-4">
            <?php displayFlashMessage(); ?>