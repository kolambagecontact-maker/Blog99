<?php
// includes/footer.php
// Shared Footer, Confirmation Modals, and Client Scripts for Blog99.
?>

    </div><!-- /.site-container -->
</main>

<!-- Delete Confirmation Modal Component -->
<div class="modal-backdrop" id="delete-modal" role="dialog" aria-modal="true" aria-labelledby="modal-title" aria-describedby="modal-description">
    <div class="modal-dialog">
        <div class="modal-header">
            <div class="modal-icon-badge">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/>
                    <line x1="12" y1="9" x2="12" y2="13"/>
                    <line x1="12" y1="17" x2="12.01" y2="17"/>
                </svg>
            </div>
            <div>
                <h3 class="modal-title" id="modal-title">Delete this story?</h3>
                <p class="modal-desc" id="modal-description">
                    This action cannot be undone. Your story and all of its content will be permanently removed from Blog99.
                </p>
            </div>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn btn-secondary btn-sm" id="modal-cancel">Cancel</button>
            <button type="button" class="btn btn-danger btn-sm" id="modal-confirm">Delete story</button>
        </div>
    </div>
</div>

<!-- Site Footer -->
<footer class="site-footer">
    <div class="footer-container">
        <div class="footer-top-row">
            <div class="footer-brand-column">
                <a href="index.php" class="brand-logo brand-logo--footer">
                    <span class="brand-name">Blog99<span class="brand-dot">.</span></span>
                </a>
                <p class="footer-tagline">
                    A modern, content-focused publishing platform for ideas, technical insights, and stories worth discovering.
                </p>
            </div>

            <div class="footer-nav-column">
                <span class="footer-heading">Navigation</span>
                <ul class="footer-nav-list">
                    <li><a href="index.php">Home</a></li>
                    <?php if (isLoggedIn()): ?>
                        <li><a href="editor.php">Write a Story</a></li>
                        <li><a href="my-posts.php">My Stories</a></li>
                        <li><a href="logout.php">Sign out</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Sign in</a></li>
                        <li><a href="register.php">Get started</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="footer-bottom-row">
            <p class="footer-academic">
                IN2120 &mdash; Web Programming Take Home Assignment &middot; University of Moratuwa &middot; Faculty of Information Technology
            </p>
            <p class="footer-copyright">
                &copy; <?php echo date('Y'); ?> Blog99. All rights reserved.
            </p>
        </div>
    </div>
</footer>

<!-- Marked.js for Fast Client-Side Markdown Parsing -->
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<!-- Custom App JavaScript -->
<script src="assets/js/app.js"></script>

</body>
</html>
