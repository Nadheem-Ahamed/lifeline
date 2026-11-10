<?php
require_once __DIR__ . '/config/bootstrap.php';

$errors = [];
$values = [
    'full_name' => '', 'age' => '', 'gender' => '', 'blood_group' => '',
    'phone' => '', 'email' => '', 'city' => '', 'last_donation_date' => '', 'is_available' => '',
];
$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$genders = ['Woman', 'Man', 'Non-binary', 'Prefer not to say'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) {
        $values[$key] = trim((string) ($_POST[$key] ?? ''));
    }
    $password = (string) ($_POST['password'] ?? '');

    if ($values['full_name'] === '' || strlen($values['full_name']) > 100) {
        $errors[] = 'Enter your full name (up to 100 characters).';
    }
    if (filter_var($values['age'], FILTER_VALIDATE_INT) === false || (int) $values['age'] < 18 || (int) $values['age'] > 100) {
        $errors[] = 'Age must be a whole number between 18 and 100.';
    }
    if (!in_array($values['gender'], $genders, true)) {
        $errors[] = 'Choose a gender option.';
    }
    if (!in_array($values['blood_group'], $groups, true)) {
        $errors[] = 'Choose a valid blood group.';
    }
    if (!preg_match('/^[0-9+() -]{7,20}$/', $values['phone'])) {
        $errors[] = 'Enter a valid phone number.';
    }
    if (!filter_var($values['email'], FILTER_VALIDATE_EMAIL) || strlen($values['email']) > 190) {
        $errors[] = 'Enter a valid email address (up to 190 characters).';
    }
    if ($values['city'] === '' || strlen($values['city']) > 100) {
        $errors[] = 'Enter your city (up to 100 characters).';
    }
    if ($values['last_donation_date'] !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $values['last_donation_date']);
        if (!$date || $date->format('Y-m-d') !== $values['last_donation_date'] || $date > new DateTime('today')) {
            $errors[] = 'Enter a valid last donation date that is not in the future.';
        }
    }
    if (!in_array($values['is_available'], ['0', '1'], true)) {
        $errors[] = 'Choose your availability status.';
    }
    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }
    if (($_POST['contact_consent'] ?? '') !== 'yes') {
        $errors[] = 'Consent is required to create a donor listing and share your phone number in search results.';
    }

    if (!$errors) {
        $pdo = db();
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$values['email']]);
        if ($check->fetch()) {
            $errors[] = 'An account already exists for that email address.';
        } else {
            try {
                $pdo->beginTransaction();
                $userInsert = $pdo->prepare('INSERT INTO users (email, password_hash) VALUES (?, ?)');
                $userInsert->execute([$values['email'], password_hash($password, PASSWORD_DEFAULT)]);
                $userId = (int) $pdo->lastInsertId();
                $donorInsert = $pdo->prepare(
                    'INSERT INTO donors (user_id, full_name, age, gender, blood_group, phone, city, last_donation_date, is_available, contact_consent_at)
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)'
                );
                $donorInsert->execute([
                    $userId, $values['full_name'], (int) $values['age'], $values['gender'],
                    $values['blood_group'], $values['phone'], $values['city'],
                    $values['last_donation_date'] ?: null, (int) $values['is_available'],
                ]);
                $pdo->commit();
                set_flash('success', 'Registration complete. You can now log in.');
                redirect('login.php');
            } catch (PDOException $exception) {
                if ($pdo->inTransaction()) {
                    $pdo->rollBack();
                }
                if ($exception->getCode() === '23000') {
                    $errors[] = 'An account already exists for that email address.';
                } else {
                    throw $exception;
                }
            }
        }
    }
}

$pageTitle = 'Donor registration';
require __DIR__ . '/includes/header.php';
?>
<section class="card form-card">
    <h1>Register as a donor</h1>
    <p class="muted">Your account details are stored securely. Search users can see your name, blood group, city and phone only while you have sharing consent enabled.</p>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="100" required value="<?= e($values['full_name']) ?>"></div>
        <div class="form-field"><label for="age">Age</label><input id="age" name="age" type="number" min="18" max="100" required value="<?= e($values['age']) ?>"></div>
        <div class="form-field"><label for="gender">Gender</label><select id="gender" name="gender" required><option value="">Select</option><?php foreach ($genders as $gender): ?><option value="<?= e($gender) ?>" <?= $values['gender'] === $gender ? 'selected' : '' ?>><?= e($gender) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label for="blood_group">Blood group</label><select id="blood_group" name="blood_group" required><option value="">Select</option><?php foreach ($groups as $group): ?><option value="<?= e($group) ?>" <?= $values['blood_group'] === $group ? 'selected' : '' ?>><?= e($group) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="20" required value="<?= e($values['phone']) ?>"></div>
        <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" required value="<?= e($values['email']) ?>"></div>
        <div class="form-field"><label for="city">City</label><input id="city" name="city" maxlength="100" required value="<?= e($values['city']) ?>"></div>
        <div class="form-field"><label for="last_donation_date">Last blood donation date</label><input id="last_donation_date" name="last_donation_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= e($values['last_donation_date']) ?>"></div>
        <div class="form-field"><label for="is_available">Availability</label><select id="is_available" name="is_available" required><option value="">Select</option><option value="1" <?= $values['is_available'] === '1' ? 'selected' : '' ?>>Available</option><option value="0" <?= $values['is_available'] === '0' ? 'selected' : '' ?>>Unavailable</option></select><span class="help-text">This is self-reported, not medically verified.</span></div>
        <div class="form-field full-width"><label for="password">Password</label><input id="password" name="password" type="password" minlength="8" autocomplete="new-password" required><span class="help-text">Use at least 8 characters.</span></div>
        <div class="form-field full-width form-check"><input id="contact_consent" name="contact_consent" type="checkbox" value="yes" required <?= isset($_POST['contact_consent']) ? 'checked' : '' ?>><label for="contact_consent">I agree to LifeLink storing my donor information and sharing my name, blood group, city, and phone number with people searching the directory. I understand this information is self-reported and can turn off sharing from my dashboard.</label></div>
        <div class="form-actions"><button class="button" type="submit">Create donor account</button></div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
