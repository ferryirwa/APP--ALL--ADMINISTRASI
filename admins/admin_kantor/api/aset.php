<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query("SELECT * FROM kantor_aset WHERE is_active=1 ORDER BY kode")->fetchAll()]);
    } elseif ($a === 'save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $id = (int)($_POST['id'] ?? 0);
        $foto = null;
        if (!empty($_FILES['foto']['name'])) $foto = kantor_upload($_FILES['foto'], 'aset');
        $p = [
            trim($_POST['kode']), trim($_POST['nama']),
            trim($_POST['kategori'] ?? ''), trim($_POST['lokasi'] ?? ''),
            $_POST['kondisi'] ?? 'baik',
            (float)($_POST['harga_beli'] ?? 0),
            $_POST['tanggal_beli'] ?: null,
            trim($_POST['keterangan'] ?? ''),
        ];
        if ($id > 0) { $p[] = $foto; $p[] = $id;
            db()->prepare("UPDATE kantor_aset SET kode=?,nama=?,kategori=?,lokasi=?,kondisi=?,harga_beli=?,tanggal_beli=?,keterangan=?,foto=COALESCE(?,foto) WHERE id=?")->execute($p);
        } else { $p[] = $foto;
            db()->prepare("INSERT INTO kantor_aset (kode,nama,kategori,lokasi,kondisi,harga_beli,tanggal_beli,keterangan,foto) VALUES (?,?,?,?,?,?,?,?,?)")->execute($p);
        }
        echo json_encode(['ok'=>true,'msg'=>'Aset disimpan.']);
    } elseif ($a === 'delete') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_aset SET is_active=0 WHERE id=?")->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Aset dihapus.']);
    } elseif ($a === 'pinjam_list') {
        echo json_encode(['data' => db()->query(
            "SELECT ap.*, a.kode AS kode_aset, a.nama AS nama_aset, p.nama AS nama_pegawai
             FROM kantor_aset_pinjam ap
             JOIN kantor_aset a ON a.id=ap.aset_id
             JOIN kantor_pegawai p ON p.id=ap.pegawai_id
             ORDER BY ap.id DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'pinjam_save') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("INSERT INTO kantor_aset_pinjam
            (aset_id,pegawai_id,jumlah,tanggal_pinjam,tanggal_kembali_plan,keterangan,user_id)
            VALUES (?,?,?,?,?,?,?)")
            ->execute([
                (int)$_POST['aset_id'], (int)$_POST['pegawai_id'], (int)($_POST['jumlah'] ?? 1),
                $_POST['tanggal_pinjam'], $_POST['tanggal_kembali_plan'],
                trim($_POST['keterangan'] ?? ''), $GLOBALS['USER_ID'] ?? null
            ]);
        echo json_encode(['ok'=>true,'msg'=>'Peminjaman dicatat.']);
    } elseif ($a === 'pinjam_kembali') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        db()->prepare("UPDATE kantor_aset_pinjam SET tanggal_kembali_act=CURDATE(),status='dikembalikan' WHERE id=?")
            ->execute([(int)$_POST['id']]);
        echo json_encode(['ok'=>true,'msg'=>'Aset dikembalikan.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }