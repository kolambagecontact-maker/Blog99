<?php
// my-posts.php — Story Management Dashboard for Authenticated Users with Cover Photo Support

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Must be logged in
requireLogin();

// Fetch only the current user's articles
$stmt = $pdo->prepare('
    SELECT * FROM blogPost
    WHERE user_id = :user_id
    ORDER BY created_at DESC
');
$stmt->execute(['user_id' => currentUserId()]);
$myPosts = $stmt->fetchAll();

$pageTitle = 'Your Stories';
include __DIR__ . '/includes/header.php';
?>

<div class="my-stories-wrapper">
    <div class="my-stories-container">

        <!-- Page Header -->
        <div class="my-stories-header">
            <div class="my-stories-header-left">
                <h1 class="my-stories-title">Your stories</h1>
                <p class="my-stories-subtitle">Manage, edit, and curate all the articles you've published on Blog99.</p>
            </div>
            <div class="my-stories-header-right">
                <a href="editor.php" class="btn btn-primary btn-sm">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd"/>
                    </svg>
                    <span>Write a story</span>
                </a>
            </div>
        </div>

        <!-- Filter Tab bar / Counter -->
        <div class="my-stories-tab-bar">
            <div class="tab-item tab-item--active">
                <span>Published</span>
                <span class="tab-count-badge"><?php echo count($myPosts); ?></span>
            </div>
        </div>

        <?php if (empty($myPosts)): ?>
            <!-- Empty state -->
            <div class="empty-state-card my-stories-empty">
                <div class="empty-state-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                    </svg>
                </div>
                <h3 class="empty-state-title">Your first story starts here</h3>
                <p class="empty-state-desc">Share an idea, technical guide, experience, or perspective with the community.</p>
                <div class="empty-state-actions">
                    <a href="editor.php" class="btn btn-primary">Write your first story</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Story List -->
            <div class="my-stories-list">
                <?php foreach ($myPosts as $post): ?>
                    <div class="my-story-row">
                        <div class="my-story-info-group">
                            <?php if (!empty($post['cover_image'])): ?>
                                <div class="my-story-thumbnail">
                                    <img src="<?php echo sanitize($post['cover_image']); ?>" alt="" loading="lazy">
                                </div>
                            <?php endif; ?>
                            <div class="my-story-info">
                                <h2 class="my-story-title">
                                    <a href="article.php?id=<?php echo $post['id']; ?>">
                                        <?php echo sanitize($post['title']); ?>
                                    </a>
                                </h2>
                                <div class="my-story-meta">
                                    <span>Published on <?php echo formatDateFull($post['created_at']); ?></span>
                                    <span class="meta-dot">&middot;</span>
                                    <span><?php echo estimateReadingTime($post['content']); ?></span>
                                    <?php if ($post['updated_at'] !== $post['created_at']): ?>
                                        <span class="meta-dot">&middot;</span>
                                        <span class="updated-badge">Edited <?php echo formatDate($post['updated_at']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>

                        <div class="my-story-actions">
                            <a href="editor.php?id=<?php echo $post['id']; ?>" class="btn btn-secondary btn-sm" title="Edit story">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                                    <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                                <span>Edit</span>
                            </a>
                            <form action="delete-post.php" method="POST" class="inline-form">
                                <input type="hidden" name="id" value="<?php echo $post['id']; ?>">
                                <button type="submit" class="btn btn-danger-ghost btn-sm btn-delete-trigger" title="Delete story">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                                    </svg>
                                    <span>Delete</span>
                                </button>
                            </form>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
