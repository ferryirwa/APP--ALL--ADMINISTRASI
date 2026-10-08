<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$page_title='Pegawai'; $active_menu='pegawai';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-users me-2"></i>Data Pegawai</h4>
    <button class="btn btn-primary btn-sm" onclick="formPeg()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblPeg" class="table table-striped w-100">
      <thead><tr><th>NIP</th><th>Nama</th><th>Jabatan</th><th>Departemen</th>
        <th>Email</th><th>Telepon</th><th class="text-end">Gaji</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalPeg"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formPeg">
  <div class="modal-header"><h5 class="modal-title">Form Pegawai</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="p_id">
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">NIP</label>
        <input type="text" name="nip" id="p_nip" class="form-control" required></div>
      <div class="col-md-8"><label class="form-label">Nama</label>
        <input type="text" name="nama" id="p_nama" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Jabatan</label>
        <input type="text" name="jabatan" id="p_jabatan" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Departemen</label>
        <input type="text" name="departemen" id="p_departemen" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Email</label>
        <input type="email" name="email" id="p_email" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Telepon</label>
        <input type="text" name="telepon" id="p_telepon" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Gaji Pokok</label>
        <input type="number" name="gaji_pokok" id="p_gaji" class="form-control" value="0"></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_P='<?= e(url('admins/admin_kantor/api/pegawai.php')) ?>';
$(function(){
  $('#tblPeg').DataTable({
    ajax:{url:API_P+'?action=list',dataSrc:'data'},
    columns:[
      {data:'nip'},{data:'nama'},{data:'jabatan'},{data:'departemen'},
      {data:'email'},{data:'telepon'},
      {data:'gaji_pokok',className:'text-end',render:d=>'Rp '+Number(d).toLocaleString('id-ID')},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editPeg(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusPeg(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],dom:'Bfrtip',buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success',text:'Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger',text:'PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary',text:'Print'}
    ],pageLength:10
  });
});
function formPeg(){$('#formPeg')[0].reset();$('#p_id').val('');$('#modalPeg').modal('show');}
function editPeg(d){
  $('#p_id').val(d.id);$('#p_nip').val(d.nip);$('#p_nama').val(d.nama);
  $('#p_jabatan').val(d.jabatan);$('#p_departemen').val(d.departemen);
  $('#p_email').val(d.email);$('#p_telepon').val(d.telepon);$('#p_gaji').val(d.gaji_pokok);
  $('#modalPeg').modal('show');
}
$('#formPeg').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_P+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalPeg').modal('hide');$('#tblPeg').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusPeg(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_P+'?action=delete',{method:'POST',body:fd});
  $('#tblPeg').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>