<?php
/**
 * EduBridge Rwanda - FAQ Page
 */

$pageTitle = 'Frequently Asked Questions';
require_once 'includes/functions.php';

if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: faq.php');
    exit;
}

require_once 'includes/header.php';
?>

<h2 class="mb-4"><i class="fas fa-question-circle me-2"></i>Frequently Asked Questions</h2>

<div class="row">
    <div class="col-lg-8">
        <div class="accordion" id="faqAccordion">
            <!-- Question 1 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#faq1">
                        What is EduBridge Rwanda?
                    </button>
                </h2>
                <div id="faq1" class="accordion-collapse collapse show" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        EduBridge Rwanda is a free web-based career discovery platform designed for Rwandan secondary school students (Senior 1-6). It helps students explore career options through an interest assessment questionnaire and provides information about careers, required skills, education pathways, and Rwandan educational institutions.
                    </div>
                </div>
            </div>

            <!-- Question 2 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq2">
                        How does the career assessment work?
                    </button>
                </h2>
                <div id="faq2" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Our career assessment consists of 30 questions based on the Holland Codes (RIASEC) model. You'll rate your agreement with statements about activities, interests, and preferences. Based on your answers, the system calculates your interest profile across six categories (Realistic, Investigative, Artistic, Social, Enterprising, and Conventional) and matches you with careers that align with your profile.
                    </div>
                </div>
            </div>

            <!-- Question 3 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq3">
                        How long does the assessment take?
                    </button>
                </h2>
                <div id="faq3" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        The assessment typically takes 10-15 minutes to complete. There's no time limit, so you can take your time to answer each question thoughtfully. We recommend answering honestly based on your true feelings and interests, not what you think is the "right" answer.
                    </div>
                </div>
            </div>

            <!-- Question 4 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq4">
                        Can I retake the assessment?
                    </button>
                </h2>
                <div id="faq4" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! You can retake the assessment as many times as you want. Your interests and preferences may change over time, so we encourage you to retake the assessment periodically, especially before making important decisions about subject selection or university applications. All your previous results are saved in your dashboard.
                    </div>
                </div>
            </div>

            <!-- Question 5 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq5">
                        Is EduBridge Rwanda free to use?
                    </button>
                </h2>
                <div id="faq5" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes, EduBridge Rwanda is completely free for all Rwandan students. There are no hidden fees, subscriptions, or premium features. Our mission is to make career guidance accessible to every student, regardless of their financial situation.
                    </div>
                </div>
            </div>

            <!-- Question 6 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq6">
                        What information do you collect about me?
                    </button>
                </h2>
                <div id="faq6" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        We collect basic information needed for your account (name, email, school, grade level) and your assessment responses. We do not share your personal data with third parties. Your data is stored securely and used only to provide career recommendations. You can delete your account at any time by contacting us.
                    </div>
                </div>
            </div>

            <!-- Question 7 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq7">
                        Can I use EduBridge on my phone?
                    </button>
                </h2>
                <div id="faq7" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! EduBridge Rwanda is fully responsive and works on smartphones, tablets, laptops, and desktop computers. We've optimized the platform for low-bandwidth connections common in Rwanda, so you can use it even with limited internet access.
                    </div>
                </div>
            </div>

            <!-- Question 8 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq8">
                        Is the platform available in Kinyarwanda?
                    </button>
                </h2>
                <div id="faq8" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Yes! EduBridge Rwanda supports both English and Kinyarwanda. You can switch languages using the language toggle in the navigation menu. All content, including assessment questions and career information, is available in both languages.
                    </div>
                </div>
            </div>

            <!-- Question 9 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq9">
                        How accurate are the career recommendations?
                    </button>
                </h2>
                <div id="faq9" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Our recommendations are based on the scientifically validated Holland Codes (RIASEC) model used worldwide. However, career choice is personal and depends on many factors including your abilities, values, and circumstances. Use our recommendations as a starting point for exploration, and consider discussing your results with parents, teachers, or career counselors.
                    </div>
                </div>
            </div>

            <!-- Question 10 -->
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#faq10">
                        How can my school use EduBridge Rwanda?
                    </button>
                </h2>
                <div id="faq10" class="accordion-collapse collapse" data-bs-parent="#faqAccordion">
                    <div class="accordion-body">
                        Schools can request a school administrator account to view aggregate statistics about student assessments (without individual data). Career guidance counselors can use the platform during guidance sessions. Contact us at info@edubridge.rw to set up your school's account.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- Contact Card -->
        <div class="card mb-4">
            <div class="card-body text-center">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h5>Still have questions?</h5>
                <p class="text-muted">Our support team is here to help you.</p>
                <a href="mailto:info@edubridge.rw" class="btn btn-primary">
                    <i class="fas fa-envelope me-2"></i>Contact Us
                </a>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Quick Links</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="register.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-user-plus me-2"></i>Create Account
                </a>
                <a href="assessment.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clipboard-list me-2"></i>Take Assessment
                </a>
                <a href="careers.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-briefcase me-2"></i>Explore Careers
                </a>
                <a href="institutions.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-university me-2"></i>View Institutions
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
