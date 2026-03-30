<?php

/**
 * InkingiX Rwanda - Registration Page
 */

$pageTitle = 'Register';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: register.php');
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$errors = [];
$success = false;

// Handle registration form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission. Please try again.';
    } else {
        $firstName = sanitize($_POST['first_name'] ?? '');
        $lastName = sanitize($_POST['last_name'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $schoolName = sanitize($_POST['school_name'] ?? '');
        $gradeLevel = sanitize($_POST['grade_level'] ?? '');

        // Validation
        if (empty($firstName)) $errors[] = 'First name is required.';
        if (empty($lastName)) $errors[] = 'Last name is required.';
        if (empty($email) || !isValidEmail($email)) $errors[] = 'Valid email is required.';
        if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
        if ($password !== $confirmPassword) $errors[] = __('register_error_password');
        if (empty($schoolName)) $errors[] = 'School name is required.';
        if (empty($gradeLevel)) $errors[] = 'Grade level is required.';

        // Check if email exists
        if (empty($errors)) {
            $db = getDBConnection();
            $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->fetch()) {
                $errors[] = __('register_error_email');
            }
        }

        // Create user
        if (empty($errors)) {
            $hashedPassword = hashPassword($password);

            $stmt = $db->prepare("
                INSERT INTO users (email, password, first_name, last_name, school_name, grade_level, role, preferred_language)
                VALUES (?, ?, ?, ?, ?, ?, 'student', ?)
            ");

            try {
                $stmt->execute([
                    $email,
                    $hashedPassword,
                    $firstName,
                    $lastName,
                    $schoolName,
                    $gradeLevel,
                    getCurrentLanguage()
                ]);

                setFlashMessage('success', __('register_success'));
                header('Location: login.php');
                exit;
            } catch (PDOException $e) {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

// Get schools for dropdown
$schools = getSchools();

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-user-plus fa-3x text-primary mb-3"></i>
                    <h2 class="mb-2"><?php echo __('register_title'); ?></h2>
                    <p class="text-muted"><?php echo __('register_subtitle'); ?></p>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="first_name" class="form-label"><?php echo __('register_first_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($_POST['first_name'] ?? ''); ?>"
                                required autofocus>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="last_name" class="form-label"><?php echo __('register_last_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?php echo htmlspecialchars($_POST['last_name'] ?? ''); ?>"
                                required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo __('register_email'); ?> <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label"><?php echo __('register_password'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password"
                                    minlength="6" required>
                            </div>
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirm_password" class="form-label"><?php echo __('register_confirm_password'); ?> <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="fas fa-lock"></i></span>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="school_name" class="form-label"><?php echo __('register_school'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="school_name" name="school_name" required>
                            <option value="">Select your school...</option>
                            <?php foreach ($schools as $school): ?>
                                <option value="<?php echo htmlspecialchars($school['name']); ?>"
                                    <?php echo (($_POST['school_name'] ?? '') === $school['name']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($school['name']); ?>
                                </option>
                            <?php endforeach; ?>
                            <option value="other">Other (specify below)</option>
                        </select>
                    </div>

                    <div class="mb-3" id="otherSchoolDiv" style="display: none;">
                        <label for="other_school" class="form-label">Specify School Name</label>
                        <input type="text" class="form-control" id="other_school" name="other_school">
                    </div>

                    <div class="mb-4">
                        <label for="grade_level" class="form-label"><?php echo __('register_grade'); ?> <span class="text-danger">*</span></label>
                        <select class="form-select" id="grade_level" name="grade_level" required>
                            <option value="">Select your grade...</option>
                            <option value="S1" <?php echo (($_POST['grade_level'] ?? '') === 'S1') ? 'selected' : ''; ?>><?php echo __('grade_s1'); ?></option>
                            <option value="S2" <?php echo (($_POST['grade_level'] ?? '') === 'S2') ? 'selected' : ''; ?>><?php echo __('grade_s2'); ?></option>
                            <option value="S3" <?php echo (($_POST['grade_level'] ?? '') === 'S3') ? 'selected' : ''; ?>><?php echo __('grade_s3'); ?></option>
                            <option value="S4" <?php echo (($_POST['grade_level'] ?? '') === 'S4') ? 'selected' : ''; ?>><?php echo __('grade_s4'); ?></option>
                            <option value="S5" <?php echo (($_POST['grade_level'] ?? '') === 'S5') ? 'selected' : ''; ?>><?php echo __('grade_s5'); ?></option>
                            <option value="S6" <?php echo (($_POST['grade_level'] ?? '') === 'S6') ? 'selected' : ''; ?>><?php echo __('grade_s6'); ?></option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                        <i class="fas fa-user-plus me-2"></i><?php echo __('register_submit'); ?>
                    </button>

                    <p class="text-center text-muted mb-0">
                        <?php echo __('register_has_account'); ?>
                        <a href="login.php" class="text-decoration-none"><?php echo __('register_login_link'); ?></a>
                    </p>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.getElementById('school_name').addEventListener('change', function() {
        const otherDiv = document.getElementById('otherSchoolDiv');
        const otherInput = document.getElementById('other_school');

        if (this.value === 'other') {
            otherDiv.style.display = 'block';
            otherInput.required = true;
        } else {
            otherDiv.style.display = 'none';
            otherInput.required = false;
        }
    });
</script>

<?php require_once 'includes/footer.php'; ?>