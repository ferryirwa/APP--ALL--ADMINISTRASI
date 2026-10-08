<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$klas = db()->query("SELECT id,kode,nama FROM kantor_klasifikasi WHERE is_active=1 ORDER BY kode")->fetchAll();
$page_title='Surat Masuk'; $active_menu='surat_masuk';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="fw-bold mb-0"><i class="fa-solid fa-envelope-open-text me-2"></i>Surat Masuk</h4>
    <button class="btn btn-primary btn-sm" onclick="formSM()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
  </div>
  <div class="card border-0 shadow-sm"><div class="card-body">
    <table id="tblSM" class="table table-striped table-hover w-100">
      <thead><tr>
        <th>Agenda</th><th>Tgl Terima</th><th>Asal</th><th>Perihal</th>
        <th>Sifat</th><th>Status</th><th>File</th><th>Aksi</th>
      </tr></thead>
    </table>
  </div></div>
</main>

<div class="modal fade" id="modalSM"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formSM" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Form Surat Masuk</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="sm_id">
    <div class="row g-3">
      <div class="col-md-6"><label class="form-label">Klasifikasi</label>
        <select name="klasifikasi_id" id="sm_klas" class="form-select" required>
          <?php foreach($klas as $k): ?>
            <option value="<?= (int)$k['id'] ?>"><?= e($k['kode'].' — '.$k['nama']) ?></option>
          <?php endforeach; ?>
        </select></div>
      <div class="col-md-6"><label class="form-label">No. Surat</label>
        <input type="text" name="no_surat" id="sm_no_surat" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Tanggal Surat</label>
        <input type="date" name="tanggal_surat" id="sm_tgl_surat" class="form-control" value="<?= date('Y-m-d') ?>"></div>
      <div class="col-md-6"><label class="form-label">Tanggal Terima</label>
        <input type="date" name="tanggal_terima" id="sm_tgl_terima" class="form-control" value="<?= date('Y-m-d') ?>"></div>
      <div class="col-md-8"><label class="form-label">Asal Surat</label>
        <input type="text" name="asal_surat" id="sm_asal" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Sifat</label>
        <select name="sifat" id="sm_sifat" class="form-select">
          <option value="biasa">Biasa</option><option value="penting">Penting</option>
          <option value="segera">Segera</option><option value="rahasia">Rahasia</option>
        </select></div>
      <div class="col-12"><label class="form-label">Perihal</label>
        <input type="text" name="perihal" id="sm_perihal" class="form-control" required></div>
      <div class="col-md-6"><label class="form-label">Lampiran</label>
        <input type="text" name="lampiran" id="sm_lampiran" class="form-control" placeholder="mis: 1 berkas"></div>
      <div class="col-md-6"><label class="form-label">Tembusan</label>
        <input type="text" name="tembusan" id="sm_tembusan" class="form-control"></div>
      <div class="col-md-6"><label class="form-label">Status</label>
        <select name="status" id="sm_status" class="form-select">
          <option value="baru">Baru</option><option value="diproses">Diproses</option>
          <option value="selesai">Selesai</option>
        </select></div>
      <div class="col-md-6"><label class="form-label">File Scan (PDF/JPG/PNG)</label>
        <input type="file" name="file_scan" class="form-control" accept=".pdf,.jpg,.jpeg,.png"></div>
      <div class="col-12"><label class="form-label">Keterangan</label>
        <textarea name="keterangan" id="sm_ket" class="form-control" rows="2"></textarea></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan</button></div>
</form></div></div>

<script>
const API_SM='<?= e(url('admins/admin_kantor/api/surat_masuk.php')) ?>';
$(function(){
  $('#tblSM').DataTable({
    ajax:{url:API_SM+'?action=list',dataSrc:'data'},
    columns:[
      {data:'no_agenda'},
      {data:'tanggal_terima',render:d=>d?new Date(d).toLocaleDateString('id-ID'):'-'},
      {data:'asal_surat'},{data:'perihal'},
      {data:'sifat',render:s=>`<span class="badge badge-${s}">${s}</span>`},
      {data:'status',render:s=>`<span class="badge bg-secondary">${s}</span>`},
      {data:'file_scan',render:f=>f?`<a href="<?= e(UPLOAD_URL) ?>${f}" target="_blank" class="btn btn-sm btn-info"><i class="fa-solid fa-file"></i></a>`:'-'},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editSM(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusSM(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],
    dom:'Bfrtip',
    buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success',text:'Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger',text:'PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary',text:'Print'}
    ],pageLength:10
  });
});
function formSM(){$('#formSM')[0].reset();$('#sm_id').val('');$('#modalSM').modal('show');}
function editSM(d){
  $('#sm_id').val(d.id);$('#sm_klas').val(d.klasifikasi_id);$('#sm_no_surat').val(d.no_surat);
  $('#sm_tgl_surat').val(d.tanggal_surat);$('#sm_tgl_terima').val(d.tanggal_terima);
  $('#sm_asal').val(d.asal_surat);$('#sm_perihal').val(d.perihal);$('#sm_sifat').val(d.sifat);
  $('#sm_lampiran').val(d.lampiran);$('#sm_tembusan').val(d.tembusan);
  $('#sm_status').val(d.status);$('#sm_ket').val(d.keterangan);
  $('#modalSM').modal('show');
}
$('#formSM').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_SM+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalSM').modal('hide');$('#tblSM').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusSM(id){
  const c=await Swal.fire({title:'Hapus surat?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  const r=await fetch(API_SM+'?action=delete',{method:'POST',body:fd});
  const d=await r.json();
  if(d.ok){$('#tblSM').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>