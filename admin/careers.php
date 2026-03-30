<?php
/**
 * EduBridge Rwanda - Admin Careers Management
 */

$pageTitle = 'Manage Careers';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $careerId = intval($_POST['career_id'] ?? 0);

    if ($action === 'toggle_status' && $careerId > 0) {
        $stmt = $db->prepare("UPDATE careers SET is_active = NOT is_active WHERE id = ?");
        $stmt->execute([$careerId]);
        setFlashMessage('success', 'Career status updated.');
    }

    if ($action === 'delete' && $careerId > 0) {
        $stmt = $db->prepare("DELETE FROM careers WHERE id = ?");
        $stmt->execute([$careerId]);
        setFlashMessage('success', 'Career deleted.');
    }

    header('Location: careers.php');
    exit;
}

// Get careers
$stmt = $db->query("
    SELECT c.*, cc.name_en as category_name, cc.code as category_code
    FROM careers c
    JOIN career_categories cc ON c.primary_category_id = cc.id
    ORDER BY c.title_en
");
$careers = $stmt->fetchAll();

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
    <!-- Admin Navbar -->
    <nav class="navbar navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-cog me-2"></i><?php echo SITE_NAME; ?> Admin
            </a>
            <div class="d-flex align-items-center">
                <a href="../index.php" class="btn btn-outline-light btn-sm me-2">
                    <i class="fas fa-home me-1"></i>Main Site
                </a>
                <a href="../logout.php" class="btn btn-danger btn-sm">
                    <i class="fas fa-sign-out-alt me-1"></i>Logout
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-3 col-lg-2 d-md-block admin-sidebar py-3">
                <div class="position-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="index.php">
                                <i class="fas fa-tachometer-alt"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="users.php">
                                <i class="fas fa-users"></i> Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="careers.php">
                                <i class="fas fa-briefcase"></i> Careers
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="institutions.php">
                                <i class="fas fa-university"></i> Institutions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="questions.php">
                                <i class="fas fa-question-circle"></i> Questions
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="fas fa-chart-bar"></i> Reports
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php displayFlashMessage(); ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-briefcase me-2"></i>Manage Careers</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCareerModal">
                        <i class="fas fa-plus me-1"></i>Add Career
                    </button>
                </div>

                <!-- Careers Table -->
                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Title</th>
                                    <th>Category</th>
                                    <th>Salary Range</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($careers as $career): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($career['title_en']); ?></strong>
                                        <?php if ($career['title_rw']): ?>
                                        <br><small class="text-muted"><?php echo htmlspecialchars($career['title_rw']); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge category-badge-<?php echo $career['category_code']; ?>">
                                            <?php echo $career['category_name']; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php echo formatCurrency($career['salary_range_min']); ?> -
                                        <?php echo formatCurrency($career['salary_range_max']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $career['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $career['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="career-edit.php?id=<?php echo $career['id']; ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="career_id" value="<?php echo $career['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $career['is_active'] ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $career['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Add Career Modal -->
    <div class="modal fade" id="addCareerModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form method="POST" action="career-add.php">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Career</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Title (English)</label>
                                <input type="text" class="form-control" name="title_en" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Title (Kinyarwanda)</label>
                                <input type="text" class="form-control" name="title_rw">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description (English)</label>
                            <textarea class="form-control" name="description_en" rows="3" required></textarea>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Primary Category</label>
                                <select class="form-select" name="primary_category_id" required>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_en']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Secondary Category</label>
                                <select class="form-select" name="secondary_category_id">
                                    <option value="">None</option>
                                    <?php foreach ($categories as $cat): ?>
                                    <option value="<?php echo $cat['id']; ?>"><?php echo $cat['name_en']; ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Min Salary (RWF)</label>
                                <input type="number" class="form-control" name="salary_range_min" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Max Salary (RWF)</label>
                                <input type="number" class="form-control" name="salary_range_max" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Required Skills (comma separated)</label>
                            <input type="text" class="form-control" name="required_skills_en" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Education Path</label>
                            <textarea class="form-control" name="education_path_en" rows="2" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Career</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
