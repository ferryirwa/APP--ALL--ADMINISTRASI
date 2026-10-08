# Aplikasi Administrasi

Aplikasi web multi-modul administrasi — satu platform untuk banyak jenis admin (Gudang, Kantor, Karyawan, dll).

## 🎯 Fitur

- **Multi-modul terpisah** — setiap admin punya akun, dashboard, dan file sendiri
- **Isolasi session** — login di satu modul tidak otomatis login di modul lain
- **CRUD lengkap** per modul
- **DataTables** dengan export Excel/PDF/Print
- **Chart.js** untuk grafik dashboard
- **Audit log** setiap aksi
- **Responsive** (HP, tablet, desktop)

## 🛠️ Teknologi

- **Backend**: PHP 8+ (native OOP, PDO prepared statements)
- **Database**: MySQL / MariaDB
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **UI**: Bootstrap 5, FontAwesome 6
- **Tabel**: DataTables 1.13
- **Grafik**: Chart.js 4

## 📁 Struktur
aplikasi-administrasi/
├── config/ # Konfigurasi global
├── includes/ # Helper umum
├── assets/ # CSS, JS, images
├── admins/ # Modul per jenis admin
│ ├── admin_gudang/ # Modul gudang
│ ├── admin_kantor/ # Modul kantor
│ └── admin_karyawan/ # Modul karyawan
├── database/ # SQL schema + seed
└── index.php # Halaman utama


## 🚀 Cara Install

### 1. Clone repo
```bash
git clone https://github.com/USERNAME/aplikasi-administrasi.git
cd aplikasi-administrasi
2. Letakkan di web root
XAMPP: C:\xampp\htdocs\aplikasi-administrasi

Laragon: C:\laragon\www\aplikasi-administrasi

Linux: /var/www/html/aplikasi-administrasi

3. Import database
Buka phpMyAdmin → Import → pilih database/core.sql → Go
Lalu import database/seed.sql.

Untuk modul gudang & kantor, import juga:

admins/admin_gudang/database.sql

admins/admin_kantor/database.sql

4. Sesuaikan kredensial database
Edit config/database.php:

php
define('DB_HOST', 'localhost');
define('DB_NAME', 'aplikasi_administrasi');
define('DB_USER', 'root');
define('DB_PASS', '');   // sesuaikan
5. Buat folder uploads (jika perlu)
bash
mkdir -p admins/admin_kantor/uploads
chmod 755 admins/admin_kantor/uploads
6. Jalankan
Buka browser: http://localhost/aplikasi-administrasi/

📝 Cara Pakai
Buka halaman utama → pilih jenis admin (mis. Admin Gudang)

Registrasi akun baru (khusus modul itu)

Login → masuk ke dashboard modul

Untuk modul lain, harus registrasi terpisah (isolasi session)

🔐 Keamanan
Password di-hash bcrypt

CSRF token di setiap form

PDO prepared statement (anti SQL injection)

Output di-escape dengan htmlspecialchars()

Upload file divalidasi MIME + folder dilindungi .htaccess