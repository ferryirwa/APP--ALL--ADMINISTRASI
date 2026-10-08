<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query("SELECT * FROM kantor_kendaraan WHERE is_active=1 ORDER BY kode")->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $p = [trim($_POST['kode']),trim($_POST['nama']),trim($_POST['plat_nomor']),
              trim($_POST['jenis'] ?? ''),(int)($_POST['kapasitas'] ?? 4),trim($_POST['driver'] ?? '')];
        if ($id > 0) { $p[] = $id;
            db()->prepare("UPDATE kantor_kendaraan SET kode=?,nama=?,plat_nomor=?,jenis=?,kapasitas=?,driver=? WHERE id=?")->execute($p);
        } else {
            db()->prepare("INSERT INTO kantor_kendaraan (kode,nama,plat_nomor,jenis,kapasitas,driver) VALUES (?,?,?,?,?,?)")->execute($p);
        }
        echo json_encode(['ok'=>true,'msg'=>'Kendaraan disimpan.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_kendaraan SET is_active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Kendaraan dihapus.']);
    } elseif ($a === 'booking_list') {
        echo json_encode(['data' => db()->query(
            "SELECT bk.*, k.nama AS nama_kendaraan, k.plat_nomor, p.nama AS nama_pegawai
             FROM kantor_booking_kendaraan bk
             JOIN kantor_kendaraan k ON k.id=bk.kendaraan_id
             JOIN kantor_pegawai p ON p.id=bk.pegawai_id
             ORDER BY bk.tanggal DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'booking_save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("INSERT INTO kantor_booking_kendaraan
            (kendaraan_id,pegawai_id,tujuan,keperluan,tanggal,jam_mulai,jam_selesai)
            VALUES (?,?,?,?,?,?,?)")
            ->execute([(int)$_POST['kendaraan_id'],(int)$_POST['pegawai_id'],
                       trim($_POST['tujuan']),trim($_POST['keperluan']),
                       $_POST['tanggal'],$_POST['jam_mulai'],$_POST['jam_selesai']]);
        echo json_encode(['ok'=>true,'msg'=>'Booking kendaraan dicatat.']);
    } elseif ($a === 'booking_status') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_booking_kendaraan SET status=? WHERE id=?")
            ->execute([$_POST['status'],(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Status diubah.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }