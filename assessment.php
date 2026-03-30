<?php

/**
 * InkingiX Rwanda - Career Assessment
 * With AJAX auto-save, resume functionality, and adaptive question weighting
 */

$pageTitle = 'Career Assessment';
$pageSubtitle = 'Discover careers that match your interests and abilities';
require_once 'includes/functions.php';
require_once 'includes/matching_engine.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: assessment.php');
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get assessment questions
$stmt = $db->query("SELECT * FROM assessment_questions WHERE is_active = 1 ORDER BY order_number ASC");
$questions = $stmt->fetchAll();

// Check for existing in-progress assessment
$existingAssessment = null;
$savedResponses = [];
$stmt = $db->prepare("
    SELECT id, started_at
    FROM user_assessments
    WHERE user_id = ? AND is_completed = 0
    ORDER BY started_at DESC
    LIMIT 1
");
$stmt->execute([$currentUser['id']]);
$existingAssessment = $stmt->fetch();

if ($existingAssessment) {
    // Get saved responses for this assessment
    $stmt = $db->prepare("
        SELECT question_id, response_value
        FROM assessment_responses
        WHERE assessment_id = ?
    ");
    $stmt->execute([$existingAssessment['id']]);
    $responses = $stmt->fetchAll();
    foreach ($responses as $response) {
        $savedResponses[$response['question_id']] = $response['response_value'];
    }
}

// Handle final form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_assessment'])) {
    $assessmentId = isset($_POST['assessment_id']) ? intval($_POST['assessment_id']) : 0;

    // Verify assessment belongs to user and is not completed
    $stmt = $db->prepare("SELECT id FROM user_assessments WHERE id = ? AND user_id = ? AND is_completed = 0");
    $stmt->execute([$assessmentId, $currentUser['id']]);
    if (!$stmt->fetch()) {
        setFlashMessage('error', 'Invalid assessment');
        header('Location: assessment.php');
        exit;
    }

    // Use matching engine to calculate and save results
    $results = completeAssessment($db, $assessmentId);

    if ($results === false) {
        setFlashMessage('error', 'Failed to calculate results. Please try again.');
        header('Location: assessment.php');
        exit;
    }

    // Redirect to results
    header('Location: results.php?id=' . $assessmentId);
    exit;
}

// Always use sidebar for logged-in pages
require_once 'includes/header-dashboard.php';
?>

<style>
    /* Save indicator styles */
    .save-indicator {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 0.85rem;
        padding: 4px 10px;
        border-radius: 20px;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .save-indicator.show {
        opacity: 1;
    }

    .save-indicator.saving {
        background: #fef3c7;
        color: #92400e;
    }

    .save-indicator.saved {
        background: #d1fae5;
        color: #065f46;
    }

    .save-indicator.error {
        background: #fee2e2;
        color: #991b1b;
    }

    /* Resume banner */
    .resume-banner {
        background: #dbeafe;
        border: 1px solid #93c5fd;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
    }

    .resume-banner .progress {
        height: 8px;
        margin-top: 0.5rem;
    }

    /* Adaptive mode banner */
    .adaptive-banner {
        background: #fef3c7;
        border: 1px solid #f59e0b;
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1rem;
        animation: fadeInSlide 0.5s ease-out;
    }

    .adaptive-banner i {
        font-size: 1.5rem;
        color: #b45309;
    }

    .adaptive-banner strong {
        color: #92400e;
    }

    .adaptive-banner small {
        color: #78350f;
    }

    @keyframes fadeInSlide {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
</style>

<div class="row justify-content-center">
    <div class="col-lg-8">
        <!-- Resume Banner (shown if there's an in-progress assessment) -->
        <?php if ($existingAssessment && count($savedResponses) > 0): ?>
            <div class="resume-banner" id="resumeBanner">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h6 class="mb-1"><i class="fas fa-redo me-2"></i><?php echo __('assessment_resume_title', 'Continue Your Assessment'); ?></h6>
                        <small class="text-muted">
                            <?php echo sprintf(__('assessment_resume_progress', 'You\'ve answered %d of %d questions'), count($savedResponses), count($questions)); ?>
                        </small>
                        <div class="progress">
                            <div class="progress-bar bg-primary" style="width: <?php echo round((count($savedResponses) / count($questions)) * 100); ?>%"></div>
                        </div>
                    </div>
                    <div>
                        <button type="button" class="btn btn-sm btn-outline-danger" onclick="startFreshAssessment()">
                            <?php echo __('assessment_start_fresh', 'Start Fresh'); ?>
                        </button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- Assessment Introduction -->
        <div class="card mb-4" id="assessmentIntro">
            <div class="card-body text-center p-5">
                <i class="fas fa-clipboard-list fa-4x text-primary mb-4"></i>
                <h2 class="mb-3"><?php echo __('assessment_title'); ?></h2>
                <p class="lead text-muted mb-4"><?php echo __('assessment_intro'); ?></p>

                <div class="alert alert-info text-start">
                    <h6><i class="fas fa-info-circle me-2"></i><?php echo __('instructions', 'Instructions'); ?>:</h6>
                    <ul class="mb-0">
                        <li><?php echo __('assessment_instructions'); ?></li>
                        <li><?php echo __('assessment_time'); ?></li>
                        <li><?php echo sprintf(__('assessment_question_count', 'There are %d questions in total.'), count($questions)); ?></li>
                        <li><strong><?php echo __('assessment_autosave', 'Your answers are saved automatically - you can close and return anytime.'); ?></strong></li>
                    </ul>
                </div>

                <button type="button" class="btn btn-primary btn-lg mt-3" onclick="startAssessment()">
                    <i class="fas fa-play me-2"></i>
                    <?php echo ($existingAssessment && count($savedResponses) > 0)
                        ? __('assessment_continue', 'Continue Assessment')
                        : __('assessment_start'); ?>
                </button>
            </div>
        </div>

        <!-- Assessment Form -->
        <div id="assessmentContainer" style="display: none;">
            <!-- Adaptive Mode Banner (shows after Q10) -->
            <div id="adaptiveBanner" class="adaptive-banner" style="display: none;">
                <div class="d-flex align-items-center">
                    <i class="fas fa-magic me-2"></i>
                    <div>
                        <strong><?php echo __('adaptive_mode', 'Adaptive Mode Active'); ?></strong>
                        <small class="d-block">
                            <?php echo __('adaptive_mode_desc', 'We\'re focusing on your top interests:'); ?>
                            <span id="topCategoriesText"></span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span>
                            <?php echo __('assessment_question'); ?>
                            <span id="currentQuestion">1</span>
                            <?php echo __('assessment_of'); ?>
                            <span id="totalQuestions"><?php echo count($questions); ?></span>
                        </span>
                        <span class="save-indicator" id="saveIndicator">
                            <i class="fas fa-spinner fa-spin"></i>
                            <span class="save-text"><?php echo __('saving', 'Saving...'); ?></span>
                        </span>
                    </div>
                    <div class="progress assessment-progress">
                        <div class="progress-bar" id="assessmentProgress" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" id="assessmentForm">
                        <?php echo csrfField(); ?>
                        <input type="hidden" name="assessment_id" id="assessmentIdField" value="">
                        <input type="hidden" name="submit_assessment" value="1">

                        <?php foreach ($questions as $index => $question): ?>
                            <div class="question-slide" data-question="<?php echo $index; ?>" data-question-id="<?php echo htmlspecialchars($question['id']); ?>" style="display: none;">
                                <h5 class="mb-4">
                                    <?php echo htmlspecialchars(getLocalizedField($question, 'question')); ?>
                                </h5>

                                <div class="likert-scale">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                        <div class="likert-option">
                                            <input type="radio"
                                                name="q_<?php echo htmlspecialchars($question['id']); ?>"
                                                id="q<?php echo htmlspecialchars($question['id']); ?>_<?php echo $i; ?>"
                                                value="<?php echo $i; ?>"
                                                data-question-id="<?php echo htmlspecialchars($question['id']); ?>"
                                                <?php echo (isset($savedResponses[$question['id']]) && $savedResponses[$question['id']] == $i) ? 'checked' : ''; ?>>
                                            <label for="q<?php echo htmlspecialchars($question['id']); ?>_<?php echo $i; ?>">
                                                <i class="fas fa-<?php
                                                                    echo $i == 1 ? 'times-circle' : ($i == 2 ? 'minus-circle' : ($i == 3 ? 'meh' : ($i == 4 ? 'plus-circle' : 'check-circle')));
                                                                    ?> d-block mb-1"></i>
                                                <?php echo htmlspecialchars(__('assessment_scale_' . $i)); ?>
                                            </label>
                                        </div>
                                    <?php endfor; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                            <button type="button" class="btn btn-outline-secondary" id="prevBtn" style="display: none;">
                                <i class="fas fa-arrow-left me-2"></i><?php echo __('assessment_previous'); ?>
                            </button>
                            <button type="button" class="btn btn-primary" id="nextBtn">
                                <?php echo __('assessment_next'); ?><i class="fas fa-arrow-right ms-2"></i>
                            </button>
                            <button type="submit" class="btn btn-success" id="submitBtn" style="display: none;">
                                <i class="fas fa-check me-2"></i><?php echo __('assessment_submit'); ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Assessment state
    let currentQuestionIndex = 0;
    let assessmentId = <?php echo $existingAssessment ? $existingAssessment['id'] : 'null'; ?>;
    const totalQuestions = <?php echo count($questions); ?>;
    const savedResponses = <?php echo json_encode($savedResponses); ?>;
    let adaptiveModeShown = false;
    const ADAPTIVE_THRESHOLD = 10; // Show adaptive mode after this many questions

    // Category names for display
    const categoryNames = {
        1: {
            en: 'Realistic',
            rw: 'Ibikorwa by\'intoki',
            code: 'R'
        },
        2: {
            en: 'Investigative',
            rw: 'Ubushakashatsi',
            code: 'I'
        },
        3: {
            en: 'Artistic',
            rw: 'Ubuhanzi',
            code: 'A'
        },
        4: {
            en: 'Social',
            rw: 'Imibereho',
            code: 'S'
        },
        5: {
            en: 'Enterprising',
            rw: 'Ubucuruzi',
            code: 'E'
        },
        6: {
            en: 'Conventional',
            rw: 'Amategeko',
            code: 'C'
        }
    };
    const currentLang = '<?php echo getCurrentLanguage(); ?>';

    // DOM elements
    const introEl = document.getElementById('assessmentIntro');
    const containerEl = document.getElementById('assessmentContainer');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const progressBar = document.getElementById('assessmentProgress');
    const currentQuestionEl = document.getElementById('currentQuestion');
    const saveIndicator = document.getElementById('saveIndicator');
    const assessmentIdField = document.getElementById('assessmentIdField');
    const adaptiveBanner = document.getElementById('adaptiveBanner');
    const topCategoriesText = document.getElementById('topCategoriesText');

    // Start or resume assessment
    async function startAssessment() {
        // If no existing assessment, create one via AJAX
        if (!assessmentId) {
            try {
                const response = await fetch('start_assessment.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });
                const data = await response.json();
                if (data.success) {
                    assessmentId = data.assessment_id;
                } else {
                    alert('Failed to start assessment. Please try again.');
                    return;
                }
            } catch (error) {
                console.error('Error starting assessment:', error);
                alert('Failed to start assessment. Please try again.');
                return;
            }
        }

        // Set assessment ID in form
        assessmentIdField.value = assessmentId;

        // Hide intro, show assessment
        introEl.style.display = 'none';
        const resumeBanner = document.getElementById('resumeBanner');
        if (resumeBanner) resumeBanner.style.display = 'none';
        containerEl.style.display = 'block';

        // Find first unanswered question or start from beginning
        let startIndex = 0;
        if (Object.keys(savedResponses).length > 0) {
            const questions = document.querySelectorAll('.question-slide');
            for (let i = 0; i < questions.length; i++) {
                const qId = questions[i].dataset.questionId;
                if (!savedResponses[qId]) {
                    startIndex = i;
                    break;
                }
                // If all answered, go to last question
                if (i === questions.length - 1) {
                    startIndex = i;
                }
            }
        }

        currentQuestionIndex = startIndex;
        showQuestion(currentQuestionIndex);
        updateProgress();

        // If resuming with 10+ answers, check adaptive mode immediately
        if (Object.keys(savedResponses).length >= ADAPTIVE_THRESHOLD) {
            checkAdaptiveMode();
        }
    }

    // Start fresh (abandon current assessment)
    async function startFreshAssessment() {
        if (!confirm('<?php echo __('assessment_fresh_confirm', 'Are you sure? Your current progress will be lost.'); ?>')) {
            return;
        }

        // Create new assessment
        try {
            // Mark current as abandoned by creating a new one
            const response = await fetch('start_assessment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    force_new: true
                })
            });

            // Reload page to get fresh state
            window.location.reload();
        } catch (error) {
            console.error('Error:', error);
            alert('Failed to start fresh assessment.');
        }
    }

    // Show specific question
    function showQuestion(index) {
        const questions = document.querySelectorAll('.question-slide');

        // Hide all questions
        questions.forEach(q => q.style.display = 'none');

        // Show current question
        if (questions[index]) {
            questions[index].style.display = 'block';
        }

        // Update current question number
        currentQuestionEl.textContent = index + 1;

        // Update button visibility
        prevBtn.style.display = index > 0 ? 'inline-block' : 'none';

        if (index === questions.length - 1) {
            nextBtn.style.display = 'none';
            submitBtn.style.display = 'inline-block';
        } else {
            nextBtn.style.display = 'inline-block';
            submitBtn.style.display = 'none';
        }
    }

    // Update progress bar
    function updateProgress() {
        // Count answered questions
        let answered = 0;
        document.querySelectorAll('.question-slide').forEach(slide => {
            const qId = slide.dataset.questionId;
            const radioName = 'q_' + qId;
            if (document.querySelector(`input[name="${radioName}"]:checked`)) {
                answered++;
            }
        });

        const percentage = Math.round((answered / totalQuestions) * 100);
        progressBar.style.width = percentage + '%';
        progressBar.setAttribute('aria-valuenow', percentage);

        // Check for adaptive mode trigger
        if (answered >= ADAPTIVE_THRESHOLD && !adaptiveModeShown) {
            checkAdaptiveMode();
        }
    }

    // Check and display adaptive mode
    async function checkAdaptiveMode() {
        if (!assessmentId || adaptiveModeShown) return;

        try {
            const response = await fetch('get_assessment_progress.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    assessment_id: assessmentId
                })
            });

            const data = await response.json();

            if (data.success && data.is_adaptive && data.top_categories && data.top_categories.length >= 2) {
                // Build category display text
                const cat1 = data.top_categories[0];
                const cat2 = data.top_categories[1];
                const lang = currentLang === 'rw' ? 'name_rw' : 'name_en';

                const displayText = `<strong>${cat1[lang] || cat1.name_en}</strong> & <strong>${cat2[lang] || cat2.name_en}</strong>`;
                topCategoriesText.innerHTML = displayText;

                // Show banner
                adaptiveBanner.style.display = 'block';
                adaptiveModeShown = true;
            }
        } catch (error) {
            console.error('Error checking adaptive mode:', error);
        }
    }

    // Save response via AJAX
    async function saveResponse(questionId, answer) {
        if (!assessmentId) return;

        // Show saving indicator
        showSaveIndicator('saving');

        try {
            const response = await fetch('save_response.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    assessment_id: assessmentId,
                    question_id: questionId,
                    answer: answer
                })
            });

            const data = await response.json();

            if (data.success) {
                showSaveIndicator('saved');
                // Update local tracking
                savedResponses[questionId] = answer;
                updateProgress();
            } else {
                showSaveIndicator('error');
                console.error('Save failed:', data.error);
            }
        } catch (error) {
            showSaveIndicator('error');
            console.error('Save error:', error);
        }
    }

    // Show save indicator with state
    function showSaveIndicator(state) {
        saveIndicator.className = 'save-indicator show ' + state;

        const icon = saveIndicator.querySelector('i');
        const text = saveIndicator.querySelector('.save-text');

        switch (state) {
            case 'saving':
                icon.className = 'fas fa-spinner fa-spin';
                text.textContent = '<?php echo __('saving', 'Saving...'); ?>';
                break;
            case 'saved':
                icon.className = 'fas fa-check';
                text.textContent = '<?php echo __('saved', 'Saved'); ?>';
                // Hide after 2 seconds
                setTimeout(() => {
                    saveIndicator.classList.remove('show');
                }, 2000);
                break;
            case 'error':
                icon.className = 'fas fa-exclamation-triangle';
                text.textContent = '<?php echo __('save_error', 'Error saving'); ?>';
                setTimeout(() => {
                    saveIndicator.classList.remove('show');
                }, 3000);
                break;
        }
    }

    // Navigate to next question
    function goToNextQuestion() {
        // Check if current question is answered
        const currentSlide = document.querySelectorAll('.question-slide')[currentQuestionIndex];
        const qId = currentSlide.dataset.questionId;
        const answered = document.querySelector(`input[name="q_${qId}"]:checked`);

        if (!answered) {
            // Highlight that answer is needed
            currentSlide.classList.add('shake');
            setTimeout(() => currentSlide.classList.remove('shake'), 500);
            return;
        }

        if (currentQuestionIndex < totalQuestions - 1) {
            currentQuestionIndex++;
            showQuestion(currentQuestionIndex);
        }
    }

    // Navigate to previous question
    function goToPrevQuestion() {
        if (currentQuestionIndex > 0) {
            currentQuestionIndex--;
            showQuestion(currentQuestionIndex);
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Navigation buttons
        nextBtn.addEventListener('click', goToNextQuestion);
        prevBtn.addEventListener('click', goToPrevQuestion);

        // Radio button change - auto-save
        document.querySelectorAll('.likert-option input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const questionId = this.dataset.questionId;
                const answer = parseInt(this.value);
                saveResponse(questionId, answer);
            });
        });

        // Form submission validation
        document.getElementById('assessmentForm').addEventListener('submit', function(e) {
            // Check all questions are answered
            let allAnswered = true;
            document.querySelectorAll('.question-slide').forEach(slide => {
                const qId = slide.dataset.questionId;
                if (!document.querySelector(`input[name="q_${qId}"]:checked`)) {
                    allAnswered = false;
                }
            });

            if (!allAnswered) {
                e.preventDefault();
                alert('<?php echo __('assessment_complete_all', 'Please answer all questions before submitting.'); ?>');
                return false;
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', function(e) {
            if (containerEl.style.display === 'none') return;

            if (e.key === 'ArrowRight' || e.key === 'Enter') {
                if (nextBtn.style.display !== 'none') {
                    goToNextQuestion();
                }
            } else if (e.key === 'ArrowLeft') {
                goToPrevQuestion();
            }
        });

        // Pre-fill saved responses (already done via PHP, but update progress)
        updateProgress();
    });
</script>

<style>
    /* Shake animation for unanswered question */
    @keyframes shake {

        0%,
        100% {
            transform: translateX(0);
        }

        25% {
            transform: translateX(-5px);
        }

        75% {
            transform: translateX(5px);
        }
    }

    .shake {
        animation: shake 0.3s ease-in-out;
    }
</style>

<?php require_once 'includes/footer-dashboard.php'; ?>