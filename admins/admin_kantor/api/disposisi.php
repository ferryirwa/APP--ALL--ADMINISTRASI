<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }

$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query(
            "SELECT d.*, s.no_agenda, s.perihal, s.asal_surat
             FROM kantor_disposisi d JOIN kantor_surat_masuk s ON s.id=d.surat_masuk_id
             ORDER BY d.id DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $params = [
            (int)$_POST['surat_masuk_id'],
            $u['user_id'],
            trim($_POST['kepada']),
            trim($_POST['isi_disposisi']),
            $_POST['sifat'] ?? 'biasa',
            $_POST['batas_waktu'] ?: null,
            $_POST['status'] ?? 'baru',
            $u['user_id'],
        ];
        if ($id > 0) {
            $params[] = $id;
            db()->prepare("UPDATE kantor_disposisi SET surat_masuk_id=?,dari_user_id=?,kepada=?,
                isi_disposisi=?,sifat=?,batas_waktu=?,status=?,user_id=? WHERE id=?")->execute($params);
            echo json_encode(['ok'=>true,'msg'=>'Disposisi diperbarui.']);
        } else {
            db()->prepare("INSERT INTO kantor_disposisi
                (surat_masuk_id,dari_user_id,kepada,isi_disposisi,sifat,batas_waktu,status,user_id)
                VALUES (?,?,?,?,?,?,?,?)")->execute($params);
            // Update status surat jadi diproses
            db()->prepare("UPDATE kantor_surat_masuk SET status='diproses' WHERE id=? AND status='baru'")
                ->execute([(int)$_POST['surat_masuk_id']]);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],'create_disposisi','');
            echo json_encode(['ok'=>true,'msg'=>'Disposisi dibuat.']);
        }
    } elseif ($a === 'update_status') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_disposisi SET status=? WHERE id=?")
            ->execute([$_POST['status'], (int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Status disposisi diubah.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("DELETE FROM kantor_disposisi WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Disposisi dihapus.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }