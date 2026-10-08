<?php
/**
 * File    : admins/admin_karyawan/index.php
 * Fungsi  : Router internal modul Admin Karyawan
 */

require_once __DIR__ . '/config.php';

if (is_logged_in(MODULE_SLUG)) {
    redirect('admins/' . MODULE_SLUG . '/dashboard.php');
} else {
    redirect('admins/' . MODULE_SLUG . '/login.php');
}