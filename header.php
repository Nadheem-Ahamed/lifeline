<?php
$pageTitle = $pageTitle ?? 'LifeLink';
$flashMessage = take_flash();
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="LifeLink connects consenting blood donors with people searching for donors in their city.">
    <title><?= e($pageTitle) ?> | LifeLink</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="js/script.js" defer></script>
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a class="brand" href="index.php"><span class="brand-mark" aria-hidden="true">+</span> LifeLink</a>
        <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="primary-nav">Menu</button>
        <nav class="site-nav" id="primary-nav" aria-label="Main navigation">
            <a href="index.php">Home</a>
            <a href="about.php">About</a>
            <a href="find-donors.php">Find Donors</a>
            <a href="blood-request.php">Request Blood</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="dashboard.php">My Dashboard</a>
                <a href="logout.php">Logout</a>
            <?php else: ?>
                <a href="register.php">Register</a>
                <a href="login.php">Login</a>
            <?php endif; ?>
        </nav>
    </div>
</header>
<main class="container main-content">
    <?php if ($flashMessage !== null): ?>
        <div class="alert alert-<?= e($flashMessage['type']) ?>" role="status"><?= e($flashMessage['message']) ?></div>
    <?php endif; ?>
