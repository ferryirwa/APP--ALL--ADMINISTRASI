<?php
/**
 * File    : config/app.php
 * Fungsi  : Konfigurasi global + inisialisasi session & helper
 * Author  : Tim Aplikasi Administrasi
 * Tanggal : 2024
 */

declare(strict_types=1);

// ---- Base URL (sesuaikan jika di subfolder) ----
define('APP_NAME', 'APLIKASI ADMINISTRASI');
define('APP_TAGLINE', 'Satu Aplikasi, Semua Administrasi');
define('APP_VERSION', '1.0.0');

// Deteksi base URL otomatis
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
$baseUrl   = preg_replace('#/(admins/[^/]+|config|includes|assets|database)(/.*)?$#', '', $scriptDir);
$baseUrl   = rtrim($baseUrl, '/');
define('BASE_URL', $baseUrl === '' ? '/' : $baseUrl . '/');

// ---- Session ----
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

// ---- Timezone ----
date_default_timezone_set('Asia/Jakarta');

// ---- Error reporting (matikan di produksi) ----
ini_set('display_errors', '1');
error_reporting(E_ALL);

// ---- Load helpers ----
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_helper.php';