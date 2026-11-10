<?php
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle = 'About';
require __DIR__ . '/includes/header.php';
?>
<section class="card">
    <p class="eyebrow">About LifeLink</p>
    <h1>A simple community directory</h1>
    <p>LifeLink is an educational prototype designed to help people find donors who have volunteered to share their contact details. It is not a blood bank, medical service, emergency service, or confirmation that blood is available.</p>
    <h2>Using the directory responsibly</h2>
    <ul>
        <li>Donor listings are self-reported and may be out of date. Contact a donor to check their current availability.</li>
        <li>Hospitals and licensed blood banks must assess medical eligibility and arrange all donations.</li>
        <li>Only the donor's name, blood group, city, and phone number are shown in search results, and only after the donor grants sharing consent.</li>
        <li>Do not use the directory to send spam or share donor information outside the purpose of coordinating a donation.</li>
    </ul>
    <p>For emergencies, contact local emergency services or a hospital directly; do not rely on this directory.</p>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
