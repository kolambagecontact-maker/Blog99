<?php
// logout.php — Destroy session and redirect to homepage

require_once __DIR__ . '/includes/auth.php';

// Clear all session data
$_SESSION = [];

// Delete the session cookie
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(), '', time() - 42000,
        $params['path'], $params['domain'],
        $params['secure'], $params['httponly']
    );
}

session_destroy();

// Start a fresh session to set the flash message
session_start();
$_SESSION['flash_success'] = 'You have been signed out successfully.';

header('Location: index.php');
exit;
