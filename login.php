<?php
// login.php — Modern User Authentication for Blog99.

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Already logged in — go home
if (isLoggedIn()) {
    redirect('index.php');
}

$errors = [];
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email)) {
        $errors[] = 'Please enter your email address.';
    }
    if (empty($password)) {
        $errors[] = 'Please enter your password.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT * FROM user WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Login successful — create session
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];

            setFlashSuccess('Welcome back, ' . sanitize($user['username']) . '!');
            redirect('index.php');
        } else {
            $errors[] = 'Incorrect email address or password.';
        }
    }
}

$pageTitle = 'Sign in';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card-container">
        
        <!-- Left / Brand Intro Pane -->
        <div class="auth-brand-side">
            <div class="auth-brand-content">
                <span class="auth-wordmark">Blog99<span class="brand-dot">.</span></span>
                <h2 class="auth-brand-quote">
                    "Ideas deserve a place to live and grow."
                </h2>
                <p class="auth-brand-sub">
                    Join an authentic community of writers, developers, and thinkers sharing stories that inspire.
                </p>
            </div>
        </div>

        <!-- Right / Form Pane -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <h1 class="auth-form-title">Welcome back</h1>
                <p class="auth-form-sub">Sign in to continue writing and managing your stories.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="toast-alert toast-alert--error" style="margin-bottom: 20px;">
                    <div class="toast-alert-content">
                        <ul class="error-list">
                            <?php foreach ($errors as $err): ?>
                                <li><?php echo sanitize($err); ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input"
                           value="<?php echo sanitize($email); ?>"
                           placeholder="you@example.com" 
                           required 
                           autocomplete="email">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input"
                           placeholder="Enter your password" 
                           required 
                           autocomplete="current-password">
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg auth-submit-btn">
                    <span>Sign in to Blog99</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-arrow">
                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </form>

            <div class="auth-footer-prompt">
                <span>New to Blog99?</span>
                <a href="register.php" class="auth-link-bold">Create an account</a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
