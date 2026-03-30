/**
 * EduBridge Rwanda - Main JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function(tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Language switcher
    handleLanguageSwitcher();

    // Assessment functionality
    initAssessment();

    // Form validation
    initFormValidation();

    // Auto-hide alerts
    autoHideAlerts();
});

/**
 * Handle language switching
 */
function handleLanguageSwitcher() {
    const langLinks = document.querySelectorAll('[href*="lang="]');
    langLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const lang = this.href.split('lang=')[1];
            const url = new URL(window.location.href);
            url.searchParams.set('lang', lang);
            window.location.href = url.toString();
        });
    });
}

/**
 * Initialize assessment functionality
 */
function initAssessment() {
    const assessmentForm = document.getElementById('assessmentForm');
    if (!assessmentForm) return;

    const questions = document.querySelectorAll('.question-slide');
    const progressBar = document.getElementById('assessmentProgress');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const currentQuestionSpan = document.getElementById('currentQuestion');
    const totalQuestionsSpan = document.getElementById('totalQuestions');

    let currentQuestion = 0;
    const totalQuestions = questions.length;

    if (totalQuestionsSpan) {
        totalQuestionsSpan.textContent = totalQuestions;
    }

    function showQuestion(index) {
        questions.forEach((q, i) => {
            q.style.display = i === index ? 'block' : 'none';
        });

        if (currentQuestionSpan) {
            currentQuestionSpan.textContent = index + 1;
        }

        if (progressBar) {
            const progress = ((index + 1) / totalQuestions) * 100;
            progressBar.style.width = progress + '%';
            progressBar.setAttribute('aria-valuenow', progress);
        }

        // Toggle buttons
        if (prevBtn) prevBtn.style.display = index === 0 ? 'none' : 'inline-block';
        if (nextBtn) nextBtn.style.display = index === totalQuestions - 1 ? 'none' : 'inline-block';
        if (submitBtn) submitBtn.style.display = index === totalQuestions - 1 ? 'inline-block' : 'none';
    }

    function validateCurrentQuestion() {
        const currentQuestionEl = questions[currentQuestion];
        const inputs = currentQuestionEl.querySelectorAll('input[type="radio"]');
        let answered = false;

        inputs.forEach(input => {
            if (input.checked) answered = true;
        });

        return answered;
    }

    if (nextBtn) {
        nextBtn.addEventListener('click', function() {
            if (!validateCurrentQuestion()) {
                alert('Please answer the question before continuing.');
                return;
            }

            if (currentQuestion < totalQuestions - 1) {
                currentQuestion++;
                showQuestion(currentQuestion);
            }
        });
    }

    if (prevBtn) {
        prevBtn.addEventListener('click', function() {
            if (currentQuestion > 0) {
                currentQuestion--;
                showQuestion(currentQuestion);
            }
        });
    }

    if (assessmentForm) {
        assessmentForm.addEventListener('submit', function(e) {
            if (!validateCurrentQuestion()) {
                e.preventDefault();
                alert('Please answer all questions before submitting.');
                return;
            }

            // Validate all questions
            let allAnswered = true;
            questions.forEach((q, i) => {
                const inputs = q.querySelectorAll('input[type="radio"]');
                let answered = false;
                inputs.forEach(input => {
                    if (input.checked) answered = true;
                });
                if (!answered) {
                    allAnswered = false;
                }
            });

            if (!allAnswered) {
                e.preventDefault();
                alert('Please answer all questions before submitting.');
                return;
            }

            // Show loading
            const spinner = document.getElementById('loadingSpinner');
            if (spinner) spinner.style.display = 'flex';
        });
    }

    // Initialize first question
    if (questions.length > 0) {
        showQuestion(0);
    }
}

/**
 * Initialize form validation
 */
function initFormValidation() {
    const forms = document.querySelectorAll('.needs-validation');

    forms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        });
    });

    // Password confirmation
    const password = document.getElementById('password');
    const confirmPassword = document.getElementById('confirm_password');

    if (password && confirmPassword) {
        confirmPassword.addEventListener('input', function() {
            if (password.value !== confirmPassword.value) {
                confirmPassword.setCustomValidity('Passwords do not match');
            } else {
                confirmPassword.setCustomValidity('');
            }
        });
    }
}

/**
 * Auto-hide alerts after 5 seconds
 */
function autoHideAlerts() {
    const alerts = document.querySelectorAll('.alert:not(.alert-permanent)');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
}

/**
 * Confirm delete action
 */
function confirmDelete(message) {
    return confirm(message || 'Are you sure you want to delete this item?');
}

/**
 * Show loading spinner
 */
function showLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = 'flex';
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    const spinner = document.getElementById('loadingSpinner');
    if (spinner) spinner.style.display = 'none';
}

/**
 * Career search filter
 */
function filterCareers() {
    const searchInput = document.getElementById('careerSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const cards = document.querySelectorAll('.career-card');

    if (!searchInput || !cards.length) return;

    const searchTerm = searchInput.value.toLowerCase();
    const category = categoryFilter ? categoryFilter.value : '';

    cards.forEach(card => {
        const title = card.querySelector('.card-title').textContent.toLowerCase();
        const cardCategory = card.dataset.category || '';

        const matchesSearch = title.includes(searchTerm);
        const matchesCategory = !category || cardCategory === category;

        card.closest('.col').style.display = matchesSearch && matchesCategory ? 'block' : 'none';
    });
}

// Export functions for global use
window.confirmDelete = confirmDelete;
window.showLoading = showLoading;
window.hideLoading = hideLoading;
window.filterCareers = filterCareers;
