<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query(
            "SELECT r.*, p.nama FROM kantor_reimbursement r
             JOIN kantor_pegawai p ON p.id=r.pegawai_id ORDER BY r.id DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $file = null;
        if (!empty($_FILES['file_bukti']['name'])) $file = kantor_upload($_FILES['file_bukti'], 'reimb');
        db()->prepare("INSERT INTO kantor_reimbursement
            (pegawai_id,tanggal,kategori,jumlah,keterangan,file_bukti)
            VALUES (?,?,?,?,?,?)")
            ->execute([(int)$_POST['pegawai_id'],$_POST['tanggal'],trim($_POST['kategori']),
                       (float)$_POST['jumlah'],trim($_POST['keterangan']),$file]);
        echo json_encode(['ok'=>true,'msg'=>'Pengajuan reimbursement disimpan.']);
    } elseif ($a === 'approve') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_reimbursement SET status=?,approver_id=?,catatan=? WHERE id=?")
            ->execute([$_POST['status'],$u['user_id'],$_POST['catatan'] ?? '',(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Status diubah.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("DELETE FROM kantor_reimbursement WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Dihapus.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }