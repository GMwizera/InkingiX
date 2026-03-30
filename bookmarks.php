<?php

/**
 * InkingiX Rwanda - My Bookmarks Page
 * Displays user's saved careers with compare functionality
 */

$pageTitle = 'My Saved Careers';
$pageSubtitle = 'Careers you\'ve bookmarked for later review';
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

// Always use sidebar for logged-in pages
require_once 'includes/header-dashboard.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <?php if (count($bookmarks) >= 2): ?>
        <p class="text-muted small mb-0">
            <i class="fas fa-balance-scale me-1"></i>
            <?php echo __('compare_hint'); ?>
        </p>
    <?php else: ?>
        <div></div>
    <?php endif; ?>
    <span class="badge bg-primary"><?php echo count($bookmarks); ?> <?php echo __('bookmarks_count', 'saved'); ?></span>
</div>

<?php if (!empty($bookmarks)): ?>
    <div class="row g-4">
        <?php foreach ($bookmarks as $career): ?>
            <div class="col-md-6 col-lg-4" id="bookmark-card-<?php echo $career['id']; ?>">
                <div class="card h-100 career-card" data-career-id="<?php echo $career['id']; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="d-flex align-items-center gap-2">
                                <!-- Compare Checkbox -->
                                <div class="form-check compare-check">
                                    <input type="checkbox"
                                        class="form-check-input compare-checkbox"
                                        id="compare_<?php echo $career['id']; ?>"
                                        data-career-id="<?php echo $career['id']; ?>"
                                        data-career-name="<?php echo htmlspecialchars(getLocalizedField($career, 'title')); ?>">
                                </div>
                                <span class="badge category-badge-<?php echo $career['category_code']; ?>">
                                    <?php echo $career['category_name']; ?>
                                </span>
                            </div>
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

<!-- Sticky Compare Bar -->
<div id="compareBar" class="compare-bar" style="display: none;">
    <div class="container">
        <div class="compare-bar-content">
            <div class="compare-bar-info">
                <i class="fas fa-balance-scale me-2"></i>
                <span id="compareText"><?php echo __('compare_select_careers', 'Select 2 careers to compare'); ?></span>
            </div>
            <a href="#" id="compareBtn" class="btn btn-primary btn-sm" style="display: none;">
                <i class="fas fa-arrow-right me-1"></i>
                <?php echo __('compare_now', 'Compare Now'); ?>
            </a>
        </div>
    </div>
</div>

<style>
    /* Compare bar styles */
    .compare-bar {
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        background: #1e3a5f;
        color: white;
        padding: 0.75rem 0;
        z-index: 1000;
        box-shadow: 0 -4px 12px rgba(0, 0, 0, 0.15);
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(100%);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    .compare-bar-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .compare-bar-info {
        display: flex;
        align-items: center;
    }

    /* Compare checkbox styling */
    .compare-check {
        margin: 0;
    }

    .compare-check .form-check-input {
        width: 20px;
        height: 20px;
        cursor: pointer;
    }

    .compare-check .form-check-input:checked {
        background-color: var(--primary, #2E7D5A);
        border-color: var(--primary, #2E7D5A);
    }

    /* Highlight selected cards */
    .career-card.selected-for-compare {
        border: 2px solid var(--primary, #2E7D5A) !important;
        box-shadow: 0 0 0 3px rgba(46, 125, 90, 0.2);
    }
</style>

<script>
    // Compare functionality
    const selectedCareers = [];
    const compareBar = document.getElementById('compareBar');
    const compareText = document.getElementById('compareText');
    const compareBtn = document.getElementById('compareBtn');

    document.querySelectorAll('.compare-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const careerId = this.dataset.careerId;
            const careerName = this.dataset.careerName;
            const card = this.closest('.career-card');

            if (this.checked) {
                // Only allow 2 selections
                if (selectedCareers.length >= 2) {
                    this.checked = false;
                    return;
                }
                selectedCareers.push({
                    id: careerId,
                    name: careerName
                });
                card.classList.add('selected-for-compare');
            } else {
                const index = selectedCareers.findIndex(c => c.id === careerId);
                if (index > -1) {
                    selectedCareers.splice(index, 1);
                }
                card.classList.remove('selected-for-compare');
            }

            updateCompareBar();
        });
    });

    function updateCompareBar() {
        if (selectedCareers.length === 0) {
            compareBar.style.display = 'none';
            return;
        }

        compareBar.style.display = 'block';

        if (selectedCareers.length === 1) {
            compareText.innerHTML = '<strong>' + selectedCareers[0].name + '</strong> — <?php echo __('compare_select_one_more', 'select 1 more to compare'); ?>';
            compareBtn.style.display = 'none';
        } else if (selectedCareers.length === 2) {
            compareText.innerHTML = '<?php echo __('compare_ready', 'Compare'); ?> <strong>' + selectedCareers[0].name + '</strong> vs <strong>' + selectedCareers[1].name + '</strong>';
            compareBtn.style.display = 'inline-flex';
            compareBtn.href = 'compare.php?a=' + selectedCareers[0].id + '&b=' + selectedCareers[1].id;
        }
    }

    // Bookmark toggle (remove) functionality
    document.querySelectorAll('.bookmark-btn').forEach(btn => {
        btn.addEventListener('click', async function() {
            const careerId = this.dataset.careerId;
            const card = document.getElementById('bookmark-card-' + careerId);

            try {
                const response = await fetch('toggle_bookmark.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        career_id: parseInt(careerId)
                    })
                });

                const data = await response.json();

                if (data.success && !data.is_bookmarked) {
                    // Remove from selectedCareers if it was selected for compare
                    const compareIndex = selectedCareers.findIndex(c => c.id === careerId);
                    if (compareIndex > -1) {
                        selectedCareers.splice(compareIndex, 1);
                        updateCompareBar();
                    }

                    // Animate removal
                    card.style.transition = 'opacity 0.3s, transform 0.3s';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';
                    setTimeout(() => {
                        card.remove();
                        // Update count badge
                        const countBadge = document.querySelector('.badge.bg-primary');
                        if (countBadge) {
                            const remaining = document.querySelectorAll('.career-card').length;
                            countBadge.textContent = remaining + ' <?php echo __('bookmarks_count', 'saved'); ?>';
                        }
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

<?php require_once 'includes/footer-dashboard.php'; ?>