<?php
/**
 * File    : admins/admin_kantor/register.php
 * Fungsi  : Registrasi khusus Admin Kantor
 */

require_once __DIR__ . '/config.php';

if (is_logged_in(MODULE_SLUG)) {
    redirect('admins/' . MODULE_SLUG . '/dashboard.php');
}

$error = ''; $sukses = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        $error = 'Token CSRF tidak valid.';
    } else {
        $res = auth_register(MODULE_SLUG, [
            'username'     => $_POST['username']     ?? '',
            'email'        => $_POST['email']        ?? '',
            'nama_lengkap' => $_POST['nama_lengkap'] ?? '',
            'password'     => $_POST['password']     ?? '',
        ]);
        if ($res['ok']) {
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'], $res['user_id'] ?? null,
                     $_POST['username'] ?? null, 'register', 'Registrasi akun baru');
            $sukses = 'Registrasi berhasil! Silakan login.';
        } else {
            $error = $res['msg'];
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Registrasi <?= e(MODULE_NAME) ?></title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container min-vh-100 d-flex align-items-center justify-content-center py-4">
  <div class="card shadow border-0" style="max-width:460px;width:100%">
    <div class="card-body p-4">
      <div class="text-center mb-3">
        <i class="<?= e($GLOBALS['MODULE_INFO']['icon']) ?> fa-3x mb-2" style="color:<?= e($GLOBALS['MODULE_INFO']['warna']) ?>"></i>
        <h4 class="fw-bold mb-0">Registrasi <?= e(MODULE_NAME) ?></h4>
      </div>

      <?php if ($error):  ?><div class="alert alert-danger"><?= e($error) ?></div><?php endif; ?>
      <?php if ($sukses): ?><div class="alert alert-success"><?= e($sukses) ?></div><?php endif; ?>

      <form method="post" autocomplete="off">
        <?= csrf_field() ?>
        <div class="mb-3">
          <label class="form-label">Nama Lengkap</label>
          <input type="text" name="nama_lengkap" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Username</label>
          <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" minlength="6" required>
          <small class="text-muted">Minimal 6 karakter</small>
        </div>
        <button class="btn btn-success w-100 mb-2">
          <i class="fa-solid fa-user-plus me-1"></i> Daftar
        </button>
      </form>

      <div class="text-center mt-3">
        <small>Sudah punya akun? <a href="login.php">Login di sini</a></small><br>
        <small><a href="<?= e(url('index.php')) ?>">&larr; Kembali ke halaman utama</a></small>
      </div>
    </div>
  </div>
</div>
</body>
</html>