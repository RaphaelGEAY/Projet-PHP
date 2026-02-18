<?php
declare(strict_types=1);

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function url(string $path = ''): string
{
    $base = defined('APP_BASE_PATH') ? rtrim(APP_BASE_PATH, '/') : '';
    $normalized = ltrim($path, '/');

    if ($normalized === '') {
        return $base === '' ? '/' : $base . '/';
    }

    return ($base === '' ? '' : $base) . '/' . $normalized;
}

function redirect(string $path): never
{
    header('Location: ' . url($path));
    exit;
}

function current_user(bool $refresh = false): ?array
{
    static $loaded = false;
    static $user = null;

    if ($refresh) {
        $loaded = false;
        $user = null;
    }

    if ($loaded) {
        return $user;
    }

    $loaded = true;

    if (empty($_SESSION['user_id'])) {
        return null;
    }

    $stmt = db()->prepare('SELECT * FROM users WHERE id = :id LIMIT 1');
    $stmt->execute(['id' => (int) $_SESSION['user_id']]);
    $result = $stmt->fetch();

    if (!$result) {
        unset($_SESSION['user_id']);
        return null;
    }

    $user = $result;
    return $user;
}

function is_logged_in(): bool
{
    return current_user() !== null;
}

function is_admin(): bool
{
    $user = current_user();
    return $user !== null && $user['role'] === 'admin';
}

function require_login(): void
{
    if (!is_logged_in()) {
        set_flash('error', 'Connectez-vous pour accéder à cette page.');
        redirect('login.php');
    }
}

function require_admin(): void
{
    require_login();

    if (!is_admin()) {
        set_flash('error', 'Accès refusé: administrateur requis.');
        redirect('index.php');
    }
}

function set_flash(string $type, string $message): void
{
    $_SESSION['flashes'][] = ['type' => $type, 'message' => $message];
}

function consume_flashes(): array
{
    $messages = $_SESSION['flashes'] ?? [];
    unset($_SESSION['flashes']);
    return $messages;
}

function format_price(float|string $amount): string
{
    return number_format((float) $amount, 2, ',', ' ') . ' €';
}

function post_string(string $key): string
{
    return trim((string) ($_POST[$key] ?? ''));
}

function post_int(string $key, int $default = 0): int
{
    return isset($_POST[$key]) ? (int) $_POST[$key] : $default;
}

function post_float(string $key, float $default = 0.0): float
{
    return isset($_POST[$key]) ? (float) $_POST[$key] : $default;
}

function is_post(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}
