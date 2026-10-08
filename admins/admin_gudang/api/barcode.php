<?php
/**
 * File    : admins/admin_gudang/api/barcode.php
 * Fungsi  : Cari barang berdasarkan barcode/kode
 */

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }

$kode = trim((string)($_GET['kode'] ?? $_POST['kode'] ?? ''));
if ($kode === '') { echo json_encode(['ok' => false, 'msg' => 'Kode kosong.']); exit; }

$barang = gudang_cari_barang($kode);
if (!$barang) {
    echo json_encode(['ok' => false, 'msg' => 'Barang tidak ditemukan untuk kode: ' . $kode]);
    exit;
}

$stok = gudang_stok_terkini((int)$barang['id']);

// Cek apakah user minta aksi cepat
$aksi = $_POST['aksi'] ?? $_GET['aksi'] ?? '';
if ($aksi === 'stok_masuk' || $aksi === 'stok_keluar') {
    if (!csrf_verify($_POST['_csrf'] ?? null)) {
        echo json_encode(['ok' => false, 'msg' => 'CSRF invalid']); exit;
    }
    $jml = (int)($_POST['jumlah'] ?? 0);
    if ($jml <= 0) { echo json_encode(['ok' => false, 'msg' => 'Jumlah harus > 0']); exit; }
    $tipe = $aksi === 'stok_masuk' ? 'masuk' : 'keluar';
    $user = current_user(MODULE_SLUG);

    db()->prepare("INSERT INTO gudang_stok (barang_id, tipe, jumlah, keterangan, tanggal, user_id)
                   VALUES (?,?,?,?,CURDATE(),?)")
        ->execute([$barang['id'], $tipe, $jml, 'Input cepat via scan barcode', $user['user_id']]);

    log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
             'scan_' . $tipe, "Scan $kode, $tipe $jml");

    echo json_encode([
        'ok' => true,
        'msg' => "Stok $tipe berhasil: {$jml} {$barang['satuan']}",
        'barang' => $barang,
        'stok_baru' => gudang_stok_terkini((int)$barang['id']),
    ]);
    exit;
}

echo json_encode(['ok' => true, 'barang' => $barang, 'stok' => $stok]);