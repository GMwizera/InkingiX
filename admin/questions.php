<?php
/**
 * EduBridge Rwanda - Admin Questions Management
 */

$pageTitle = 'Manage Assessment Questions';
require_once '../includes/functions.php';

requireRole(['system_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Get questions
$stmt = $db->query("
    SELECT q.*, cc.name_en as category_name, cc.code as category_code
    FROM assessment_questions q
    JOIN career_categories cc ON q.category_id = cc.id
    ORDER BY q.order_number
");
$questions = $stmt->fetchAll();

$categories = getCareerCategories();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo SITE_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/style.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i><?php echo SITE_NAME; ?> Admin
            </a>
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-2"><i class="fas fa-home me-1"></i>Main Site</a>
                <a href="../logout.php" class="btn btn-danger btn-sm"><i class="fas fa-sign-out-alt me-1"></i>Logout</a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar py-3">
                <ul class="nav flex-column">
                    <li class="nav-item"><a class="nav-link" href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="users.php"><i class="fas fa-users"></i> Users</a></li>
                    <li class="nav-item"><a class="nav-link" href="careers.php"><i class="fas fa-briefcase"></i> Careers</a></li>
                    <li class="nav-item"><a class="nav-link" href="institutions.php"><i class="fas fa-university"></i> Institutions</a></li>
                    <li class="nav-item"><a class="nav-link active" href="questions.php"><i class="fas fa-question-circle"></i> Questions</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-question-circle me-2"></i>Assessment Questions</h2>
                    <span class="badge bg-primary"><?php echo count($questions); ?> questions</span>
                </div>

                <!-- Category Summary -->
                <div class="row mb-4">
                    <?php foreach ($categories as $cat): ?>
                    <?php
                    $catQuestions = array_filter($questions, fn($q) => $q['category_id'] == $cat['id']);
                    ?>
                    <div class="col-md-2 mb-2">
                        <div class="card text-center">
                            <div class="card-body py-2">
                                <span class="badge category-badge-<?php echo $cat['code']; ?> mb-1"><?php echo $cat['code']; ?></span>
                                <div class="h5 mb-0"><?php echo count($catQuestions); ?></div>
                                <small class="text-muted"><?php echo $cat['name_en']; ?></small>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Questions Table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Question (English)</th>
                                    <th>Category</th>
                                    <th>Weight</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($questions as $q): ?>
                                <tr>
                                    <td><?php echo $q['order_number']; ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($q['question_en']); ?>
                                        <?php if ($q['question_rw']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($q['question_rw']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge category-badge-<?php echo $q['category_code']; ?>">
                                            <?php echo $q['category_name']; ?>
                                        </span>
                                    </td>
                                    <td><?php echo $q['weight']; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $q['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $q['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="alert alert-info mt-4">
                    <i class="fas fa-info-circle me-2"></i>
                    Assessment questions are based on the RIASEC (Holland Codes) model. Each category should have 5 questions for balanced assessment results.
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
