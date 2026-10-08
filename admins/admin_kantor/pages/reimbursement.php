<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Reimbursement'; $active_menu='reimbursement';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-2"></i>Reimbursement</h4>
    <button class="btn btn-primary btn-sm" onclick="formRe()"><i class="fa-solid fa-plus me-1"></i> Ajukan</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblRe" class="table table-striped w-100">
      <thead><tr><th>Pegawai</th><th>Tanggal</th><th>Kategori</th><th class="text-end">Jumlah</th>
        <th>Keterangan</th><th>Bukti</th><th>Status</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalRe"><div class="modal-dialog">
<form class="modal-content" id="formRe" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Pengajuan Reimbursement</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3"><label class="form-label">Pegawai</label>
      <select name="pegawai_id" class="form-select" required>
        <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="row g-3">
      <div class="col-6"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-6"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" placeholder="mis: Transport" required></div>
      <div class="col-6"><label class="form-label">Jumlah</label><input type="number" name="jumlah" class="form-control" required></div>
      <div class="col-6"><label class="form-label">Bukti (foto/PDF)</label><input type="file" name="file_bukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
      <div class="col-12"><label class="form-label">Keterangan</label><textarea name="keterangan" class="form-control" rows="2" required></textarea></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Ajukan</button></div>
</form></div></div>

<script>
const API_Re='<?= e(url('admins/admin_kantor/api/reimbursement.php')) ?>';
$(function(){
  $('#tblRe').DataTable({
    ajax:{url:API_Re+'?action=list',dataSrc:'data'},
    columns:[
      {data:'nama'},{data:'tanggal'},{data:'kategori'},
      {data:'jumlah',className:'text-end',render:d=>'Rp '+Number(d).toLocaleString('id-ID')},
      {data:'keterangan'},
      {data:'file_bukti',render:f=>f?`<a href="<?= e(UPLOAD_URL) ?>${f}" target="_blank" class="btn btn-sm btn-info"><i class="fa-solid fa-file"></i></a>`:'-'},
      {data:'status',render:s=>{
        const m={pending:'warning',disetujui:'success',ditolak:'danger',dibayar:'info'};
        return `<span class="badge bg-${m[s]||'secondary'}">${s}</span>`;
      }},
      {data:null,orderable:false,render:r=>{
        let b='';
        if(r.status==='pending'){
          b+=`<button class="btn btn-sm btn-success" onclick="approveRe(${r.id},'disetujui')"><i class="fa-solid fa-check"></i></button>
              <button class="btn btn-sm btn-danger" onclick="approveRe(${r.id},'ditolak')"><i class="fa-solid fa-xmark"></i></button>`;
        } else if(r.status==='disetujui'){
          b+=`<button class="btn btn-sm btn-info" onclick="approveRe(${r.id},'dibayar')"><i class="fa-solid fa-money-bill"></i></button>`;
        }
        b+=` <button class="btn btn-sm btn-outline-danger" onclick="hapusRe(${r.id})"><i class="fa-solid fa-trash"></i></button>`;
        return b;
      }}
    ],pageLength:25
  });
});
function formRe(){$('#formRe')[0].reset();$('#modalRe').modal('show');}
$('#formRe').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_Re+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalRe').modal('hide');$('#tblRe').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function approveRe(id,status){
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);fd.append('status',status);
  await fetch(API_Re+'?action=approve',{method:'POST',body:fd});
  $('#tblRe').DataTable().ajax.reload(null,false);Swal.fire('OK','Status diubah.','success');
}
async function hapusRe(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_Re+'?action=delete',{method:'POST',body:fd});
  $('#tblRe').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>