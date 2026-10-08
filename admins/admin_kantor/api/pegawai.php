<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query("SELECT * FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $p = [
            trim($_POST['nip']), trim($_POST['nama']),
            trim($_POST['jabatan'] ?? ''), trim($_POST['departemen'] ?? ''),
            trim($_POST['email'] ?? ''), trim($_POST['telepon'] ?? ''),
            (float)($_POST['gaji_pokok'] ?? 0),
        ];
        if ($id > 0) { $p[] = $id;
            db()->prepare("UPDATE kantor_pegawai SET nip=?,nama=?,jabatan=?,departemen=?,email=?,telepon=?,gaji_pokok=? WHERE id=?")->execute($p);
        } else {
            db()->prepare("INSERT INTO kantor_pegawai (nip,nama,jabatan,departemen,email,telepon,gaji_pokok) VALUES (?,?,?,?,?,?,?)")->execute($p);
        }
        echo json_encode(['ok'=>true,'msg'=>'Pegawai disimpan.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_pegawai SET is_active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Pegawai dihapus.']);
    } elseif ($a === 'all') {
        echo json_encode(['data' => db()->query("SELECT id,nip,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll()]);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }