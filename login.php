<?php

/**
 * InkingiX Rwanda - Login Page
 */

$pageTitle = 'Login';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: login.php');
    exit;
}

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $db = getDBConnection();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND is_active = 1");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && verifyPassword($password, $user['password'])) {
            // Login successful
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['language'] = $user['preferred_language'];

            // Update last login
            $updateStmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $updateStmt->execute([$user['id']]);

            // Redirect based on role
            $redirectUrl = $_SESSION['redirect_url'] ?? 'dashboard.php';
            unset($_SESSION['redirect_url']);

            if ($user['role'] === 'system_admin' || $user['role'] === 'school_admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: ' . $redirectUrl);
            }
            exit;
        } else {
            $error = __('login_error');
        }
    }
}

require_once 'includes/header.php';
?>

<div class="row justify-content-center">
    <div class="col-md-6 col-lg-5">
        <div class="card shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <i class="fas fa-graduation-cap fa-3x text-primary mb-3"></i>
                    <h2 class="mb-2"><?php echo __('login_title'); ?></h2>
                    <p class="text-muted"><?php echo __('login_subtitle'); ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="needs-validation" novalidate>
                    <?php echo csrfField(); ?>

                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo __('login_email'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                            <input type="email" class="form-control" id="email" name="email"
                                value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                                required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label"><?php echo __('login_password'); ?></label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="fas fa-lock"></i></span>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember">
                            <label class="form-check-label" for="remember"><?php echo __('login_remember'); ?></label>
                        </div>
                        <a href="forgot-password.php" class="text-decoration-none small"><?php echo __('login_forgot'); ?></a>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-lg mb-3">
                        <i class="fas fa-sign-in-alt me-2"></i><?php echo __('login_submit'); ?>
                    </button>

                    <p class="text-center text-muted mb-0">
                        <?php echo __('login_no_account'); ?>
                        <a href="register.php" class="text-decoration-none"><?php echo __('login_register_link'); ?></a>
                    </p>
                </form>
            </div>
        </div>

        <!-- Demo Accounts Info -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title"><i class="fas fa-info-circle me-2"></i>Demo Accounts</h6>
                <small class="text-muted">
                    <strong>Admin:</strong> admin@inkingi.rw / Admin123<br>
                    <em>Or register a new student account</em>
                </small>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>