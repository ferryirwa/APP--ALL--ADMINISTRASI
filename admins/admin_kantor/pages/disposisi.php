<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$surat = db()->query("SELECT id,no_agenda,perihal,asal_surat FROM kantor_surat_masuk WHERE status!='selesai' ORDER BY id DESC")->fetchAll();
$page_title='Disposisi'; $active_menu='disposisi';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-share-nodes me-2"></i>Disposisi Surat</h4>
    <button class="btn btn-primary btn-sm" onclick="formDisp()"><i class="fa-solid fa-plus me-1"></i> Buat Disposisi</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblDisp" class="table table-striped w-100">
      <thead><tr><th>Agenda</th><th>Perihal</th><th>Kepada</th><th>Isi</th>
        <th>Sifat</th><th>Batas</th><th>Status</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalDisp"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formDisp">
  <div class="modal-header"><h5 class="modal-title">Form Disposisi</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="d_id">
    <div class="mb-3"><label class="form-label">Surat Masuk</label>
      <select name="surat_masuk_id" id="d_surat" class="form-select" required>
        <option value="">-- Pilih --</option>
        <?php foreach($surat as $s): ?>
          <option value="<?= (int)$s['id'] ?>"><?= e($s['no_agenda'].' — '.$s['perihal']) ?></option>
        <?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Kepada</label>
      <input type="text" name="kepada" id="d_kepada" class="form-control" required placeholder="mis: Bagian Keuangan"></div>
    <div class="mb-3"><label class="form-label">Isi Disposisi</label>
      <textarea name="isi_disposisi" id="d_isi" class="form-control" rows="3" required></textarea></div>
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Sifat</label>
        <select name="sifat" id="d_sifat" class="form-select">
          <option value="biasa">Biasa</option><option value="penting">Penting</option>
          <option value="segera">Segera</option><option value="rahasia">Rahasia</option>
        </select></div>
      <div class="col-md-4"><label class="form-label">Batas Waktu</label>
        <input type="date" name="batas_waktu" id="d_batas" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Status</label>
        <select name="status" id="d_status" class="form-select">
          <option value="baru">Baru</option><option value="dibaca">Dibaca</option>
          <option value="selesai">Selesai</option>
        </select></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_D='<?= e(url('admins/admin_kantor/api/disposisi.php')) ?>';
$(function(){
  $('#tblDisp').DataTable({
    ajax:{url:API_D+'?action=list',dataSrc:'data'},
    columns:[
      {data:'no_agenda'},{data:'perihal'},{data:'kepada'},{data:'isi_disposisi'},
      {data:'sifat',render:s=>`<span class="badge badge-${s}">${s}</span>`},
      {data:'batas_waktu'},{data:'status',render:s=>`<span class="badge bg-secondary">${s}</span>`},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editD(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusD(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],pageLength:10
  });
});
function formDisp(){$('#formDisp')[0].reset();$('#d_id').val('');$('#modalDisp').modal('show');}
function editD(d){
  $('#d_id').val(d.id);$('#d_surat').val(d.surat_masuk_id);$('#d_kepada').val(d.kepada);
  $('#d_isi').val(d.isi_disposisi);$('#d_sifat').val(d.sifat);$('#d_batas').val(d.batas_waktu);
  $('#d_status').val(d.status);$('#modalDisp').modal('show');
}
$('#formDisp').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_D+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalDisp').modal('hide');$('#tblDisp').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusD(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_D+'?action=delete',{method:'POST',body:fd});
  $('#tblDisp').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>