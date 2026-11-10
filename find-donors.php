<?php
require_once __DIR__ . '/config/bootstrap.php';

$groups = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
$bloodGroup = trim((string) ($_GET['blood_group'] ?? ''));
$city = trim((string) ($_GET['city'] ?? ''));
$errors = [];
$donors = [];

if ($bloodGroup !== '' && !in_array($bloodGroup, $groups, true)) {
    $errors[] = 'Choose a valid blood group.';
} elseif (strlen($city) > 100) {
    $errors[] = 'City must be 100 characters or fewer.';
} elseif ($bloodGroup !== '' || $city !== '') {
    $sql = 'SELECT full_name, blood_group, city, phone
            FROM donors
            WHERE is_available = 1 AND contact_consent_at IS NOT NULL';
    $parameters = [];
    if ($bloodGroup !== '') {
        $sql .= ' AND blood_group = ?';
        $parameters[] = $bloodGroup;
    }
    if ($city !== '') {
        $sql .= ' AND city LIKE ?';
        $parameters[] = '%' . $city . '%';
    }
    $sql .= ' ORDER BY city, full_name LIMIT 100';
    $query = db()->prepare($sql);
    $query->execute($parameters);
    $donors = $query->fetchAll();
}

$pageTitle = 'Find donors';
require __DIR__ . '/includes/header.php';
?>
<section>
    <h1>Find blood donors</h1>
    <p class="muted">Search self-reported listings shared by donors. Availability and medical eligibility are not verified.</p>
    <form class="card form-card" method="get">
        <div class="form-field"><label for="blood_group">Blood group</label><select id="blood_group" name="blood_group"><option value="">Any group</option><?php foreach ($groups as $group): ?><option value="<?= e($group) ?>" <?= $bloodGroup === $group ? 'selected' : '' ?>><?= e($group) ?></option><?php endforeach; ?></select></div>
        <div class="form-field"><label for="city">City</label><input id="city" name="city" maxlength="100" value="<?= e($city) ?>" placeholder="Enter a city"></div>
        <div class="form-actions"><button class="button" type="submit">Search donors</button></div>
    </form>
    <div class="notice">Results are not medical advice or a guarantee of blood availability. Contact a listed donor directly, and coordinate eligibility and donation arrangements through a hospital or licensed blood bank.</div>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <?php if ($bloodGroup !== '' || $city !== ''): ?>
        <h2 class="section">Search results (<?= count($donors) ?>)</h2>
        <?php if ($donors): ?>
            <div class="table-wrap"><table><thead><tr><th>Name</th><th>Blood group</th><th>City</th><th>Contact</th></tr></thead><tbody>
            <?php foreach ($donors as $donor): ?><tr><td><?= e($donor['full_name']) ?></td><td><strong><?= e($donor['blood_group']) ?></strong></td><td><?= e($donor['city']) ?></td><td><a href="tel:<?= e($donor['phone']) ?>"><?= e($donor['phone']) ?></a></td></tr><?php endforeach; ?>
            </tbody></table></div>
        <?php else: ?><div class="empty-state">No available donors matched this search. Try another city or blood group.</div><?php endif; ?>
    <?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
