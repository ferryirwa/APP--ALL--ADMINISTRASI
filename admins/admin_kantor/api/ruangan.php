<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query("SELECT * FROM kantor_ruangan WHERE is_active=1 ORDER BY kode")->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $p = [trim($_POST['kode']), trim($_POST['nama']), (int)$_POST['kapasitas'],
              trim($_POST['fasilitas'] ?? ''), trim($_POST['lokasi'] ?? '')];
        if ($id > 0) { $p[] = $id;
            db()->prepare("UPDATE kantor_ruangan SET kode=?,nama=?,kapasitas=?,fasilitas=?,lokasi=? WHERE id=?")->execute($p);
        } else {
            db()->prepare("INSERT INTO kantor_ruangan (kode,nama,kapasitas,fasilitas,lokasi) VALUES (?,?,?,?,?)")->execute($p);
        }
        echo json_encode(['ok'=>true,'msg'=>'Ruangan disimpan.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_ruangan SET is_active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Ruangan dihapus.']);
    } elseif ($a === 'booking_list') {
        echo json_encode(['data' => db()->query(
            "SELECT b.*, r.nama AS nama_ruangan, p.nama AS nama_pegawai
             FROM kantor_booking_ruangan b
             JOIN kantor_ruangan r ON r.id=b.ruangan_id
             JOIN kantor_pegawai p ON p.id=b.pegawai_id
             ORDER BY b.tanggal DESC, b.jam_mulai DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'booking_save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        // Cek bentrok jadwal
        $stmt = db()->prepare("SELECT COUNT(*) FROM kantor_booking_ruangan
            WHERE ruangan_id=? AND tanggal=? AND status IN ('menunggu','disetujui')
            AND ((jam_mulai < ? AND jam_selesai > ?) OR (jam_mulai < ? AND jam_selesai > ?))
        ");
        // Sederhananya: cek overlap (mulai < selesai_lain AND selesai > mulai_lain)
        $stmt = db()->prepare("SELECT COUNT(*) FROM kantor_booking_ruangan
            WHERE ruangan_id=? AND tanggal=? AND status IN ('menunggu','disetujui')
            AND (jam_mulai < ? AND jam_selesai > ?)");
        $stmt->execute([(int)$_POST['ruangan_id'], $_POST['tanggal'], $_POST['jam_selesai'], $_POST['jam_mulai']]);
        if ((int)$stmt->fetchColumn() > 0) throw new Exception('Jadwal bentrok dengan booking lain.');

        db()->prepare("INSERT INTO kantor_booking_ruangan
            (ruangan_id,pegawai_id,kegiatan,tanggal,jam_mulai,jam_selesai,jumlah_peserta,keterangan)
            VALUES (?,?,?,?,?,?,?,?)")
            ->execute([(int)$_POST['ruangan_id'],(int)$_POST['pegawai_id'],trim($_POST['kegiatan']),
                       $_POST['tanggal'],$_POST['jam_mulai'],$_POST['jam_selesai'],
                       (int)($_POST['jumlah_peserta'] ?? 0), trim($_POST['keterangan'] ?? '')]);
        echo json_encode(['ok'=>true,'msg'=>'Booking dicatat.']);
    } elseif ($a === 'booking_status') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_booking_ruangan SET status=? WHERE id=?")
            ->execute([$_POST['status'], (int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Status diubah.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }