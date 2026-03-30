<?php

/**
 * InkingiX Rwanda - Start/Resume Assessment Endpoint
 * Creates a new assessment or returns existing in-progress assessment
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
$forceNew = isset($input['force_new']) && $input['force_new'] === true;

try {
    // If force_new, mark any existing in-progress assessments as abandoned
    if ($forceNew) {
        $stmt = $db->prepare("
            DELETE FROM user_assessments
            WHERE user_id = ? AND is_completed = 0
        ");
        $stmt->execute([$currentUser['id']]);
    }

    // Check for existing in-progress assessment
    $stmt = $db->prepare("
        SELECT id, started_at
        FROM user_assessments
        WHERE user_id = ? AND is_completed = 0
        ORDER BY started_at DESC
        LIMIT 1
    ");
    $stmt->execute([$currentUser['id']]);
    $existingAssessment = $stmt->fetch();

    if ($existingAssessment && !$forceNew) {
        // Return existing assessment with saved responses
        $assessmentId = $existingAssessment['id'];

        // Get saved responses
        $stmt = $db->prepare("
            SELECT question_id, response_value
            FROM assessment_responses
            WHERE assessment_id = ?
        ");
        $stmt->execute([$assessmentId]);
        $responses = $stmt->fetchAll();

        // Convert to key-value map
        $savedResponses = [];
        foreach ($responses as $response) {
            $savedResponses[$response['question_id']] = $response['response_value'];
        }

        echo json_encode([
            'success' => true,
            'assessment_id' => $assessmentId,
            'is_resuming' => true,
            'started_at' => $existingAssessment['started_at'],
            'saved_responses' => $savedResponses,
            'answered_count' => count($savedResponses)
        ]);
    } else {
        // Create new assessment
        $stmt = $db->prepare("INSERT INTO user_assessments (user_id) VALUES (?)");
        $stmt->execute([$currentUser['id']]);
        $assessmentId = $db->lastInsertId();

        echo json_encode([
            'success' => true,
            'assessment_id' => $assessmentId,
            'is_resuming' => false,
            'saved_responses' => [],
            'answered_count' => 0
        ]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
