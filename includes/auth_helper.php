<?php
/**
 * File    : includes/auth_helper.php
 * Fungsi  : Helper autentikasi terisolasi per modul admin
 * Author  : Tim Aplikasi Administrasi
 * Tanggal : 2024
 */

declare(strict_types=1);

/** Kunci session per modul. */
function session_key(string $moduleSlug): string
{
    return 'auth_' . $moduleSlug;
}

/** Cek apakah user sudah login di modul tertentu. */
function is_logged_in(string $moduleSlug): bool
{
    $key = session_key($moduleSlug);
    return !empty($_SESSION[$key]['user_id']);
}

/** Ambil data user yang login di modul tertentu. */
function current_user(string $moduleSlug): ?array
{
    $key = session_key($moduleSlug);
    return $_SESSION[$key] ?? null;
}

/**
 * Wajib login. Jika belum, redirect ke login modul.
 * @param string $moduleSlug slug modul (mis. 'admin_gudang')
 */
function auth_check(string $moduleSlug): void
{
    if (!is_logged_in($moduleSlug)) {
        $_SESSION['_redirect_after_login'] = $_SERVER['REQUEST_URI'] ?? null;
        redirect("admins/{$moduleSlug}/login.php");
    }
}

/** Set session login. */
function auth_login(string $moduleSlug, array $user): void
{
    session_regenerate_id(true);
    $_SESSION[session_key($moduleSlug)] = [
        'user_id'       => (int) $user['id'],
        'username'      => $user['username'],
        'nama_lengkap'  => $user['nama_lengkap'],
        'email'         => $user['email'],
        'jenis_admin_id'=> (int) $user['jenis_admin_id'],
        'login_at'      => time(),
    ];
}

/** Logout dari modul tertentu saja. */
function auth_logout(string $moduleSlug): void
{
    unset($_SESSION[session_key($moduleSlug)]);
}

/**
 * Cek kredensial login.
 * @return array|null data user jika valid
 */
function auth_attempt(string $moduleSlug, string $username, string $password): ?array
{
    $jenis = get_jenis_admin($moduleSlug);
    if (!$jenis) return null;

    $stmt = db()->prepare(
        'SELECT * FROM admins_users
         WHERE jenis_admin_id = ? AND (username = ? OR email = ?) AND is_active = 1
         LIMIT 1'
    );
    $stmt->execute([$jenis['id'], $username, $username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        db()->prepare('UPDATE admins_users SET last_login = NOW() WHERE id = ?')
            ->execute([$user['id']]);
        return $user;
    }
    return null;
}

/**
 * Registrasi user baru di modul tertentu.
 * @return array{ok:bool, msg:string, user_id?:int}
 */
function auth_register(string $moduleSlug, array $data): array
{
    $jenis = get_jenis_admin($moduleSlug);
    if (!$jenis) return ['ok' => false, 'msg' => 'Modul tidak ditemukan.'];

    $username = trim((string)($data['username'] ?? ''));
    $email    = trim((string)($data['email'] ?? ''));
    $nama     = trim((string)($data['nama_lengkap'] ?? ''));
    $pass     = (string)($data['password'] ?? '');

    if ($username === '' || $email === '' || $nama === '' || $pass === '') {
        return ['ok' => false, 'msg' => 'Semua field wajib diisi.'];
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return ['ok' => false, 'msg' => 'Format email tidak valid.'];
    }
    if (strlen($pass) < 6) {
        return ['ok' => false, 'msg' => 'Password minimal 6 karakter.'];
    }

    // Cek duplikat di modul yang sama
    $cek = db()->prepare('SELECT id FROM admins_users WHERE jenis_admin_id = ? AND (username = ? OR email = ?) LIMIT 1');
    $cek->execute([$jenis['id'], $username, $email]);
    if ($cek->fetch()) {
        return ['ok' => false, 'msg' => 'Username atau email sudah terdaftar di modul ini.'];
    }

    $stmt = db()->prepare(
        'INSERT INTO admins_users (jenis_admin_id, username, email, password, nama_lengkap)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $jenis['id'],
        $username,
        $email,
        password_hash($pass, PASSWORD_BCRYPT),
        $nama,
    ]);

    return ['ok' => true, 'msg' => 'Registrasi berhasil.', 'user_id' => (int) db()->lastInsertId()];
}