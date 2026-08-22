<?php
// delete-post.php — Handles article deletion via POST
// Verifies ownership before deleting. Never uses GET for destructive actions.

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Must be logged in
requireLogin();

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('index.php');
}

$postId = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

if (!$postId) {
    setFlashError('Invalid article request.');
    redirect('index.php');
}

// Fetch the article to verify ownership
$stmt = $pdo->prepare('SELECT user_id FROM blogPost WHERE id = :id LIMIT 1');
$stmt->execute(['id' => $postId]);
$article = $stmt->fetch();

if (!$article) {
    setFlashError('Article not found.');
    redirect('index.php');
}

// Ownership verification — reject if user does not own this article
if ($article['user_id'] != currentUserId()) {
    setFlashError('You are not authorized to delete this story.');
    redirect('index.php');
}

// Delete the article
$stmt = $pdo->prepare('DELETE FROM blogPost WHERE id = :id AND user_id = :user_id');
$stmt->execute([
    'id'      => $postId,
    'user_id' => currentUserId(),
]);

setFlashSuccess('Story deleted successfully.');
redirect('my-posts.php');
