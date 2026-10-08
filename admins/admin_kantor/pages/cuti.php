<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nip,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Cuti & Izin'; $active_menu='cuti';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-calendar-check me-2"></i>Pengajuan Cuti / Izin</h4>
    <button class="btn btn-primary btn-sm" onclick="formCuti()"><i class="fa-solid fa-plus me-1"></i> Ajukan</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblCuti" class="table table-striped w-100">
      <thead><tr><th>NIP</th><th>Nama</th><th>Jenis</th><th>Mulai</th><th>Selesai</th>
        <th>Alasan</th><th>Status</th><th>Aksi</th></tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalCuti"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formCuti" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Pengajuan Cuti / Izin</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="c_id">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Pegawai</label>
        <select name="pegawai_id" id="c_peg" class="form-select" required>
          <option value="">-- Pilih --</option>
          <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nip'].' — '.$p['nama']) ?></option><?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">Jenis</label>
        <select name="jenis" id="c_jenis" class="form-select">
          <option value="cuti_tahunan">Cuti Tahunan</option>
          <option value="cuti_sakit">Cuti Sakit</option>
          <option value="cuti_melahirkan">Cuti Melahirkan</option>
          <option value="izin">Izin</option>
          <option value="lembur">Lembur</option>
          <option value="lainnya">Lainnya</option>
        </select></div>
      <div class="col-md-6"><label class="form-label">Mulai</label>
        <input type="date" name="tanggal_mulai" id="c_mulai" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Selesai</label>
        <input type="date" name="tanggal_selesai" id="c_selesai" class="form-control" required></div>
      <div class="col-12"><label class="form-label">Alasan</label>
        <textarea name="alasan" id="c_alasan" class="form-control" rows="2" required></textarea></div>
      <div class="col-12"><label class="form-label">File Bukti (opsional)</label>
        <input type="file" name="file_bukti" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Ajukan</button></div>
</form></div></div>

<script>
const API_C='<?= e(url('admins/admin_kantor/api/cuti.php')) ?>';
$(function(){
  $('#tblCuti').DataTable({
    ajax:{url:API_C+'?action=list',dataSrc:'data'},
    columns:[
      {data:'nip'},{data:'nama'},{data:'jenis'},{data:'tanggal_mulai'},{data:'tanggal_selesai'},
      {data:'alasan'},
      {data:'status',render:s=>{
        const m={pending:'warning',disetujui:'success',ditolak:'danger',selesai:'secondary'};
        return `<span class="badge bg-${m[s]||'secondary'}">${s}</span>`;
      }},
      {data:null,orderable:false,render:r=>{
        let btn = `<button class="btn btn-sm btn-warning" onclick='editC(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>`;
        if(r.status==='pending'){
          btn += ` <button class="btn btn-sm btn-success" onclick="approve(${r.id},'disetujui')"><i class="fa-solid fa-check"></i></button>`;
          btn += ` <button class="btn btn-sm btn-danger" onclick="approve(${r.id},'ditolak')"><i class="fa-solid fa-xmark"></i></button>`;
        }
        btn += ` <button class="btn btn-sm btn-outline-danger" onclick="hapusC(${r.id})"><i class="fa-solid fa-trash"></i></button>`;
        return btn;
      }}
    ],pageLength:25
  });
});
function formCuti(){$('#formCuti')[0].reset();$('#c_id').val('');$('#modalCuti').modal('show');}
function editC(d){
  ['id','jenis','tanggal_mulai','tanggal_selesai','alasan'].forEach(k=>$('#c_'+({id:'id',jenis:'jenis',tanggal_mulai:'mulai',tanggal_selesai:'selesai',alasan:'alasan'})[k]).val(d[k]));
  $('#c_peg').val(d.pegawai_id);$('#modalCuti').modal('show');
}
$('#formCuti').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_C+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalCuti').modal('hide');$('#tblCuti').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function approve(id,status){
  const c=await Swal.fire({title:'Konfirmasi',text:`Ubah status jadi "${status}"?`,icon:'question',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);fd.append('status',status);
  await fetch(API_C+'?action=approve',{method:'POST',body:fd});
  $('#tblCuti').DataTable().ajax.reload(null,false);Swal.fire('OK','Status diubah.','success');
}
async function hapusC(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_C+'?action=delete',{method:'POST',body:fd});
  $('#tblCuti').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>