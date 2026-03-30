<?php
/**
 * EduBridge Rwanda - Admin Institutions Management
 */

$pageTitle = 'Manage Institutions';
require_once '../includes/functions.php';

requireRole(['system_admin', 'school_admin']);

$currentUser = getCurrentUser();
$db = getDBConnection();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add') {
        $stmt = $db->prepare("
            INSERT INTO institutions (name_en, name_rw, type, location, website, description_en, is_public)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            sanitize($_POST['name_en']),
            sanitize($_POST['name_rw'] ?? ''),
            sanitize($_POST['type']),
            sanitize($_POST['location']),
            sanitize($_POST['website'] ?? ''),
            sanitize($_POST['description_en'] ?? ''),
            isset($_POST['is_public']) ? 1 : 0
        ]);
        setFlashMessage('success', 'Institution added successfully.');
    }

    if ($action === 'toggle_status') {
        $institutionId = intval($_POST['institution_id'] ?? 0);
        if ($institutionId > 0) {
            $stmt = $db->prepare("UPDATE institutions SET is_active = NOT is_active WHERE id = ?");
            $stmt->execute([$institutionId]);
            setFlashMessage('success', 'Institution status updated.');
        }
    }

    header('Location: institutions.php');
    exit;
}

// Get institutions
$stmt = $db->query("SELECT * FROM institutions ORDER BY type, name_en");
$institutions = $stmt->fetchAll();
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
                    <li class="nav-item"><a class="nav-link active" href="institutions.php"><i class="fas fa-university"></i> Institutions</a></li>
                    <li class="nav-item"><a class="nav-link" href="questions.php"><i class="fas fa-question-circle"></i> Questions</a></li>
                    <li class="nav-item"><a class="nav-link" href="reports.php"><i class="fas fa-chart-bar"></i> Reports</a></li>
                </ul>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <?php displayFlashMessage(); ?>

                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2><i class="fas fa-university me-2"></i>Manage Institutions</h2>
                    <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addModal">
                        <i class="fas fa-plus me-1"></i>Add Institution
                    </button>
                </div>

                <div class="card">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Type</th>
                                    <th>Location</th>
                                    <th>Public/Private</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($institutions as $inst): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($inst['name_en']); ?></strong>
                                        <?php if ($inst['website']): ?>
                                        <br><small><a href="<?php echo $inst['website']; ?>" target="_blank" class="text-muted">
                                            <i class="fas fa-external-link-alt me-1"></i>Website
                                        </a></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $inst['type'] === 'university' ? 'primary' : ($inst['type'] === 'tvet' ? 'success' : 'info'); ?>">
                                            <?php echo ucfirst($inst['type']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($inst['location']); ?></td>
                                    <td><?php echo $inst['is_public'] ? 'Public' : 'Private'; ?></td>
                                    <td>
                                        <span class="badge bg-<?php echo $inst['is_active'] ? 'success' : 'secondary'; ?>">
                                            <?php echo $inst['is_active'] ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <form method="POST" class="d-inline">
                                            <input type="hidden" name="institution_id" value="<?php echo $inst['id']; ?>">
                                            <input type="hidden" name="action" value="toggle_status">
                                            <button type="submit" class="btn btn-sm btn-outline-<?php echo $inst['is_active'] ? 'warning' : 'success'; ?>">
                                                <i class="fas fa-<?php echo $inst['is_active'] ? 'eye-slash' : 'eye'; ?>"></i>
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

    <!-- Add Modal -->
    <div class="modal fade" id="addModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST">
                    <input type="hidden" name="action" value="add">
                    <div class="modal-header">
                        <h5 class="modal-title">Add Institution</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Name (English) *</label>
                            <input type="text" class="form-control" name="name_en" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Name (Kinyarwanda)</label>
                            <input type="text" class="form-control" name="name_rw">
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Type *</label>
                                <select class="form-select" name="type" required>
                                    <option value="university">University</option>
                                    <option value="tvet">TVET</option>
                                    <option value="college">College</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Location *</label>
                                <input type="text" class="form-control" name="location" required>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Website</label>
                            <input type="url" class="form-control" name="website" placeholder="https://...">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description_en" rows="2"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" name="is_public" id="is_public" checked>
                            <label class="form-check-label" for="is_public">Public Institution</label>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Add Institution</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
