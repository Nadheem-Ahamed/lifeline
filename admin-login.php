<?php
require_once __DIR__ . '/config/bootstrap.php';
$errors = [];
$email = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $email = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || $password === '') {
        $errors[] = 'Enter a valid email address and password.';
    } else {
        $query = db()->prepare('SELECT id, password_hash FROM admins WHERE email = ? LIMIT 1');
        $query->execute([$email]);
        $admin = $query->fetch();
        if (!$admin || !password_verify($password, $admin['password_hash'])) {
            $errors[] = 'The email or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['admin_id'] = (int) $admin['id'];
            set_flash('success', 'Administrator login successful.');
            redirect('admin-dashboard.php');
        }
    }
}

$pageTitle = 'Admin login';
require __DIR__ . '/includes/header.php';
?>
<section class="card form-card">
    <h1>Administrator login</h1>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field full-width"><label for="email">Admin email</label><input id="email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e($email) ?>"></div>
        <div class="form-field full-width"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <div class="form-actions"><button class="button" type="submit">Log in</button></div>
    </form>
    <p class="help-text">Create administrator accounts using the CLI script described in README.md.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
