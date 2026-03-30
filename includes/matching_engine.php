<?php

/**
 * InkingiX Rwanda - Matching Engine
 * Adaptive question selection and career matching logic
 *
 * Based on RIASEC/Holland Codes model:
 * R = Realistic, I = Investigative, A = Artistic
 * S = Social, E = Enterprising, C = Conventional
 */

/**
 * Get the top 2 scoring categories from answers so far
 * Used after question 10 to determine adaptive weighting
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @return array Array of top 2 category IDs
 */
function getTopCategoriesSoFar(PDO $pdo, int $assessmentId): array
{
    $stmt = $pdo->prepare("
        SELECT q.category_id, SUM(r.response_value * q.weight) AS score
        FROM assessment_responses r
        JOIN assessment_questions q ON r.question_id = q.id
        WHERE r.assessment_id = ?
        GROUP BY q.category_id
        ORDER BY score DESC
        LIMIT 2
    ");
    $stmt->execute([$assessmentId]);
    return array_column($stmt->fetchAll(), 'category_id');
}

/**
 * Get next questions with adaptive weighting
 * After question 10, weights selection 60% toward top 2 categories
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @param array $answeredQuestionIds IDs of already answered questions
 * @param int $limit Number of questions to return
 * @return array Array of next question(s)
 */
function getNextQuestionsAdaptive(PDO $pdo, int $assessmentId, array $answeredQuestionIds, int $limit = 1): array
{
    $answeredCount = count($answeredQuestionIds);

    // Get all unanswered questions
    $placeholders = !empty($answeredQuestionIds)
        ? 'AND id NOT IN (' . implode(',', array_fill(0, count($answeredQuestionIds), '?')) . ')'
        : '';

    $sql = "
        SELECT id, question_en, question_rw, category_id, weight, order_number
        FROM assessment_questions
        WHERE is_active = 1 $placeholders
        ORDER BY order_number ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($answeredQuestionIds);
    $unansweredQuestions = $stmt->fetchAll();

    if (empty($unansweredQuestions)) {
        return [];
    }

    // If fewer than 10 questions answered, return questions in order
    if ($answeredCount < 10) {
        return array_slice($unansweredQuestions, 0, $limit);
    }

    // After 10 questions: apply adaptive logic
    // Get top 2 categories from answers so far
    $topCategories = getTopCategoriesSoFar($pdo, $assessmentId);

    if (count($topCategories) < 2) {
        // Not enough data for adaptation, return in order
        return array_slice($unansweredQuestions, 0, $limit);
    }

    // Separate questions into top-category and other-category pools
    $topCategoryQuestions = [];
    $otherQuestions = [];

    foreach ($unansweredQuestions as $question) {
        if (in_array($question['category_id'], $topCategories)) {
            $topCategoryQuestions[] = $question;
        } else {
            $otherQuestions[] = $question;
        }
    }

    // Build weighted selection: 60% from top categories, 40% from others
    $selectedQuestions = [];
    $topCount = ceil($limit * 0.6);
    $otherCount = $limit - $topCount;

    // If not enough questions in either pool, adjust
    if (count($topCategoryQuestions) < $topCount) {
        $topCount = count($topCategoryQuestions);
        $otherCount = $limit - $topCount;
    }
    if (count($otherQuestions) < $otherCount) {
        $otherCount = count($otherQuestions);
        $topCount = min(count($topCategoryQuestions), $limit - $otherCount);
    }

    // Select questions from each pool
    $selectedQuestions = array_merge(
        array_slice($topCategoryQuestions, 0, $topCount),
        array_slice($otherQuestions, 0, $otherCount)
    );

    // Sort by order_number to maintain some question flow
    usort($selectedQuestions, function ($a, $b) {
        return $a['order_number'] - $b['order_number'];
    });

    return array_slice($selectedQuestions, 0, $limit);
}

/**
 * Calculate final matches after assessment completion
 * Returns category scores and matched careers
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @return array Array with 'categories' and 'careers' keys
 */
function calculateFinalMatches(PDO $pdo, int $assessmentId): array
{
    // Calculate raw scores per category
    $stmt = $pdo->prepare("
        SELECT q.category_id,
               SUM(r.response_value * q.weight) AS raw_score,
               SUM(5 * q.weight) AS max_possible,
               COUNT(*) AS question_count
        FROM assessment_responses r
        JOIN assessment_questions q ON r.question_id = q.id
        WHERE r.assessment_id = ?
        GROUP BY q.category_id
    ");
    $stmt->execute([$assessmentId]);
    $categoryData = $stmt->fetchAll();

    // Calculate percentages for each category
    $categoryScores = [];
    foreach ($categoryData as $cat) {
        $percentage = ($cat['max_possible'] > 0)
            ? round(($cat['raw_score'] / $cat['max_possible']) * 100, 2)
            : 0;
        $categoryScores[$cat['category_id']] = [
            'category_id' => $cat['category_id'],
            'raw_score' => $cat['raw_score'],
            'max_possible' => $cat['max_possible'],
            'percentage' => $percentage,
            'question_count' => $cat['question_count']
        ];
    }

    // Sort categories by percentage descending
    uasort($categoryScores, function ($a, $b) {
        return $b['percentage'] <=> $a['percentage'];
    });

    // Get top 3 category IDs for career matching
    $topCatIds = array_slice(array_keys($categoryScores), 0, 3);

    // Get careers from top categories
    if (empty($topCatIds)) {
        return ['categories' => $categoryScores, 'careers' => []];
    }

    $placeholders = implode(',', array_fill(0, count($topCatIds), '?'));
    $stmt = $pdo->prepare("
        SELECT c.id, c.title_en, c.title_rw, c.description_en, c.description_rw,
               c.salary_range_min, c.salary_range_max, c.primary_category_id,
               c.secondary_category_id, c.required_skills_en, c.required_skills_rw,
               cc.name_en AS category_name_en, cc.name_rw AS category_name_rw,
               cc.code AS category_code
        FROM careers c
        JOIN career_categories cc ON c.primary_category_id = cc.id
        WHERE c.primary_category_id IN ($placeholders) AND c.is_active = 1
        ORDER BY FIELD(c.primary_category_id, " . implode(',', $topCatIds) . ")
        LIMIT 10
    ");
    $stmt->execute($topCatIds);
    $careers = $stmt->fetchAll();

    // Calculate match percentage for each career
    $careerMatches = [];
    foreach ($careers as $career) {
        $primaryCatId = $career['primary_category_id'];
        $secondaryCatId = $career['secondary_category_id'];

        // Primary category contributes 70%, secondary contributes 30%
        $primaryScore = $categoryScores[$primaryCatId]['percentage'] ?? 0;
        $secondaryScore = ($secondaryCatId && isset($categoryScores[$secondaryCatId]))
            ? $categoryScores[$secondaryCatId]['percentage']
            : 0;

        $matchPercentage = ($primaryScore * 0.7) + ($secondaryScore * 0.3);

        $careerMatches[] = [
            'career_id' => $career['id'],
            'career' => $career,
            'match_percentage' => round($matchPercentage, 2),
            'primary_match' => $primaryScore,
            'secondary_match' => $secondaryScore
        ];
    }

    // Sort by match percentage descending
    usort($careerMatches, function ($a, $b) {
        return $b['match_percentage'] <=> $a['match_percentage'];
    });

    // Assign ranks and limit to top 5
    $rankedCareers = [];
    foreach (array_slice($careerMatches, 0, 5) as $index => $match) {
        $match['rank'] = $index + 1;
        $rankedCareers[] = $match;
    }

    return [
        'categories' => $categoryScores,
        'careers' => $rankedCareers
    ];
}

/**
 * Save assessment results to database
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @param array $results Results from calculateFinalMatches()
 * @return bool Success status
 */
function saveAssessmentResults(PDO $pdo, int $assessmentId, array $results): bool
{
    try {
        $pdo->beginTransaction();

        // Clear any existing results for this assessment
        $stmt = $pdo->prepare("DELETE FROM assessment_results WHERE assessment_id = ?");
        $stmt->execute([$assessmentId]);

        $stmt = $pdo->prepare("DELETE FROM career_matches WHERE assessment_id = ?");
        $stmt->execute([$assessmentId]);

        // Save category scores
        $stmt = $pdo->prepare("
            INSERT INTO assessment_results (assessment_id, category_id, score, percentage)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($results['categories'] as $catScore) {
            $stmt->execute([
                $assessmentId,
                $catScore['category_id'],
                $catScore['raw_score'],
                $catScore['percentage']
            ]);
        }

        // Save career matches
        $stmt = $pdo->prepare("
            INSERT INTO career_matches (assessment_id, career_id, match_percentage, rank_order)
            VALUES (?, ?, ?, ?)
        ");

        foreach ($results['careers'] as $careerMatch) {
            $stmt->execute([
                $assessmentId,
                $careerMatch['career_id'],
                $careerMatch['match_percentage'],
                $careerMatch['rank']
            ]);
        }

        // Mark assessment as completed
        $stmt = $pdo->prepare("
            UPDATE user_assessments
            SET is_completed = 1, completed_at = NOW()
            WHERE id = ?
        ");
        $stmt->execute([$assessmentId]);

        $pdo->commit();
        return true;
    } catch (PDOException $e) {
        $pdo->rollBack();
        error_log("Error saving assessment results: " . $e->getMessage());
        return false;
    }
}

/**
 * Get assessment progress info including adaptive status
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @return array Progress information
 */
function getAssessmentProgress(PDO $pdo, int $assessmentId): array
{
    // Get total questions
    $stmt = $pdo->query("SELECT COUNT(*) FROM assessment_questions WHERE is_active = 1");
    $totalQuestions = (int) $stmt->fetchColumn();

    // Get answered question IDs
    $stmt = $pdo->prepare("
        SELECT question_id FROM assessment_responses WHERE assessment_id = ?
    ");
    $stmt->execute([$assessmentId]);
    $answeredIds = array_column($stmt->fetchAll(), 'question_id');
    $answeredCount = count($answeredIds);

    // Check if adaptive mode is active (after question 10)
    $isAdaptive = $answeredCount >= 10;
    $topCategories = [];

    if ($isAdaptive) {
        $topCategories = getTopCategoriesSoFar($pdo, $assessmentId);

        // Get category names
        if (!empty($topCategories)) {
            $placeholders = implode(',', array_fill(0, count($topCategories), '?'));
            $stmt = $pdo->prepare("
                SELECT id, code, name_en, name_rw FROM career_categories
                WHERE id IN ($placeholders)
            ");
            $stmt->execute($topCategories);
            $topCategories = $stmt->fetchAll();
        }
    }

    return [
        'total_questions' => $totalQuestions,
        'answered_count' => $answeredCount,
        'answered_ids' => $answeredIds,
        'percentage' => ($totalQuestions > 0) ? round(($answeredCount / $totalQuestions) * 100) : 0,
        'is_adaptive' => $isAdaptive,
        'top_categories' => $topCategories,
        'is_complete' => ($answeredCount >= $totalQuestions)
    ];
}

/**
 * Complete assessment and calculate results
 *
 * @param PDO $pdo Database connection
 * @param int $assessmentId Assessment ID
 * @return array|false Results array or false on failure
 */
function completeAssessment(PDO $pdo, int $assessmentId)
{
    // Calculate final matches
    $results = calculateFinalMatches($pdo, $assessmentId);

    // Save results to database
    if (saveAssessmentResults($pdo, $assessmentId, $results)) {
        return $results;
    }

    return false;
}
