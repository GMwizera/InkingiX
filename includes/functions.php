<?php
/**
 * EduBridge Rwanda - Utility Functions
 */

require_once __DIR__ . '/../config/database.php';

/**
 * Sanitize user input
 */
function sanitize($input) {
    return htmlspecialchars(strip_tags(trim($input)), ENT_QUOTES, 'UTF-8');
}

/**
 * Validate email format
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Generate secure random token
 */
function generateToken($length = 32) {
    return bin2hex(random_bytes($length));
}

/**
 * Hash password using bcrypt
 */
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

/**
 * Verify password against hash
 */
function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get current user data
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ? AND is_active = 1");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

/**
 * Check user role
 */
function hasRole($role) {
    $user = getCurrentUser();
    if (!$user) return false;

    if (is_array($role)) {
        return in_array($user['role'], $role);
    }
    return $user['role'] === $role;
}

/**
 * Require login - redirect if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
        header('Location: /login.php');
        exit;
    }
}

/**
 * Require specific role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: /unauthorized.php');
        exit;
    }
}

/**
 * Get current language
 */
function getCurrentLanguage() {
    if (isset($_SESSION['language'])) {
        return $_SESSION['language'];
    }

    $user = getCurrentUser();
    if ($user && isset($user['preferred_language'])) {
        return $user['preferred_language'];
    }

    return DEFAULT_LANGUAGE;
}

/**
 * Set language
 */
function setLanguage($lang) {
    if (in_array($lang, ['en', 'rw'])) {
        $_SESSION['language'] = $lang;

        if (isLoggedIn()) {
            $db = getDBConnection();
            $stmt = $db->prepare("UPDATE users SET preferred_language = ? WHERE id = ?");
            $stmt->execute([$lang, $_SESSION['user_id']]);
        }
    }
}

/**
 * Translate text
 */
function __($key, $default = null) {
    global $translations;
    $lang = getCurrentLanguage();

    if (!isset($translations)) {
        $langFile = __DIR__ . '/../lang/' . $lang . '.php';
        if (file_exists($langFile)) {
            $translations = require $langFile;
        } else {
            $translations = [];
        }
    }

    return $translations[$key] ?? ($default ?? $key);
}

/**
 * Get localized field value
 */
function getLocalizedField($row, $field) {
    $lang = getCurrentLanguage();
    $localizedField = $field . '_' . $lang;
    $englishField = $field . '_en';

    if (isset($row[$localizedField]) && !empty($row[$localizedField])) {
        return $row[$localizedField];
    }

    return $row[$englishField] ?? '';
}

/**
 * Format currency (Rwandan Francs)
 */
function formatCurrency($amount) {
    return number_format($amount, 0, ',', ',') . ' RWF';
}

/**
 * Get all schools
 */
function getSchools() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM schools WHERE is_active = 1 ORDER BY name");
    return $stmt->fetchAll();
}

/**
 * Get career categories
 */
function getCareerCategories() {
    $db = getDBConnection();
    $stmt = $db->query("SELECT * FROM career_categories ORDER BY id");
    return $stmt->fetchAll();
}

/**
 * Flash message system
 */
function setFlashMessage($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

function getFlashMessage() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Display flash message
 */
function displayFlashMessage() {
    $flash = getFlashMessage();
    if ($flash) {
        $alertClass = $flash['type'] === 'success' ? 'alert-success' :
                     ($flash['type'] === 'error' ? 'alert-danger' : 'alert-info');
        echo '<div class="alert ' . $alertClass . ' alert-dismissible fade show" role="alert">';
        echo htmlspecialchars($flash['message']);
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>';
        echo '</div>';
    }
}

/**
 * Get user's latest assessment
 */
function getLatestAssessment($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT * FROM user_assessments
        WHERE user_id = ?
        ORDER BY started_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

/**
 * Get assessment results
 */
function getAssessmentResults($assessmentId) {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT ar.*, cc.name_en, cc.name_rw, cc.code, cc.icon
        FROM assessment_results ar
        JOIN career_categories cc ON ar.category_id = cc.id
        WHERE ar.assessment_id = ?
        ORDER BY ar.percentage DESC
    ");
    $stmt->execute([$assessmentId]);
    return $stmt->fetchAll();
}

/**
 * Get career matches
 */
function getCareerMatches($assessmentId) {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT cm.*, c.title_en, c.title_rw, c.description_en, c.description_rw,
               c.salary_range_min, c.salary_range_max, c.demand_level
        FROM career_matches cm
        JOIN careers c ON cm.career_id = c.id
        WHERE cm.assessment_id = ?
        ORDER BY cm.rank_order ASC
        LIMIT 5
    ");
    $stmt->execute([$assessmentId]);
    return $stmt->fetchAll();
}

/**
 * Check if a career is bookmarked by the current user
 */
function isCareerBookmarked($careerId) {
    if (!isLoggedIn()) {
        return false;
    }

    $db = getDBConnection();
    $stmt = $db->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND career_id = ?");
    $stmt->execute([$_SESSION['user_id'], $careerId]);
    return (bool) $stmt->fetch();
}

/**
 * Get all bookmarked careers for a user
 */
function getUserBookmarks($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("
        SELECT c.*, b.saved_at, cc.name_en as category_name, cc.code as category_code
        FROM bookmarks b
        JOIN careers c ON b.career_id = c.id
        JOIN career_categories cc ON c.primary_category_id = cc.id
        WHERE b.user_id = ? AND c.is_active = 1
        ORDER BY b.saved_at DESC
    ");
    $stmt->execute([$userId]);
    return $stmt->fetchAll();
}

/**
 * Get bookmark count for a user
 */
function getUserBookmarkCount($userId) {
    $db = getDBConnection();
    $stmt = $db->prepare("SELECT COUNT(*) FROM bookmarks WHERE user_id = ?");
    $stmt->execute([$userId]);
    return (int) $stmt->fetchColumn();
}

/**
 * Get demand badge HTML for a career
 * @param string $demandLevel 'low', 'growing', or 'high'
 * @param bool $showIcon Whether to show an icon
 * @return string HTML for the badge
 */
function getDemandBadge($demandLevel, $showIcon = true) {
    $levels = [
        'high' => [
            'class' => 'badge-demand-high',
            'icon' => 'fa-arrow-trend-up',
            'label_en' => 'High Demand',
            'label_rw' => 'Ibasabwa cyane'
        ],
        'growing' => [
            'class' => 'badge-demand-growing',
            'icon' => 'fa-chart-line',
            'label_en' => 'Growing',
            'label_rw' => 'Iriyongera'
        ],
        'low' => [
            'class' => 'badge-demand-low',
            'icon' => 'fa-arrow-trend-down',
            'label_en' => 'Low Demand',
            'label_rw' => 'Ibasabwa gike'
        ]
    ];

    $level = $levels[$demandLevel] ?? $levels['growing'];
    $lang = getCurrentLanguage();
    $label = ($lang === 'rw') ? $level['label_rw'] : $level['label_en'];

    $iconHtml = $showIcon ? '<i class="fas ' . $level['icon'] . '"></i>' : '';

    return '<span class="badge badge-demand ' . $level['class'] . '">' . $iconHtml . ' ' . htmlspecialchars($label) . '</span>';
}

/**
 * CSRF Token functions
 */
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

function csrfField() {
    return '<input type="hidden" name="csrf_token" value="' . generateCSRFToken() . '">';
}
?>
