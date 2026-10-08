<?php
/**
 * File    : admins/admin_gudang/pages/barcode.php
 * Fungsi  : Generate & Cetak Barcode Barang
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

// Ambil semua barang aktif untuk dropdown
$barang = db()->query(
    "SELECT id, kode, barcode, nama, satuan FROM gudang_barang
     WHERE is_active=1 ORDER BY nama"
)->fetchAll();

$page_title  = 'Barcode Barang';
$active_menu = 'barcode';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header no-print">
    <div>
      <h1>Barcode Barang</h1>
      <div class="subtitle">Generate & cetak barcode dari kode barang</div>
    </div>
  </div>

  <!-- FORM FILTER -->
  <div class="card mb-3 no-print">
    <div class="card-body">
      <div class="row g-3 align-items-end">
        <div class="col-md-4">
          <label class="form-label">Pilih Barang</label>
          <select id="pilihBarang" class="form-select">
            <option value="">-- Pilih Barang --</option>
            <?php foreach ($barang as $b): ?>
              <option value="<?= (int)$b['id'] ?>"
                      data-kode="<?= e($b['kode']) ?>"
                      data-barcode="<?= e($b['barcode'] ?: $b['kode']) ?>"
                      data-nama="<?= e($b['nama']) ?>">
                <?= e($b['kode'] . ' — ' . $b['nama']) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Jumlah Label</label>
          <input type="number" id="jmlLabel" class="form-control" value="1" min="1" max="100">
        </div>
        <div class="col-md-2">
          <label class="form-label">Format</label>
          <select id="formatBarcode" class="form-select">
            <option value="CODE128">CODE128</option>
            <option value="EAN13">EAN13</option>
            <option value="CODE39">CODE39</option>
            <option value="UPC">UPC</option>
          </select>
        </div>
        <div class="col-md-2">
          <button class="btn btn-primary w-100" onclick="generateLabel()">
            <i class="fa-solid fa-play me-1"></i> Tampilkan
          </button>
        </div>
        <div class="col-md-2">
          <button class="btn btn-success w-100" onclick="cetakBarcode()">
            <i class="fa-solid fa-print me-1"></i> Cetak
          </button>
        </div>
      </div>

      <hr>

      <div class="d-flex gap-2 flex-wrap">
        <button class="btn btn-outline-primary btn-sm" onclick="generateSemua()">
          <i class="fa-solid fa-layer-group me-1"></i> Generate Semua Barang
        </button>
        <button class="btn btn-outline-danger btn-sm" onclick="clearLabel()">
          <i class="fa-solid fa-trash me-1"></i> Bersihkan
        </button>
        <span class="ms-auto align-self-center text-muted small">
          Total: <strong id="countLabel">0</strong> label
        </span>
      </div>
    </div>
  </div>

  <!-- PREVIEW LABEL -->
  <div class="card" id="areaCetak">
    <div class="card-body">

      <!-- KOP CETAK (muncul saat print) -->
      <div class="text-center mb-4 d-none d-print-block">
        <h4 class="fw-bold mb-0"><?= e(APP_NAME) ?></h4>
        <h5 class="mb-0"><?= e(MODULE_NAME) ?></h5>
        <hr style="border:2px solid #000;">
        <p class="mb-3">Dicetak: <?= e(date('d M Y H:i')) ?></p>
      </div>

      <div id="previewLabel" class="row g-2"></div>
      <p id="emptyText" class="text-muted text-center my-4">
        <i class="fa-solid fa-barcode fa-3x mb-2 d-block opacity-25"></i>
        Belum ada label. Pilih barang lalu klik <strong>Tampilkan</strong>.
      </p>
    </div>
  </div>
</main>

<style>
  .barcode-label {
    border: 1px dashed #ccc;
    border-radius: 6px;
    padding: 8px;
    text-align: center;
    background: #fff;
  }
  .barcode-label .kode { font-size: 11px; color: #666; }
  .barcode-label .nama { font-size: 12px; font-weight: 600; margin-bottom: 4px; }
  .barcode-label svg { max-width: 100%; height: auto; }

  @media print {
    .no-print, .sidebar, .page-header, .btn { display: none !important; }
    .layout { display: block !important; }
    .main { padding: 0 !important; }
    body { background: #fff !important; }
    .card { box-shadow: none !important; border: none !important; }
    .barcode-label { page-break-inside: avoid; border: 1px solid #ddd; }
    .col-6.col-md-4.col-lg-3 { width: 25% !important; }
  }
</style>

<script>
// Data barang dari PHP
const BARANG_LIST = <?= json_encode(array_map(fn($b) => [
    'id'      => (int)$b['id'],
    'kode'    => $b['kode'],
    'barcode' => $b['barcode'] ?: $b['kode'],
    'nama'    => $b['nama'],
    'satuan'  => $b['satuan'],
], $barang), JSON_UNESCAPED_UNICODE) ?>;

console.log('[barcode] Barang tersedia:', BARANG_LIST.length);

function buatLabelSVG(barcodeValue, format) {
  const svg = document.createElementNS('http://www.w3.org/2000/svg', 'svg');
  try {
    JsBarcode(svg, barcodeValue, {
      format: format,
      lineColor: '#000',
      width: 1.6,
      height: 45,
      displayValue: true,
      fontSize: 11,
      margin: 4
    });
    return svg;
  } catch (e) {
    console.error('[barcode] Gagal generate:', barcodeValue, e.message);
    const div = document.createElement('div');
    div.className = 'text-danger small';
    div.textContent = 'Barcode tidak valid: ' + barcodeValue;
    return div;
  }
}

function tambahLabel(item, jml, format) {
  const container = document.getElementById('previewLabel');
  document.getElementById('emptyText').style.display = 'none';

  for (let i = 0; i < jml; i++) {
    const col = document.createElement('div');
    col.className = 'col-6 col-md-4 col-lg-3 barcode-label';

    const info = document.createElement('div');
    info.innerHTML = `<div class="nama">${item.nama}</div>`;
    col.appendChild(info);

    const svg = buatLabelSVG(item.barcode, format);
    col.appendChild(svg);

    const kode = document.createElement('div');
    kode.className = 'kode';
    kode.textContent = item.kode;
    col.appendChild(kode);

    container.appendChild(col);
  }

  updateCount();
}

function updateCount() {
  const n = document.getElementById('previewLabel').children.length;
  document.getElementById('countLabel').textContent = n;
}

function generateLabel() {
  const sel = document.getElementById('pilihBarang');
  const opt = sel.options[sel.selectedIndex];
  if (!opt || !opt.value) {
    Swal.fire('Info', 'Pilih barang dulu.', 'info');
    return;
  }
  const item = {
    id:      parseInt(opt.value),
    kode:    opt.dataset.kode,
    barcode: opt.dataset.barcode,
    nama:    opt.dataset.nama
  };
  const jml = Math.max(1, parseInt(document.getElementById('jmlLabel').value) || 1);
  const format = document.getElementById('formatBarcode').value;
  tambahLabel(item, jml, format);
}

function generateSemua() {
  if (BARANG_LIST.length === 0) {
    Swal.fire('Info', 'Belum ada barang di database.', 'info');
    return;
  }
  Swal.fire({
    title: 'Generate semua?',
    text: `Akan generate ${BARANG_LIST.length} barcode (1 per barang).`,
    icon: 'question',
    showCancelButton: true,
    confirmButtonText: 'Ya, generate',
    cancelButtonText: 'Batal'
  }).then((r) => {
    if (!r.isConfirmed) return;
    const format = document.getElementById('formatBarcode').value;
    BARANG_LIST.forEach(b => tambahLabel(b, 1, format));
  });
}

function clearLabel() {
  document.getElementById('previewLabel').innerHTML = '';
  document.getElementById('emptyText').style.display = '';
  updateCount();
}

function cetakBarcode() {
  const n = document.getElementById('previewLabel').children.length;
  if (n === 0) {
    Swal.fire('Info', 'Belum ada label untuk dicetak.', 'info');
    return;
  }
  window.print();
}

// Init: cek library JsBarcode
document.addEventListener('DOMContentLoaded', () => {
  if (typeof JsBarcode === 'undefined') {
    console.error('[barcode] JsBarcode library TIDAK ter-load!');
    Swal.fire('Error', 'Library JsBarcode tidak ter-load. Cek koneksi internet.', 'error');
  } else {
    console.log('[barcode] JsBarcode siap ✓');
  }
});
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>