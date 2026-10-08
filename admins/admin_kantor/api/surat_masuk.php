<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }

$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query(
            "SELECT s.*, k.nama AS nama_klasifikasi FROM kantor_surat_masuk s
             LEFT JOIN kantor_klasifikasi k ON k.id=s.klasifikasi_id
             ORDER BY s.id DESC LIMIT 500"
        )->fetchAll()]);

    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id  = (int)($_POST['id'] ?? 0);
        $klas= (int)($_POST['klasifikasi_id'] ?? 1);
        $file= null;
        if (!empty($_FILES['file_scan']['name'])) $file = kantor_upload($_FILES['file_scan'], 'sm');

        $params = [
            trim($_POST['no_surat'] ?? ''),
            $klas,
            $_POST['tanggal_surat'] ?? date('Y-m-d'),
            $_POST['tanggal_terima'] ?? date('Y-m-d'),
            trim($_POST['asal_surat'] ?? ''),
            trim($_POST['perihal'] ?? ''),
            $_POST['sifat'] ?? 'biasa',
            trim($_POST['lampiran'] ?? ''),
            trim($_POST['tembusan'] ?? ''),
            trim($_POST['keterangan'] ?? ''),
            $_POST['status'] ?? 'baru',
        ];

        if ($id > 0) {
            $params[] = $file; $params[] = $id;
            db()->prepare("UPDATE kantor_surat_masuk SET no_surat=?,klasifikasi_id=?,tanggal_surat=?,
                tanggal_terima=?,asal_surat=?,perihal=?,sifat=?,lampiran=?,tembusan=?,keterangan=?,status=?,
                file_scan=COALESCE(?,file_scan) WHERE id=?")->execute($params);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],'update_sm',"ID $id");
            echo json_encode(['ok'=>true,'msg'=>'Surat masuk diperbarui.']);
        } else {
            $agenda = kantor_gen_agenda('masuk', $klas);
            $params[] = $file; $params[] = $agenda; $params[] = $u['user_id'];
            db()->prepare("INSERT INTO kantor_surat_masuk
                (no_surat,klasifikasi_id,tanggal_surat,tanggal_terima,asal_surat,perihal,sifat,
                 lampiran,tembusan,keterangan,status,file_scan,no_agenda,user_id)
                VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute($params);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],'create_sm',$agenda);
            echo json_encode(['ok'=>true,'msg'=>"Surat masuk $agenda disimpan.",'no_agenda'=>$agenda]);
        }

    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)$_POST['id'];
        $f = db()->prepare("SELECT file_scan FROM kantor_surat_masuk WHERE id=?");
        $f->execute([$id]);
        $file = $f->fetchColumn();
        if ($file && file_exists(UPLOAD_DIR.$file)) @unlink(UPLOAD_DIR.$file);
        db()->prepare("DELETE FROM kantor_surat_masuk WHERE id=?")->execute([$id]);
        log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],'delete_sm',"ID $id");
        echo json_encode(['ok'=>true,'msg'=>'Surat dihapus.']);

    } elseif ($a === 'get') {
        $stmt = db()->prepare("SELECT * FROM kantor_surat_masuk WHERE id=?");
        $stmt->execute([(int)$_GET['id']]);
        echo json_encode(['data'=>$stmt->fetch()]);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }