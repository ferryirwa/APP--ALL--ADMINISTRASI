<?php
/**
 * File    : admins/admin_gudang/api/supplier.php
 * Fungsi  : Endpoint AJAX supplier
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
                "SELECT * FROM gudang_supplier WHERE is_active=1 ORDER BY id DESC"
            )->fetchAll();
            echo json_encode(['data' => $rows]);
            break;

        case 'save':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $id   = (int)($_POST['id'] ?? 0);
            $kode = trim((string)($_POST['kode'] ?? ''));
            $nama = trim((string)($_POST['nama'] ?? ''));
            if ($kode === '' || $nama === '') throw new Exception('Kode & nama wajib diisi.');

            $params = [
                $kode, $nama,
                trim((string)($_POST['kontak'] ?? '')),
                trim((string)($_POST['telepon'] ?? '')),
                trim((string)($_POST['email'] ?? '')),
                trim((string)($_POST['alamat'] ?? '')),
            ];
            if ($id > 0) {
                $params[] = $id;
                db()->prepare("UPDATE gudang_supplier SET kode=?,nama=?,kontak=?,telepon=?,email=?,alamat=? WHERE id=?")
                    ->execute($params);
                log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                         'update_supplier', "Update supplier #$id");
            } else {
                db()->prepare("INSERT INTO gudang_supplier (kode,nama,kontak,telepon,email,alamat) VALUES (?,?,?,?,?,?)")
                    ->execute($params);
                log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                         'create_supplier', "Tambah supplier $kode");
            }
            echo json_encode(['ok'=>true,'msg'=>'Supplier disimpan.']);
            break;

        case 'delete':
            if (!csrf_verify($_POST['_csrf'] ?? null)) throw new Exception('CSRF invalid');
            $id = (int)($_POST['id'] ?? 0);
            db()->prepare("UPDATE gudang_supplier SET is_active=0 WHERE id=?")->execute([$id]);
            log_aksi((int)$GLOBALS['MODULE_INFO']['id'], (int)$user['user_id'], $user['username'],
                     'delete_supplier', "Hapus supplier #$id");
            echo json_encode(['ok'=>true,'msg'=>'Supplier dihapus.']);
            break;

        default:
            echo json_encode(['error' => 'unknown action']);
    }
} catch (Throwable $e) {
    http_response_code(400);
    echo json_encode(['ok' => false, 'msg' => $e->getMessage()]);
}