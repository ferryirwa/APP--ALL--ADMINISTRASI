<?php
/**
 * File    : admins/admin_gudang/pages/barang.php
 * Fungsi  : CRUD Barang
 */

require_once __DIR__ . '/../config.php';
auth_check(MODULE_SLUG);

$page_title  = 'Barang';
$active_menu = 'barang';
include __DIR__ . '/../includes/module_header.php';
include __DIR__ . '/../includes/module_sidebar.php';
?>
<main class="main">
  <div class="page-header">
    <div>
      <h1>Data Barang</h1>
      <div class="subtitle">Kelola master barang gudang</div>
    </div>
    <button class="btn btn-primary btn-sm" onclick="formBarang()">
      <i class="fa-solid fa-plus me-1"></i> Tambah Barang
    </button>
  </div>

  <div class="card">
    <div class="card-body">
      <div id="debugInfo" class="alert alert-info small d-none"></div>
      <div class="table-responsive">
        <table id="tblBarang" class="table table-hover w-100">
          <thead>
            <tr>
              <th>Kode</th><th>Barcode</th><th>Nama</th><th>Kategori</th>
              <th>Satuan</th><th class="text-end">H. Beli</th>
              <th class="text-end">H. Jual</th><th class="text-end">Stok</th>
              <th>Rak</th><th>Aksi</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Modal -->
<div class="modal fade" id="modalBarang" tabindex="-1">
  <div class="modal-dialog modal-lg">
    <form class="modal-content" id="formBarang">
      <div class="modal-header">
        <h5 class="modal-title">Form Barang</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="_csrf" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="id" id="f_id">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Kode Barang *</label>
            <input type="text" name="kode" id="f_kode" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Barcode</label>
            <input type="text" name="barcode" id="f_barcode" class="form-control"
                   placeholder="Kosongkan untuk auto-generate">
          </div>
          <div class="col-md-8">
            <label class="form-label">Nama Barang *</label>
            <input type="text" name="nama" id="f_nama" class="form-control" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Kategori</label>
            <input type="text" name="kategori" id="f_kategori" class="form-control">
          </div>
          <div class="col-md-4">
            <label class="form-label">Satuan</label>
            <input type="text" name="satuan" id="f_satuan" class="form-control" value="pcs">
          </div>
          <div class="col-md-4">
            <label class="form-label">Harga Beli</label>
            <input type="number" name="harga_beli" id="f_harga_beli" class="form-control" value="0">
          </div>
          <div class="col-md-4">
            <label class="form-label">Harga Jual</label>
            <input type="number" name="harga_jual" id="f_harga_jual" class="form-control" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label">Stok Minimum</label>
            <input type="number" name="stok_minimum" id="f_stok_minimum" class="form-control" value="0">
          </div>
          <div class="col-md-6">
            <label class="form-label">Lokasi Rak</label>
            <input type="text" name="lokasi_rak" id="f_lokasi_rak" class="form-control">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
        <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Simpan</button>
      </div>
    </form>
  </div>
</div>

<script>
console.log('=== HALAMAN BARANG DIMUAT ===');
console.log('jQuery:', typeof jQuery);
console.log('DataTables:', typeof jQuery !== 'undefined' && jQuery.fn ? typeof jQuery.fn.DataTable : 'jQuery belum ada');

const API_BARANG = '<?= e(url('admins/admin_gudang/api/barang.php')) ?>';
const CSRF_TOKEN = '<?= e(csrf_token()) ?>';
console.log('API URL:', API_BARANG);

let dtBarang = null;

jQuery(document).ready(function ($) {
  console.log('Document ready, jQuery OK');

  if (typeof $.fn.DataTable !== 'function') {
    $('#debugInfo').removeClass('d-none').addClass('alert-danger')
      .html('<strong>ERROR:</strong> DataTables tidak ter-load. Cek koneksi internet atau cache browser.');
    return;
  }

  console.log('DataTables OK, inisialisasi tabel...');

  dtBarang = $('#tblBarang').DataTable({
    ajax: {
      url: API_BARANG + '?action=list',
      dataSrc: function (json) {
        console.log('API Response diterima:', json);
        if (json.error) {
          $('#debugInfo').removeClass('d-none').addClass('alert-danger')
            .html('<strong>API Error:</strong> ' + (json.msg || json.error));
          return [];
        }
        const data = json.data || [];
        console.log('Jumlah barang:', data.length);
        if (data.length === 0) {
          $('#debugInfo').removeClass('d-none').removeClass('alert-danger').addClass('alert-warning')
            .html('Database kosong. Silakan tambah barang.');
        } else {
          $('#debugInfo').addClass('d-none');
        }
        return data;
      },
      error: function (xhr, status, err) {
        console.error('AJAX Error:', status, err, xhr.responseText);
        $('#debugInfo').removeClass('d-none').addClass('alert-danger')
          .html('<strong>AJAX Error:</strong> ' + status + ' — ' + err +
                '<br><small>Response: <code>' + xhr.responseText.substring(0, 200) + '</code></small>');
      }
    },
    columns: [
      { data: 'kode' },
      { data: 'barcode', render: function(d) { return d ? '<code>'+d+'</code>' : '-'; } },
      { data: 'nama' },
      { data: 'kategori', defaultContent: '-' },
      { data: 'satuan', defaultContent: '-' },
      { data: 'harga_beli', className:'text-end', render: function(d) { return 'Rp ' + Number(d||0).toLocaleString('id-ID'); } },
      { data: 'harga_jual', className:'text-end', render: function(d) { return 'Rp ' + Number(d||0).toLocaleString('id-ID'); } },
      { data: 'stok', className:'text-end fw-bold', defaultContent: '0' },
      { data: 'lokasi_rak', defaultContent: '-' },
      { data: null, orderable:false, render: function(r) {
        var json = JSON.stringify(r).replace(/'/g, "&#39;").replace(/"/g, '&quot;');
        return '<button class="btn btn-sm btn-warning" onclick="editBarang(' + json + ')">' +
               '<i class="fa-solid fa-pen"></i></button> ' +
               '<button class="btn btn-sm btn-danger" onclick="hapusBarang(' + r.id + ')">' +
               '<i class="fa-solid fa-trash"></i></button>';
      } }
    ],
    dom: 'Bfrtip',
    buttons: [
      { extend:'excelHtml5', className:'btn btn-sm btn-success', text:'Excel' },
      { extend:'pdfHtml5', className:'btn btn-sm btn-danger', text:'PDF' },
      { extend:'print', className:'btn btn-sm btn-secondary', text:'Print' }
    ],
    pageLength: 10,
    language: {
      search: 'Cari:',
      lengthMenu: 'Tampil _MENU_ data',
      info: 'Menampilkan _START_–_END_ dari _TOTAL_',
      infoEmpty: 'Tidak ada data',
      zeroRecords: 'Data tidak ditemukan',
      emptyTable: 'Belum ada data',
      paginate: { previous: 'Sebelumnya', next: 'Selanjutnya' }
    }
  });
});

function formBarang() {
  document.getElementById('formBarang').reset();
  document.getElementById('f_id').value = '';
  var modal = new bootstrap.Modal(document.getElementById('modalBarang'));
  modal.show();
}

function editBarang(data) {
  document.getElementById('f_id').value = data.id;
  document.getElementById('f_kode').value = data.kode;
  document.getElementById('f_barcode').value = data.barcode || '';
  document.getElementById('f_nama').value = data.nama;
  document.getElementById('f_kategori').value = data.kategori || '';
  document.getElementById('f_satuan').value = data.satuan || 'pcs';
  document.getElementById('f_harga_beli').value = data.harga_beli || 0;
  document.getElementById('f_harga_jual').value = data.harga_jual || 0;
  document.getElementById('f_stok_minimum').value = data.stok_minimum || 0;
  document.getElementById('f_lokasi_rak').value = data.lokasi_rak || '';
  var modal = new bootstrap.Modal(document.getElementById('modalBarang'));
  modal.show();
}

document.addEventListener('DOMContentLoaded', function() {
  var form = document.getElementById('formBarang');
  if (!form) return;

  form.addEventListener('submit', async function (e) {
    e.preventDefault();
    console.log('=== SUBMIT FORM BARANG ===');

    var fd = new FormData(this);
    var dataObj = {};
    fd.forEach(function(v, k) { dataObj[k] = v; });
    console.log('Data dikirim:', dataObj);

    try {
      var r = await fetch(API_BARANG + '?action=save', {
        method: 'POST',
        body: fd
      });

      console.log('HTTP Status:', r.status);
      var text = await r.text();
      console.log('Response text:', text);

      var d;
      try {
        d = JSON.parse(text);
      } catch (err) {
        Swal.fire('Error', 'Response bukan JSON:<br><small>' + text.substring(0, 300) + '</small>', 'error');
        return;
      }

      if (d.ok) {
        var modalEl = document.getElementById('modalBarang');
        var modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
        if (dtBarang) dtBarang.ajax.reload(null, false);
        Swal.fire('Berhasil', d.msg, 'success');
      } else {
        Swal.fire('Gagal', d.msg || JSON.stringify(d), 'error');
      }
    } catch (err) {
      console.error('Submit error:', err);
      Swal.fire('Error', err.message, 'error');
    }
  });
});

async function hapusBarang(id) {
  var c = await Swal.fire({
    title: 'Hapus barang?',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Ya, hapus',
    cancelButtonText: 'Batal'
  });
  if (!c.isConfirmed) return;

  var fd = new FormData();
  fd.append('_csrf', CSRF_TOKEN);
  fd.append('id', id);

  var r = await fetch(API_BARANG + '?action=delete', { method:'POST', body:fd });
  var d = await r.json();
  if (d.ok) {
    if (dtBarang) dtBarang.ajax.reload(null, false);
    Swal.fire('Terhapus', d.msg, 'success');
  } else {
    Swal.fire('Gagal', d.msg, 'error');
  }
}
</script>

<?php include __DIR__ . '/../includes/module_footer.php'; ?>