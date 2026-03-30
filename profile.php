<?php

/**
 * InkingiX Rwanda - User Profile
 */

$pageTitle = 'My Profile';
$pageSubtitle = 'Manage your account settings and preferences';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: profile.php');
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$db = getDBConnection();
$schools = getSchools();
$errors = [];
$success = false;

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $firstName = sanitize($_POST['first_name'] ?? '');
    $lastName = sanitize($_POST['last_name'] ?? '');
    $schoolName = sanitize($_POST['school_name'] ?? '');
    $gradeLevel = sanitize($_POST['grade_level'] ?? '');
    $gender = sanitize($_POST['gender'] ?? '');
    $dateOfBirth = sanitize($_POST['date_of_birth'] ?? '');
    $phone = sanitize($_POST['phone'] ?? '');
    $preferredLanguage = sanitize($_POST['preferred_language'] ?? 'en');

    // Validation
    if (empty($firstName)) $errors[] = 'First name is required.';
    if (empty($lastName)) $errors[] = 'Last name is required.';

    // Handle password change
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (!empty($newPassword)) {
        if (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }
    }

    if (empty($errors)) {
        $sql = "UPDATE users SET
                first_name = ?,
                last_name = ?,
                school_name = ?,
                grade_level = ?,
                gender = ?,
                date_of_birth = ?,
                phone = ?,
                preferred_language = ?,
                profile_completed = 1";

        $params = [
            $firstName,
            $lastName,
            $schoolName,
            $gradeLevel ?: null,
            $gender ?: null,
            $dateOfBirth ?: null,
            $phone ?: null,
            $preferredLanguage
        ];

        if (!empty($newPassword)) {
            $sql .= ", password = ?";
            $params[] = hashPassword($newPassword);
        }

        $sql .= " WHERE id = ?";
        $params[] = $currentUser['id'];

        $stmt = $db->prepare($sql);

        try {
            $stmt->execute($params);
            setLanguage($preferredLanguage);
            setFlashMessage('success', __('profile_updated'));
            header('Location: profile.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Failed to update profile. Please try again.';
        }
    }
}

// Refresh user data
$currentUser = getCurrentUser();

// Always use sidebar for logged-in pages
require_once 'includes/header-dashboard.php';
?>

<!-- Profile Header -->
<div class="profile-header mb-4">
    <div class="row align-items-center">
        <div class="col-auto">
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
        </div>
        <div class="col">
            <h2 class="mb-1"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h2>
            <p class="mb-0 opacity-75">
                <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($currentUser['email']); ?>
            </p>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0"><i class="fas fa-user-edit me-2"></i><?php echo __('profile_edit'); ?></h5>
            </div>
            <div class="card-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <?php echo csrfField(); ?>

                    <!-- Personal Information -->
                    <h6 class="mb-3"><i class="fas fa-id-card me-2"></i><?php echo __('profile_personal'); ?></h6>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="first_name" class="form-label"><?php echo __('register_first_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="first_name" name="first_name"
                                value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="last_name" class="form-label"><?php echo __('register_last_name'); ?> <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="last_name" name="last_name"
                                value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="gender" class="form-label">Gender</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Select...</option>
                                <option value="male" <?php echo $currentUser['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo $currentUser['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo $currentUser['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="date_of_birth" class="form-label">Date of Birth</label>
                            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth"
                                value="<?php echo $currentUser['date_of_birth']; ?>">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="phone" class="form-label">Phone Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone"
                            value="<?php echo htmlspecialchars($currentUser['phone'] ?? ''); ?>"
                            placeholder="+250 7XX XXX XXX">
                    </div>

                    <!-- School Information -->
                    <h6 class="mb-3"><i class="fas fa-school me-2"></i><?php echo __('profile_school'); ?></h6>

                    <div class="row mb-4">
                        <div class="col-md-8">
                            <label for="school_name" class="form-label"><?php echo __('register_school'); ?></label>
                            <select class="form-select" id="school_name" name="school_name">
                                <option value="">Select your school...</option>
                                <?php foreach ($schools as $school): ?>
                                    <option value="<?php echo htmlspecialchars($school['name']); ?>"
                                        <?php echo $currentUser['school_name'] === $school['name'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($school['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label for="grade_level" class="form-label"><?php echo __('register_grade'); ?></label>
                            <select class="form-select" id="grade_level" name="grade_level">
                                <option value="">Select...</option>
                                <?php foreach (['S1', 'S2', 'S3', 'S4', 'S5', 'S6'] as $grade): ?>
                                    <option value="<?php echo $grade; ?>" <?php echo $currentUser['grade_level'] === $grade ? 'selected' : ''; ?>>
                                        <?php echo $grade; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Account Settings -->
                    <h6 class="mb-3"><i class="fas fa-cog me-2"></i><?php echo __('profile_settings'); ?></h6>

                    <div class="mb-3">
                        <label for="preferred_language" class="form-label"><?php echo __('profile_language'); ?></label>
                        <select class="form-select" id="preferred_language" name="preferred_language">
                            <option value="en" <?php echo $currentUser['preferred_language'] === 'en' ? 'selected' : ''; ?>>English</option>
                            <option value="rw" <?php echo $currentUser['preferred_language'] === 'rw' ? 'selected' : ''; ?>>Kinyarwanda</option>
                        </select>
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" class="form-control" id="new_password" name="new_password"
                                placeholder="Leave blank to keep current">
                            <small class="text-muted">Minimum 6 characters</small>
                        </div>
                        <div class="col-md-6">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-2"></i><?php echo __('profile_save'); ?>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Sidebar -->
    <div class="col-lg-4">
        <!-- Account Info -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-info-circle me-2"></i>Account Info</h6>
            </div>
            <div class="card-body">
                <ul class="list-unstyled mb-0 small">
                    <li class="mb-2">
                        <strong>Account Type:</strong>
                        <span class="badge bg-primary"><?php echo ucfirst($currentUser['role']); ?></span>
                    </li>
                    <li class="mb-2">
                        <strong>Member Since:</strong><br>
                        <?php echo date('F j, Y', strtotime($currentUser['created_at'])); ?>
                    </li>
                    <li>
                        <strong>Last Login:</strong><br>
                        <?php echo $currentUser['last_login'] ? date('F j, Y \a\t g:i A', strtotime($currentUser['last_login'])) : 'N/A'; ?>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Quick Links -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-link me-2"></i>Quick Links</h6>
            </div>
            <div class="list-group list-group-flush">
                <a href="dashboard.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-tachometer-alt me-2"></i>Dashboard
                </a>
                <a href="results.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-chart-bar me-2"></i>My Results
                </a>
                <a href="assessment.php" class="list-group-item list-group-item-action">
                    <i class="fas fa-clipboard-list me-2"></i>Take Assessment
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer-dashboard.php'; ?>