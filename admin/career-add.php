<?php
/**
 * EduBridge Rwanda - Add Career (Admin)
 */

require_once '../includes/functions.php';

requireRole(['system_admin']);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: careers.php');
    exit;
}

$db = getDBConnection();

$stmt = $db->prepare("
    INSERT INTO careers (
        title_en, title_rw, description_en, description_rw,
        required_skills_en, education_path_en,
        salary_range_min, salary_range_max,
        primary_category_id, secondary_category_id
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
");

try {
    $stmt->execute([
        sanitize($_POST['title_en']),
        sanitize($_POST['title_rw'] ?? ''),
        sanitize($_POST['description_en']),
        sanitize($_POST['description_rw'] ?? ''),
        sanitize($_POST['required_skills_en']),
        sanitize($_POST['education_path_en']),
        intval($_POST['salary_range_min']),
        intval($_POST['salary_range_max']),
        intval($_POST['primary_category_id']),
        !empty($_POST['secondary_category_id']) ? intval($_POST['secondary_category_id']) : null
    ]);

    setFlashMessage('success', 'Career added successfully.');
} catch (PDOException $e) {
    setFlashMessage('error', 'Failed to add career. Please try again.');
}

header('Location: careers.php');
exit;
?>
