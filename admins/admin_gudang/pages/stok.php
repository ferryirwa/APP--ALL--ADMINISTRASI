<?php
/**
 * File    : admins/admin_gudang/pages/stok.php
 * Fungsi  : Kartu stok
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$barang = db()->query("SELECT id,kode,nama,satuan FROM gudang_barang WHERE is_active=1 ORDER BY nama")->fetchAll();

$page_title  = 'Stok';
$active_menu = 'stok';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Kartu Stok</h1>
      <div class="subtitle">Riwayat pergerakan stok barang</div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="formStok()">
      <i class="fa-solid fa-plus me-1"></i> Tambah Stok
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblStok" class="table table-hover w-100">
          <thead>
            <tr><th>Tanggal</th><th>Kode</th><th>Barang</th><th>Tipe</th>
              <th class="text-end">Jumlah</th><th>Keterangan</th></tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalStok">
  <div class="modal-dialog">
    <form class="modal-content" id="formStok">
      <div class="modal-header">
        <h5 class="modal-title">Tambah Stok</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <div class="mb-3">
          <label class="form-label">Barang</label>
          <select name="barang_id" class="form-select" required>
            <option value="">-- Pilih --</option>
            <?php foreach ($barang as $b): ?>
              <option value="<?= (int)$b['id'] ?>"><?= e($b['kode'].' — '.$b['nama']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="mb-3">
          <label class="form-label">Tipe</label>
          <select name="tipe" class="form-select">
            <option value="masuk">Masuk</option>
            <option value="keluar">Keluar</option>
            <option value="koreksi">Koreksi</option>
          </select>
        </div>
        <div class="row g-3">
          <div class="col-6"><label class="form-label">Jumlah</label>
            <input type="number" name="jumlah" class="form-control" value="1" min="1" required></div>
          <div class="col-6"><label class="form-label">Tanggal</label>
            <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>"></div>
        </div>
        <div class="mt-3">
          <label class="form-label">Keterangan</label>
          <input type="text" name="keterangan" class="form-control">
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
const API_STOK = '<?= e(url('admins/admin_gudang/api/stok.php')) ?>';
$(function () {
  $('#tblStok').DataTable({
    ajax: { url: API_STOK + '?action=list', dataSrc: 'data' },
    columns: [
      { data: 'tanggal' },
      { data: 'kode' },
      { data: 'nama_barang' },
      { data: 'tipe', render: t => `<span class="badge bg-${t==='masuk'?'success':t==='keluar'?'danger':'secondary'}">${t}</span>` },
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
function formStok() { $('#formStok')[0].reset(); $('#modalStok').modal('show'); }
$('#formStok').on('submit', async function (e) {
  e.preventDefault();
  const r = await fetch(API_STOK + '?action=save', { method:'POST', body:new FormData(this) });
  const d = await r.json();
  if (d.ok) { $('#modalStok').modal('hide'); $('#tblStok').DataTable().ajax.reload(null,false); Swal.fire('OK', d.msg, 'success'); }
  else Swal.fire('Gagal', d.msg, 'error');
});
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>