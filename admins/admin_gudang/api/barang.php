<?php
/**
 * File    : admins/admin_gudang/api/barang.php
 * Fungsi  : Endpoint AJAX barang
 */

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in(MODULE_SLUG)) {
    http_response_code(401);
    echo json_encode(['error'=>'unauthorized']);
    exit;
}

$action = $_GET['action'] ?? $_POST['action'] ?? 'list';
$user   = current_user(MODULE_SLUG);

try {
    switch ($action) {
        case 'list':
            $rows = db()->query(
                "SELECT b.*, COALESCE(
                    (SELECT SUM(CASE WHEN s.tipe='masuk'  THEN s.jumlah
                                     WHEN s.tipe='keluar' THEN -s.jumlah END)
                     FROM gudang_stok s WHERE s.barang_id=b.id),0) AS stok
                 FROM gudang_barang b WHERE b.is_active=1 ORDER BY b.id DESC"
            )->fetchAll();
            echo json_encode(['data' => $rows]);
            break;

        case 'get':
            $stmt = db()->prepare("SELECT * FROM gudang_barang WHERE id=? LIMIT 1");
            $stmt->execute([(int)($_GET['id'] ?? 0)]);
            echo json_encode(['data' => $stmt->fetch() ?: null]);
            break;

        case 'save':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $id    = (int)($_POST['id'] ?? 0);
            $kode  = trim((string)($_POST['kode'] ?? ''));
            $barc  = trim((string)($_POST['barcode'] ?? ''));
            $nama  = trim((string)($_POST['nama'] ?? ''));
            $kat   = trim((string)($_POST['kategori'] ?? ''));
            $sat   = trim((string)($_POST['satuan'] ?? 'pcs'));
            $hBeli = (float)($_POST['harga_beli'] ?? 0);
            $hJual = (float)($_POST['harga_jual'] ?? 0);
            $min   = (int)($_POST['stok_minimum'] ?? 0);
            $rak   = trim((string)($_POST['lokasi_rak'] ?? ''));

            if ($kode === '' || $nama === '') throw new Exception('Kode & nama wajib diisi.');
            if ($barc === '') $barc = gudang_gen_barcode($kode);

            if ($id > 0) {
                $stmt = db()->prepare("UPDATE gudang_barang SET kode=?,barcode=?,nama=?,kategori=?,satuan=?,
                    harga_beli=?,harga_jual=?,stok_minimum=?,lokasi_rak=? WHERE id=?");
                $stmt->execute([$kode,$barc,$nama,$kat,$sat,$hBeli,$hJual,$min,$rak,$id]);
                log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                         'update_barang', "Update barang #$id ($kode)");
                echo json_encode(['ok'=>true,'msg'=>'Barang diperbarui.']);
            } else {
                $stmt = db()->prepare("INSERT INTO gudang_barang
                    (kode,barcode,nama,kategori,satuan,harga_beli,harga_jual,stok_minimum,lokasi_rak)
                    VALUES (?,?,?,?,?,?,?,?,?)");
                $stmt->execute([$kode,$barc,$nama,$kat,$sat,$hBeli,$hJual,$min,$rak]);
                $newId = (int) db()->lastInsertId();
                log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                         'create_barang', "Tambah barang #$newId ($kode)");
                echo json_encode(['ok'=>true,'msg'=>'Barang ditambahkan.','id'=>$newId]);
            }
            break;

        case 'delete':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $id = (int)($_POST['id'] ?? 0);
            if ($id <= 0) throw new Exception('ID tidak valid.');
            db()->prepare("UPDATE gudang_barang SET is_active=0 WHERE id=?")->execute([$id]);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                     'delete_barang', "Hapus barang #$id");
            echo json_encode(['ok'=>true,'msg'=>'Barang dihapus.']);
            break;

        default:
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}