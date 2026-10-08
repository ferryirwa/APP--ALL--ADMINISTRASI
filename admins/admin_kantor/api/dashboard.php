<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json; charset=utf-8');

if (!is_logged_in(MODULE_SLUG)) {
    echo json_encode(['error' => 'unauthorized']);
    exit;
}

try {
    $bulan = []; $sm = []; $sk = [];
    for ($i = 5; $i >= 0; $i--) {
        $tgl = date('Y-m-01', strtotime("-$i months"));
        $y = date('Y', strtotime($tgl));
        $m = date('n', strtotime($tgl));
        $bulan[] = date('M Y', strtotime($tgl));

        $stmt = db()->prepare("SELECT COUNT(*) FROM kantor_surat_masuk
            WHERE YEAR(tanggal_terima)=? AND MONTH(tanggal_terima)=?");
        $stmt->execute([$y, $m]);
        $sm[] = (int)$stmt->fetchColumn();

        $stmt = db()->prepare("SELECT COUNT(*) FROM kantor_surat_keluar
            WHERE YEAR(tanggal_surat)=? AND MONTH(tanggal_surat)=?");
        $stmt->execute([$y, $m]);
        $sk[] = (int)$stmt->fetchColumn();
    }

    $sifat = db()->query("SELECT sifat, COUNT(*) n FROM kantor_surat_masuk GROUP BY sifat")->fetchAll();

    echo json_encode([
        'bulan'        => $bulan,
        'surat_masuk'  => $sm,
        'surat_keluar' => $sk,
        'sifat_label'  => array_column($sifat, 'sifat'),
        'sifat_total'  => array_map('intval', array_column($sifat, 'n')),
    ]);
} catch (Throwable $e) {
    echo json_encode(['error' => 'exception', 'msg' => $e->getMessage()]);
}