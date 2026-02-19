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

    // Only allow local file paths; external/inlined URLs are intentionally ignored.
    if (preg_match('#^(https?:)?//#i', $src) === 1 || str_starts_with(strtolower($src), 'data:')) {
        return $fallback;
    }

    if (str_starts_with($src, '/')) {
        $base = defined('APP_BASE_PATH') ? rtrim(APP_BASE_PATH, '/') : '';
        if ($base !== '' && str_starts_with($src, $base . '/')) {
            $src = ltrim(substr($src, strlen($base)), '/');
        } else {
            $normalized = ltrim($src, '/');
            $filePath = __DIR__ . '/../' . $normalized;
            if (is_file($filePath) && filesize($filePath) > 0) {
                $version = (string) filemtime($filePath);
                $separator = str_contains($src, '?') ? '&' : '?';
                return $src . $separator . 'v=' . $version;
            }

            return $fallback;
        }
    }

    $filePath = __DIR__ . '/../' . ltrim($src, '/');
    if (!is_file($filePath) || filesize($filePath) <= 0) {
        return $fallback;
    }

    return asset_url($src);
}

function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Image trop volumineuse (max 5 Mo).',
        UPLOAD_ERR_PARTIAL => 'Le transfert de l\'image a été interrompu.',
        UPLOAD_ERR_NO_TMP_DIR => 'Serveur indisponible: dossier temporaire manquant.',
        UPLOAD_ERR_CANT_WRITE => 'Serveur indisponible: écriture impossible.',
        UPLOAD_ERR_EXTENSION => 'Le serveur a bloqué l\'upload de l\'image.',
        default => 'Erreur inconnue pendant l\'upload de l\'image.',
    };
}

function store_uploaded_local_image(string $field, string $relativeDirectory, int $maxBytes = 5242880): array
{
    if (!isset($_FILES[$field])) {
        return ['path' => null, 'error' => null];
    }

    $upload = $_FILES[$field];
    if (!is_array($upload) || !isset($upload['error'], $upload['tmp_name'], $upload['size'])) {
        return ['path' => null, 'error' => 'Upload d\'image invalide.'];
    }

    $uploadError = (int) $upload['error'];
    if ($uploadError === UPLOAD_ERR_NO_FILE) {
        return ['path' => null, 'error' => null];
    }

    if ($uploadError !== UPLOAD_ERR_OK) {
        return ['path' => null, 'error' => upload_error_message($uploadError)];
    }

    $tmpPath = (string) $upload['tmp_name'];
    if (!is_uploaded_file($tmpPath)) {
        return ['path' => null, 'error' => 'Le fichier image envoyé est invalide.'];
    }

    $size = (int) $upload['size'];
    if ($size <= 0) {
        return ['path' => null, 'error' => 'Le fichier image est vide.'];
    }

    if ($size > $maxBytes) {
        return ['path' => null, 'error' => 'Image trop volumineuse (max 5 Mo).'];
    }

    $detectedMime = '';
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $detected = finfo_file($finfo, $tmpPath);
            finfo_close($finfo);
            if (is_string($detected)) {
                $detectedMime = strtolower(trim($detected));
            }
        }
    }

    if ($detectedMime === '' && function_exists('getimagesize')) {
        $imageInfo = @getimagesize($tmpPath);
        if (is_array($imageInfo) && isset($imageInfo['mime'])) {
            $detectedMime = strtolower(trim((string) $imageInfo['mime']));
        }
    }

    $allowedMimeTypes = [
        'image/jpeg' => 'jpg',
        'image/jpg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
    ];

    if (!isset($allowedMimeTypes[$detectedMime])) {
        return ['path' => null, 'error' => 'Format image non autorisé. Utilisez JPG, PNG, WEBP ou GIF.'];
    }

    $relativeDirectory = trim($relativeDirectory, '/');
    if ($relativeDirectory === '') {
        return ['path' => null, 'error' => 'Dossier d\'upload invalide.'];
    }

    $absoluteDirectory = __DIR__ . '/../' . $relativeDirectory;
    if (!is_dir($absoluteDirectory) && !mkdir($absoluteDirectory, 0775, true) && !is_dir($absoluteDirectory)) {
        return ['path' => null, 'error' => 'Impossible de créer le dossier de stockage des images.'];
    }

    if (!is_writable($absoluteDirectory)) {
        return ['path' => null, 'error' => 'Le dossier des images n\'est pas accessible en écriture.'];
    }

    try {
        $randomPart = bin2hex(random_bytes(8));
    } catch (Throwable) {
        $randomPart = (string) mt_rand(10000000, 99999999);
    }

    $extension = $allowedMimeTypes[$detectedMime];
    $filename = date('Ymd-His') . '-' . $randomPart . '.' . $extension;
    $relativePath = $relativeDirectory . '/' . $filename;
    $absolutePath = __DIR__ . '/../' . $relativePath;

    if (!move_uploaded_file($tmpPath, $absolutePath)) {
        return ['path' => null, 'error' => 'Impossible de stocker l\'image sur le serveur.'];
    }

    @chmod($absolutePath, 0644);

    return ['path' => $relativePath, 'error' => null];
}

function store_uploaded_car_image(string $field = 'image_file'): array
{
    return store_uploaded_local_image($field, 'assets/images/cars');
}

function store_uploaded_profile_image(string $field = 'profile_photo_file'): array
{
    return store_uploaded_local_image($field, 'assets/images/profiles');
}

function delete_uploaded_car_image(?string $relativePath): void
{
    $path = trim((string) $relativePath);
    if ($path === '') {
        return;
    }

    $normalized = ltrim($path, '/');
    $prefix = 'assets/images/cars/';
    if (!str_starts_with($normalized, $prefix)) {
        return;
    }

    $baseDir = realpath(__DIR__ . '/../assets/images/cars');
    if ($baseDir === false) {
        return;
    }

    $absolutePath = __DIR__ . '/../' . $normalized;
    $realPath = realpath($absolutePath);
    if ($realPath === false || !is_file($realPath)) {
        return;
    }

    if (!str_starts_with($realPath, $baseDir . DIRECTORY_SEPARATOR)) {
        return;
    }

    @unlink($realPath);
}

function delete_uploaded_profile_image(?string $relativePath): void
{
    $path = trim((string) $relativePath);
    if ($path === '') {
        return;
    }

    $normalized = ltrim($path, '/');
    $basePath = null;
    if (str_starts_with($normalized, 'assets/images/profiles/')) {
        $basePath = __DIR__ . '/../assets/images/profiles';
    } elseif (str_starts_with($normalized, 'assets/images/cars/profiles/')) {
        // Legacy path kept for cleanup after migration.
        $basePath = __DIR__ . '/../assets/images/cars/profiles';
    }

    if ($basePath === null) {
        return;
    }

    $baseDir = realpath($basePath);
    if ($baseDir === false) {
        return;
    }

    $absolutePath = __DIR__ . '/../' . $normalized;
    $realPath = realpath($absolutePath);
    if ($realPath === false || !is_file($realPath)) {
        return;
    }

    if (!str_starts_with($realPath, $baseDir . DIRECTORY_SEPARATOR)) {
        return;
    }

    @unlink($realPath);
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
