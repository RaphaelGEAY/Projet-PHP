<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!defined('APP_BASE_PATH')) {
    $script = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $parts = explode('/', trim($script, '/'));
    if (!empty($parts[0])) {
        define('APP_BASE_PATH', '/' . $parts[0]);
    } else {
        define('APP_BASE_PATH', '');
    }
}

require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/layout.php';
