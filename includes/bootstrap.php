<?php
declare(strict_types=1);

if (!defined('APP_BASE_PATH')) {
    $basePath = '';
    $projectRoot = realpath(__DIR__ . '/..') ?: '';
    $documentRoot = realpath((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')) ?: '';

    if (
        $projectRoot !== ''
        && $documentRoot !== ''
        && str_starts_with(str_replace('\\', '/', $projectRoot), str_replace('\\', '/', $documentRoot))
    ) {
        $relative = trim(str_replace('\\', '/', substr($projectRoot, strlen($documentRoot))), '/');
        $basePath = $relative === '' ? '' : '/' . $relative;
    } else {
        $script = str_replace('\\', '/', (string) ($_SERVER['SCRIPT_NAME'] ?? ''));
        $scriptDir = trim(dirname($script), '/');
        if ($scriptDir !== '' && $scriptDir !== '.') {
            $segments = explode('/', $scriptDir);
            $basePath = '/' . $segments[0];
        }
    }

    define('APP_BASE_PATH', $basePath);
}

if (session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443)
    );

    $cookiePath = APP_BASE_PATH === '' ? '/' : APP_BASE_PATH . '/';

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => $cookiePath,
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/layout.php';
