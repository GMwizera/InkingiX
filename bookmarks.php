<?php
/**
 * EduBridge Rwanda - My Bookmarks Page
 * Displays user's saved careers
 */

$pageTitle = 'My Bookmarks';
require_once 'includes/functions.php';

// Handle language switch
if (isset($_GET['lang'])) {
    setLanguage($_GET['lang']);
    header('Location: bookmarks.php');
    exit;
}

requireLogin();

$currentUser = getCurrentUser();
$bookmarks = getUserBookmarks($currentUser['id']);

require_once 'includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h2><i class="fas fa-bookmark me-2"></i><?php echo __('bookmarks_title', 'My Saved Careers'); ?></h2>
    <span class="badge bg-primary"><?php echo count($bookmarks); ?> <?php echo __('bookmarks_count', 'saved'); ?></span>
</div>

<?php if (!empty($bookmarks)): ?>
<div class="row g-4">
    <?php foreach ($bookmarks as $career): ?>
    <div class="col-md-6 col-lg-4" id="bookmark-card-<?php echo $career['id']; ?>">
        <div class="card h-100 career-card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <span class="badge category-badge-<?php echo $career['category_code']; ?>">
                        <?php echo $career['category_name']; ?>
                    </span>
                    <div class="d-flex align-items-center gap-2">
                        <?php echo getDemandBadge($career['demand_level'] ?? 'growing'); ?>
                        <button type="button"
                                class="btn btn-sm btn-warning bookmark-btn"
                                data-career-id="<?php echo $career['id']; ?>"
                                title="<?php echo __('bookmark_remove', 'Remove bookmark'); ?>">
                            <i class="fas fa-bookmark"></i>
                        </button>
                    </div>
                </div>
                <h5 class="card-title"><?php echo getLocalizedField($career, 'title'); ?></h5>
                <p class="card-text text-muted small">
                    <?php echo substr(getLocalizedField($career, 'description'), 0, 120); ?>...
                </p>
                <div class="salary-range small mb-3">
                    <i class="fas fa-money-bill-wave me-1"></i>
                    <?php echo formatCurrency($career['salary_range_min']); ?> - <?php echo formatCurrency($career['salary_range_max']); ?>/month
                </div>
                <div class="small text-muted mb-3">
                    <i class="fas fa-clock me-1"></i>
                    <?php echo __('saved_on', 'Saved'); ?>: <?php echo date('M j, Y', strtotime($career['saved_at'])); ?>
                </div>
            </div>
            <div class="card-footer bg-transparent">
                <a href="career.php?id=<?php echo $career['id']; ?>" class="btn btn-outline-primary btn-sm w-100">
                    <i class="fas fa-arrow-right me-1"></i><?php echo __('view_details', 'View Details'); ?>
                </a>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php else: ?>
<div class="text-center py-5">
    <i class="fas fa-bookmark fa-4x text-muted mb-4"></i>
    <h4 class="text-muted"><?php echo __('bookmarks_empty_title', 'No saved careers yet'); ?></h4>
    <p class="text-muted mb-4"><?php echo __('bookmarks_empty_desc', 'Save careers you\'re interested in to view them here later.'); ?></p>
    <a href="careers.php" class="btn btn-primary">
        <i class="fas fa-search me-2"></i><?php echo __('explore_careers', 'Explore Careers'); ?>
    </a>
</div>
<?php endif; ?>

<script>
// Bookmark toggle (remove) functionality
document.querySelectorAll('.bookmark-btn').forEach(btn => {
    btn.addEventListener('click', async function() {
        const careerId = this.dataset.careerId;
        const card = document.getElementById('bookmark-card-' + careerId);

        try {
            const response = await fetch('toggle_bookmark.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ career_id: parseInt(careerId) })
            });

            const data = await response.json();

            if (data.success && !data.is_bookmarked) {
                // Animate removal
                card.style.transition = 'opacity 0.3s, transform 0.3s';
                card.style.opacity = '0';
                card.style.transform = 'scale(0.9)';
                setTimeout(() => {
                    card.remove();
                    // Check if any bookmarks remain
                    if (document.querySelectorAll('.career-card').length === 0) {
                        window.location.reload();
                    }
                }, 300);
            }
        } catch (error) {
            console.error('Bookmark error:', error);
        }
    });
});
</script>

<?php require_once 'includes/footer.php'; ?>
