-- ============================================================
-- File    : seed.sql
-- Fungsi  : Data awal jenis admin
-- ============================================================

INSERT INTO `admins_jenis` (`slug`, `nama`, `deskripsi`, `icon`, `warna`, `urutan`) VALUES
('admin_gudang',   'Admin Gudang',   'Kelola barang, stok, supplier, mutasi gudang',        'fa-solid fa-warehouse',         '#0d6efd', 1),
('admin_kantor',   'Admin Kantor',   'Kelola surat masuk, surat keluar, dan arsip kantor',   'fa-solid fa-building',          '#198754', 2),
('admin_karyawan', 'Admin Karyawan', 'Kelola data karyawan, absensi, dan penggajian',        'fa-solid fa-users',             '#ffc107', 3),
('admin_keuangan', 'Admin Keuangan', 'Kelola kas, laporan keuangan, dan transaksi',          'fa-solid fa-coins',             '#dc3545', 4),
('admin_penjualan','Admin Penjualan','Kelola produk, order, dan laporan penjualan',          'fa-solid fa-cart-shopping',     '#6f42c1', 5)
ON DUPLICATE KEY UPDATE `nama` = VALUES(`nama`);