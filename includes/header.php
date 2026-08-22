<?php
// includes/header.php
// Shared HTML head, navigation bar, and dark mode support for Blog99.

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/helpers.php';

$currentPage = basename($_SERVER['PHP_SELF']);
$searchQuery = trim($_GET['q'] ?? '');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? sanitize($pageTitle) . ' — Blog99.' : 'Blog99. — Ideas Worth Sharing'; ?></title>
    <meta name="description" content="A modern publishing platform for sharing stories, perspectives, and ideas.">
    
    <!-- Fast Theme Initialization (Prevents FOUC) -->
    <script>
        (function() {
            try {
                var savedTheme = localStorage.getItem('blog99_theme');
                if (savedTheme) {
                    document.documentElement.setAttribute('data-theme', savedTheme);
                } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                    document.documentElement.setAttribute('data-theme', 'dark');
                }
            } catch (e) {}
        })();
    </script>
    
    <!-- Google Fonts: Inter for UI & Playfair / Newsreader for Editorial Headlines -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Newsreader:ital,opsz,wght@0,6..72,400;0,6..72,600;0,6..72,700;1,6..72,400&display=swap" rel="stylesheet">
    
    <!-- Core Application Stylesheet -->
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>

<!-- Navigation Bar -->
<header class="site-header">
    <div class="header-container">
        <!-- Left: Brand & Desktop Search -->
        <div class="header-left">
            <a href="index.php" class="brand-logo" aria-label="Blog99. Home">
                <span class="brand-name">Blog99<span class="brand-dot">.</span></span>
            </a>

            <!-- Search Form (Desktop) -->
            <form action="index.php" method="GET" class="header-search-form" role="search">
                <div class="search-input-wrapper">
                    <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <circle cx="8.5" cy="8.5" r="5.5"/>
                        <line x1="12.5" y1="12.5" x2="17" y2="17"/>
                    </svg>
                    <input type="search" 
                           name="q" 
                           value="<?php echo sanitize($searchQuery); ?>" 
                           placeholder="Search stories, topics, authors..." 
                           class="search-input"
                           autocomplete="off"
                           aria-label="Search stories">
                    <?php if (!empty($searchQuery)): ?>
                        <a href="index.php" class="search-clear-btn" title="Clear search" aria-label="Clear search">&times;</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Right: Actions, Theme Switcher & User Controls -->
        <nav class="header-nav" aria-label="Main Navigation">
            
            <!-- Dark Mode Toggle Button -->
            <button type="button" class="theme-toggle-btn" id="theme-toggle-btn" aria-label="Toggle dark/light mode" title="Toggle theme">
                <!-- Sun Icon (shown in dark mode) -->
                <svg class="theme-icon theme-icon--sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="5"/>
                    <line x1="12" y1="1" x2="12" y2="3"/>
                    <line x1="12" y1="21" x2="12" y2="23"/>
                    <line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/>
                    <line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/>
                    <line x1="1" y1="12" x2="3" y2="12"/>
                    <line x1="21" y1="12" x2="23" y2="12"/>
                    <line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/>
                    <line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/>
                </svg>
                <!-- Moon Icon (shown in light mode) -->
                <svg class="theme-icon theme-icon--moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>
                </svg>
            </button>

            <!-- Mobile Search Toggle Button -->
            <button type="button" class="icon-btn mobile-search-toggle" id="mobile-search-toggle" aria-label="Toggle search bar">
                <svg viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="8.5" cy="8.5" r="5.5"/>
                    <line x1="12.5" y1="12.5" x2="17" y2="17"/>
                </svg>
            </button>

            <?php if (isLoggedIn()): ?>
                <!-- Write Story Link (Primary Subtle Action) -->
                <a href="editor.php" class="nav-write-btn">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="write-icon" aria-hidden="true">
                        <path d="M12 20h9"/>
                        <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                    </svg>
                    <span>Write</span>
                </a>

                <a href="my-posts.php" class="nav-link nav-link--muted hide-mobile">My Stories</a>

                <!-- User Profile Dropdown -->
                <div class="profile-dropdown" id="profile-dropdown">
                    <button type="button" class="profile-trigger-btn" id="profile-dropdown-trigger" aria-expanded="false" aria-haspopup="true" aria-label="User account menu">
                        <span class="user-avatar" style="background-color: <?php echo avatarColor(currentUsername()); ?>;">
                            <?php echo avatarLetter(currentUsername()); ?>
                        </span>
                        <span class="user-name hide-mobile"><?php echo sanitize(currentUsername()); ?></span>
                        <svg class="chevron-icon hide-mobile" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <!-- Dropdown Menu -->
                    <div class="dropdown-menu" id="profile-dropdown-menu" role="menu">
                        <div class="dropdown-header">
                            <span class="dropdown-signed-in">Signed in as</span>
                            <strong class="dropdown-username">@<?php echo sanitize(currentUsername()); ?></strong>
                        </div>
                        <div class="dropdown-divider"></div>
                        <a href="editor.php" class="dropdown-item" role="menuitem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-item-icon">
                                <path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                            </svg>
                            <span>Write a Story</span>
                        </a>
                        <a href="my-posts.php" class="dropdown-item" role="menuitem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-item-icon">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/>
                            </svg>
                            <span>My Stories</span>
                        </a>
                        <div class="dropdown-divider"></div>
                        <a href="logout.php" class="dropdown-item dropdown-item--danger" role="menuitem">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="dropdown-item-icon">
                                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                                <polyline points="16 17 21 12 16 7"/>
                                <line x1="21" y1="12" x2="9" y2="12"/>
                            </svg>
                            <span>Sign out</span>
                        </a>
                    </div>
                </div>

            <?php else: ?>
                <!-- Guest Actions -->
                <a href="login.php" class="nav-link">Sign in</a>
                <a href="register.php" class="btn btn-primary btn-sm">Get started</a>
            <?php endif; ?>
        </nav>
    </div>

    <!-- Mobile Search Bar Expandable Drawer -->
    <div class="mobile-search-drawer" id="mobile-search-drawer">
        <form action="index.php" method="GET" class="mobile-search-form">
            <div class="search-input-wrapper">
                <svg class="search-icon" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8">
                    <circle cx="8.5" cy="8.5" r="5.5"/>
                    <line x1="12.5" y1="12.5" x2="17" y2="17"/>
                </svg>
                <input type="search" 
                       name="q" 
                       value="<?php echo sanitize($searchQuery); ?>" 
                       placeholder="Search stories, topics, authors..." 
                       class="search-input"
                       aria-label="Search stories">
                <?php if (!empty($searchQuery)): ?>
                    <a href="index.php" class="search-clear-btn" title="Clear search">&times;</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
</header>

<!-- Main Page Content Wrapper -->
<main class="main-content">
    <div class="site-container">

        <!-- Global Toast / Flash Notifications -->
        <?php if (isset($_SESSION['flash_success'])): ?>
            <div class="toast-alert toast-alert--success" role="alert">
                <div class="toast-alert-content">
                    <svg class="toast-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span><?php echo sanitize($_SESSION['flash_success']); ?></span>
                </div>
                <button type="button" class="toast-close-btn" onclick="this.closest('.toast-alert').remove()" aria-label="Dismiss alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_error'])): ?>
            <div class="toast-alert toast-alert--error" role="alert">
                <div class="toast-alert-content">
                    <svg class="toast-icon" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                    <span><?php echo sanitize($_SESSION['flash_error']); ?></span>
                </div>
                <button type="button" class="toast-close-btn" onclick="this.closest('.toast-alert').remove()" aria-label="Dismiss alert">&times;</button>
            </div>
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
