/**
 * Blog99. — Core Client-Side Interactions
 * Clean, lightweight Vanilla JavaScript for:
 * 1. Dark Mode / Light Mode Theme Switching
 * 2. Local Story Cover Photo Upload & Live Preview
 * 3. Markdown Parsing & Rendering (Marked.js)
 * 4. Real-time Editor Live Preview & Auto-resizing
 * 5. Markdown Formatting Toolbar Shortcuts
 * 6. User Profile Dropdown Menu & Click-Outside Handling
 * 7. Mobile Search Drawer Toggle
 * 8. Delete Confirmation Modal
 */

document.addEventListener('DOMContentLoaded', function () {
    initThemeToggle();
    initCoverImageControl();
    initMarkdownRendering();
    initEditorPreview();
    initEditorToolbar();
    initProfileDropdown();
    initMobileSearch();
    initDeleteModal();
});

// ==========================================================
// 1. DARK MODE / LIGHT MODE THEME SWITCHER
// ==========================================================
function initThemeToggle() {
    const toggleBtn = document.getElementById('theme-toggle-btn');
    if (!toggleBtn) return;

    toggleBtn.addEventListener('click', function () {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';

        document.documentElement.setAttribute('data-theme', newTheme);
        try {
            localStorage.setItem('blog99_theme', newTheme);
        } catch (e) {}
    });
}

// ==========================================================
// 2. LOCAL STORY COVER PHOTO CONTROLLER
// ==========================================================
function initCoverImageControl() {
    const fileInput     = document.getElementById('cover_file');
    const dropzone      = document.getElementById('cover-dropzone');
    const previewBox    = document.getElementById('cover-preview-box');
    const previewImg    = document.getElementById('cover-preview-img');
    const changeBtn     = document.getElementById('cover-change-btn');
    const removeBtn     = document.getElementById('cover-remove-btn');
    const existingInput = document.getElementById('existing_cover');
    const removeInput   = document.getElementById('remove_cover');

    if (!fileInput || !dropzone || !previewBox || !previewImg) return;

    // Trigger file selection on dropzone click
    dropzone.addEventListener('click', function () {
        fileInput.click();
    });

    // Drag and Drop support
    ['dragenter', 'dragover'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.add('cover-dropzone--dragover');
        });
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropzone.addEventListener(eventName, function (e) {
            e.preventDefault();
            e.stopPropagation();
            dropzone.classList.remove('cover-dropzone--dragover');
        });
    });

    dropzone.addEventListener('drop', function (e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files && files.length > 0) {
            fileInput.files = files;
            handleFileSelect(files[0]);
        }
    });

    // Native file input change
    fileInput.addEventListener('change', function () {
        if (fileInput.files && fileInput.files.length > 0) {
            handleFileSelect(fileInput.files[0]);
        }
    });

    function handleFileSelect(file) {
        if (!file.type.startsWith('image/')) {
            alert('Please choose a valid image file (JPG, PNG, WEBP, or GIF).');
            return;
        }

        const objectUrl = URL.createObjectURL(file);
        previewImg.src = objectUrl;
        previewBox.classList.remove('hide');
        dropzone.classList.add('hide');

        if (removeInput) removeInput.value = '0';
    }

    // Change image button
    if (changeBtn) {
        changeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fileInput.click();
        });
    }

    // Remove image button
    if (removeBtn) {
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            fileInput.value = '';
            previewImg.src = '';
            previewBox.classList.add('hide');
            dropzone.classList.remove('hide');

            if (existingInput) existingInput.value = '';
            if (removeInput) removeInput.value = '1';
        });
    }
}

// ==========================================================
// 3. MARKDOWN RENDERING
// ==========================================================
function initMarkdownRendering() {
    if (typeof marked === 'undefined') return;

    marked.setOptions({
        gfm: true,
        breaks: true,
        headerIds: true
    });

    const elements = document.querySelectorAll('.markdown-render');
    elements.forEach(function (el) {
        const rawContent = el.getAttribute('data-content');
        if (rawContent !== null && rawContent !== '') {
            el.innerHTML = marked.parse(rawContent);
        }
    });
}

// ==========================================================
// 4. EDITOR LIVE PREVIEW & AUTO-RESIZING
// ==========================================================
function initEditorPreview() {
    const titleInput = document.getElementById('editor-title');
    const contentInput = document.getElementById('editor-content');
    const previewContainer = document.getElementById('editor-preview');

    if (!contentInput || !previewContainer || typeof marked === 'undefined') return;

    function updatePreview() {
        const text = contentInput.value.trim();
        if (!text) {
            previewContainer.innerHTML = '<p class="preview-empty-notice">Your story preview will appear here...</p>';
            return;
        }
        previewContainer.innerHTML = marked.parse(text);
    }

    // Auto-resize title textarea as user types
    function autoResizeTitle() {
        if (!titleInput) return;
        titleInput.style.height = 'auto';
        titleInput.style.height = titleInput.scrollHeight + 'px';
    }

    contentInput.addEventListener('input', updatePreview);
    
    if (titleInput) {
        titleInput.addEventListener('input', autoResizeTitle);
        autoResizeTitle();
    }

    // Initial render on page load (edit mode)
    updatePreview();
}

// ==========================================================
// 5. EDITOR FORMATTING TOOLBAR
// ==========================================================
function initEditorToolbar() {
    const toolbar = document.getElementById('editor-toolbar');
    const textarea = document.getElementById('editor-content');

    if (!toolbar || !textarea) return;

    toolbar.addEventListener('click', function (e) {
        const btn = e.target.closest('.toolbar-btn');
        if (!btn) return;

        const action = btn.getAttribute('data-action');
        if (action) {
            applyFormatting(textarea, action);
        }
    });
}

function applyFormatting(textarea, action) {
    const start = textarea.selectionStart;
    const end = textarea.selectionEnd;
    const text = textarea.value;
    const selected = text.substring(start, end);
    const before = text.substring(0, start);
    const after = text.substring(end);
    let insertion = '';
    let cursorOffset = 0;

    switch (action) {
        case 'heading':
            insertion = '\n## ' + (selected || 'Heading Title') + '\n';
            cursorOffset = insertion.length;
            break;
        case 'bold':
            insertion = '**' + (selected || 'bold text') + '**';
            cursorOffset = selected ? insertion.length : 2;
            break;
        case 'italic':
            insertion = '*' + (selected || 'italic text') + '*';
            cursorOffset = selected ? insertion.length : 1;
            break;
        case 'link':
            if (selected) {
                insertion = '[' + selected + '](https://example.com)';
            } else {
                insertion = '[Link text](https://example.com)';
            }
            cursorOffset = insertion.length;
            break;
        case 'ul':
            insertion = '\n* ' + (selected || 'List item') + '\n';
            cursorOffset = insertion.length;
            break;
        case 'ol':
            insertion = '\n1. ' + (selected || 'List item') + '\n';
            cursorOffset = insertion.length;
            break;
        case 'blockquote':
            insertion = '\n> ' + (selected || 'Quote or thought here...') + '\n';
            cursorOffset = insertion.length;
            break;
        case 'code':
            if (selected && selected.includes('\n')) {
                insertion = '\n```javascript\n' + selected + '\n```\n';
            } else {
                insertion = '`' + (selected || 'code') + '`';
            }
            cursorOffset = selected ? insertion.length : 1;
            break;
        default:
            return;
    }

    textarea.value = before + insertion + after;
    const newPos = start + cursorOffset;
    textarea.setSelectionRange(newPos, newPos);
    textarea.focus();

    // Trigger preview update event
    textarea.dispatchEvent(new Event('input'));
}

// ==========================================================
// 6. USER PROFILE DROPDOWN MENU
// ==========================================================
function initProfileDropdown() {
    const trigger = document.getElementById('profile-dropdown-trigger');
    const dropdown = document.getElementById('profile-dropdown');
    const menu = document.getElementById('profile-dropdown-menu');

    if (!trigger || !dropdown || !menu) return;

    trigger.addEventListener('click', function (e) {
        e.stopPropagation();
        const isOpen = dropdown.classList.contains('active');
        if (isOpen) {
            dropdown.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
        } else {
            dropdown.classList.add('active');
            trigger.setAttribute('aria-expanded', 'true');
        }
    });

    // Close on click outside
    document.addEventListener('click', function (e) {
        if (!dropdown.contains(e.target)) {
            dropdown.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
        }
    });

    // Close on Escape key
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && dropdown.classList.contains('active')) {
            dropdown.classList.remove('active');
            trigger.setAttribute('aria-expanded', 'false');
            trigger.focus();
        }
    });
}

// ==========================================================
// 7. MOBILE SEARCH DRAWER TOGGLE
// ==========================================================
function initMobileSearch() {
    const toggleBtn = document.getElementById('mobile-search-toggle');
    const drawer = document.getElementById('mobile-search-drawer');

    if (!toggleBtn || !drawer) return;

    toggleBtn.addEventListener('click', function () {
        drawer.classList.toggle('active');
        if (drawer.classList.contains('active')) {
            const input = drawer.querySelector('.search-input');
            if (input) input.focus();
        }
    });
}

// ==========================================================
// 8. DELETE CONFIRMATION MODAL
// ==========================================================
let pendingDeleteForm = null;

function initDeleteModal() {
    const modal = document.getElementById('delete-modal');
    const cancelBtn = document.getElementById('modal-cancel');
    const confirmBtn = document.getElementById('modal-confirm');

    if (!modal) return;

    document.querySelectorAll('.btn-delete-trigger').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            pendingDeleteForm = btn.closest('form');
            modal.classList.add('active');
            document.body.style.overflow = 'hidden';
            if (cancelBtn) cancelBtn.focus();
        });
    });

    function closeModal() {
        modal.classList.remove('active');
        document.body.style.overflow = '';
        pendingDeleteForm = null;
    }

    if (cancelBtn) {
        cancelBtn.addEventListener('click', closeModal);
    }

    if (confirmBtn) {
        confirmBtn.addEventListener('click', function () {
            if (pendingDeleteForm) {
                pendingDeleteForm.submit();
            }
        });
    }

    modal.addEventListener('click', function (e) {
        if (e.target === modal) {
            closeModal();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape' && modal.classList.contains('active')) {
            closeModal();
        }
    });
}
