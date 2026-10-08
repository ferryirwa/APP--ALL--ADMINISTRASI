<?php
/**
 * File    : admins/admin_gudang/pages/supplier.php
 * Fungsi  : CRUD Supplier
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$page_title  = 'Supplier';
$active_menu = 'supplier';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Data Supplier</h1>
      <div class="subtitle">Kelola master supplier gudang</div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="formSupplier()">
      <i class="fa-solid fa-plus me-1"></i> Tambah Supplier
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <div class="table-responsive">
        <table id="tblSupplier" class="table table-hover w-100">
          <thead>
            <tr><th>Kode</th><th>Nama</th><th>Kontak</th><th>Telepon</th>
                <th>Email</th><th>Alamat</th><th>Aksi</th></tr>
          </thead>
        </table>
      </div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalSupplier" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formSupplier">
      <div class="modal-header">
        <h5 class="modal-title">Form Supplier</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="s_id">
        <div class="row g-3">
          <div class="col-md-4"><label class="form-label">Kode *</label>
            <input type="text" name="kode" id="s_kode" class="form-control" required></div>
          <div class="col-md-8"><label class="form-label">Nama *</label>
            <input type="text" name="nama" id="s_nama" class="form-control" required></div>
          <div class="col-md-4"><label class="form-label">Kontak</label>
            <input type="text" name="kontak" id="s_kontak" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Telepon</label>
            <input type="text" name="telepon" id="s_telepon" class="form-control"></div>
          <div class="col-md-4"><label class="form-label">Email</label>
            <input type="email" name="email" id="s_email" class="form-control"></div>
          <div class="col-12"><label class="form-label">Alamat</label>
            <textarea name="alamat" id="s_alamat" class="form-control" rows="2"></textarea></div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
const API_SUP = '<?= e(url('admins/admin_gudang/api/supplier.php')) ?>';
$(function () {
  $('#tblSupplier').DataTable({
    ajax: { url: API_SUP + '?action=list', dataSrc: 'data' },
    columns: [
      { data: 'kode' }, { data: 'nama' }, { data: 'kontak' },
      { data: 'telepon' }, { data: 'email' }, { data: 'alamat' },
      { data: null, orderable:false, render: r => `
        <button class="btn btn-sm btn-warning" onclick='editSupplier(${JSON.stringify(r).replace(/'/g,"&#39;")})'>
          <i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusSupplier(${r.id})">
          <i class="fa-solid fa-trash"></i></button>` }
    ],
    dom: 'Bfrtip',
    buttons: [
      { extend:'excelHtml5', className:'btn btn-sm btn-success', text:'<i class="fa-solid fa-file-excel"></i> Excel' },
      { extend:'pdfHtml5',   className:'btn btn-sm btn-danger',  text:'<i class="fa-solid fa-file-pdf"></i> PDF' },
      { extend:'print',      className:'btn btn-sm btn-secondary', text:'<i class="fa-solid fa-print"></i> Print' },
    ],
    pageLength: 10
  });
});
function formSupplier() { $('#formSupplier')[0].reset(); $('#s_id').val(''); $('#modalSupplier').modal('show'); }
function editSupplier(d) {
  ['id','kode','nama','kontak','telepon','email','alamat'].forEach(k => $('#s_'+k).val(d[k]));
  $('#modalSupplier').modal('show');
}
$('#formSupplier').on('submit', async function (e) {
  e.preventDefault();
  const r = await fetch(API_SUP + '?action=save', { method:'POST', body:new FormData(this) });
  const d = await r.json();
  if (d.ok) { $('#modalSupplier').modal('hide'); $('#tblSupplier').DataTable().ajax.reload(null,false); Swal.fire('Berhasil', d.msg, 'success'); }
  else Swal.fire('Gagal', d.msg, 'error');
});
async function hapusSupplier(id) {
  const c = await Swal.fire({title:'Hapus supplier?', icon:'warning', showCancelButton:true, confirmButtonText:'Ya, hapus'});
  if (!c.isConfirmed) return;
  const fd = new FormData(); fd.append('_csrf', GUDANG.csrf); fd.append('id', id);
  const r = await fetch(API_SUP + '?action=delete', { method:'POST', body:fd });
  const d = await r.json();
  if (d.ok) { $('#tblSupplier').DataTable().ajax.reload(null,false); Swal.fire('Terhapus', d.msg, 'success'); }
  else Swal.fire('Gagal', d.msg, 'error');
}
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>