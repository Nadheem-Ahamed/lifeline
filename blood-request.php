<?php
require_once __DIR__ . '/config/bootstrap.php';
$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$levels = ['Routine', 'Urgent', 'Emergency'];
$values = ['patient_name' => '', 'blood_group' => '', 'hospital_name' => '', 'city' => '', 'required_units' => '', 'contact_number' => '', 'emergency_level' => ''];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    foreach ($values as $key => $_) $values[$key] = trim((string) ($_POST[$key] ?? ''));
    if ($values['patient_name'] === '' || strlen($values['patient_name']) > 100) $errors[] = 'Enter the patient name (up to 100 characters).';
    if (!in_array($values['blood_group'], $groups, true)) $errors[] = 'Choose a valid blood group.';
    if ($values['hospital_name'] === '' || strlen($values['hospital_name']) > 150) $errors[] = 'Enter a hospital name (up to 150 characters).';
    if ($values['city'] === '' || strlen($values['city']) > 100) $errors[] = 'Enter the city (up to 100 characters).';
    if (filter_var($values['required_units'], FILTER_VALIDATE_INT) === false || (int) $values['required_units'] < 1 || (int) $values['required_units'] > 100) $errors[] = 'Required units must be a whole number between 1 and 100.';
    if (!preg_match('/^[0-9+() -]{7,20}$/', $values['contact_number'])) $errors[] = 'Enter a valid contact number.';
    if (!in_array($values['emergency_level'], $levels, true)) $errors[] = 'Choose an emergency level.';

    if (!$errors) {
        $insert = db()->prepare(
            'INSERT INTO blood_requests (patient_name, blood_group, hospital_name, city, required_units, contact_number, emergency_level)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $insert->execute([
            $values['patient_name'], $values['blood_group'], $values['hospital_name'], $values['city'],
            (int) $values['required_units'], $values['contact_number'], $values['emergency_level'],
        ]);
        set_flash('success', 'Your blood request has been submitted. For urgent care, contact the hospital or emergency services directly.');
        redirect('blood-request.php');
    }
}

$pageTitle = 'Blood request';
require __DIR__ . '/includes/header.php';
?>
<section class="card form-card">
    <h1>Submit a blood request</h1>
    <p class="muted">This request is visible to LifeLink administrators. Submitting a request does not notify donors or guarantee a match.</p>
    <div class="notice">For medical emergencies, call local emergency services or contact the hospital/blood bank directly. LifeLink is not an emergency service.</div>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <div class="form-field"><label for="patient_name">Patient name</label><input id="patient_name" name="patient_name" maxlength="100" required value="<?= e($values['patient_name']) ?>"></div>
        <div class="form-field"><label for="blood_group">Blood group required</label><select id="blood_group" name="blood_group" required><option value="">Select</option><?php foreach ($groups as $group): ?><option value="<?= e($group) ?>" <?= $values['blood_group'] === $group ? 'selected' : '' ?>><?= e($group) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label for="hospital_name">Hospital name</label><input id="hospital_name" name="hospital_name" maxlength="150" required value="<?= e($values['hospital_name']) ?>"></div>
        <div class="form-field"><label for="city">City</label><input id="city" name="city" maxlength="100" required value="<?= e($values['city']) ?>"></div>
        <div class="form-field"><label for="required_units">Required units</label><input id="required_units" name="required_units" type="number" min="1" max="100" required value="<?= e($values['required_units']) ?>"></div>
        <div class="form-field"><label for="contact_number">Contact number</label><input id="contact_number" name="contact_number" type="tel" maxlength="20" required value="<?= e($values['contact_number']) ?>"></div>
        <div class="form-field"><label for="emergency_level">Emergency level</label><select id="emergency_level" name="emergency_level" required><option value="">Select</option><?php foreach ($levels as $level): ?><option value="<?= e($level) ?>" <?= $values['emergency_level'] === $level ? 'selected' : '' ?>><?= e($level) ?></option><?php endforeach; ?></select></div>
        <div class="form-actions"><button class="button" type="submit">Submit request</button></div>
    </form>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
