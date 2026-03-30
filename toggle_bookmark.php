<?php
/**
 * EduBridge Rwanda - Toggle Bookmark AJAX Endpoint
 * Adds or removes a career from user's bookmarks
 */

require_once 'includes/functions.php';

// Set JSON response header
header('Content-Type: application/json');

// Require login
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Not authenticated']);
    exit;
}

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get JSON input
$input = json_decode(file_get_contents('php://input'), true);
$careerId = isset($input['career_id']) ? intval($input['career_id']) : 0;

if (!$careerId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Career ID required']);
    exit;
}

try {
    // Verify career exists
    $stmt = $db->prepare("SELECT id FROM careers WHERE id = ? AND is_active = 1");
    $stmt->execute([$careerId]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['success' => false, 'error' => 'Career not found']);
        exit;
    }

    // Check if bookmark exists
    $stmt = $db->prepare("SELECT 1 FROM bookmarks WHERE user_id = ? AND career_id = ?");
    $stmt->execute([$currentUser['id'], $careerId]);
    $isBookmarked = $stmt->fetch();

    if ($isBookmarked) {
        // Remove bookmark
        $stmt = $db->prepare("DELETE FROM bookmarks WHERE user_id = ? AND career_id = ?");
        $stmt->execute([$currentUser['id'], $careerId]);

        echo json_encode([
            'success' => true,
            'action' => 'removed',
            'is_bookmarked' => false,
            'message' => __('bookmark_removed', 'Career removed from bookmarks')
        ]);
    } else {
        // Add bookmark
        $stmt = $db->prepare("INSERT INTO bookmarks (user_id, career_id) VALUES (?, ?)");
        $stmt->execute([$currentUser['id'], $careerId]);

        echo json_encode([
            'success' => true,
            'action' => 'added',
            'is_bookmarked' => true,
            'message' => __('bookmark_added', 'Career saved to bookmarks')
        ]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
