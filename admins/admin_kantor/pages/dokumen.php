<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$page_title='Arsip Digital'; $active_menu='dokumen';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-folder-open me-2"></i>Arsip Digital</h4>
    <div class="d-flex gap-2">
      <input type="text" id="searchDok" class="form-control form-control-sm" placeholder="Cari dokumen..." style="width:250px;">
      <button class="btn btn-primary btn-sm" onclick="formDok()"><i class="fa-solid fa-upload me-1"></i> Upload</button>
    </div>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblDok" class="table table-striped w-100">
      <thead><tr><th>Nama</th><th>Kategori</th><th>Ukuran</th><th>Tipe</th><th>Akses</th>
        <th>Diunggah</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalDok"><div class="modal-dialog">
<form class="modal-content" id="formDok" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Upload Dokumen</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3"><label class="form-label">Nama Dokumen</label>
      <input type="text" name="nama" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Kategori</label>
      <input type="text" name="kategori" class="form-control" placeholder="mis: Keuangan, Kepegawaian"></div>
    <div class="mb-3"><label class="form-label">Deskripsi</label>
      <textarea name="deskripsi" class="form-control" rows="2"></textarea></div>
    <div class="mb-3"><label class="form-label">Hak Akses</label>
      <select name="akses" class="form-select">
        <option value="public">Public (semua user)</option>
        <option value="internal" selected>Internal (staff ke atas)</option>
        <option value="private">Private (hanya admin)</option>
      </select></div>
    <div class="mb-3"><label class="form-label">File (PDF/JPG/PNG)</label>
      <input type="file" name="file" class="form-control" required accept=".pdf,.jpg,.jpeg,.png"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Upload</button></div>
</form></div></div>

<script>
const API_Dok='<?= e(url('admins/admin_kantor/api/dokumen.php')) ?>';
let dtDok;
$(function(){
  dtDok = $('#tblDok').DataTable({
    ajax:{url:API_Dok+'?action=list',dataSrc:'data'},
    columns:[
      {data:'nama'},{data:'kategori'},
      {data:'file_size',render:d=>((d/1024).toFixed(1))+' KB'},
      {data:'file_type'},
      {data:'akses',render:a=>{
        const m={public:'success',internal:'info',private:'secondary'};
        return `<span class="badge bg-${m[a]||'secondary'}">${a}</span>`;
      }},
      {data:'created_at'},
      {data:null,orderable:false,render:r=>`
        <a href="<?= e(UPLOAD_URL) ?>${r.file_path}" target="_blank" class="btn btn-sm btn-info"><i class="fa-solid fa-download"></i></a>
        <button class="btn btn-sm btn-danger" onclick="hapusDok(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],
    dom:'Bfrtip',buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success',text:'Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger',text:'PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary',text:'Print'}
    ],pageLength:10
  });

  let timer;
  $('#searchDok').on('input',function(){
    clearTimeout(timer);
    timer = setTimeout(()=>{
      const q = this.value;
      dtDok.ajax.url(API_Dok+'?action=list&q='+encodeURIComponent(q)).load();
    }, 400);
  });
});
function formDok(){$('#formDok')[0].reset();$('#modalDok').modal('show');}
$('#formDok').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_Dok+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalDok').modal('hide');$('#tblDok').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusDok(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_Dok+'?action=delete',{method:'POST',body:fd});
  $('#tblDok').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>