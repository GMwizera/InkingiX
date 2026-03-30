<?php

/**
 * InkingiX Rwanda - Unauthorized Access Page
 */

$pageTitle = 'Access Denied';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="text-center py-5">
    <i class="fas fa-lock fa-5x text-danger mb-4"></i>
    <h1>Access Denied</h1>
    <p class="lead text-muted">You don't have permission to access this page.</p>
    <p class="text-muted">If you believe this is an error, please contact the administrator.</p>
    <div class="mt-4">
        <a href="dashboard.php" class="btn btn-primary me-2">
            <i class="fas fa-tachometer-alt me-1"></i>Go to Dashboard
        </a>
        <a href="index.php" class="btn btn-outline-secondary">
            <i class="fas fa-home me-1"></i>Go to Home
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>