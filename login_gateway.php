<?php
/**
 * File    : login_gateway.php
 * Fungsi  : Cek session modul, arahkan ke dashboard / login
 * Author  : Tim Aplikasi Administrasi
 * Tanggal : 2024
 */

require_once __DIR__ . '/config/app.php';

$slug = trim((string)($_GET['slug'] ?? ''));
$jenis = $slug !== '' ? get_jenis_admin($slug) : null;

if (!$jenis) {
    flash('error', 'Modul administrasi tidak ditemukan.');
    redirect('index.php');
}

// Sudah login? langsung ke dashboard
if (is_logged_in($slug)) {
    redirect("admins/{$slug}/dashboard.php");
}

// Belum login → ke halaman login modul
redirect("admins/{$slug}/login.php");