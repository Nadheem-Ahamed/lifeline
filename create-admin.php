<?php
declare(strict_types=1);

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

require_once __DIR__ . '/../config/db.php';

$email = $argv[1] ?? '';
if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) {
    fwrite(STDERR, "Usage: php database/create-admin.php admin@example.com\n");
    exit(1);
}

fwrite(STDOUT, 'Admin password (at least 12 characters; input is visible): ');
$password = trim((string) fgets(STDIN));
if (strlen($password) < 12) {
    fwrite(STDERR, "Password must be at least 12 characters.\n");
    exit(1);
}

$insert = db()->prepare('INSERT INTO admins (email, password_hash) VALUES (?, ?)');
try {
    $insert->execute([$email, password_hash($password, PASSWORD_DEFAULT)]);
    fwrite(STDOUT, "Administrator account created for {$email}.\n");
} catch (PDOException $exception) {
    if ($exception->getCode() === '23000') {
        fwrite(STDERR, "An administrator with that email already exists.\n");
        exit(1);
    }
    throw $exception;
}
