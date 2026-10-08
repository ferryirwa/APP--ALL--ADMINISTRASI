<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Kendaraan'; $active_menu='kendaraan';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <h4 class="fw-bold mb-3"><i class="fa-solid fa-car me-2"></i>Kendaraan Dinas</h4>
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabKend">Kendaraan</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBookK">Booking</a></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabKend">
      <button class="btn btn-primary btn-sm mb-2" onclick="formKend()"><i class="fa-solid fa-plus me-1"></i> Tambah</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblKend" class="table table-striped w-100">
          <thead><tr><th>Kode</th><th>Nama</th><th>Plat</th><th>Jenis</th><th>Kapasitas</th><th>Driver</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>
    <div class="tab-pane fade" id="tabBookK">
      <button class="btn btn-primary btn-sm mb-2" onclick="formBookK()"><i class="fa-solid fa-plus me-1"></i> Booking</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblBookK" class="table table-striped w-100">
          <thead><tr><th>Kendaraan</th><th>Pegawai</th><th>Tujuan</th><th>Keperluan</th>
            <th>Tanggal</th><th>Jam</th><th>Status</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalKend"><div class="modal-dialog">
<form class="modal-content" id="formKend">
  <div class="modal-header"><h5 class="modal-title">Form Kendaraan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="kd_id">
    <div class="row g-3">
      <div class="col-4"><label class="form-label">Kode</label><input type="text" name="kode" id="kd_kode" class="form-control" required></div>
      <div class="col-8"><label class="form-label">Nama</label><input type="text" name="nama" id="kd_nama" class="form-control" required></div>
      <div class="col-6"><label class="form-label">Plat</label><input type="text" name="plat_nomor" id="kd_plat" class="form-control" required></div>
      <div class="col-6"><label class="form-label">Jenis</label><input type="text" name="jenis" id="kd_jenis" class="form-control"></div>
      <div class="col-6"><label class="form-label">Kapasitas</label><input type="number" name="kapasitas" id="kd_kap" class="form-control" value="4"></div>
      <div class="col-6"><label class="form-label">Driver</label><input type="text" name="driver" id="kd_driver" class="form-control"></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<div class="modal fade" id="modalBookK"><div class="modal-dialog">
<form class="modal-content" id="formBookK">
  <div class="modal-header"><h5 class="modal-title">Booking Kendaraan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3"><label class="form-label">Kendaraan</label><select name="kendaraan_id" id="bk_kend" class="form-select" required></select></div>
    <div class="mb-3"><label class="form-label">Pegawai</label>
      <select name="pegawai_id" class="form-select" required>
        <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Tujuan</label><input type="text" name="tujuan" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Keperluan</label><input type="text" name="keperluan" class="form-control" required></div>
    <div class="row g-3">
      <div class="col-4"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-4"><label class="form-label">Mulai</label><input type="time" name="jam_mulai" class="form-control" required></div>
      <div class="col-4"><label class="form-label">Selesai</label><input type="time" name="jam_selesai" class="form-control" required></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_K='<?= e(url('admins/admin_kantor/api/kendaraan.php')) ?>';
$(function(){
  $('#tblKend').DataTable({
    ajax:{url:API_K+'?action=list',dataSrc:'data'},
    columns:[
      {data:'kode'},{data:'nama'},{data:'plat_nomor'},{data:'jenis'},{data:'kapasitas'},{data:'driver'},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editKd(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusKd(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],pageLength:10
  });
  $('#tblBookK').DataTable({
    ajax:{url:API_K+'?action=booking_list',dataSrc:'data'},
    columns:[
      {data:'nama_kendaraan',render:(d,t,r)=>`${d} (${r.plat_nomor})`},
      {data:'nama_pegawai'},{data:'tujuan'},{data:'keperluan'},
      {data:'tanggal'},
      {data:null,render:r=>`${r.jam_mulai} - ${r.jam_selesai}`},
      {data:'status',render:s=>{
        const m={menunggu:'warning',disetujui:'success',ditolak:'danger',selesai:'secondary'};
        return `<span class="badge bg-${m[s]||'secondary'}">${s}</span>`;
      }},
      {data:null,orderable:false,render:r=>{
        if(r.status==='menunggu'){
          return `<button class="btn btn-sm btn-success" onclick="setBookK(${r.id},'disetujui')"><i class="fa-solid fa-check"></i></button>
                  <button class="btn btn-sm btn-danger" onclick="setBookK(${r.id},'ditolak')"><i class="fa-solid fa-xmark"></i></button>`;
        }
        return '';
      }}
    ],pageLength:10
  });
  fetch(API_K+'?action=list').then(r=>r.json()).then(d=>{
    $('#bk_kend').html(d.data.map(x=>`<option value="${x.id}">${x.kode} — ${x.nama} (${x.plat_nomor})</option>`).join(''));
  });
});
function formKend(){$('#formKend')[0].reset();$('#kd_id').val('');$('#modalKend').modal('show');}
function editKd(d){['id','kode','nama','plat_nomor','jenis','kapasitas','driver'].forEach(k=>{
  const map={id:'id',kode:'kode',nama:'nama',plat_nomor:'plat',jenis:'jenis',kapasitas:'kap',driver:'driver'};
  $('#kd_'+map[k]).val(d[k]);
});$('#modalKend').modal('show');}
$('#formKend').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_K+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalKend').modal('hide');$('#tblKend').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusKd(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_K+'?action=delete',{method:'POST',body:fd});
  $('#tblKend').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
function formBookK(){$('#formBookK')[0].reset();$('#modalBookK').modal('show');}
$('#formBookK').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_K+'?action=booking_save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalBookK').modal('hide');$('#tblBookK').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function setBookK(id,status){
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);fd.append('status',status);
  await fetch(API_K+'?action=booking_status',{method:'POST',body:fd});
  $('#tblBookK').DataTable().ajax.reload(null,false);Swal.fire('OK','Status diubah.','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>