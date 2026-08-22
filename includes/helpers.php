<?php
// includes/helpers.php
// General utility functions used across the application

/**
 * Escape output for safe HTML rendering (prevents XSS)
 */
function sanitize($text) {
    return htmlspecialchars(trim((string)$text), ENT_QUOTES, 'UTF-8');
}

/**
 * Estimate reading time based on word count.
 * Average reading speed: ~200 words per minute.
 */
function estimateReadingTime($content) {
    $wordCount = str_word_count(strip_tags((string)$content));
    $minutes = max(1, ceil($wordCount / 200));
    return $minutes . ' min read';
}

/**
 * Create a short text excerpt from article content.
 * Strips markdown-style formatting characters before truncating.
 */
function createExcerpt($content, $maxLength = 190) {
    // Remove code blocks and common markdown symbols
    $clean = preg_replace('/```[\s\S]*?```/', '', (string)$content);
    $clean = preg_replace('/[#*`_>~\[\]\(\)]/', '', $clean);
    $clean = preg_replace('/\s+/', ' ', trim($clean));

    if (mb_strlen($clean, 'UTF-8') <= $maxLength) {
        return $clean;
    }

    // Cut at the last space before the limit to avoid breaking words
    $excerpt = mb_substr($clean, 0, $maxLength, 'UTF-8');
    $lastSpace = mb_strrpos($excerpt, ' ', 0, 'UTF-8');
    if ($lastSpace !== false) {
        $excerpt = mb_substr($excerpt, 0, $lastSpace, 'UTF-8');
    }

    return $excerpt . '...';
}

/**
 * Format a database timestamp into a friendly readable date.
 * Example: "Aug 22, 2026"
 */
function formatDate($timestamp) {
    return date('M j, Y', strtotime($timestamp));
}

/**
 * Format a database timestamp into a full date.
 * Example: "August 22, 2026"
 */
function formatDateFull($timestamp) {
    return date('F j, Y', strtotime($timestamp));
}

/**
 * Generate a letter-avatar initial from a username.
 * Returns the uppercase first letter.
 */
function avatarLetter($username) {
    $trimmed = trim((string)$username);
    return !empty($trimmed) ? strtoupper(substr($trimmed, 0, 1)) : 'U';
}

/**
 * Generate a consistent HSL background color from a username string.
 * Uses a refined, sophisticated color palette.
 */
function avatarColor($username) {
    $colors = [
        '#2563eb', // Royal Blue
        '#059669', // Emerald
        '#7c3aed', // Purple
        '#db2777', // Pink
        '#d97706', // Amber
        '#0891b2', // Cyan
        '#4f46e5', // Indigo
        '#dc2626', // Red
    ];
    $index = abs(crc32((string)$username)) % count($colors);
    return $colors[$index];
}
