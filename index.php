<?php
// index.php — Homepage: Editorial Hero & Stories Card List Below

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

$searchQuery = trim($_GET['q'] ?? '');
$isSearching = !empty($searchQuery);

if ($isSearching) {
    // Perform search across title, content, and author username using PDO prepared statements
    $searchTerm = '%' . $searchQuery . '%';
    $stmt = $pdo->prepare('
        SELECT b.*, u.username AS author_name
        FROM blogPost b
        JOIN user u ON b.user_id = u.id
        WHERE b.title LIKE :q1 OR b.content LIKE :q2 OR u.username LIKE :q3
        ORDER BY b.created_at DESC
    ');
    $stmt->execute([
        'q1' => $searchTerm,
        'q2' => $searchTerm,
        'q3' => $searchTerm
    ]);
    $posts = $stmt->fetchAll();
    $pageTitle = 'Search: ' . $searchQuery;
} else {
    // Fetch all published blog posts with author information, newest first
    $stmt = $pdo->query('
        SELECT b.*, u.username AS author_name
        FROM blogPost b
        JOIN user u ON b.user_id = u.id
        ORDER BY b.created_at DESC
    ');
    $posts = $stmt->fetchAll();
    $pageTitle = 'Home — Stories Worth Discovering';
}

include __DIR__ . '/includes/header.php';
?>

<?php if ($isSearching): ?>
    <!-- =========================================================
         SEARCH RESULTS VIEW
         ========================================================= -->
    <div class="content-feed-layout search-results-container">
        <div class="search-results-header">
            <div class="search-results-meta">
                <span class="section-tag">Search Results</span>
                <h1 class="search-results-title">
                    Showing results for <span class="search-query-highlight">"<?php echo sanitize($searchQuery); ?>"</span>
                </h1>
                <p class="search-results-count">
                    <?php 
                    $resultCount = count($posts);
                    echo $resultCount . ' ' . ($resultCount === 1 ? 'story' : 'stories') . ' found'; 
                    ?>
                </p>
            </div>
            <a href="index.php" class="btn btn-secondary btn-sm clear-search-action">
                <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon" aria-hidden="true">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
                <span>Clear search</span>
            </a>
        </div>

        <?php if (empty($posts)): ?>
            <!-- No search results empty state -->
            <div class="empty-state-card">
                <div class="empty-state-icon-wrapper">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
                        <circle cx="11" cy="11" r="8"/>
                        <line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        <line x1="8" y1="11" x2="14" y2="11"/>
                    </svg>
                </div>
                <h2 class="empty-state-title">No matching stories found</h2>
                <p class="empty-state-desc">
                    We couldn't find any articles matching "<strong><?php echo sanitize($searchQuery); ?></strong>". Try searching for different keywords or explore our latest stories.
                </p>
                <div class="empty-state-actions">
                    <a href="index.php" class="btn btn-primary">Browse all stories</a>
                </div>
            </div>
        <?php else: ?>
            <!-- Search Results Feed -->
            <div class="stories-feed">
                <?php foreach ($posts as $post): ?>
                    <article class="story-preview-card <?php echo !empty($post['cover_image']) ? 'story-preview-card--has-cover' : ''; ?>">
                        <div class="story-preview-body">
                            <div class="story-preview-meta">
                                <span class="user-avatar user-avatar--sm" style="background-color: <?php echo avatarColor($post['author_name']); ?>;">
                                    <?php echo avatarLetter($post['author_name']); ?>
                                </span>
                                <span class="story-author-name"><?php echo sanitize($post['author_name']); ?></span>
                                <span class="meta-dot">&middot;</span>
                                <time class="story-date" datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                                    <?php echo formatDate($post['created_at']); ?>
                                </time>
                            </div>

                            <h2 class="story-preview-title">
                                <a href="article.php?id=<?php echo $post['id']; ?>">
                                    <?php echo sanitize($post['title']); ?>
                                </a>
                            </h2>

                            <p class="story-preview-excerpt">
                                <?php echo sanitize(createExcerpt($post['content'])); ?>
                            </p>

                            <div class="story-preview-footer">
                                <span class="reading-time-badge"><?php echo estimateReadingTime($post['content']); ?></span>
                                <a href="article.php?id=<?php echo $post['id']; ?>" class="read-more-link">
                                    <span>Read story</span>
                                    <svg viewBox="0 0 20 20" fill="currentColor" class="read-more-arrow" aria-hidden="true">
                                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                    </svg>
                                </a>
                            </div>
                        </div>

                        <?php if (!empty($post['cover_image'])): ?>
                            <div class="story-preview-thumbnail">
                                <a href="article.php?id=<?php echo $post['id']; ?>" tabindex="-1" aria-hidden="true">
                                    <img src="<?php echo sanitize($post['cover_image']); ?>" alt="<?php echo sanitize($post['title']); ?>" loading="lazy">
                                </a>
                            </div>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

<?php else: ?>
    <!-- =========================================================
         STANDARD HOMEPAGE: CLEAN EDITORIAL HERO & STORIES FEED BELOW
         ========================================================= -->
    
    <!-- Clean Hero Section -->
    <section class="editorial-hero">
        <div class="hero-content-centered">
            <span class="hero-badge">Modern Publishing</span>
            <h1 class="hero-headline">
                Ideas worth sharing.<br>
                Stories worth discovering.
            </h1>
            <p class="hero-description">
                Read thoughtful perspectives, engineering practices, and insightful stories crafted by writers in our community.
            </p>
            <div class="hero-cta-group">
                <a href="#latest-stories" class="btn btn-primary btn-lg">Explore stories</a>
                <?php if (isLoggedIn()): ?>
                    <a href="editor.php" class="btn btn-secondary btn-lg">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="btn-icon">
                            <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                        </svg>
                        <span>Write a story</span>
                    </a>
                <?php else: ?>
                    <a href="register.php" class="btn btn-secondary btn-lg">Start writing</a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Content Feed Section (Below the Hero Section) -->
    <section class="content-feed-section" id="latest-stories">
        <div class="content-feed-layout">
            
            <div class="feed-section-header">
                <div>
                    <h2 class="feed-section-title">Latest Stories</h2>
                    <p class="feed-section-subtitle">Explore the newest perspectives published by our community.</p>
                </div>
                <div class="feed-count-badge">
                    <?php 
                    $totalStories = count($posts); 
                    echo $totalStories . ' ' . ($totalStories === 1 ? 'story' : 'stories');
                    ?>
                </div>
            </div>

            <?php if (empty($posts)): ?>
                <!-- Empty state -->
                <div class="empty-state-card">
                    <div class="empty-state-icon-wrapper">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" class="empty-state-icon">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="12" y1="18" x2="12" y2="12"/>
                            <line x1="9" y1="15" x2="15" y2="15"/>
                        </svg>
                    </div>
                    <h3 class="empty-state-title">No stories published yet</h3>
                    <p class="empty-state-desc">Be the first to share an inspiring idea, technical tutorial, or insightful article.</p>
                    <div class="empty-state-actions">
                        <?php if (isLoggedIn()): ?>
                            <a href="editor.php" class="btn btn-primary">Write the first story</a>
                        <?php else: ?>
                            <a href="register.php" class="btn btn-primary">Get started &amp; write</a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- Stories Card List -->
                <div class="stories-feed">
                    <?php foreach ($posts as $post): ?>
                        <article class="story-preview-card <?php echo !empty($post['cover_image']) ? 'story-preview-card--has-cover' : ''; ?>">
                            <div class="story-preview-body">
                                <div class="story-preview-meta">
                                    <span class="user-avatar user-avatar--sm" style="background-color: <?php echo avatarColor($post['author_name']); ?>;">
                                        <?php echo avatarLetter($post['author_name']); ?>
                                    </span>
                                    <span class="story-author-name"><?php echo sanitize($post['author_name']); ?></span>
                                    <span class="meta-dot">&middot;</span>
                                    <time class="story-date" datetime="<?php echo date('Y-m-d', strtotime($post['created_at'])); ?>">
                                        <?php echo formatDate($post['created_at']); ?>
                                    </time>
                                </div>

                                <h2 class="story-preview-title">
                                    <a href="article.php?id=<?php echo $post['id']; ?>">
                                        <?php echo sanitize($post['title']); ?>
                                    </a>
                                </h2>

                                <p class="story-preview-excerpt">
                                    <?php echo sanitize(createExcerpt($post['content'], 160)); ?>
                                </p>

                                <div class="story-preview-footer">
                                    <span class="reading-time-badge"><?php echo estimateReadingTime($post['content']); ?></span>
                                    <a href="article.php?id=<?php echo $post['id']; ?>" class="read-more-link">
                                        <span>Read story</span>
                                        <svg viewBox="0 0 20 20" fill="currentColor" class="read-more-arrow" aria-hidden="true">
                                            <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                                        </svg>
                                    </a>
                                </div>
                            </div>

                            <?php if (!empty($post['cover_image'])): ?>
                                <div class="story-preview-thumbnail">
                                    <a href="article.php?id=<?php echo $post['id']; ?>" tabindex="-1" aria-hidden="true">
                                        <img src="<?php echo sanitize($post['cover_image']); ?>" alt="<?php echo sanitize($post['title']); ?>" loading="lazy">
                                    </a>
                                </div>
                            <?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </section>
<?php endif; ?>

<?php include __DIR__ . '/includes/footer.php'; ?>
