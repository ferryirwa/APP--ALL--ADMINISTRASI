<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Ruangan'; $active_menu='ruangan';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <h4 class="fw-bold mb-3"><i class="fa-solid fa-door-open me-2"></i>Ruang Rapat</h4>
  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabRuang">Ruangan</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabBooking">Booking</a></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabRuang">
      <button class="btn btn-primary btn-sm mb-2" onclick="formRuang()"><i class="fa-solid fa-plus me-1"></i> Tambah Ruangan</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblRuang" class="table table-striped w-100">
          <thead><tr><th>Kode</th><th>Nama</th><th>Kapasitas</th><th>Fasilitas</th><th>Lokasi</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>
    <div class="tab-pane fade" id="tabBooking">
      <button class="btn btn-primary btn-sm mb-2" onclick="formBook()"><i class="fa-solid fa-plus me-1"></i> Booking Ruangan</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblBook" class="table table-striped w-100">
          <thead><tr><th>Ruangan</th><th>Pegawai</th><th>Kegiatan</th><th>Tanggal</th>
            <th>Jam</th><th>Peserta</th><th>Status</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalRuang"><div class="modal-dialog">
<form class="modal-content" id="formRuang">
  <div class="modal-header"><h5 class="modal-title">Form Ruangan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="r_id">
    <div class="mb-3"><label class="form-label">Kode</label><input type="text" name="kode" id="r_kode" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Nama</label><input type="text" name="nama" id="r_nama" class="form-control" required></div>
    <div class="mb-3"><label class="form-label">Kapasitas</label><input type="number" name="kapasitas" id="r_kap" class="form-control" value="10"></div>
    <div class="mb-3"><label class="form-label">Fasilitas</label><input type="text" name="fasilitas" id="r_fas" class="form-control"></div>
    <div class="mb-3"><label class="form-label">Lokasi</label><input type="text" name="lokasi" id="r_lok" class="form-control"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<div class="modal fade" id="modalBook"><div class="modal-dialog">
<form class="modal-content" id="formBook">
  <div class="modal-header"><h5 class="modal-title">Booking Ruangan</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3"><label class="form-label">Ruangan</label><select name="ruangan_id" id="b_ruang" class="form-select" required></select></div>
    <div class="mb-3"><label class="form-label">Pegawai</label>
      <select name="pegawai_id" class="form-select" required>
        <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Kegiatan</label><input type="text" name="kegiatan" class="form-control" required></div>
    <div class="row g-3">
      <div class="col-6"><label class="form-label">Tanggal</label><input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-6"><label class="form-label">Peserta</label><input type="number" name="jumlah_peserta" class="form-control" value="1"></div>
      <div class="col-6"><label class="form-label">Jam Mulai</label><input type="time" name="jam_mulai" class="form-control" required></div>
      <div class="col-6"><label class="form-label">Jam Selesai</label><input type="time" name="jam_selesai" class="form-control" required></div>
    </div>
    <div class="mt-3"><label class="form-label">Keterangan</label><input type="text" name="keterangan" class="form-control"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_R='<?= e(url('admins/admin_kantor/api/ruangan.php')) ?>';
$(function(){
  $('#tblRuang').DataTable({
    ajax:{url:API_R+'?action=list',dataSrc:'data'},
    columns:[
      {data:'kode'},{data:'nama'},{data:'kapasitas'},{data:'fasilitas'},{data:'lokasi'},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editR(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusR(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],pageLength:10
  });

  $('#tblBook').DataTable({
    ajax:{url:API_R+'?action=booking_list',dataSrc:'data'},
    columns:[
      {data:'nama_ruangan'},{data:'nama_pegawai'},{data:'kegiatan'},{data:'tanggal'},
      {data:null,render:r=>`${r.jam_mulai} - ${r.jam_selesai}`},
      {data:'jumlah_peserta'},
      {data:'status',render:s=>{
        const m={menunggu:'warning',disetujui:'success',ditolak:'danger',selesai:'secondary'};
        return `<span class="badge bg-${m[s]||'secondary'}">${s}</span>`;
      }},
      {data:null,orderable:false,render:r=>{
        let b='';
        if(r.status==='menunggu'){
          b+=`<button class="btn btn-sm btn-success" onclick="setBookStatus(${r.id},'disetujui')"><i class="fa-solid fa-check"></i></button> `;
          b+=`<button class="btn btn-sm btn-danger" onclick="setBookStatus(${r.id},'ditolak')"><i class="fa-solid fa-xmark"></i></button>`;
        }
        return b;
      }}
    ],pageLength:10
  });

  fetch(API_R+'?action=list').then(r=>r.json()).then(d=>{
    $('#b_ruang').html(d.data.map(x=>`<option value="${x.id}">${x.kode} — ${x.nama}</option>`).join(''));
  });
});
function formRuang(){$('#formRuang')[0].reset();$('#r_id').val('');$('#modalRuang').modal('show');}
function editR(d){$('#r_id').val(d.id);$('#r_kode').val(d.kode);$('#r_nama').val(d.nama);$('#r_kap').val(d.kapasitas);$('#r_fas').val(d.fasilitas);$('#r_lok').val(d.lokasi);$('#modalRuang').modal('show');}
$('#formRuang').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_R+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalRuang').modal('hide');$('#tblRuang').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusR(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_R+'?action=delete',{method:'POST',body:fd});
  $('#tblRuang').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
function formBook(){$('#formBook')[0].reset();$('#modalBook').modal('show');}
$('#formBook').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_R+'?action=booking_save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalBook').modal('hide');$('#tblBook').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function setBookStatus(id,status){
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);fd.append('status',status);
  await fetch(API_R+'?action=booking_status',{method:'POST',body:fd});
  $('#tblBook').DataTable().ajax.reload(null,false);Swal.fire('OK','Status diubah.','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>