<?php
// register.php — Modern User Registration for Blog99.

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

// Already logged in — go home
if (isLoggedIn()) {
    redirect('index.php');
}

$errors   = [];
$username = '';
$email    = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username         = trim($_POST['username'] ?? '');
    $email            = trim($_POST['email'] ?? '');
    $password         = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    // --- Validation ---
    if (empty($username)) {
        $errors[] = 'Username is required.';
    } elseif (strlen($username) < 3 || strlen($username) > 100) {
        $errors[] = 'Username must be between 3 and 100 characters.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Username can only contain letters, numbers, and underscores.';
    }

    if (empty($email)) {
        $errors[] = 'Email address is required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (empty($password)) {
        $errors[] = 'Password is required.';
    } elseif (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirm_password) {
        $errors[] = 'Passwords do not match.';
    }

    // Check for existing username or email using PDO prepared statements
    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id FROM user WHERE username = :username LIMIT 1');
        $stmt->execute(['username' => $username]);
        if ($stmt->fetch()) {
            $errors[] = 'This username is already taken. Please choose another.';
        }

        $stmt = $pdo->prepare('SELECT id FROM user WHERE email = :email LIMIT 1');
        $stmt->execute(['email' => $email]);
        if ($stmt->fetch()) {
            $errors[] = 'This email address is already registered. Please sign in.';
        }
    }

    // Create account
    if (empty($errors)) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $stmt = $pdo->prepare('
            INSERT INTO user (username, email, password, role) 
            VALUES (:username, :email, :password, :role)
        ');
        $stmt->execute([
            'username' => $username,
            'email'    => $email,
            'password' => $hashedPassword,
            'role'     => 'user',
        ]);

        // Auto-login after registration
        $_SESSION['user_id']  = $pdo->lastInsertId();
        $_SESSION['username'] = $username;
        $_SESSION['role']     = 'user';

        setFlashSuccess('Account created successfully! Welcome to Blog99.');
        redirect('index.php');
    }
}

$pageTitle = 'Create an account';
include __DIR__ . '/includes/header.php';
?>

<div class="auth-page-wrapper">
    <div class="auth-card-container">
        
        <!-- Left / Brand Intro Pane -->
        <div class="auth-brand-side">
            <div class="auth-brand-content">
                <span class="auth-wordmark">Blog99<span class="brand-dot">.</span></span>
                <h2 class="auth-brand-quote">
                    "Start your writing journey today."
                </h2>
                <p class="auth-brand-sub">
                    Create a free account to publish stories, read articles from other developers and writers, and build your voice.
                </p>
            </div>
        </div>

        <!-- Right / Form Pane -->
        <div class="auth-form-side">
            <div class="auth-form-header">
                <h1 class="auth-form-title">Create your account</h1>
                <p class="auth-form-sub">Sign up in seconds and start sharing your thoughts.</p>
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

            <form action="register.php" method="POST" autocomplete="off" class="auth-form">
                <div class="form-group">
                    <label for="username" class="form-label">Username</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input"
                           value="<?php echo sanitize($username); ?>"
                           placeholder="e.g. chamika_k" 
                           required>
                </div>

                <div class="form-group">
                    <label for="email" class="form-label">Email address</label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           class="form-input"
                           value="<?php echo sanitize($email); ?>"
                           placeholder="you@example.com" 
                           required>
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input"
                           placeholder="Minimum 6 characters" 
                           required>
                </div>

                <div class="form-group">
                    <label for="confirm_password" class="form-label">Confirm password</label>
                    <input type="password" 
                           id="confirm_password" 
                           name="confirm_password" 
                           class="form-input"
                           placeholder="Re-enter your password" 
                           required>
                </div>

                <button type="submit" class="btn btn-primary btn-full btn-lg auth-submit-btn">
                    <span>Create account &amp; get started</span>
                    <svg viewBox="0 0 20 20" fill="currentColor" class="btn-arrow">
                        <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                    </svg>
                </button>
            </form>

            <div class="auth-footer-prompt">
                <span>Already have an account?</span>
                <a href="login.php" class="auth-link-bold">Sign in</a>
            </div>
        </div>

    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
