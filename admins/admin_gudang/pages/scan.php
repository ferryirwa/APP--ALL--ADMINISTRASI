<?php
/**
 * File    : admins/admin_gudang/pages/scan.php
 * Fungsi  : Scan barcode multi-metode (kamera / USB / manual)
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$page_title  = 'Scan Barcode';
$active_menu = 'scan';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Scan Barcode</h1>
      <div class="subtitle">Pilih metode scan sesuai perangkat Anda</div>
    </div>
  </div>

  <div class="row g-3">
    <!-- KIRI -->
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-sliders me-2 text-primary"></i> Pilih Metode Scan
          </h6>

          <ul class="nav nav-pills nav-fill mb-3">
            <li class="nav-item">
              <button class="nav-link active" data-metode="kamera" onclick="pilihMetode('kamera')">
                <i class="fa-solid fa-camera me-1"></i> Kamera
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-metode="usb" onclick="pilihMetode('usb')">
                <i class="fa-solid fa-usb me-1"></i> USB
              </button>
            </li>
            <li class="nav-item">
              <button class="nav-link" data-metode="manual" onclick="pilihMetode('manual')">
                <i class="fa-solid fa-keyboard me-1"></i> Manual
              </button>
            </li>
          </ul>

          <!-- KAMERA -->
          <div id="boxKamera">
            <label class="form-label small text-muted">Pilih Kamera:</label>
            <select id="pilihKamera" class="form-select form-select-sm mb-2">
              <option value="">-- Deteksi kamera... --</option>
            </select>
            <small class="text-muted d-block mb-2">
              💡 HP: kamera belakang &middot; Laptop: webcam
            </small>
            <div id="reader" class="mx-auto mb-2"></div>
            <div class="d-flex gap-2 justify-content-center mb-2">
              <button class="btn btn-primary btn-sm" onclick="mulaiKamera()">
                <i class="fa-solid fa-play me-1"></i> Mulai
              </button>
              <button class="btn btn-secondary btn-sm" onclick="stopKamera()">
                <i class="fa-solid fa-stop me-1"></i> Stop
              </button>
            </div>
          </div>

          <!-- USB -->
          <div id="boxUsb" class="d-none">
            <div class="alert alert-info small">
              <i class="fa-solid fa-info-circle me-1"></i>
              Klik kolom di bawah, lalu scan barcode dengan alat USB scanner.
            </div>
            <input type="text" id="inputUsb" class="form-control form-control-lg text-center"
                   placeholder="Klik di sini lalu scan...">
            <small class="text-muted d-block mt-2">
              Alat USB scanner bekerja seperti keyboard — otomatis Enter di akhir scan.
            </small>
          </div>

          <!-- MANUAL -->
          <div id="boxManual" class="d-none">
            <label class="form-label">Ketik Kode / Barcode</label>
            <div class="input-group">
              <input type="text" id="inputManual" class="form-control"
                     placeholder="Contoh: 8991234567890 atau BRG-001">
              <button class="btn btn-success" onclick="cariBarang(document.getElementById('inputManual').value)">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- KANAN -->
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h6 class="fw-bold mb-3">
            <i class="fa-solid fa-box-open me-2 text-success"></i> Hasil Pencarian
          </h6>
          <div id="hasil">
            <div class="text-center text-muted py-5">
              <i class="fa-solid fa-barcode fa-3x mb-3 d-block opacity-25"></i>
              <p class="mb-0">Scan atau ketik kode barang.</p>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</main>

<script>
const API_BARCODE = '<?= e(url('admins/admin_gudang/api/barcode.php')) ?>';
const CSRF        = '<?= e(csrf_token()) ?>';

let html5QrCode = null;
let cameras     = [];
let lastKode    = '';   // <— simpan kode terakhir yang berhasil di-scan

// ---------- PILIH METODE ----------
function pilihMetode(m) {
  document.querySelectorAll('[data-metode]').forEach(b => b.classList.remove('active'));
  document.querySelector(`[data-metode="${m}"]`).classList.add('active');

  document.getElementById('boxKamera').classList.toggle('d-none', m !== 'kamera');
  document.getElementById('boxUsb').classList.toggle('d-none',    m !== 'usb');
  document.getElementById('boxManual').classList.toggle('d-none', m !== 'manual');

  if (m !== 'kamera') stopKamera();
  if (m === 'usb')    setTimeout(() => document.getElementById('inputUsb').focus(), 100);
  if (m === 'manual') setTimeout(() => document.getElementById('inputManual').focus(), 100);
}

// ---------- KAMERA ----------
async function loadKamera() {
  try {
    cameras = await Html5Qrcode.getCameras();
    const sel = document.getElementById('pilihKamera');
    sel.innerHTML = '';
    if (!cameras.length) {
      sel.innerHTML = '<option value="">Tidak ada kamera terdeteksi</option>';
      return;
    }
    cameras.forEach((c, i) => {
      const opt = document.createElement('option');
      opt.value = c.id;
      opt.textContent = c.label || `Kamera ${i+1}`;
      sel.appendChild(opt);
    });
    console.log('[scan] Kamera terdeteksi:', cameras);
  } catch (e) {
    console.error('[scan] Load kamera gagal:', e);
    document.getElementById('pilihKamera').innerHTML = '<option value="">Kamera tidak tersedia</option>';
  }
}

async function mulaiKamera() {
  if (html5QrCode) return;
  const camId = document.getElementById('pilihKamera').value;
  if (!camId) { Swal.fire('Info', 'Pilih kamera dulu.', 'info'); return; }

  html5QrCode = new Html5Qrcode("reader");
  try {
    await html5QrCode.start(
      camId,
      { fps: 10, qrbox: { width: 250, height: 150 } },
      (decoded) => {
        console.log('[scan] Terdeteksi:', decoded);
        stopKamera();
        cariBarang(decoded);
      },
      () => {} // ignore per-frame errors
    );
  } catch (e) {
    console.error('[scan] Mulai kamera gagal:', e);
    Swal.fire('Gagal', 'Tidak bisa akses kamera: ' + e, 'error');
    html5QrCode = null;
  }
}

async function stopKamera() {
  if (html5QrCode) {
    try { await html5QrCode.stop(); await html5QrCode.clear(); } catch(e) {}
    html5QrCode = null;
  }
}

// ---------- CARI ----------
async function cariBarang(kode) {
  kode = (kode || '').trim();
  console.log('[scan] cariBarang dipanggil dengan kode:', JSON.stringify(kode));

  if (!kode) {
    Swal.fire('Info', 'Kode kosong. Ketik atau scan dulu.', 'info');
    return;
  }

  const box = document.getElementById('hasil');
  box.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary"></div><p class="mt-2 small text-muted">Mencari...</p></div>';

  try {
    const url = API_BARCODE + '?kode=' + encodeURIComponent(kode);
    console.log('[scan] Fetch:', url);

    const r = await fetch(url);
    const d = await r.json();
    console.log('[scan] Response:', d);

    if (!d.ok) {
      box.innerHTML = `
        <div class="alert alert-danger">
          <i class="fa-solid fa-circle-exclamation me-2"></i>${d.msg}
        </div>`;
      return;
    }

    lastKode = kode;   // <— simpan untuk aksi cepat
    const b = d.barang;
    const stok = Number(d.stok || 0);
    const minStok = Number(b.stok_minimum || 0);
    const stokClass = stok <= minStok ? 'text-danger' : 'text-success';

    box.innerHTML = `
      <div class="border rounded p-3">
        <div class="d-flex justify-content-between align-items-start mb-2">
          <div>
            <h5 class="fw-bold mb-1">${b.nama}</h5>
            <div class="text-muted small">
              Kode: <code>${b.kode}</code> &middot; Barcode: <code>${b.barcode || '-'}</code>
            </div>
          </div>
          <span class="badge bg-primary">${b.satuan}</span>
        </div>
        <div class="row g-2 mb-3">
          <div class="col-6"><small class="text-muted">Kategori</small><div>${b.kategori || '-'}</div></div>
          <div class="col-6"><small class="text-muted">Lokasi Rak</small><div>${b.lokasi_rak || '-'}</div></div>
          <div class="col-6"><small class="text-muted">Harga Jual</small>
            <div>Rp ${Number(b.harga_jual || 0).toLocaleString('id-ID')}</div></div>
          <div class="col-6"><small class="text-muted">Stok Saat Ini</small>
            <div class="fs-5 fw-bold ${stokClass}">${stok} ${b.satuan}</div></div>
        </div>

        <hr>
        <h6 class="fw-bold mb-2"><i class="fa-solid fa-bolt me-1 text-warning"></i> Input Cepat Stok</h6>
        <div class="row g-2 align-items-end">
          <div class="col-4">
            <label class="form-label small">Jumlah</label>
            <input type="number" id="scanJumlah" class="form-control" value="1" min="1">
          </div>
          <div class="col-4">
            <button class="btn btn-success w-100" onclick="aksiCepat('stok_masuk')">
              <i class="fa-solid fa-arrow-down me-1"></i> Masuk
            </button>
          </div>
          <div class="col-4">
            <button class="btn btn-danger w-100" onclick="aksiCepat('stok_keluar')">
              <i class="fa-solid fa-arrow-up me-1"></i> Keluar
            </button>
          </div>
        </div>
      </div>`;
  } catch (e) {
    console.error('[scan] Fetch error:', e);
    box.innerHTML = `<div class="alert alert-danger">Gagal fetch: ${e.message || e}</div>`;
  }
}

// ---------- AKSI CEPAT ----------
async function aksiCepat(aksi) {
  console.log('[scan] aksiCepat:', aksi, 'kode:', lastKode);

  if (!lastKode) {
    Swal.fire('Info', 'Scan dulu barangnya.', 'info');
    return;
  }

  const jmlEl = document.getElementById('scanJumlah');
  const jml = parseInt(jmlEl?.value || 0);
  if (jml <= 0) { Swal.fire('Info', 'Jumlah harus > 0', 'info'); return; }

  const fd = new FormData();
  fd.append('kode', lastKode);
  fd.append('aksi', aksi);
  fd.append('jumlah', jml);
  fd.append('_csrf', CSRF);

  try {
    const r = await fetch(API_BARCODE, { method:'POST', body:fd });
    const d = await r.json();
    console.log('[scan] aksiCepat response:', d);

    if (d.ok) {
      Swal.fire({ icon:'success', title:'Sukses', text: d.msg, timer:1500, showConfirmButton:false })
        .then(() => cariBarang(lastKode));
    } else {
      Swal.fire('Gagal', d.msg, 'error');
    }
  } catch (e) {
    console.error('[scan] aksiCepat error:', e);
    Swal.fire('Gagal', 'Error: ' + e.message, 'error');
  }
}

// ---------- AUTO ENTER HANDLER ----------
document.addEventListener('DOMContentLoaded', () => {
  loadKamera();

  // USB scanner: otomatis cari saat Enter
  const usb = document.getElementById('inputUsb');
  if (usb) {
    usb.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        cariBarang(usb.value);
        usb.value = '';   // reset kolom untuk scan berikutnya
      }
    });
  }

  // Manual input: Enter juga cari
  const man = document.getElementById('inputManual');
  if (man) {
    man.addEventListener('keypress', (e) => {
      if (e.key === 'Enter') {
        e.preventDefault();
        cariBarang(man.value);
      }
    });
  }
});
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>