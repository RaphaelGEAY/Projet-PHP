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

function asset_url(string $path): string
{
    $normalized = ltrim($path, '/');
    $publicUrl = url($normalized);
    $filePath = __DIR__ . '/../' . $normalized;

    if (!is_file($filePath)) {
        return $publicUrl;
    }

    $version = (string) filemtime($filePath);
    $separator = str_contains($publicUrl, '?') ? '&' : '?';
    return $publicUrl . $separator . 'v=' . $version;
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

function media_src(null|string $value, string $fallback = ''): string
{
    $src = trim((string) $value);
    if ($src === '') {
        return $fallback;
    }

    if (preg_match('#^(https?:)?//#i', $src) === 1 || str_starts_with($src, 'data:image/')) {
        return $src;
    }

    if (str_starts_with($src, '/')) {
        $base = defined('APP_BASE_PATH') ? rtrim(APP_BASE_PATH, '/') : '';
        if ($base !== '' && str_starts_with($src, $base . '/')) {
            $relative = ltrim(substr($src, strlen($base)), '/');
            return asset_url($relative);
        }

        $normalized = ltrim($src, '/');
        $filePath = __DIR__ . '/../' . $normalized;
        if (is_file($filePath)) {
            $version = (string) filemtime($filePath);
            $separator = str_contains($src, '?') ? '&' : '?';
            return $src . $separator . 'v=' . $version;
        }

        return $src;
    }

    return asset_url($src);
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
