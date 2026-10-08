<?php
require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);
$peg = db()->query("SELECT id,nama FROM kantor_pegawai WHERE is_active=1 ORDER BY nama")->fetchAll();
$page_title='Aset'; $active_menu='aset';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="col-md-10 p-4">
  <h4 class="fw-bold mb-3"><i class="fa-solid fa-boxes-stacked me-2"></i>Manajemen Aset</h4>

  <ul class="nav nav-tabs mb-3">
    <li class="nav-item"><a class="nav-link active" data-bs-toggle="tab" href="#tabAset">Data Aset</a></li>
    <li class="nav-item"><a class="nav-link" data-bs-toggle="tab" href="#tabPinjam">Peminjaman</a></li>
  </ul>

  <div class="tab-content">
    <div class="tab-pane fade show active" id="tabAset">
      <button class="btn btn-primary btn-sm mb-2" onclick="formAset()"><i class="fa-solid fa-plus me-1"></i> Tambah Aset</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblAset" class="table table-striped w-100">
          <thead><tr><th>Kode</th><th>Nama</th><th>Kategori</th><th>Lokasi</th><th>Kondisi</th>
            <th class="text-end">Harga</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>

    <div class="tab-pane fade" id="tabPinjam">
      <button class="btn btn-primary btn-sm mb-2" onclick="formPinjam()"><i class="fa-solid fa-plus me-1"></i> Catat Peminjaman</button>
      <div class="card border-0 shadow-sm"><div class="card-body">
        <table id="tblPinjam" class="table table-striped w-100">
          <thead><tr><th>Aset</th><th>Pegawai</th><th>Jumlah</th><th>Tgl Pinjam</th>
            <th>Rencana Kembali</th><th>Aktual Kembali</th><th>Status</th><th>Aksi</th></tr></thead>
        </table>
      </div></div>
    </div>
  </div>
</main>

<div class="modal fade" id="modalAset"><div class="modal-dialog modal-lg">
<form class="modal-content" id="formAset" enctype="multipart/form-data">
  <div class="modal-header"><h5 class="modal-title">Form Aset</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <input type="hidden" name="id" id="a_id">
    <div class="row g-3">
      <div class="col-md-4"><label class="form-label">Kode</label><input type="text" name="kode" id="a_kode" class="form-control" required></div>
      <div class="col-md-8"><label class="form-label">Nama</label><input type="text" name="nama" id="a_nama" class="form-control" required></div>
      <div class="col-md-4"><label class="form-label">Kategori</label><input type="text" name="kategori" id="a_kat" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Lokasi</label><input type="text" name="lokasi" id="a_lok" class="form-control"></div>
      <div class="col-md-4"><label class="form-label">Kondisi</label>
        <select name="kondisi" id="a_kon" class="form-select">
          <option value="baik">Baik</option><option value="rusak_ringan">Rusak Ringan</option><option value="rusak_berat">Rusak Berat</option>
        </select></div>
      <div class="col-md-6"><label class="form-label">Harga Beli</label><input type="number" name="harga_beli" id="a_hrg" class="form-control" value="0"></div>
      <div class="col-md-6"><label class="form-label">Tanggal Beli</label><input type="date" name="tanggal_beli" id="a_tgl" class="form-control"></div>
      <div class="col-12"><label class="form-label">Keterangan</label><textarea name="keterangan" id="a_ket" class="form-control" rows="2"></textarea></div>
      <div class="col-12"><label class="form-label">Foto</label><input type="file" name="foto" class="form-control" accept="image/*"></div>
    </div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<div class="modal fade" id="modalPinjam"><div class="modal-dialog">
<form class="modal-content" id="formPinjam">
  <div class="modal-header"><h5 class="modal-title">Peminjaman Aset</h5>
    <button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
  <div class="modal-body">
    <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
    <div class="mb-3"><label class="form-label">Aset</label>
      <select name="aset_id" id="pj_aset" class="form-select" required></select></div>
    <div class="mb-3"><label class="form-label">Pegawai</label>
      <select name="pegawai_id" class="form-select" required>
        <?php foreach($peg as $p): ?><option value="<?= (int)$p['id'] ?>"><?= e($p['nama']) ?></option><?php endforeach; ?>
      </select></div>
    <div class="mb-3"><label class="form-label">Jumlah</label><input type="number" name="jumlah" class="form-control" value="1" min="1"></div>
    <div class="row g-3">
      <div class="col-6"><label class="form-label">Tgl Pinjam</label><input type="date" name="tanggal_pinjam" class="form-control" value="<?= date('Y-m-d') ?>" required></div>
      <div class="col-6"><label class="form-label">Rencana Kembali</label><input type="date" name="tanggal_kembali_plan" class="form-control" required></div>
    </div>
    <div class="mt-3"><label class="form-label">Keterangan</label><input type="text" name="keterangan" class="form-control"></div>
  </div>
  <div class="modal-footer"><button class="btn btn-primary">Simpan</button></div>
</form></div></div>

<script>
const API_A='<?= e(url('admins/admin_kantor/api/aset.php')) ?>';
let ASET_CACHE=[];
$(function(){
  $('#tblAset').DataTable({
    ajax:{url:API_A+'?action=list',dataSrc:'data'},
    columns:[
      {data:'kode'},{data:'nama'},{data:'kategori'},{data:'lokasi'},
      {data:'kondisi',render:k=>`<span class="badge bg-${k==='baik'?'success':k==='rusak_ringan'?'warning':'danger'}">${k}</span>`},
      {data:'harga_beli',className:'text-end',render:d=>'Rp '+Number(d).toLocaleString('id-ID')},
      {data:null,orderable:false,render:r=>`
        <button class="btn btn-sm btn-warning" onclick='editAset(${JSON.stringify(r).replace(/'/g,"&#39;")})'><i class="fa-solid fa-pen"></i></button>
        <button class="btn btn-sm btn-danger" onclick="hapusAset(${r.id})"><i class="fa-solid fa-trash"></i></button>`}
    ],
    dom:'Bfrtip',buttons:[
      {extend:'excelHtml5',className:'btn btn-sm btn-success',text:'Excel'},
      {extend:'pdfHtml5',className:'btn btn-sm btn-danger',text:'PDF'},
      {extend:'print',className:'btn btn-sm btn-secondary',text:'Print'}
    ],pageLength:10
  });

  $('#tblPinjam').DataTable({
    ajax:{url:API_A+'?action=pinjam_list',dataSrc:'data'},
    columns:[
      {data:'kode_aset',render:(d,t,r)=>`${d} — ${r.nama_aset}`},
      {data:'nama_pegawai'},{data:'jumlah'},{data:'tanggal_pinjam'},
      {data:'tanggal_kembali_plan'},{data:'tanggal_kembali_act'},
      {data:'status',render:s=>`<span class="badge bg-${s==='dikembalikan'?'success':'warning'}">${s}</span>`},
      {data:null,orderable:false,render:r=>r.status==='dipinjam'?
        `<button class="btn btn-sm btn-success" onclick="kembalikan(${r.id})"><i class="fa-solid fa-check"></i> Kembali</button>`:''}
    ],pageLength:10
  });

  // Cache aset untuk dropdown pinjam
  fetch(API_A+'?action=list').then(r=>r.json()).then(d=>{
    ASET_CACHE = d.data;
    $('#pj_aset').html(d.data.map(a=>`<option value="${a.id}">${a.kode} — ${a.nama}</option>`).join(''));
  });
});
function formAset(){$('#formAset')[0].reset();$('#a_id').val('');$('#modalAset').modal('show');}
function editAset(d){
  ['id','kode','nama','kategori','lokasi','kondisi','harga_beli','tanggal_beli','keterangan'].forEach(k=>{
    const map={id:'id',kode:'kode',nama:'nama',kategori:'kat',lokasi:'lok',kondisi:'kon',harga_beli:'hrg',tanggal_beli:'tgl',keterangan:'ket'};
    $('#a_'+map[k]).val(d[k]);
  });
  $('#modalAset').modal('show');
}
$('#formAset').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_A+'?action=save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalAset').modal('hide');$('#tblAset').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function hapusAset(id){
  const c=await Swal.fire({title:'Hapus?',icon:'warning',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_A+'?action=delete',{method:'POST',body:fd});
  $('#tblAset').DataTable().ajax.reload(null,false);Swal.fire('OK','Terhapus','success');
}
function formPinjam(){$('#formPinjam')[0].reset();$('#modalPinjam').modal('show');}
$('#formPinjam').on('submit',async e=>{e.preventDefault();
  const r=await fetch(API_A+'?action=pinjam_save',{method:'POST',body:new FormData(e.target)});
  const d=await r.json();
  if(d.ok){$('#modalPinjam').modal('hide');$('#tblPinjam').DataTable().ajax.reload(null,false);Swal.fire('OK',d.msg,'success');}
  else Swal.fire('Gagal',d.msg,'error');
});
async function kembalikan(id){
  const c=await Swal.fire({title:'Konfirmasi',text:'Tandai dikembalikan?',icon:'question',showCancelButton:true});
  if(!c.isConfirmed)return;
  const fd=new FormData();fd.append('_csrf',KANTOR.csrf);fd.append('id',id);
  await fetch(API_A+'?action=pinjam_kembali',{method:'POST',body:fd});
  $('#tblPinjam').DataTable().ajax.reload(null,false);Swal.fire('OK','Dikembalikan.','success');
}
</script>
<?php include __DIR__ . '/../includes/module_footer.php'; ?>