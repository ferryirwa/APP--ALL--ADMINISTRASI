<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nip,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Absensi'; $active_menu='absensi';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <h4 class="fw-bold mb-3"><i class="fa-solid fa-fingerprint me-2"></i>Absensi</h4>

  <div class="row g-3 mb-4">
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100"><div class="card-body">
        <h6 class="fw-bold mb-3">Check In / Check Out (GPS)</h6>
        <div class="mb-3"><label class="form-label">Pegawai</label>
          <select id="ab_peg" class="form-select">
            <?php foreach($peg as $p): ?>
              <option value="<?= (int)$p['id'] ?>"><?= e($p['nip'].' — '.$p['nama']) ?></option>
            <?php endforeach; ?>
          </select></div>
        <div id="gpsInfo" class="alert alert-info small mb-3">
          <i class="fa-solid fa-location-dot me-1"></i> <span id="gpsText">Belum ambil lokasi</span>
        </div>
        <button class="btn btn-primary" onclick="ambilLokasi()"><i class="fa-solid fa-location-crosshairs me-1"></i> Ambil Lokasi</button>
        <button class="btn btn-success" onclick="checkIn()"><i class="fa-solid fa-right-to-bracket me-1"></i> Check In</button>
        <button class="btn btn-warning" onclick="checkOut()"><i class="fa-solid fa-right-from-bracket me-1"></i> Check Out</button>
      </div></div>
    </div>
    <div class="col-md-6">
      <div class="card border-0 shadow-sm h-100"><div class="card-body">
        <h6 class="fw-bold mb-3">Input Manual</h6>
        <div class="mb-3"><label class="form-label">Pegawai</label>
          <select id="mn_peg" class="form-select">
            <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
          </select></div>
        <div class="row g-2">
          <div class="col-6"><label class="form-label">Tanggal</label>
            <input type="date" id="mn_tgl" class="form-control" value="<?= date('Y-m-d') ?>"></div>
          <div class="col-6"><label class="form-label">Status</label>
            <select id="mn_status" class="form-select">
              <option value="hadir">Hadir</option><option value="izin">Izin</option>
              <option value="sakit">Sakit</option><option value="cuti">Cuti</option>
              <option value="alpha">Alpha</option><option value="terlambat">Terlambat</option>
            </select></div>
          <div class="col-12"><label class="form-label">Keterangan</label>
            <input type="text" id="mn_ket" class="form-control"></div>
        </div>
        <button class="btn btn-primary mt-3" onclick="simpanManual()"><i class="fa-solid fa-save me-1"></i> Simpan</button>
      </div></div>
    </div>
  </div>

  <div class="card border-0 shadow-sm"><div class="card-body">
    <h6 class="fw-bold mb-3">Riwayat Absensi</h6>
    <table id="tblAb" class="table table-striped w-100">
      <thead><tr><th>Tanggal</th><th>NIP</th><th>Nama</th><th>Masuk</th><th>Keluar</th>
        <th>Status</th><th>Keterangan</th></tr></thead>
    </table>
  </div></div>
</main>

<script>
const API_AB='<?= e(url('admins/admin_kantor/api/absensi.php')) ?>';
let gps = {lat:null,lng:null};

$(function(){
  $('#tblAb').DataTable({
    ajax:{url:API_AB+'?action=list',dataSrc:'data'},
    columns:[
      {data:'tanggal'},{data:'nip'},{data:'nama'},
      {data:'jam_masuk'},{data:'jam_keluar'},
      {data:'status',render:s=>`<span class="badge bg-secondary">${s}</span>`},
      {data:'keterangan'}
    ],pageLength:25
  });
});

function ambilLokasi(){
  if (!navigator.geolocation) { Swal.fire('Info','Browser tidak support GPS.','info'); return; }
  navigator.geolocation.getCurrentPosition(
    p => { gps.lat=p.coords.latitude; gps.lng=p.coords.longitude;
      $('#gpsText').text(`Lat: ${gps.lat.toFixed(6)}, Lng: ${gps.lng.toFixed(6)}`);
      $('#gpsInfo').removeClass('alert-info').addClass('alert-success'); },
    e => Swal.fire('Gagal', 'GPS error: ' + e.message, 'error'),
    {enableHighAccuracy:true, timeout:10000}
  );
}

async function checkIn(){ return kirimAbsen('check_in'); }
async function checkOut(){ return kirimAbsen('check_out'); }

async function kirimAbsen(aksi){
  if (!gps.lat) { Swal.fire('Info','Ambil lokasi dulu.','info'); return; }
  const fd = new FormData();
  fd.append('_csrf',KANTOR.csrf);
  fd.append('pegawai_id', document.getElementById('ab_peg').value);
  fd.append('lat', gps.lat); fd.append('lng', gps.lng);
  const r = await fetch(API_AB+'?action='+aksi, {method:'POST',body:fd});
  const d = await r.json();
  if (d.ok) {
    Swal.fire('OK', d.msg + (d.jarak_m ? ` (jarak: ${d.jarak_m} m, ${d.in_range?'dalam radius':'di luar radius'})` : ''), 'success');
    $('#tblAb').DataTable().ajax.reload(null,false);
  } else Swal.fire('Gagal', d.msg, 'error');
}

async function simpanManual(){
  const fd = new FormData();
  fd.append('_csrf',KANTOR.csrf);
  fd.append('pegawai_id', document.getElementById('mn_peg').value);
  fd.append('tanggal', document.getElementById('mn_tgl').value);
  fd.append('status', document.getElementById('mn_status').value);
  fd.append('keterangan', document.getElementById('mn_ket').value);
  const r = await fetch(API_AB+'?action=manual',{method:'POST',body:fd});
  const d = await r.json();
  if (d.ok) { Swal.fire('OK',d.msg,'success'); $('#tblAb').DataTable().ajax.reload(null,false); }
  else Swal.fire('Gagal',d.msg,'error');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>