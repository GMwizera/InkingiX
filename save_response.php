<?php
/**
 * EduBridge Rwanda - AJAX Save Response Endpoint
 * Saves individual assessment responses without page reload
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

// Get JSON input or form data
$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    $input = $_POST;
}

// Validate required fields
$assessmentId = isset($input['assessment_id']) ? intval($input['assessment_id']) : 0;
$questionId = isset($input['question_id']) ? intval($input['question_id']) : 0;
$answer = isset($input['answer']) ? intval($input['answer']) : 0;

if (!$assessmentId || !$questionId || $answer < 1 || $answer > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid input data']);
    exit;
}

$currentUser = getCurrentUser();
$db = getDBConnection();

try {
    // Verify the assessment belongs to the current user and is not completed
    $stmt = $db->prepare("
        SELECT id, is_completed
        FROM user_assessments
        WHERE id = ? AND user_id = ?
    ");
    $stmt->execute([$assessmentId, $currentUser['id']]);
    $assessment = $stmt->fetch();

    if (!$assessment) {
        http_response_code(403);
        echo json_encode(['success' => false, 'error' => 'Assessment not found or access denied']);
        exit;
    }

    if ($assessment['is_completed']) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Assessment already completed']);
        exit;
    }

    // Verify the question exists and is active
    $stmt = $db->prepare("SELECT id FROM assessment_questions WHERE id = ? AND is_active = 1");
    $stmt->execute([$questionId]);
    if (!$stmt->fetch()) {
        http_response_code(400);
        echo json_encode(['success' => false, 'error' => 'Invalid question']);
        exit;
    }

    // Insert or update the response (upsert)
    $stmt = $db->prepare("
        INSERT INTO assessment_responses (assessment_id, question_id, response_value)
        VALUES (?, ?, ?)
        ON DUPLICATE KEY UPDATE response_value = VALUES(response_value)
    ");
    $stmt->execute([$assessmentId, $questionId, $answer]);

    // Get count of answered questions for progress tracking
    $stmt = $db->prepare("
        SELECT COUNT(*) as answered
        FROM assessment_responses
        WHERE assessment_id = ?
    ");
    $stmt->execute([$assessmentId]);
    $result = $stmt->fetch();
    $answeredCount = $result['answered'];

    // Get total question count
    $stmt = $db->query("SELECT COUNT(*) as total FROM assessment_questions WHERE is_active = 1");
    $totalCount = $stmt->fetch()['total'];

    echo json_encode([
        'success' => true,
        'message' => 'Response saved',
        'answered_count' => $answeredCount,
        'total_count' => $totalCount,
        'progress' => round(($answeredCount / $totalCount) * 100)
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
