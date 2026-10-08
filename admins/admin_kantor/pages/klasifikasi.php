<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$page_title='Klasifikasi'; $active_menu='klasifikasi';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-tags me-2"></i>Klasifikasi Surat</h4>
    <button class="btn btn-primary btn-sm" onclick="formKlas()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblKlas" class="table table-striped w-100">
      <thead><tr><th>Kode</th><th>Nama</th><th>Retensi (th)</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalKlas"><div class="modal-dialog"><form class="modal-content" id="formKlas">
  <div class="modal-header"><h5 class="modal-title">Form Klasifikasi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="k_id">
    <div class="mb-3"><label class="form-label">Kode</label>
      <input type="text" name="kode" id="k_kode" class="form-control" required maxlength="10"></div>
    <div class="mb-3"><label class="form-label">Nama</label>
      <input type="text" name="nama" id="k_nama" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Retensi (tahun)</label>
      <input type="number" name="retensi_tahun" id="k_ret" class="form-control" value="5"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API='<?= e(url('admins/admin_kantor/api/klasifikasi.php')) ?>';
$(function(){
  $('#tblKlas').DataTable({
    ajax:{url:API+'?action=list',dataSrc:'data'},
    columns:[
      {data:'kode'},{data:'nama'},{data:'retensi_tahun'},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editKlas(${JSON.stringify(r)})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusKlas(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],pageLength:25
  });
});
function formKlas(){$('#formKlas')[0].reset();$('#k_id').val('');$('#modalKlas').modal('show');}
function editKlas(d){$('#k_id').val(d.id);$('#k_kode').val(d.kode);$('#k_nama').val(d.nama);$('#k_ret').val(d.retensi_tahun);$('#modalKlas').modal('show');}
$('#formKlas').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalKlas').modal('hide');$('#tblKlas').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusKlas(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  const r=await fetch(API+'?action=delete',{method:'POST',body:fd});
  const d=await r.json();
  if(d.ok){$('#tblKlas').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>