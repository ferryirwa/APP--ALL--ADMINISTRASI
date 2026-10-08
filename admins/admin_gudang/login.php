<?php
/**
 * File    : admins/admin_gudang/login.php
 * Fungsi  : Login khusus Admin Gudang
 */

require_once __DIR__ . '/config.php';

// Kalau sudah login, langsung dashboard
if (is_logged_in(MODULE_SLUG)) {
    redirect('admins/' . MODULE_SLUG . '/dashboard.php');
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $username = trim((string)($_POST['username'] ?? ''));
        $password = (string)($_POST['password'] ?? '');

        if ($username === '' || $password === '') {
            $error = 'Username dan password wajib diisi.';
        } else {
            $user = auth_attempt(MODULE_SLUG, $username, $password);
            if ($user) {
                auth_login(MODULE_SLUG, $user);
                log_aksi((int)$user['jenis_admin_id'], (int)$user['id'], $user['username'], 'login', 'Login berhasil');
                $redirect = $_SESSION['_redirect_after_login'] ?? null;
                unset($_SESSION['_redirect_after_login']);
                if ($redirect) { header('Location: ' . $redirect); exit; }
                redirect('admins/' . MODULE_SLUG . '/dashboard.php');
            } else {
                $error = 'Username/email atau password salah.';
                log_aksi((int)$GLOBALS['MODULE_INFO']['id'], null, $username, 'login_gagal', 'Percobaan login gagal');
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Login <?= e(MODULE_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
<link href="<?= e(url('assets/css/style.css')) ?>" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center">
  <div class="card shadow border-0" style="max-width:420px;width:100%">
    <div class="card-body p-4">
      <div class="text-center mb-3">
        <i class="<?= e($GLOBALS['MODULE_INFO']['icon']) ?> fa-3x mb-2" style="color:<?= e($GLOBALS['MODULE_INFO']['warna']) ?>"></i>
        <h4 class="fw-bold mb-0">Login <?= e(MODULE_NAME) ?></h4>
        <small class="text-muted">Modul terpisah — akun tidak berlaku di modul lain</small>
      </div>

      <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
      <?php endif; ?>

      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Username / Email</label>
          <input type="text" name="username" class="form-control" required autofocus>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button class="btn btn-primary w-100 mb-2">
          <i class="fa-solid fa-right-to-bracket me-1"></i> Masuk
        </button>
      </form>

      <div class="text-center mt-3">
        <small>Belum punya akun? <a href="register.php">Registrasi di sini</a></small><br>
        <small><a href="<?= e(url('index.php')) ?>">&larr; Kembali ke halaman utama</a></small>
      </div>
    </div>
  </div>
</div>
</body>
</html>