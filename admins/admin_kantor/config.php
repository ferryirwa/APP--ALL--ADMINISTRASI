<?php
/**
 * File    : admins/admin_kantor/config.php
 * Fungsi  : Konfigurasi + helper modul Admin Kantor
 */

declare(strict_types=1);

// ---------- Load core DULU (agar url(), e(), db() tersedia) ----------
require_once __DIR__ . '/../../config/app.php';

// ---------- Baru define konstanta ----------
define('MODULE_SLUG',        'admin_kantor');
define('MODULE_NAME',        'Admin Kantor');
define('MODULE_SESSION_KEY', 'auth_admin_kantor');
define('UPLOAD_DIR',         __DIR__ . '/uploads/');
define('UPLOAD_URL',         url('admins/admin_kantor/uploads/'));

// ---------- Validasi modul ----------
$GLOBALS['MODULE_INFO'] = get_jenis_admin(MODULE_SLUG);
if (!$GLOBALS['MODULE_INFO']) {
    http_response_code(500);
    exit('Modul "' . MODULE_SLUG . '" tidak terdaftar di database.');
}

// ============================================================
// Helper khusus modul kantor
// ============================================================

/** Generate nomor agenda otomatis: SM-YYYYMM-0001 */
function kantor_gen_agenda(string $tipe, int $klasId): string
{
    $prefix = $tipe === 'masuk' ? 'SM' : 'SK';
    $tahun  = (int) date('Y');
    $bulan  = (int) date('n');

    db()->beginTransaction();
    try {
        $stmt = db()->prepare("SELECT id, counter FROM kantor_penomoran
            WHERE klasifikasi_id=? AND tipe=? AND tahun=? AND bulan=? FOR UPDATE");
        $stmt->execute([$klasId, $tipe, $tahun, $bulan]);
        $row = $stmt->fetch();

        if ($row) {
            $counter = (int)$row['counter'] + 1;
            db()->prepare("UPDATE kantor_penomoran SET counter=? WHERE id=?")
                ->execute([$counter, $row['id']]);
        } else {
            $counter = 1;
            db()->prepare("INSERT INTO kantor_penomoran
                (klasifikasi_id,tipe,tahun,bulan,counter) VALUES (?,?,?,?,?)")
                ->execute([$klasId, $tipe, $tahun, $bulan, $counter]);
        }
        db()->commit();
    } catch (Throwable $e) {
        db()->rollBack();
        throw $e;
    }

    return sprintf('%s-%04d%02d-%04d', $prefix, $tahun, $bulan, $counter);
}

/** Generate nomor surat: 001/SM/KTR/X/2024 */
function kantor_gen_no_surat(string $kodeKlas, string $unit = 'KTR'): string
{
    $bulanRomawi = ['','I','II','III','IV','V','VI','VII','VIII','IX','X','XI','XII'];
    $bulan = (int) date('n');
    $tahun = date('Y');

    $stmt = db()->prepare("SELECT COUNT(*) FROM kantor_surat_keluar
        WHERE YEAR(created_at)=? AND MONTH(created_at)=?");
    $stmt->execute([$tahun, $bulan]);
    $urut = (int)$stmt->fetchColumn() + 1;

    return sprintf('%03d/%s/%s/%s/%s', $urut, $kodeKlas, $unit, $bulanRomawi[$bulan], $tahun);
}

/** Upload file aman. Return nama file atau null. */
function kantor_upload(array $file, string $prefix): ?string
{
    if (empty($file['name']) || $file['error'] !== UPLOAD_ERR_OK) return null;
    if ($file['size'] > 5 * 1024 * 1024) throw new Exception('File melebihi 5 MB.');

    $allowed = ['application/pdf' => 'pdf', 'image/jpeg' => 'jpg', 'image/png' => 'png'];
    $finfo   = new finfo(FILEINFO_MIME_TYPE);
    $mime    = $finfo->file($file['tmp_name']);
    if (!isset($allowed[$mime])) throw new Exception('Tipe file tidak diizinkan (hanya PDF/JPG/PNG).');

    if (!is_dir(UPLOAD_DIR)) mkdir(UPLOAD_DIR, 0755, true);

    $ext  = $allowed[$mime];
    $name = $prefix . '_' . date('YmdHis') . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    if (!move_uploaded_file($file['tmp_name'], UPLOAD_DIR . $name)) {
        throw new Exception('Gagal memindahkan file.');
    }
    return $name;
}

/** Hitung jarak (meter) antara 2 koordinat (haversine). */
function kantor_jarak_meter(float $lat1, float $lng1, float $lat2, float $lng2): float
{
    $R = 6371000;
    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);
    $a = sin($dLat/2)**2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng/2)**2;
    return 2 * $R * asin(sqrt($a));
}

/** Kirim notifikasi in-app. */
function kantor_notif(int $userId, string $judul, string $pesan = '', string $link = ''): void
{
    try {
        db()->prepare("INSERT INTO kantor_notifikasi (user_id,judul,pesan,link) VALUES (?,?,?,?)")
            ->execute([$userId, $judul, $pesan, $link]);
    } catch (Throwable $e) {}
}

/** Ambil role user saat ini. */
function kantor_role(): string
{
    $u = current_user(MODULE_SLUG);
    return $u['role'] ?? 'staff';
}

/** Cek apakah user punya minimal level tertentu. */
function kantor_can(string $minRole): bool
{
    $levels = ['viewer'=>1,'staff'=>2,'manajer'=>3,'admin'=>4];
    return ($levels[kantor_role()] ?? 0) >= ($levels[$minRole] ?? 0);
}