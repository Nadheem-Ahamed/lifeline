<?php
require_once __DIR__ . '/config/bootstrap.php';
$pageTitle = 'Home';
require __DIR__ . '/includes/header.php';
?>
<section class="hero">
    <div>
        <p class="eyebrow">Every donation can make a difference</p>
        <h1>Connect with blood donors in your community.</h1>
        <p>LifeLink is a community directory that helps people find donors who have chosen to share their contact information. Reach out directly and coordinate with a hospital or blood bank.</p>
        <div class="button-row">
            <a class="button" href="find-donors.php">Find blood donors</a>
            <a class="button button-secondary" href="register.php">Become a donor</a>
        </div>
    </div>
    <div class="hero-visual" aria-hidden="true">+</div>
</section>
<section class="section">
    <div class="section-heading">
        <h2>Blood groups</h2>
        <p class="muted">Use the donor search to look for volunteers by blood group and city.</p>
    </div>
    <div class="group-grid">
        <?php foreach (['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'] as $group): ?>
            <div class="group-card"><strong><?= e($group) ?></strong><span>Search for a consenting donor</span></div>
        <?php endforeach; ?>
    </div>
</section>
<section class="section card">
    <h2>How LifeLink works</h2>
    <p>Donors register and choose whether to share their name, city, blood group, and phone number with people using the directory. Search results reflect donor-entered information only; availability and medical eligibility are not verified.</p>
    <a href="about.php">Learn more about LifeLink</a>
</section>
<?php require __DIR__ . '/includes/footer.php'; ?>
