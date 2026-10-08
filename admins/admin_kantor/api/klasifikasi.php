<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }

$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query("SELECT * FROM kantor_klasifikasi WHERE is_active=1 ORDER BY kode")->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id   = (int)($_POST['id'] ?? 0);
        $kode = strtoupper(trim($_POST['kode'] ?? ''));
        $nama = trim($_POST['nama'] ?? '');
        $ret  = (int)($_POST['retensi_tahun'] ?? 5);
        if ($kode===''||$nama==='') throw new Exception('Kode & nama wajib.');
        if ($id>0) {
            db()->prepare("UPDATE kantor_klasifikasi SET kode=?,nama=?,retensi_tahun=? WHERE id=?")
                ->execute([$kode,$nama,$ret,$id]);
        } else {
            db()->prepare("INSERT INTO kantor_klasifikasi (kode,nama,retensi_tahun) VALUES (?,?,?)")
                ->execute([$kode,$nama,$ret]);
        }
        log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],'save_klasifikasi',$kode);
        echo json_encode(['ok'=>true,'msg'=>'Klasifikasi disimpan.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_klasifikasi SET is_active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Klasifikasi dihapus.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }