<?php
/**
 * File    : admins/admin_gudang/pages/mutasi.php
 * Fungsi  : Mutasi barang
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$barang   = db()->query("SELECT id,kode,nama FROM gudang_barang WHERE is_active=1 ORDER BY nama")->fetchAll();
$supplier = db()->query("SELECT id,kode,nama FROM gudang_supplier WHERE is_active=1 ORDER BY nama")->fetchAll();

$page_title  = 'Mutasi';
$active_menu = 'mutasi';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Mutasi Barang</h1>
      <div class="subtitle">Pencatatan barang masuk / keluar dari supplier</div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="formMutasi()">
      <i class="fa-solid fa-plus me-1"></i> Tambah Mutasi
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblMutasi" class="table table-hover w-100">
          <thead>
            <tr><th>No. Referensi</th><th>Tanggal</th><th>Barang</th>
              <th>Supplier</th><th>Tipe</th><th class="text-end">Jumlah</th>
              <th>Keterangan</th></tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalMutasi">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formMutasi">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Mutasi</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Barang</label>
            <select name="barang_id" class="form-select" required>
              <option value="">-- Pilih --</option>
              <?php foreach ($barang as $b): ?>
                <option value="<?= (int)$b['id'] ?>"><?= e($b['kode'].' — '.$b['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-6">
            <label class="form-label">Supplier (opsional)</label>
            <select name="supplier_id" class="form-select">
              <option value="">-- Tidak ada --</option>
              <?php foreach ($supplier as $s): ?>
                <option value="<?= (int)$s['id'] ?>"><?= e($s['kode'].' — '.$s['nama']) ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-md-4"><label class="form-label">Tipe</label>
            <select name="tipe" class="form-select">
              <option value="masuk">Masuk</option>
              <option value="keluar">Keluar</option>
            </select></div>
          <div class="col-md-4"><label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="1" min="1" required></div>
          <div class="col-md-4"><label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          <div class="col-12"><label class="form-label">Keterangan</label>
            <input type="text" name="keterangan" class="form-control"></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
const API_MUT = '<?= e(url('admins/admin_gudang/api/mutasi.php')) ?>';
$(function () {
  $('#tblMutasi').DataTable({
    ajax: { url: API_MUT + '?action=list', dataSrc:'data' },
    columns: [
      { data: 'no_referensi' },
      { data: 'tanggal' },
      { data: 'nama_barang' },
      { data: 'nama_supplier', render: d => d ?? '-' },
      { data: 'tipe', render: t => `<span class="badge bg-${t==='masuk'?'success':'danger'}">${t}</span>` },
      { data: 'jumlah', className:'text-end fw-bold' },
      { data: 'keterangan' }
    ],
    dom:'Bfrtip',
    buttons:[
      { extend:'excelHtml5', className:'btn btn-sm btn-success', text:'Excel' },
      { extend:'pdfHtml5', className:'btn btn-sm btn-danger', text:'PDF' },
      { extend:'print', className:'btn btn-sm btn-secondary', text:'Print' }
    ],
    pageLength: 25
  });
});
function formMutasi() { $('#formMutasi')[0].reset(); $('#modalMutasi').modal('show'); }
$('#formMutasi').on('submit', async function (e) {
  e.preventDefault();
  const r = await fetch(API_MUT + '?action=save', { method:'POST', body:new FormData(this) });
  const d = await r.json();
  if (d.ok) { $('#modalMutasi').modal('hide'); $('#tblMutasi').DataTable().ajax.reload(null,false); Swal.fire('OK', d.msg, 'success'); }
  else Swal.fire('Gagal', d.msg, 'error');
});
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>