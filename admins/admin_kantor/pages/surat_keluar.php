<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$klas = db()->query("SELECT id,kode,nama FROM kantor_klasifikasi WHERE is_active=1 ORDER BY kode")->fetchAll();
$page_title='Surat Keluar'; $active_menu='surat_keluar';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-paper-plane me-2"></i>Surat Keluar</h4>
    <button class="btn btn-primary btn-sm" onclick="formSK()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblSK" class="table table-striped w-100">
      <thead><tr><th>Agenda</th><th>No. Surat</th><th>Tgl</th><th>Tujuan</th>
        <th>Perihal</th><th>Sifat</th><th>Status</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalSK"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formSK" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Form Surat Keluar</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="sk_id">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Klasifikasi</label>
        <select name="klasifikasi_id" id="sk_klas" class="form-select" required>
          <?php foreach($klas as $k): ?>
            <option value="<?= (int)$k['id'] ?>"><?= e($k['kode'].' — '.$k['nama']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">No. Surat (kosong = auto)</label>
        <input type="text" name="no_surat" id="sk_no_surat" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Tanggal Surat</label>
        <input type="date" name="tanggal_surat" id="sk_tgl" class="form-control" value="<?= date('Y-m-d') ?>"></div>
      <div class="col-md-6"><label class="form-label">Sifat</label>
        <select name="sifat" id="sk_sifat" class="form-select">
          <option value="biasa">Biasa</option><option value="penting">Penting</option>
          <option value="segera">Segera</option><option value="rahasia">Rahasia</option>
        </select></div>
      <div class="col-md-8"><label class="form-label">Tujuan</label>
        <input type="text" name="tujuan" id="sk_tujuan" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Status</label>
        <select name="status" id="sk_status" class="form-select">
          <option value="draft">Draft</option><option value="terkirim">Terkirim</option>
        </select></div>
      <div class="col-12"><label class="form-label">Perihal</label>
        <input type="text" name="perihal" id="sk_perihal" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Isi Surat</label>
        <textarea name="isi_surat" id="sk_isi" class="form-control" rows="4"></textarea></div>
      <div class="col-md-6"><label class="form-label">Lampiran</label>
        <input type="text" name="lampiran" id="sk_lampiran" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Tembusan</label>
        <input type="text" name="tembusan" id="sk_tembusan" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">File Scan</label>
        <input type="file" name="file_scan" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
      <div class="col-md-6"><label class="form-label">Keterangan</label>
        <input type="text" name="keterangan" id="sk_ket" class="form-control"></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_SK='<?= e(url('admins/admin_kantor/api/surat_keluar.php')) ?>';
$(function(){
  $('#tblSK').DataTable({
    ajax:{url:API_SK+'?action=list',dataSrc:'data'},
    columns:[
      {data:'no_agenda'},{data:'no_surat'},{data:'tanggal_surat'},{data:'tujuan'},
      {data:'perihal'},
      {data:'sifat',render:s=>`<span class="badge badge-${s}">${s}</span>`},
      {data:'status',render:s=>`<span class="badge bg-${s==='terkirim'?'success':'secondary'}">${s}</span>`},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editSK(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusSK(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],dom:'Bfrtip',buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success',text:'Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger',text:'PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary',text:'Print'}
    ],pageLength:10
  });
});
function formSK(){$('#formSK')[0].reset();$('#sk_id').val('');$('#modalSK').modal('show');}
function editSK(d){
  ['id','no_surat','tanggal_surat','tujuan','perihal','sifat','lampiran','tembusan','isi_surat','keterangan','status'].forEach(k=>{
    const el=$('#sk_'+({id:'id',no_surat:'no_surat',tanggal_surat:'tgl',tujuan:'tujuan',perihal:'perihal',sifat:'sifat',lampiran:'lampiran',tembusan:'tembusan',isi_surat:'isi',keterangan:'ket',status:'status'})[k]);
    if(el.length) el.val(d[k]);
  });
  $('#sk_klas').val(d.klasifikasi_id);
  $('#modalSK').modal('show');
}
$('#formSK').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_SK+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalSK').modal('hide');$('#tblSK').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusSK(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_SK+'?action=delete',{method:'POST',body:fd});
  $('#tblSK').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>