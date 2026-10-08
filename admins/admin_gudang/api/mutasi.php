<?php
/**
 * File    : admins/admin_gudang/api/mutasi.php
 * Fungsi  : Endpoint AJAX mutasi
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
            $sql = "SELECT m.*, b.kode, b.nama AS nama_barang, s.nama AS nama_supplier
                    FROM gudang_mutasi m
                    JOIN gudang_barang b ON b.id = m.barang_id
                    LEFT JOIN gudang_supplier s ON s.id = m.supplier_id
                    ORDER BY m.id DESC LIMIT 500";
            $rows = db()->query($sql)->fetchAll();
            echo json_encode(['data' => $rows]);
            break;

        case 'save':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $bid = (int)($_POST['barang_id'] ?? 0);
            $sid = (int)($_POST['supplier_id'] ?? 0) ?: null;
            $tipe = $_POST['tipe'] ?? 'masuk';
            $jml  = (int)($_POST['jumlah'] ?? 0);

            if ($bid <= 0) throw new Exception('Barang wajib dipilih.');
            if ($jml <= 0) throw new Exception('Jumlah harus > 0.');
            if (!in_array($tipe, ['masuk','keluar'], true)) throw new Exception('Tipe invalid.');

            $ref = gudang_gen_referensi();
            $tgl = $_POST['tanggal'] ?? date('Y-m-d');
            $ket = trim((string)($_POST['keterangan'] ?? ''));

            db()->beginTransaction();

            db()->prepare("INSERT INTO gudang_mutasi
                (no_referensi,barang_id,supplier_id,tipe,jumlah,tanggal,keterangan,user_id)
                VALUES (?,?,?,?,?,?,?,?)")
                ->execute([$ref,$bid,$sid,$tipe,$jml,$tgl,$ket,$user['user_id']]);

            db()->prepare("INSERT INTO gudang_stok
                (barang_id,tipe,jumlah,keterangan,tanggal,user_id)
                VALUES (?,?,?,?,?,?)")
                ->execute([$bid,$tipe,$jml,"Mutasi $ref",$tgl,$user['user_id']]);

            db()->commit();

            log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                     'mutasi', "$ref $tipe $jml");
            echo json_encode(['ok'=>true,'msg'=>"Mutasi $ref tersimpan."]);
            break;

        case 'delete':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            db()->prepare("DELETE FROM gudang_mutasi WHERE id=?")->execute([(int)$_POST['id']]);
            echo json_encode(['ok'=>true,'msg'=>'Mutasi dihapus.']);
            break;

        default:
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $e) {
    if (db()->inTransaction()) db()->rollBack();
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}