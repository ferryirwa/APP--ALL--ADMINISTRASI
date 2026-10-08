<?php
/**
 * File    : admins/admin_karyawan/config.php
 * Fungsi  : Konfigurasi modul Admin Karyawan
 * Author  : Tim Aplikasi Administrasi
 */

declare(strict_types=1);

define('MODULE_SLUG',        'admin_karyawan');
define('MODULE_NAME',        'Admin Karyawan');
define('MODULE_SESSION_KEY', 'auth_admin_karyawan');

require_once __DIR__ . '/../../config/app.php';

$GLOBALS['MODULE_INFO'] = get_jenis_admin(MODULE_SLUG);
if (!$GLOBALS['MODULE_INFO']) {
    http_response_code(500);
    exit('Modul "' . MODULE_SLUG . '" tidak terdaftar di database.');
}