<?php
/**
 * EduBridge Rwanda - Get Assessment Progress
 * Returns current progress and adaptive mode status
 */

require_once 'includes/functions.php';
require_once 'includes/matching_engine.php';

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
$assessmentId = isset($input['assessment_id']) ? intval($input['assessment_id']) : 0;

if (!$assessmentId) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Assessment ID required']);
    exit;
}

try {
    // Verify assessment belongs to user
    $stmt = $db->prepare("SELECT id FROM user_assessments WHERE id = ? AND user_id = ?");
    $stmt->execute([$assessmentId, $currentUser['id']]);
    if (!$stmt->fetch()) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Assessment not found']);
        exit;
    }

    // Get progress info using matching engine
    $progress = getAssessmentProgress($db, $assessmentId);

    echo json_encode([
        'success' => true,
        'total_questions' => $progress['total_questions'],
        'answered_count' => $progress['answered_count'],
        'percentage' => $progress['percentage'],
        'is_adaptive' => $progress['is_adaptive'],
        'top_categories' => $progress['top_categories'],
        'is_complete' => $progress['is_complete']
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
