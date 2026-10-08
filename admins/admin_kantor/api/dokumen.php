<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        $q = $_GET['q'] ?? '';
        if ($q) {
            $stmt = db()->prepare("SELECT * FROM kantor_dokumen
                WHERE nama LIKE ? OR deskripsi LIKE ? ORDER BY id DESC LIMIT 500");
            $stmt->execute(["%$q%","%$q%"]);
        } else {
            $stmt = db()->query("SELECT * FROM kantor_dokumen ORDER BY id DESC LIMIT 500");
        }
        echo json_encode(['data' => $stmt->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        if (empty($_FILES['file']['name'])) throw new Exception('File wajib diupload.');
        $file = kantor_upload($_FILES['file'], 'dok');
        if (!$file) throw new Exception('Upload gagal.');
        db()->prepare("INSERT INTO kantor_dokumen
            (nama,kategori,file_path,file_size,file_type,deskripsi,akses,user_id)
            VALUES (?,?,?,?,?,?,?,?)")
            ->execute([trim($_POST['nama']), trim($_POST['kategori'] ?? ''),
                       $file, (int)$_FILES['file']['size'],
                       pathinfo($file, PATHINFO_EXTENSION),
                       trim($_POST['deskripsi'] ?? ''), $_POST['akses'] ?? 'internal',
                       $u['user_id']]);
        echo json_encode(['ok'=>true,'msg'=>'Dokumen diunggah.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)$_POST['id'];
        $s = db()->prepare("SELECT file_path FROM kantor_dokumen WHERE id=?");
        $s->execute([$id]);
        $f = $s->fetchColumn();
        if ($f && file_exists(UPLOAD_DIR.$f)) @unlink(UPLOAD_DIR.$f);
        db()->prepare("DELETE FROM kantor_dokumen WHERE id=?")->execute([$id]);
        echo json_encode(['ok'=>true,'msg'=>'Dokumen dihapus.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }