<?php
require_once __DIR__ . '/config/bootstrap.php';
require_admin();
$pdo = db();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
    $action = (string) ($_POST['action'] ?? '');
    $recordId = filter_var($_POST['record_id'] ?? null, FILTER_VALIDATE_INT);
    if (!$recordId || $recordId < 1) {
        $errors[] = 'Choose a valid record.';
    } elseif ($action === 'delete_donor') {
        $lookup = $pdo->prepare('SELECT user_id FROM donors WHERE id = ?');
        $lookup->execute([$recordId]);
        $userId = $lookup->fetchColumn();
        if ($userId !== false) {
            $accountDelete = $pdo->prepare('DELETE FROM users WHERE id = ?');
            $accountDelete->execute([(int) $userId]);
            set_flash('success', 'The donor record and linked account were deleted.');
            redirect('admin-dashboard.php');
        } else {
            $errors[] = 'That donor record no longer exists.';
        }
    } elseif ($action === 'update_request') {
        $status = (string) ($_POST['status'] ?? '');
        if (!in_array($status, ['Pending', 'Completed', 'Rejected'], true)) {
            $errors[] = 'Choose a valid request status.';
        } else {
            $update = $pdo->prepare('UPDATE blood_requests SET status = ? WHERE id = ?');
            $update->execute([$status, $recordId]);
            set_flash('success', $update->rowCount() > 0 ? 'Request status updated.' : 'Request status is unchanged or the request does not exist.');
            redirect('admin-dashboard.php');
        }
    } else {
        $errors[] = 'Unknown administrative action.';
    }
}

$donorCount = (int) $pdo->query('SELECT COUNT(*) FROM donors')->fetchColumn();
$requestCount = (int) $pdo->query('SELECT COUNT(*) FROM blood_requests')->fetchColumn();
$donors = $pdo->query(
    'SELECT donors.id, donors.full_name, donors.age, donors.gender, donors.blood_group, donors.phone,
            donors.city, donors.last_donation_date, donors.is_available, donors.contact_consent_at, users.email
     FROM donors JOIN users ON users.id = donors.user_id ORDER BY donors.created_at DESC LIMIT 500'
)->fetchAll();
$requests = $pdo->query(
    'SELECT id, patient_name, blood_group, hospital_name, city, required_units, contact_number,
            emergency_level, status, created_at FROM blood_requests ORDER BY created_at DESC LIMIT 500'
)->fetchAll();

$pageTitle = 'Admin dashboard';
require __DIR__ . '/includes/header.php';
?>
<section>
    <div class="status-line"><h1>Admin dashboard</h1><a class="button button-small" href="logout.php">Log out</a></div>
    <p class="muted">Donor contact details and request information are private. Use them only to administer this prototype.</p>
    <?php if ($errors): ?><ul class="error-list"><?php foreach ($errors as $error): ?><li><?= e($error) ?></li><?php endforeach; ?></ul><?php endif; ?>
    <div class="stats">
        <div class="stat-card"><strong><?= $donorCount ?></strong><span>Registered donors</span></div>
        <div class="stat-card"><strong><?= $requestCount ?></strong><span>Blood requests</span></div>
    </div>

    <h2>Donors</h2>
    <?php if (!$donors): ?><div class="empty-state">No donors are registered.</div><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Name and email</th><th>Details</th><th>Availability / sharing</th><th>Action</th></tr></thead><tbody>
    <?php foreach ($donors as $donor): ?>
        <tr>
            <td><?= e($donor['full_name']) ?><br><span class="muted"><?= e($donor['email']) ?></span></td>
            <td><?= e($donor['age']) ?> · <?= e($donor['gender']) ?><br><?= e($donor['blood_group']) ?> · <?= e($donor['city']) ?><br><a href="tel:<?= e($donor['phone']) ?>"><?= e($donor['phone']) ?></a><br>Last donation: <?= e($donor['last_donation_date'] ?: 'Not provided') ?></td>
            <td><span class="badge <?= $donor['is_available'] ? 'badge-available' : 'badge-unavailable' ?>"><?= $donor['is_available'] ? 'Available' : 'Unavailable' ?></span><br><?= $donor['contact_consent_at'] !== null ? 'Sharing consent on' : 'Sharing disabled' ?></td>
            <td><form method="post" onsubmit="return confirm('Delete this donor account and profile?');"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="delete_donor"><input type="hidden" name="record_id" value="<?= (int) $donor['id'] ?>"><button class="button button-small" type="submit">Delete donor</button></form></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div><?php endif; ?>

    <h2 class="section">Blood requests</h2>
    <?php if (!$requests): ?><div class="empty-state">No blood requests have been submitted.</div><?php else: ?>
    <div class="table-wrap"><table><thead><tr><th>Patient / need</th><th>Hospital / city</th><th>Contact</th><th>Level / status</th><th>Update status</th></tr></thead><tbody>
    <?php foreach ($requests as $request): ?>
        <tr>
            <td><?= e($request['patient_name']) ?><br><?= e($request['blood_group']) ?> · <?= (int) $request['required_units'] ?> unit(s)</td>
            <td><?= e($request['hospital_name']) ?><br><?= e($request['city']) ?></td>
            <td><a href="tel:<?= e($request['contact_number']) ?>"><?= e($request['contact_number']) ?></a></td>
            <td><?= e($request['emergency_level']) ?><br><span class="badge badge-<?= strtolower(e($request['status'])) ?>"><?= e($request['status']) ?></span></td>
            <td><form class="inline-form" method="post"><input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>"><input type="hidden" name="action" value="update_request"><input type="hidden" name="record_id" value="<?= (int) $request['id'] ?>"><select name="status" aria-label="Request status"><?php foreach (['Pending', 'Completed', 'Rejected'] as $status): ?><option value="<?= e($status) ?>" <?= $request['status'] === $status ? 'selected' : '' ?>><?= e($status) ?></option><?php endforeach; ?></select><button class="button button-small" type="submit">Save</button></form></td>
        </tr>
    <?php endforeach; ?>
    </tbody></table></div><?php endif; ?>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
