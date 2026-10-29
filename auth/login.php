<?php
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) redirect('/index.php');

$error  = '';
$email  = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = sanitize($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!$email || !$password) {
        $error = 'Please fill in all fields.';
    } else {
        $db   = getDB();
        $stmt = $db->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            startSession();
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'email' => $user['email'],
                'role'  => $user['role'],
            ];
            flash('success', 'Welcome back, ' . $user['name'] . '!');

            // Role-based redirect
            $dest = match($user['role']) {
                'admin' => '/admin/dashboard.php',
                'agent' => '/agent/dashboard.php',
                default => '/index.php',
            };
            redirect($dest);
        } else {
            $error = 'Invalid email or password.';
        }
    }
}

$pageTitle = 'Login — EstateHub';
include __DIR__ . '/../includes/header.php';
?>

<div class="auth-page">
    <div class="container">
        <div class="auth-card">
            <div class="auth-card-header">
                <div class="auth-logo">🏠</div>
                <h2>Welcome Back</h2>
                <p>Sign in to your EstateHub account</p>
            </div>
            <div class="auth-card-body">
                <?php if ($error): ?>
                    <div class="alert alert-error mb-3" style="position:static;animation:none;">
                        <i data-lucide="x-circle"></i> <?= e($error) ?>
                    </div>
                <?php endif; ?>

                <form method="POST" novalidate>
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <div class="input-group">
                            <span class="input-icon"><i data-lucide="mail"></i></span>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control"
                                value="<?= e($email) ?>"
                                placeholder="you@example.com"
                                required
                                autofocus
                            />
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password">
                            Password
                        </label>
                        <div class="input-group">
                            <span class="input-icon"><i data-lucide="lock"></i></span>
                            <input
                                type="password"
                                id="password"
                                name="password"
                                class="form-control"
                                placeholder="Your password"
                                required
                            />
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block btn-lg mt-2">
                        <i data-lucide="log-in"></i> Sign In
                    </button>
                </form>

                <!-- Local demo accounts use passwords configured when running the CLI setup. -->
                <div class="auth-notice"><p class="text-xs text-gray">Demo accounts must be seeded locally. Sign in with the unique password set during setup.</p></div>
            </div>
            <div class="auth-footer">
                Don't have an account? <a href="<?= SITE_URL ?>/auth/register.php">Sign Up</a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
