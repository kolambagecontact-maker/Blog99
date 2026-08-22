<?php
// includes/auth.php
// Authentication and session helper functions

// Start session if not already active
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if a user is currently logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Get the currently logged-in user's ID
 */
function currentUserId() {
    return $_SESSION['user_id'] ?? null;
}

/**
 * Get the currently logged-in user's username
 */
function currentUsername() {
    return $_SESSION['username'] ?? null;
}

/**
 * Redirect to login page if user is not authenticated.
 * Used to protect pages that require login (editor, my-posts, etc.)
 */
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['flash_error'] = 'You must sign in to access this page.';
        redirect('login.php');
    }
}

/**
 * Redirect to a given URL and stop script execution
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set a success flash message (displayed once on next page load)
 */
function setFlashSuccess($message) {
    $_SESSION['flash_success'] = $message;
}

/**
 * Set an error flash message (displayed once on next page load)
 */
function setFlashError($message) {
    $_SESSION['flash_error'] = $message;
}
