<?php
require_once __DIR__ . '/../config.php';
header('Content-Type: application/json');
if (!is_logged_in(MODULE_SLUG)) { http_response_code(401); echo json_encode(['error'=>'unauth']); exit; }
$a = $_GET['action'] ?? 'list';
$u = current_user(MODULE_SLUG);
try {
    if ($a === 'list') {
        echo json_encode(['data' => db()->query(
            "SELECT a.*, p.nama, p.nip FROM kantor_absensi a
             JOIN kantor_pegawai p ON p.id=a.pegawai_id
             ORDER BY a.tanggal DESC, a.id DESC LIMIT 500"
        )->fetchAll()]);
    } elseif ($a === 'check_in' || $a === 'check_out') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $pid = (int)$_POST['pegawai_id'];
        $lat = (float)($_POST['lat'] ?? 0); $lng = (float)($_POST['lng'] ?? 0);
        $tgl = date('Y-m-d'); $jam = date('H:i:s');

        // Validasi geofence
        $gf = db()->query("SELECT * FROM kantor_geofence WHERE is_active=1 LIMIT 1")->fetch();
        $inRange = false; $jarak = null;
        if ($gf && $lat && $lng) {
            $jarak = kantor_jarak_meter($lat, $lng, (float)$gf['latitude'], (float)$gf['longitude']);
            $inRange = $jarak <= (int)$gf['radius_m'];
        }

        if ($a === 'check_in') {
            $stmt = db()->prepare("INSERT INTO kantor_absensi
                (pegawai_id,tanggal,jam_masuk,lat_masuk,lng_masuk,status)
                VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE jam_masuk=VALUES(jam_masuk),lat_masuk=VALUES(lat_masuk),lng_masuk=VALUES(lng_masuk)");
            $stmt->execute([$pid,$tgl,$jam,$lat,$lng,'hadir']);
        } else {
            db()->prepare("UPDATE kantor_absensi SET jam_keluar=?,lat_keluar=?,lng_keluar=? WHERE pegawai_id=? AND tanggal=?")
                ->execute([$jam,$lat,$lng,$pid,$tgl]);
        }
        log_aksi((int)$GLOBALS['MODULE_INFO']['id'],(int)$u['user_id'],$u['username'],"absensi_$a","Pegawai $pid");
        echo json_encode(['ok'=>true,'msg'=>'Absensi tercatat.','in_range'=>$inRange,'jarak_m'=>$jarak ? round($jarak) : null]);
    } elseif ($a === 'manual') {
        if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
        $pid = (int)$_POST['pegawai_id'];
        $tgl = $_POST['tanggal']; $status = $_POST['status'] ?? 'hadir';
        db()->prepare("INSERT INTO kantor_absensi (pegawai_id,tanggal,status,keterangan)
            VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE status=VALUES(status),keterangan=VALUES(keterangan)")
            ->execute([$pid,$tgl,$status,$_POST['keterangan'] ?? '']);
        echo json_encode(['ok'=>true,'msg'=>'Absensi manual dicatat.']);
    }
} catch (Throwable $e) { http_response_code(400); echo json_encode(['ok'=>false,'msg'=>$e->getMessage()]); }