<?php
declare(strict_types=1);

require_once __DIR__ . '/db.php';

$isSecure = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off';
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => $isSecure,
    'httponly' => true,
    'samesite' => 'Lax',
]);
session_start();

set_exception_handler(static function (Throwable $exception): void {
    error_log((string) $exception);
    http_response_code(500);
    echo '<!doctype html><html lang="en"><meta charset="utf-8"><title>Service unavailable</title>';
    echo '<body style="font-family:Arial,sans-serif;margin:3rem;color:#333"><h1>Sorry, something went wrong.</h1>';
    echo '<p>Please check the database configuration and try again.</p></body></html>';
});

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function redirect(string $location): never
{
    header('Location: ' . $location);
    exit;
}

function csrf_token(): string
{
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verify_csrf(): void
{
    $submitted = $_POST['csrf_token'] ?? '';
    if (!is_string($submitted) || !hash_equals(csrf_token(), $submitted)) {
        http_response_code(400);
        exit('The form expired or was invalid. Please go back and try again.');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function take_flash(): ?array
{
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);

    return is_array($flash) ? $flash : null;
}

function require_donor(): void
{
    if (!isset($_SESSION['user_id'])) {
        set_flash('error', 'Please log in to view your donor dashboard.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    if (!isset($_SESSION['admin_id'])) {
        set_flash('error', 'Please log in as an administrator.');
        redirect('admin-login.php');
    }
}
