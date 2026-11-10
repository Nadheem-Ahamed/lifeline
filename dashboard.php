<?php
require_once __DIR__ . '/config/bootstrap.php';
require_donor();
$pdo = db();
$profileQuery = $pdo->prepare(
    'SELECT users.email, donors.* FROM users
     JOIN donors ON donors.user_id = users.id WHERE users.id = ? LIMIT 1'
);
$profileQuery->execute([$_SESSION['user_id']]);
$profile = $profileQuery->fetch();
if (!$profile) {
    unset($_SESSION['user_id']);
    set_flash('error', 'Your donor profile could not be found. Please register again or contact support.');
    redirect('register.php');
}

$errors = [];
$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$genders = ['Woman', 'Man', 'Non-binary', 'Prefer not to say'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $fullName = trim((string) ($_POST['full_name'] ?? ''));
    $ageInput = trim((string) ($_POST['age'] ?? ''));
    $gender = (string) ($_POST['gender'] ?? '');
    $bloodGroup = (string) ($_POST['blood_group'] ?? '');
    $phone = trim((string) ($_POST['phone'] ?? ''));
    $email = trim((string) ($_POST['email'] ?? ''));
    $city = trim((string) ($_POST['city'] ?? ''));
    $lastDonation = trim((string) ($_POST['last_donation_date'] ?? ''));
    $availability = (string) ($_POST['is_available'] ?? '');
    $shareContact = ($_POST['contact_consent'] ?? '') === 'yes';

    if ($fullName === '' || strlen($fullName) > 100) $errors[] = 'Enter your full name (up to 100 characters).';
    if (filter_var($ageInput, FILTER_VALIDATE_INT) === false || (int) $ageInput < 18 || (int) $ageInput > 100) $errors[] = 'Age must be a whole number between 18 and 100.';
    if (!in_array($gender, $genders, true)) $errors[] = 'Choose a valid gender option.';
    if (!in_array($bloodGroup, $groups, true)) $errors[] = 'Choose a valid blood group.';
    if (!preg_match('/^[0-9+() -]{7,20}$/', $phone)) $errors[] = 'Enter a valid phone number.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $errors[] = 'Enter a valid email address.';
    if ($city === '' || strlen($city) > 100) $errors[] = 'Enter your city (up to 100 characters).';
    if (!in_array($availability, ['0', '1'], true)) $errors[] = 'Choose an availability status.';
    if ($lastDonation !== '') {
        $date = DateTime::createFromFormat('Y-m-d', $lastDonation);
        if (!$date || $date->format('Y-m-d') !== $lastDonation || $date > new DateTime('today')) $errors[] = 'Enter a valid last donation date that is not in the future.';
    }

    if (!$errors) {
        $emailCheck = $pdo->prepare('SELECT id FROM users WHERE email = ? AND id <> ?');
        $emailCheck->execute([$email, $_SESSION['user_id']]);
        if ($emailCheck->fetch()) {
            $errors[] = 'Another account already uses that email address.';
        } else {
            $pdo->beginTransaction();
            $userUpdate = $pdo->prepare('UPDATE users SET email = ? WHERE id = ?');
            $userUpdate->execute([$email, $_SESSION['user_id']]);
            $donorUpdate = $pdo->prepare(
                'UPDATE donors SET full_name = ?, age = ?, gender = ?, blood_group = ?, phone = ?,
                 city = ?, last_donation_date = ?, is_available = ?, contact_consent_at = ? WHERE user_id = ?'
            );
            $donorUpdate->execute([
                $fullName, (int) $ageInput, $gender, $bloodGroup, $phone, $city,
                $lastDonation ?: null, (int) $availability,
                $shareContact ? ($profile['contact_consent_at'] ?? date('Y-m-d H:i:s')) : null,
                $_SESSION['user_id'],
            ]);
            $pdo->commit();
            set_flash('success', 'Your donor profile has been updated.');
            redirect('dashboard.php');
        }
    }
}

$pageTitle = 'Donor dashboard';
require __DIR__ . '/includes/header.php';
?>
<section>
    <h1>My donor dashboard</h1>
    <p class="muted">Manage the information in your listing and decide whether your contact details appear in search results.</p>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php
    $field = static function (string $key, string $postKey = '') use ($profile): string {
        $name = $postKey !== '' ? $postKey : $key;
        return e((string) ($_POST[$name] ?? $profile[$key] ?? ''));
    };
    ?>
    <div class="card form-card">
        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
            <div class="form-field"><label for="full_name">Full name</label><input id="full_name" name="full_name" maxlength="100" required value="<?= $field('full_name') ?>"></div>
            <div class="form-field"><label for="age">Age</label><input id="age" name="age" type="number" min="18" max="100" required value="<?= $field('age') ?>"></div>
            <div class="form-field"><label for="gender">Gender</label><select id="gender" name="gender" required><option value="">Select</option><?php foreach ($genders as $gender): ?><option value="<?= e($gender) ?>" <?= (($_POST['gender'] ?? $profile['gender']) === $gender) ? 'selected' : '' ?>><?= e($gender) ?></option><?php endforeach; ?></select></div>
            <div class="form-field"><label for="blood_group">Blood group</label><select id="blood_group" name="blood_group" required><?php foreach ($groups as $group): ?><option value="<?= e($group) ?>" <?= (($_POST['blood_group'] ?? $profile['blood_group']) === $group) ? 'selected' : '' ?>><?= e($group) ?></option><?php endforeach; ?></select></div>
            <div class="form-field"><label for="phone">Phone number</label><input id="phone" name="phone" type="tel" maxlength="20" required value="<?= $field('phone') ?>"></div>
            <div class="form-field"><label for="email">Email</label><input id="email" name="email" type="email" maxlength="190" required value="<?= $field('email', 'email') ?>"></div>
            <div class="form-field"><label for="city">City</label><input id="city" name="city" maxlength="100" required value="<?= $field('city') ?>"></div>
            <div class="form-field"><label for="last_donation_date">Last donation date</label><input id="last_donation_date" name="last_donation_date" type="date" max="<?= date('Y-m-d') ?>" value="<?= $field('last_donation_date') ?>"></div>
            <div class="form-field"><label for="is_available">Availability</label><select id="is_available" name="is_available" required><option value="1" <?= (string) ($_POST['is_available'] ?? $profile['is_available']) === '1' ? 'selected' : '' ?>>Available</option><option value="0" <?= (string) ($_POST['is_available'] ?? $profile['is_available']) === '0' ? 'selected' : '' ?>>Unavailable</option></select><span class="help-text">This is self-reported, not medically verified.</span></div>
            <div class="form-field full-width form-check"><input id="contact_consent" name="contact_consent" type="checkbox" value="yes" <?= (($_SERVER['REQUEST_METHOD'] === 'POST' ? ($_POST['contact_consent'] ?? '') === 'yes' : $profile['contact_consent_at'] !== null)) ? 'checked' : '' ?>><label for="contact_consent">Allow my name, blood group, city, and phone number to appear in donor search results. Uncheck to remove the public listing.</label></div>
            <div class="form-actions"><button class="button" type="submit">Save changes</button></div>
        </form>
    </div>
    <p class="section muted">Account email: <?= e($profile['email']) ?>. Need to change your password? Log out and contact the project administrator to arrange a reset.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
