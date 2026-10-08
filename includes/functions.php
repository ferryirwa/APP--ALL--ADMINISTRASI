<?php
/**
 * File    : includes/functions.php
 * Fungsi  : Helper umum (escape, CSRF, redirect, flash, log)
 * Author  : Tim Aplikasi Administrasi
 * Tanggal : 2024
 */

declare(strict_types=1);

/** Escape output HTML. */
function e(?string $s): string
{
    return htmlspecialchars((string)$s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/** Redirect relatif terhadap BASE_URL. */
function redirect(string $path): void
{
    $url = preg_match('#^https?://#', $path) ? $path : BASE_URL . ltrim($path, '/');
    header('Location: ' . $url);
    exit;
}

/** URL builder. */
function url(string $path = ''): string
{
    return BASE_URL . ltrim($path, '/');
}

/** Ambil nilai POST/GET dengan default. */
function input(string $key, $default = null, string $method = 'POST')
{
    $src = strtoupper($method) === 'GET' ? $_GET : $_POST;
    return $src[$key] ?? $default;
}

/** CSRF token generator. */
function csrf_token(): string
{
    if (empty($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

/** Field hidden CSRF. */
function csrf_field(): string
{
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

/** Validasi CSRF. */
function csrf_verify(?string $token): bool
{
    return is_string($token) && !empty($_SESSION['_csrf'])
        && hash_equals($_SESSION['_csrf'], $token);
}

/** Set flash message. */
function flash(string $key, ?string $msg = null): ?string
{
    if ($msg === null) {
        $val = $_SESSION['_flash'][$key] ?? null;
        unset($_SESSION['_flash'][$key]);
        return $val;
    }
    $_SESSION['_flash'][$key] = $msg;
    return null;
}

/** Tampilkan flash alert Bootstrap. */
function flash_alert(string $key, string $type = 'info'): string
{
    $msg = flash($key);
    if (!$msg) return '';
    return '<div class="alert alert-' . e($type) . ' alert-dismissible fade show">'
         . e($msg)
         . '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
}

/** Catat audit log. */
function log_aksi(int $jenisId, ?int $userId, ?string $username, string $aksi, string $ket = ''): void
{
    try {
        $stmt = db()->prepare(
            'INSERT INTO admins_log (jenis_admin_id, user_id, username, aksi, keterangan, ip_address, user_agent)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $jenisId,
            $userId,
            $username,
            $aksi,
            $ket,
            $_SERVER['REMOTE_ADDR'] ?? null,
            substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 255),
        ]);
    } catch (Throwable $e) {
        // Diamkan agar tidak mengganggu alur
    }
}

/** Ambil data jenis admin berdasarkan slug. */
function get_jenis_admin(string $slug): ?array
{
    $stmt = db()->prepare('SELECT * FROM admins_jenis WHERE slug = ? AND aktif = 1 LIMIT 1');
    $stmt->execute([$slug]);
    $row = $stmt->fetch();
    return $row ?: null;
}

/** Ambil semua jenis admin aktif. */
function get_semua_jenis_admin(): array
{
    return db()->query('SELECT * FROM admins_jenis WHERE aktif = 1 ORDER BY urutan ASC, nama ASC')
               ->fetchAll();
}

/** Statistik global untuk halaman utama. */
function get_statistik_global(): array
{
    $totalJenis = (int) db()->query('SELECT COUNT(*) FROM admins_jenis WHERE aktif = 1')->fetchColumn();
    $totalUser  = (int) db()->query('SELECT COUNT(*) FROM admins_users WHERE is_active = 1')->fetchColumn();
    $totalLogin = (int) db()->query('SELECT COUNT(*) FROM admins_log WHERE aksi = "login"')->fetchColumn();
    return compact('totalJenis', 'totalUser', 'totalLogin');
}