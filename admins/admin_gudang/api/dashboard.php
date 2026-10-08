<?php
/**
 * File    : admins/admin_gudang/api/dashboard.php
 * Fungsi  : Data grafik dashboard
 */

require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');

if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauthorized']); exit; }

$bulan = []; $masuk = []; $keluar = [];
for ($i = 5; $i >= 0; $i--) {
    $tgl = date('Y-m-01', strtotime("-$i months"));
    $bulan[] = date('M Y', strtotime($tgl));
    $stmt = db()->prepare(
        "SELECT COALESCE(SUM(CASE WHEN tipe='masuk'  THEN jumlah END),0) AS m,
                COALESCE(SUM(CASE WHEN tipe='keluar' THEN jumlah END),0) AS k
         FROM gudang_mutasi WHERE YEAR(tanggal)=? AND MONTH(tanggal)=?"
    );
    $stmt->execute([date('Y', strtotime($tgl)), date('n', strtotime($tgl))]);
    $r = $stmt->fetch() ?: ['m'=>0,'k'=>0];
    $masuk[]  = (int)$r['m'];
    $keluar[] = (int)$r['k'];
}

$kat = db()->query(
    "SELECT COALESCE(kategori,'Lain-lain') AS k, COUNT(*) AS n
     FROM gudang_barang WHERE is_active=1
     GROUP BY kategori ORDER BY n DESC"
)->fetchAll();

echo json_encode([
    'bulan'          => $bulan,
    'masuk'          => $masuk,
    'keluar'         => $keluar,
    'kategori_label' => array_column($kat, 'k'),
    'kategori_total' => array_map('intval', array_column($kat, 'n')),
]);