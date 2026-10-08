<?php
/**
 * File    : admins/admin_gudang/config.php
 * Fungsi  : Konfigurasi + helper modul Admin Gudang
 * Author  : Tim Aplikasi Administrasi
 */

declare(strict_types=1);

define('MODULE_SLUG',        'admin_gudang');
define('MODULE_NAME',        'Admin Gudang');
define('MODULE_SESSION_KEY', 'auth_admin_gudang');

require_once __DIR__ . '/../../config/app.php';

$GLOBALS['MODULE_INFO'] = get_jenis_admin(MODULE_SLUG);
if (!$GLOBALS['MODULE_INFO']) {
    http_response_code(500);
    exit('Modul "' . MODULE_SLUG . '" tidak terdaftar di database.');
}

/** Hitung stok terkini satu barang. */
function gudang_stok_terkini(int $barangId): int
{
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(CASE WHEN tipe='masuk' THEN jumlah END),0)
              - COALESCE(SUM(CASE WHEN tipe='keluar' THEN jumlah END),0)
         FROM gudang_stok WHERE barang_id = ?"
    );
    $stmt->execute([$barangId]);
    return (int) $stmt->fetchColumn();
}

/** Map barang_id => stok terkini. */
function gudang_map_stok(): array
{
    $rows = db()->query(
        "SELECT barang_id,
                COALESCE(SUM(CASE WHEN tipe='masuk'  THEN jumlah END),0)
              - COALESCE(SUM(CASE WHEN tipe='keluar' THEN jumlah END),0) AS stok
         FROM gudang_stok GROUP BY barang_id"
    )->fetchAll();
    $map = [];
    foreach ($rows as $r) $map[(int)$r['barang_id']] = (int)$r['stok'];
    return $map;
}

/** Cari barang by barcode atau kode. */
function gudang_cari_barang(string $kode): ?array
{
    $stmt = db()->prepare(
        "SELECT * FROM gudang_barang
         WHERE (barcode = ? OR kode = ?) AND is_active = 1 LIMIT 1"
    );
    $stmt->execute([$kode, $kode]);
    $r = $stmt->fetch();
    return $r ?: null;
}

/** Generate nomor referensi mutasi. */
function gudang_gen_referensi(): string
{
    $prefix = 'MUT-' . date('Ymd') . '-';
    $stmt = db()->prepare(
        "SELECT no_referensi FROM gudang_mutasi
         WHERE no_referensi LIKE ? ORDER BY id DESC LIMIT 1"
    );
    $stmt->execute([$prefix . '%']);
    $last = $stmt->fetchColumn();
    $num = $last ? ((int) substr((string)$last, -4)) + 1 : 1;
    return $prefix . str_pad((string)$num, 4, '0', STR_PAD_LEFT);
}

/** Generate barcode numerik 13 digit dari kode barang. */
function gudang_gen_barcode(string $kodeBarang): string
{
    $hash = (int) sprintf('%u', crc32($kodeBarang)) % 1000000000;
    return '899' . str_pad((string)$hash, 9, '0', STR_PAD_LEFT) . '0';
}