<?php
// editor.php — Distraction-Free Story Editor with Local Cover Photo Upload Support

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Must be logged in to write
requireLogin();

$errors     = [];
$title      = '';
$coverImage = '';
$content    = '';
$isEditMode = false;
$postId     = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// --- EDIT MODE: Load existing article ---
if ($postId) {
    $stmt = $pdo->prepare('SELECT * FROM blogPost WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => $postId]);
    $existingPost = $stmt->fetch();

    if (!$existingPost) {
        setFlashError('Story not found.');
        redirect('index.php');
    }

    // Ownership check — only the author can edit their article
    if ($existingPost['user_id'] != currentUserId()) {
        setFlashError('You are not authorized to edit this story.');
        redirect('index.php');
    }

    $isEditMode = true;
    $title      = $existingPost['title'];
    $coverImage = $existingPost['cover_image'] ?? '';
    $content    = $existingPost['content'];
}

// --- HANDLE FORM SUBMISSION (Create or Update) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title        = trim($_POST['title'] ?? '');
    $content      = trim($_POST['content'] ?? '');
    $submittedId  = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT);
    $existingCover = trim($_POST['existing_cover'] ?? '');
    $removeCover  = ($_POST['remove_cover'] ?? '0') === '1';

    // Start with current/existing cover
    $finalCoverImage = $existingCover;
    if ($removeCover) {
        $finalCoverImage = null;
    }

    // Validation
    if (empty($title)) {
        $errors[] = 'Please give your story a title.';
    }
    if (empty($content)) {
        $errors[] = 'Your story content cannot be empty.';
    }

    // --- Process Local File Upload ---
    if (isset($_FILES['cover_file']) && $_FILES['cover_file']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['cover_file']['tmp_name'];
        $fileName    = $_FILES['cover_file']['name'];
        $fileSize    = $_FILES['cover_file']['size'];

        $allowedExtensions = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileSize > 5 * 1024 * 1024) {
            $errors[] = 'Cover image must be under 5MB.';
        } elseif (!in_array($fileExtension, $allowedExtensions)) {
            $errors[] = 'Invalid image format. Please upload a JPG, PNG, WEBP, or GIF.';
        } else {
            // Verify file is a genuine image
            $imageCheck = @getimagesize($fileTmpPath);
            if ($imageCheck === false) {
                $errors[] = 'The uploaded file is not a valid image.';
            } else {
                $uploadDir = __DIR__ . '/uploads/covers/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $newFileName = 'cover_' . time() . '_' . bin2hex(random_bytes(6)) . '.' . $fileExtension;
                $destPath = $uploadDir . $newFileName;

                if (move_uploaded_file($fileTmpPath, $destPath)) {
                    $finalCoverImage = 'uploads/covers/' . $newFileName;

                    // Sync to XAMPP htdocs mirror if directory exists
                    $xamppUploads = 'C:/xampp/htdocs/inkwell/uploads/covers/';
                    if (is_dir($xamppUploads)) {
                        @copy($destPath, $xamppUploads . $newFileName);
                    }
                } else {
                    $errors[] = 'Could not save the uploaded image. Please check directory permissions.';
                }
            }
        }
    }

    // If updating, verify ownership again
    if ($submittedId) {
        $stmt = $pdo->prepare('SELECT user_id FROM blogPost WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $submittedId]);
        $check = $stmt->fetch();

        if (!$check || $check['user_id'] != currentUserId()) {
            $errors[] = 'You are not authorized to update this story.';
        }
    }

    if (empty($errors)) {
        if ($submittedId) {
            // UPDATE existing article with local cover image
            $stmt = $pdo->prepare('
                UPDATE blogPost
                SET title = :title, cover_image = :cover_image, content = :content, updated_at = NOW()
                WHERE id = :id AND user_id = :user_id
            ');
            $stmt->execute([
                'title'       => $title,
                'cover_image' => !empty($finalCoverImage) ? $finalCoverImage : null,
                'content'     => $content,
                'id'          => $submittedId,
                'user_id'     => currentUserId(),
            ]);
            setFlashSuccess('Story updated successfully.');
            redirect('article.php?id=' . $submittedId);
        } else {
            // INSERT new article with local cover image
            $stmt = $pdo->prepare('
                INSERT INTO blogPost (user_id, title, cover_image, content, created_at, updated_at)
                VALUES (:user_id, :title, :cover_image, :content, NOW(), NOW())
            ');
            $stmt->execute([
                'user_id'     => currentUserId(),
                'title'       => $title,
                'cover_image' => !empty($finalCoverImage) ? $finalCoverImage : null,
                'content'     => $content,
            ]);
            $newId = $pdo->lastInsertId();
            setFlashSuccess('Story published successfully.');
            redirect('article.php?id=' . $newId);
        }
    }

    // Preserve edit mode on error
    if ($submittedId) {
        $isEditMode = true;
        $postId     = $submittedId;
    }
    $coverImage = $finalCoverImage;
}

$pageTitle = $isEditMode ? 'Edit story — ' . $title : 'Write a story';
include __DIR__ . '/includes/header.php';
?>

<div class="editor-workspace">
    <!-- Editor Sticky Top Header -->
    <div class="editor-action-bar">
        <div class="editor-action-bar-inner">
            <div class="editor-action-left">
                <a href="<?php echo $isEditMode ? 'article.php?id=' . $postId : 'index.php'; ?>" class="btn btn-ghost btn-sm">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Back</span>
                </a>
                <span class="editor-mode-indicator">
                    <?php echo $isEditMode ? 'Editing story' : 'Draft in ' . sanitize(currentUsername()); ?>
                </span>
            </div>

            <div class="editor-action-right">
                <button type="submit" form="story-editor-form" class="btn btn-primary btn-sm editor-publish-btn">
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-icon" aria-hidden="true">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                    </svg>
                    <span><?php echo $isEditMode ? 'Save changes' : 'Publish story'; ?></span>
                </button>
            </div>
        </div>
    </div>

    <!-- Main Editor Writing Area -->
    <div class="editor-container">
        <?php if (!empty($errors)): ?>
            <div class="toast-alert toast-alert--error" style="margin-bottom: 24px;">
                <div class="toast-alert-content">
                    <ul class="error-list">
                        <?php foreach ($errors as $err): ?>
                            <li><?php echo sanitize($err); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        <?php endif; ?>

        <form id="story-editor-form" 
              action="editor.php<?php echo $postId ? '?id=' . $postId : ''; ?>" 
              method="POST" 
              enctype="multipart/form-data">
            
            <?php if ($isEditMode): ?>
                <input type="hidden" name="post_id" value="<?php echo $postId; ?>">
            <?php endif; ?>

            <!-- Hidden Inputs to preserve or remove cover image -->
            <input type="hidden" name="existing_cover" id="existing_cover" value="<?php echo sanitize($coverImage); ?>">
            <input type="hidden" name="remove_cover" id="remove_cover" value="0">

            <!-- Story Title Input -->
            <div class="editor-title-group">
                <textarea name="title" 
                          id="editor-title" 
                          class="editor-title-input" 
                          rows="1"
                          placeholder="Give your story a title..." 
                          required><?php echo sanitize($title); ?></textarea>
            </div>

            <!-- Local Cover Photo Upload Control -->
            <div class="editor-cover-upload-section">
                <!-- Hidden native file input -->
                <input type="file" 
                       name="cover_file" 
                       id="cover_file" 
                       accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" 
                       class="cover-file-input">

                <!-- Upload Dropzone / Trigger Area (shown when no cover image is selected) -->
                <div class="cover-dropzone <?php echo !empty($coverImage) ? 'hide' : ''; ?>" id="cover-dropzone">
                    <div class="cover-dropzone-content">
                        <div class="cover-dropzone-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2"/>
                                <circle cx="8.5" cy="8.5" r="1.5"/>
                                <polyline points="21 15 16 10 5 21"/>
                            </svg>
                        </div>
                        <div class="cover-dropzone-text">
                            <span class="cover-dropzone-title">Upload cover photo from your computer</span>
                            <span class="cover-dropzone-subtitle">Click to browse or drag & drop (JPG, PNG, WEBP, GIF — max 5MB)</span>
                        </div>
                    </div>
                </div>

                <!-- Cover Photo Live Preview Area (shown when cover image exists or is selected) -->
                <div class="cover-preview-card <?php echo empty($coverImage) ? 'hide' : ''; ?>" id="cover-preview-box">
                    <div class="cover-preview-img-wrapper">
                        <img src="<?php echo sanitize($coverImage); ?>" alt="Cover preview" id="cover-preview-img">
                    </div>
                    <div class="cover-preview-toolbar">
                        <span class="cover-status-badge">Cover Photo</span>
                        <div class="cover-action-buttons">
                            <button type="button" class="btn btn-secondary btn-sm" id="cover-change-btn">Change image</button>
                            <button type="button" class="btn btn-danger-ghost btn-sm" id="cover-remove-btn">Remove</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formatting Toolbar -->
            <div class="editor-toolbar" id="editor-toolbar" role="toolbar" aria-label="Markdown formatting tools">
                <button type="button" class="toolbar-btn" data-action="heading" title="Heading (H2)">
                    <span>H</span>
                </button>
                <button type="button" class="toolbar-btn" data-action="bold" title="Bold text">
                    <strong>B</strong>
                </button>
                <button type="button" class="toolbar-btn" data-action="italic" title="Italic text">
                    <em>I</em>
                </button>
                <span class="toolbar-divider"></span>
                <button type="button" class="toolbar-btn" data-action="link" title="Insert Link">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="toolbar-icon">
                        <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/>
                        <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>
                    </svg>
                </button>
                <button type="button" class="toolbar-btn" data-action="ul" title="Bullet list">
                    <span>&bull; List</span>
                </button>
                <button type="button" class="toolbar-btn" data-action="ol" title="Numbered list">
                    <span>1. List</span>
                </button>
                <span class="toolbar-divider"></span>
                <button type="button" class="toolbar-btn" data-action="blockquote" title="Quote block">
                    <svg viewBox="0 0 24 24" fill="currentColor" class="toolbar-icon">
                        <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                    </svg>
                </button>
                <button type="button" class="toolbar-btn" data-action="code" title="Code snippet">
                    <span>&lt;/&gt;</span>
                </button>
            </div>

            <!-- Story Body Textarea -->
            <div class="editor-textarea-wrapper">
                <textarea name="content" 
                          id="editor-content" 
                          class="editor-textarea" 
                          placeholder="Tell your story... (Markdown supported)" 
                          required><?php echo sanitize($content); ?></textarea>
            </div>
        </form>

        <!-- Live Rendered Markdown Preview Section -->
        <div class="editor-preview-container">
            <div class="editor-preview-header">
                <span class="preview-tag">Live Preview</span>
                <span class="preview-note">Updates automatically as you write</span>
            </div>
            <div id="editor-preview" class="article-body-content markdown-render">
                <p class="preview-empty-notice">Your story preview will appear here...</p>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
