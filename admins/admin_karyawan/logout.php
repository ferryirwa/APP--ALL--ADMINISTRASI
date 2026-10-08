<?php
/**
 * File    : admins/admin_karyawan/logout.php
 * Fungsi  : Logout dari modul Admin Karyawan saja
 */

require_once __DIR__ . '/config.php';

$user = current_user(MODULE_SLUG);
if ($user) {
    log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'], 'logout', 'Logout');
}
auth_logout(MODULE_SLUG);
flash('info', 'Anda telah logout dari ' . MODULE_NAME . '.');
redirect('admins/' . MODULE_SLUG . '/login.php');