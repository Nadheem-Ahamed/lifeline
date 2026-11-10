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
        $query = db()->prepare(
            'SELECT users.id, users.password_hash FROM users WHERE users.email = ? LIMIT 1'
        );
        $query->execute([$email]);
        $user = $query->fetch();
        if (!$user || !password_verify($password, $user['password_hash'])) {
            $errors[] = 'The email or password is incorrect.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user_id'] = (int) $user['id'];
            set_flash('success', 'You are now logged in.');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Donor login';
require __DIR__ . '/includes/header.php';
?>
<section class="card form-card">
    <h1>Donor login</h1>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field full-width"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" autocomplete="username" required value="<?= e($email) ?>"></div>
        <div class="form-field full-width"><label for="password">Password</label><input id="password" name="password" type="password" autocomplete="current-password" required></div>
        <div class="form-actions"><button class="button" type="submit">Log in</button></div>
    </form>
    <p class="section">New donor? <a href="register.php">Create an account</a>.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
