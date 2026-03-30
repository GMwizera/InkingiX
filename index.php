<?php

/**
 * InkingiX Rwanda - Landing Page
 * eCoach-style design
 */

$pageTitle = 'Career Discovery Platform for Rwandan Students';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: index.php');
    exit;
}

$currentLang = getCurrentLanguage();
$currentUser = getCurrentUser();

// Get career categories and careers with error handling
$categories = [];
$careers = [];
try {
    $db = getDBConnection();
    // Get categories from career_categories table
    $categories = $db->query("SELECT * FROM career_categories ORDER BY name_en")->fetchAll();
    // Get sample careers with category name
    $careers = $db->query("
        SELECT c.*, cc.name_en as category_name, cc.name_rw as category_name_rw
        FROM careers c
        LEFT JOIN career_categories cc ON c.primary_category_id = cc.id
        WHERE c.is_active = 1
        ORDER BY RAND()
        LIMIT 4
    ")->fetchAll();
} catch (Exception $e) {
    // Silently fail - will show placeholder content
}
?>
<!DOCTYPE html>
<html lang="<?php echo $currentLang; ?>">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo __('meta_description', 'InkingiX Rwanda - Career Discovery Platform for Rwandan Students. Discover your ideal career path through personalized assessments.'); ?>">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>

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

<body class="landing-page">
    <!-- Top Accent Bar -->
    <div class="top-accent-bar"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg landing-navbar">
        <div class="container">
            <a class="navbar-brand fw-bold" href="index.php">
                <i class="fas fa-graduation-cap me-2"></i> InkingiX Rwanda 
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="careers.php"><?php echo __('nav_careers', 'Careers'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="institutions.php"><?php echo __('nav_institutions', 'Institutions'); ?></a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="faq.php">FAQ</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-globe me-1"></i>
                            <?php echo $currentLang === 'en' ? 'EN' : 'RW'; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $currentLang === 'en' ? 'active' : ''; ?>" href="?lang=en">English</a></li>
                            <li><a class="dropdown-item <?php echo $currentLang === 'rw' ? 'active' : ''; ?>" href="?lang=rw">Kinyarwanda</a></li>
                        </ul>
                    </li>
                </ul>

                <ul class="navbar-nav">
                    <?php if (isLoggedIn()): ?>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="dashboard.php">
                                <?php echo __('nav_dashboard', 'Dashboard'); ?>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="btn btn-primary" href="login.php">
                                <?php echo __('nav_login', 'Login'); ?> / <?php echo __('nav_register', 'Register'); ?>
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-ecoach">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6">
                    <div class="hero-content">
                        <h1 class="hero-title">
                            <?php echo __('hero_ecoach_title', 'Education that prepares you for what\'s next.'); ?>
                        </h1>
                        <p class="hero-subtitle">
                            <?php echo __('hero_ecoach_subtitle', 'Start, switch, or advance your career with personalized assessments and guidance from Rwanda\'s leading career discovery platform.'); ?>
                        </p>
                        <?php if (isLoggedIn()): ?>
                            <a href="assessment.php" class="btn btn-primary">
                                <?php echo __('hero_cta', 'Take Assessment'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">
                                <?php echo __('hero_cta_explore', 'Explore Careers'); ?>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-area">
                        <!-- Decorative Elements -->
                        <div class="hero-decoration mint"></div>
                        <div class="hero-decoration yellow">
                            <i class="fas fa-star"></i>
                        </div>

                        <!-- Hero Image -->
                        <div class="hero-person-image">
                            <img src="assets/images/homepage_image.png"
                                alt="African student discovering career path"
                                class="hero-img">
                        </div>

                        <!-- Success Rate Card -->
                        <div class="success-card">
                            <div class="success-card-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="success-card-content">
                                <strong>93%</strong>
                                <span><?php echo __('hero_success_rate', 'Success Rates'); ?></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Feature Cards Section -->
    <section class="feature-cards-section">
        <div class="container">
            <div class="feature-cards-row">
                <div class="feature-card-bordered">
                    <h4><?php echo __('feature_card_1_title', 'Personalized Guidance'); ?></h4>
                    <p><?php echo __('feature_card_1_desc', 'Our career assessment helps you discover paths that match your unique interests and personality - no matter where you are in your journey.'); ?></p>
                    <a href="assessment.php" class="learn-more">
                        <?php echo __('learn_more', 'More about this'); ?>
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
                <div class="feature-card-bordered">
                    <h4><?php echo __('feature_card_2_title', 'Instant Results'); ?></h4>
                    <p><?php echo __('feature_card_2_desc', 'Get your career matches quickly after completing our 30-question assessment. See detailed insights about each recommended career path.'); ?></p>
                    <a href="careers.php" class="learn-more">
                        <?php echo __('learn_more', 'More about this'); ?>
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
                <div class="feature-card-bordered">
                    <h4><?php echo __('feature_card_3_title', 'Rwanda Focused'); ?></h4>
                    <p><?php echo __('feature_card_3_desc', 'All careers and institutions are tailored for Rwandan students, with local salary data and educational pathways.'); ?></p>
                    <a href="institutions.php" class="learn-more">
                        <?php echo __('learn_more', 'More about this'); ?>
                        <i class="fas fa-arrow-up-right-from-square"></i>
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Popular Careers Section -->
    <section class="category-section">
        <div class="container">
            <div class="category-header">
                <div class="category-badge">
                    <i class="fas fa-bolt"></i>
                    <?php echo __('popular_careers', 'Popular Careers'); ?>
                </div>
                <h2 class="category-title"><?php echo __('find_perfect_career', 'Find your perfect career.'); ?></h2>
                <div class="category-title-underline"></div>
            </div>

            <!-- Category Tabs -->
            <div class="category-tabs">
                <a href="careers.php" class="category-tab active"><?php echo __('all_careers', 'All Careers'); ?></a>
                <?php if (!empty($categories)): ?>
                    <?php foreach (array_slice($categories, 0, 5) as $cat): ?>
                        <a href="careers.php?category=<?php echo $cat['id']; ?>" class="category-tab">
                            <?php echo htmlspecialchars($cat['name_en']); ?>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <a href="careers.php" class="category-tab">Technology</a>
                    <a href="careers.php" class="category-tab">Healthcare</a>
                    <a href="careers.php" class="category-tab">Business</a>
                    <a href="careers.php" class="category-tab">Education</a>
                <?php endif; ?>
                <a href="careers.php" class="category-tab"><?php echo __('view_all', 'View All'); ?></a>
            </div>

            <!-- Career Cards Grid -->
            <div class="career-cards-grid">
                <?php if (!empty($careers)): ?>
                    <?php foreach ($careers as $index => $career): ?>
                        <a href="career.php?id=<?php echo $career['id']; ?>" class="career-card-new">
                            <div class="career-card-image">
                                <i class="fas fa-<?php
                                                    $icons = ['briefcase', 'laptop-code', 'stethoscope', 'building', 'chart-line', 'graduation-cap'];
                                                    echo $icons[$index % count($icons)];
                                                    ?>"></i>
                                <?php if ($index === 2): ?>
                                    <span class="career-card-badge"><?php echo __('popular', 'Popular'); ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="career-card-body">
                                <h5><?php echo getLocalizedField($career, 'title'); ?></h5>
                                <p class="career-category"><?php echo htmlspecialchars($career['category_name'] ?? 'General'); ?></p>
                                <div class="career-card-meta">
                                    <span><i class="fas fa-money-bill-wave me-1"></i><?php
                                                                                        $min = isset($career['salary_range_min']) ? number_format($career['salary_range_min'] / 1000) . 'K' : '200K';
                                                                                        $max = isset($career['salary_range_max']) ? number_format($career['salary_range_max'] / 1000) . 'K' : '1M';
                                                                                        echo $min . ' - ' . $max . ' RWF';
                                                                                        ?></span>
                                </div>
                            </div>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Placeholder cards if no careers in database -->
                    <a href="careers.php" class="career-card-new">
                        <div class="career-card-image">
                            <i class="fas fa-laptop-code"></i>
                        </div>
                        <div class="career-card-body">
                            <h5>Software Developer</h5>
                            <p class="career-category">Technology</p>
                            <div class="career-card-meta">
                                <span><i class="fas fa-money-bill-wave me-1"></i>500K - 2M RWF</span>
                            </div>
                        </div>
                    </a>
                    <a href="careers.php" class="career-card-new">
                        <div class="career-card-image">
                            <i class="fas fa-stethoscope"></i>
                        </div>
                        <div class="career-card-body">
                            <h5>Medical Doctor</h5>
                            <p class="career-category">Healthcare</p>
                            <div class="career-card-meta">
                                <span><i class="fas fa-money-bill-wave me-1"></i>800K - 3M RWF</span>
                            </div>
                        </div>
                    </a>
                    <a href="careers.php" class="career-card-new">
                        <div class="career-card-image">
                            <i class="fas fa-chart-line"></i>
                            <span class="career-card-badge"><?php echo __('popular', 'Popular'); ?></span>
                        </div>
                        <div class="career-card-body">
                            <h5>Business Analyst</h5>
                            <p class="career-category">Business</p>
                            <div class="career-card-meta">
                                <span><i class="fas fa-money-bill-wave me-1"></i>400K - 1.5M RWF</span>
                            </div>
                        </div>
                    </a>
                    <a href="careers.php" class="career-card-new">
                        <div class="career-card-image">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="career-card-body">
                            <h5>Teacher</h5>
                            <p class="career-category">Education</p>
                            <div class="career-card-meta">
                                <span><i class="fas fa-money-bill-wave me-1"></i>200K - 600K RWF</span>
                            </div>
                        </div>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Stats Section -->
    <section class="stats-ecoach">
        <div class="container">
            <div class="stats-row">
                <div class="stat-item">
                    <div class="stat-number">1,000+</div>
                    <div class="stat-label"><?php echo __('stats_students', 'Students Guided'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">15+</div>
                    <div class="stat-label"><?php echo __('stats_careers', 'Career Paths'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">10+</div>
                    <div class="stat-label"><?php echo __('stats_institutions', 'Partner Institutions'); ?></div>
                </div>
                <div class="stat-item">
                    <div class="stat-number">500+</div>
                    <div class="stat-label"><?php echo __('stats_assessments', 'Assessments Completed'); ?></div>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works Section -->
    <section class="how-section-ecoach">
        <div class="container">
            <div class="category-header">
                <div class="category-badge">
                    <i class="fas fa-bolt"></i>
                    <?php echo __('how_badge', 'Simple Process'); ?>
                </div>
                <h2 class="category-title"><?php echo __('how_title', 'How It Works'); ?></h2>
                <div class="category-title-underline"></div>
            </div>

            <div class="steps-ecoach">
                <div class="step-ecoach">
                    <div class="step-ecoach-number">1</div>
                    <h4><?php echo __('step_1_title', 'Create Account'); ?></h4>
                    <p><?php echo __('step_1_desc', 'Sign up for free with your email and basic information about your education.'); ?></p>
                </div>
                <div class="step-ecoach">
                    <div class="step-ecoach-number">2</div>
                    <h4><?php echo __('step_2_title', 'Take Assessment'); ?></h4>
                    <p><?php echo __('step_2_desc', 'Answer 30 questions about your interests, preferences, and personality traits.'); ?></p>
                </div>
                <div class="step-ecoach">
                    <div class="step-ecoach-number">3</div>
                    <h4><?php echo __('step_3_title', 'Explore Results'); ?></h4>
                    <p><?php echo __('step_3_desc', 'Discover matching careers, explore institutions, and plan your educational journey.'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta-ecoach">
        <div class="container">
            <div class="cta-content">
                <h2 class="cta-title"><?php echo __('cta_title', 'Ready to Discover Your Future?'); ?></h2>
                <p class="cta-subtitle"><?php echo __('cta_subtitle', 'Join thousands of Rwandan students who have found their career path. Start your free assessment today.'); ?></p>
                <?php if (isLoggedIn()): ?>
                    <a href="assessment.php" class="btn-light">
                        <?php echo __('cta_btn_assessment', 'Take Assessment Now'); ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn-light">
                        <?php echo __('cta_btn_start', 'Get Started Free'); ?>
                        <i class="fas fa-arrow-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer-ecoach">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <div class="footer-logo">
                        <i class="fas fa-graduation-cap"></i>
                        <?php echo SITE_NAME; ?>
                    </div>
                    <p><?php echo __('footer_description', 'Empowering Rwandan students to discover their career paths through technology and personalized guidance.'); ?></p>
                </div>

                <div class="footer-links">
                    <h5><?php echo __('footer_platform', 'Platform'); ?></h5>
                    <ul>
                        <li><a href="careers.php"><?php echo __('nav_careers', 'Careers'); ?></a></li>
                        <li><a href="institutions.php"><?php echo __('nav_institutions', 'Institutions'); ?></a></li>
                        <li><a href="faq.php">FAQ</a></li>
                    </ul>
                </div>

                <div class="footer-links">
                    <h5><?php echo __('footer_account', 'Account'); ?></h5>
                    <ul>
                        <?php if (isLoggedIn()): ?>
                            <li><a href="dashboard.php"><?php echo __('nav_dashboard', 'Dashboard'); ?></a></li>
                            <li><a href="assessment.php"><?php echo __('nav_assessment', 'Assessment'); ?></a></li>
                            <li><a href="profile.php"><?php echo __('nav_profile', 'Profile'); ?></a></li>
                        <?php else: ?>
                            <li><a href="login.php"><?php echo __('nav_login', 'Login'); ?></a></li>
                            <li><a href="register.php"><?php echo __('nav_register', 'Register'); ?></a></li>
                        <?php endif; ?>
                    </ul>
                </div>

                <div class="footer-links">
                    <h5><?php echo __('footer_contact', 'Contact'); ?></h5>
                    <ul>
                        <li><a href="mailto:info@InkingiX.rw"><i class="fas fa-envelope me-2"></i>info@InkingiX.rw</a></li>
                        <li><a href="tel:+250788000000"><i class="fas fa-phone me-2"></i>+250 788 000 000</a></li>
                        <li><a href="#"><i class="fas fa-map-marker-alt me-2"></i>Kigali, Rwanda</a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <div class="footer-copyright">
                    &copy; <?php echo date('Y'); ?> <?php echo SITE_NAME; ?>. <?php echo __('footer_rights', 'All rights reserved.'); ?>
                </div>
                <div class="footer-social">
                    <a href="#" title="Facebook"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" title="Twitter"><i class="fab fa-twitter"></i></a>
                    <a href="#" title="Instagram"><i class="fab fa-instagram"></i></a>
                    <a href="#" title="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>