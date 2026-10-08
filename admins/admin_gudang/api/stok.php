<?php
/**
 * File    : admins/admin_gudang/api/stok.php
 * Fungsi  : Endpoint AJAX stok
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
            $sql = "SELECT s.*, b.kode, b.nama AS nama_barang, b.satuan
                    FROM gudang_stok s
                    JOIN gudang_barang b ON b.id = s.barang_id
                    ORDER BY s.id DESC LIMIT 500";
            $rows = db()->query($sql)->fetchAll();
            echo json_encode(['data' => $rows]);
            break;

        case 'save':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $bid = (int)($_POST['barang_id'] ?? 0);
            $tipe = $_POST['tipe'] ?? 'masuk';
            $jml = (int)($_POST['jumlah'] ?? 0);
            if ($bid <= 0) throw new Exception('Barang wajib dipilih.');
            if ($jml <= 0) throw new Exception('Jumlah harus > 0.');
            if (!in_array($tipe, ['masuk','keluar','koreksi'], true)) throw new Exception('Tipe invalid.');

            db()->prepare("INSERT INTO gudang_stok (barang_id,tipe,jumlah,keterangan,tanggal,user_id)
                           VALUES (?,?,?,?,?,?)")
                ->execute([
                    $bid, $tipe, $jml,
                    trim((string)($_POST['keterangan'] ?? '')),
                    $_POST['tanggal'] ?? date('Y-m-d'),
                    $user['user_id']
                ]);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                     'stok_'.$tipe, "Barang #$bid $tipe $jml");
            echo json_encode(['ok'=>true,'msg'=>'Stok dicatat.']);
            break;

        case 'delete':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            db()->prepare("DELETE FROM gudang_stok WHERE id=?")->execute([(int)$_POST['id']]);
            echo json_encode(['ok'=>true,'msg'=>'Riwayat stok dihapus.']);
            break;

        default:
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}