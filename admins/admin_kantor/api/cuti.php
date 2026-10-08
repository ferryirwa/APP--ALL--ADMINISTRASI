<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query(
            "SELECT c.*, p.nama, p.nip FROM kantor_cuti c
             JOIN kantor_pegawai p ON p.id=c.pegawai_id ORDER BY c.id DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $file = null;
        if (!empty($_FILES['file_bukti']['name'])) $file = kantor_upload($_FILES['file_bukti'], 'cuti');
        $p = [
            (int)$_POST['pegawai_id'], $_POST['jenis'],
            $_POST['tanggal_mulai'], $_POST['tanggal_selesai'],
            trim($_POST['alasan']), $file, $u['user_id'],
        ];
        if ($id > 0) { $p[] = $id;
            db()->prepare("UPDATE kantor_cuti SET pegawai_id=?,jenis=?,tanggal_mulai=?,tanggal_selesai=?,alasan=?,file_bukti=COALESCE(?,file_bukti),user_id=? WHERE id=?")->execute($p);
        } else {
            db()->prepare("INSERT INTO kantor_cuti (pegawai_id,jenis,tanggal_mulai,tanggal_selesai,alasan,file_bukti,user_id) VALUES (?,?,?,?,?,?,?)")->execute($p);
        }
        echo json_encode(['ok'=>true,'msg'=>'Pengajuan disimpan.']);
    } elseif ($a === 'approve') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)$_POST['id']; $status = $_POST['status'];
        if (!in_array($status,['disetujui','ditolak','selesai'])) throw new Exception('Status invalid');
        db()->prepare("UPDATE kantor_cuti SET status=?,approver_id=?,catatan_approver=? WHERE id=?")
            ->execute([$status, $u['user_id'], $_POST['catatan'] ?? '', $id]);
        echo json_encode(['ok'=>true,'msg'=>'Status pengajuan diubah.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("DELETE FROM kantor_cuti WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Pengajuan dihapus.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }