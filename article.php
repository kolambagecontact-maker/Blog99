<?php
// article.php — Single Story Editorial Reading Experience with Cover Photo

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Validate the article ID
$articleId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$articleId) {
    setFlashError('Invalid story request.');
    redirect('index.php');
}

// Fetch the article with author information
$stmt = $pdo->prepare('
    SELECT b.*, u.username AS author_name, u.email AS author_email
    FROM blogPost b
    JOIN user u ON b.user_id = u.id
    WHERE b.id = :id
    LIMIT 1
');
$stmt->execute(['id' => $articleId]);
$article = $stmt->fetch();

if (!$article) {
    setFlashError('Story not found.');
    redirect('index.php');
}

// Check if the logged-in user owns this article
$isOwner = isLoggedIn() && (currentUserId() == $article['user_id']);

$pageTitle = $article['title'];
include __DIR__ . '/includes/header.php';
?>

<div class="article-reading-wrapper">
    <div class="reading-container">

        <!-- Top Breadcrumb / Back Link -->
        <div class="article-nav-bar">
            <a href="index.php" class="back-link">
                <svg viewBox="0 0 20 20" fill="currentColor" class="back-arrow-icon" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.707 14.707a1 1 0 01-1.414 0l-5-5a1 1 0 010-1.414l5-5a1 1 0 111.414 1.414L3.414 9H17a1 1 0 110 2H3.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd"/>
                </svg>
                <span>Back to stories</span>
            </a>
            
            <?php if ($isOwner): ?>
                <div class="article-owner-actions-top">
                    <a href="editor.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary btn-sm">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                        <span>Edit story</span>
                    </a>
                    <form action="delete-post.php" method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                        <button type="submit" class="btn btn-danger-ghost btn-sm btn-delete-trigger" aria-label="Delete this story">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                                <polyline points="3 6 5 6 21 6"/>
                                <path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>
                            </svg>
                            <span>Delete</span>
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <!-- Article Header -->
        <header class="article-hero-header">
            <h1 class="article-main-title"><?php echo sanitize($article['title']); ?></h1>

            <div class="article-author-byline">
                <span class="user-avatar user-avatar--lg" style="background-color: <?php echo avatarColor($article['author_name']); ?>;">
                    <?php echo avatarLetter($article['author_name']); ?>
                </span>
                <div class="byline-meta-details">
                    <div class="byline-author-row">
                        <strong class="byline-author-name"><?php echo sanitize($article['author_name']); ?></strong>
                    </div>
                    <div class="byline-date-row">
                        <time datetime="<?php echo date('Y-m-d', strtotime($article['created_at'])); ?>">
                            Published on <?php echo formatDateFull($article['created_at']); ?>
                        </time>
                        <span class="meta-dot">&middot;</span>
                        <span class="reading-time"><?php echo estimateReadingTime($article['content']); ?></span>
                        
                        <?php if ($article['updated_at'] !== $article['created_at']): ?>
                            <span class="meta-dot">&middot;</span>
                            <span class="updated-badge">Updated <?php echo formatDate($article['updated_at']); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </header>

        <!-- Article Cover Photo Banner (if available) -->
        <?php if (!empty($article['cover_image'])): ?>
            <div class="article-cover-banner">
                <img src="<?php echo sanitize($article['cover_image']); ?>" 
                     alt="<?php echo sanitize($article['title']); ?>" 
                     class="article-cover-image"
                     loading="lazy">
            </div>
        <?php endif; ?>

        <!-- Article Body Content (Markdown rendered on client-side with marked.js) -->
        <article class="article-body-content markdown-render" data-content="<?php echo htmlspecialchars($article['content'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php echo nl2br(sanitize($article['content'])); ?>
        </article>

        <!-- Author Signature Footer Card -->
        <div class="article-author-bio-card">
            <span class="user-avatar user-avatar--xl" style="background-color: <?php echo avatarColor($article['author_name']); ?>;">
                <?php echo avatarLetter($article['author_name']); ?>
            </span>
            <div class="author-bio-info">
                <span class="author-bio-subtitle">Written by</span>
                <h3 class="author-bio-name"><?php echo sanitize($article['author_name']); ?></h3>
                <p class="author-bio-desc">
                    Writer and contributor on Blog99. Sharing stories, engineering thoughts, and perspectives.
                </p>
            </div>
        </div>

        <!-- Bottom Action Toolbar for Author -->
        <?php if ($isOwner): ?>
            <div class="article-bottom-actions">
                <span class="actions-label">Manage your story:</span>
                <div class="actions-group">
                    <a href="editor.php?id=<?php echo $article['id']; ?>" class="btn btn-secondary btn-sm">Edit story</a>
                    <form action="delete-post.php" method="POST" class="inline-form">
                        <input type="hidden" name="id" value="<?php echo $article['id']; ?>">
                        <button type="submit" class="btn btn-danger-ghost btn-sm btn-delete-trigger">Delete story</button>
                    </form>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
