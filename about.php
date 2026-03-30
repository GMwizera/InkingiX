<?php

/**
 * InkingiX Rwanda - About Page
 */

$pageTitle = 'About Us';
$pageSubtitle = 'Learn about our mission to empower Rwandan students';
require_once 'includes/functions.php';

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: about.php');
    exit;
}

// Use sidebar for logged-in users, top navbar for guests
if (isLoggedIn()) {
    require_once 'includes/header-dashboard.php';
} else {
    require_once 'includes/header.php';
}
?>

<div class="row">
    <div class="col-lg-8">
        <!-- Mission -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-bullseye me-2 text-primary"></i>Our Mission</h4>
                <p>To empower Rwandan secondary school students through technology by improving access to career discovery and guidance. We align with ALU's goal of developing leaders who solve Africa's challenges and Rwanda's Vision 2050 emphasis on building a knowledge-based economy.</p>
            </div>
        </div>

        <!-- Problem We Solve -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-lightbulb me-2 text-warning"></i>The Problem We Solve</h4>
                <p>Many Rwandan students lack access to career counseling - the Rwanda Education Board (2023) reports only <strong>one counselor per 1,500 students</strong>. Students make uninformed subject and career choices due to:</p>
                <ul>
                    <li>Lack of self-assessment tools</li>
                    <li>Limited career information localized for Rwanda</li>
                    <li>No digital tools designed for the Rwandan education system</li>
                </ul>
                <p class="mb-0">According to NISR (2022), <strong>38% of Rwandan graduates work in fields unrelated to their studies</strong>, wasting resources and contributing to youth underemployment.</p>
            </div>
        </div>

        <!-- Our Solution -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-check-circle me-2 text-success"></i>Our Solution</h4>
                <p>InkingiX Rwanda provides a simple, accessible digital tool for career exploration targeted at Senior 1-6 students in Rwandan schools:</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-clipboard-list fa-2x text-primary me-3"></i>
                            <div>
                                <h6>Career Interest Assessment</h6>
                                <small class="text-muted">30-question questionnaire based on RIASEC model</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-briefcase fa-2x text-primary me-3"></i>
                            <div>
                                <h6>Career Profiles</h6>
                                <small class="text-muted">Detailed information about skills, education, and salaries</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-university fa-2x text-primary me-3"></i>
                            <div>
                                <h6>Education Pathways</h6>
                                <small class="text-muted">Rwandan universities and TVET institutions</small>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="d-flex align-items-start">
                            <i class="fas fa-globe fa-2x text-primary me-3"></i>
                            <div>
                                <h6>Bilingual Support</h6>
                                <small class="text-muted">Available in English and Kinyarwanda</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- What Makes Us Unique -->
        <div class="card mb-4">
            <div class="card-body">
                <h4><i class="fas fa-star me-2 text-warning"></i>What Makes Us Unique</h4>
                <ul class="mb-0">
                    <li><strong>Localized for Rwanda:</strong> Kinyarwanda language option, local career data, and Rwandan education pathways</li>
                    <li><strong>Optimized for Low Bandwidth:</strong> Simple enough for areas with limited internet access</li>
                    <li><strong>Free for Students:</strong> No cost to students, ever</li>
                    <li><strong>Education System Aligned:</strong> Designed specifically for the Rwandan education system and job market</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Contact Info -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="fas fa-envelope fa-3x text-primary mb-3"></i>
                <h5>Contact Us</h5>
                <p class="text-muted">Have questions or feedback?</p>
                <ul class="list-unstyled">
                    <li class="mb-2"><i class="fas fa-envelope me-2"></i>info@InkingiX.rw</li>
                    <li class="mb-2"><i class="fas fa-phone me-2"></i>+250 788 000 000</li>
                    <li><i class="fas fa-map-marker-alt me-2"></i>Kigali, Rwanda</li>
                </ul>
            </div>
        </div>

        <!-- Developer Info -->
        <div class="card mb-4">
            <div class="card-body">
                <h6><i class="fas fa-code me-2"></i>Developed By</h6>
                <p class="mb-2"><strong>Gisele Mwizera Amen</strong></p>
                <p class="small text-muted mb-0">
                    African Leadership University (ALU)<br>
                    Software Engineering Student<br>
                    Class of 2026
                </p>
            </div>
        </div>

        <!-- References -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-book me-2"></i>References</h6>
            </div>
            <div class="card-body small text-muted">
                <p class="mb-2">National Institute of Statistics of Rwanda. (2022). Labour Force Survey Annual Report.</p>
                <p class="mb-2">Rwanda Education Board. (2023). Annual Report on School Counseling Services.</p>
                <p class="mb-0">Ministry of Education Rwanda. (2021). Education Sector Strategic Plan 2018-2024.</p>
            </div>
        </div>
    </div>
</div>

<!-- CTA -->
<div class="text-center py-5 mt-4 bg-light rounded">
    <h3>Ready to Discover Your Career Path?</h3>
    <p class="text-muted mb-4">Join thousands of Rwandan students finding their future.</p>
    <?php if (!isLoggedIn()): ?>
        <a href="register.php" class="btn btn-primary btn-lg">
            <i class="fas fa-user-plus me-2"></i>Get Started Free
        </a>
    <?php else: ?>
        <a href="assessment.php" class="btn btn-primary btn-lg">
            <i class="fas fa-clipboard-list me-2"></i>Take Assessment
        </a>
    <?php endif; ?>
</div>

<?php
// Use matching footer for the header
if (isLoggedIn()) {
    require_once 'includes/footer-dashboard.php';
} else {
    require_once 'includes/footer.php';
}
?>