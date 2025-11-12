<?php
include "koneksi.php";
$view = isset($_GET['view']) ? $_GET['view'] : 'utama';

// Ambil data untuk dropdown
$customers = mysqli_query($koneksi, "SELECT DISTINCT nama_customer FROM pakai_pestisida ORDER BY nama_customer ASC");

// ambil pestisida sebagai array agar bisa dipakai berkali-kali tanpa masalah pointer
$res_p = mysqli_query($koneksi, "SELECT * FROM pestisida ORDER BY nama_pestisida ASC");
if ($res_p === false) {
    die("Query pestisida error: " . mysqli_error($koneksi));
}
$pestisidas_arr = mysqli_fetch_all($res_p, MYSQLI_ASSOC);

?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Data Pestisida</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
<style>
  .stok-control { display: flex; align-items: center; justify-content: center; gap: 4px; }
  .stok-control input[type=number] { width: 70px; text-align: center; }
  .form-section { background: #f8f9fa; border-radius: 10px; padding: 15px; margin-bottom: 20px; }
</style>
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-bug"></i> Data Pestisida</h4>
      <div class="d-flex gap-2">
      <a href="tambah_pestisida.php" class="btn btn-light btn-sm">
      <i class="bi bi-plus-lg"></i> Tambah Pestisida
      </a>
      <a href="data_pestisida.php?view=pakai" 
      class="btn btn-sm <?= ($view==='pakai') ? 'btn-primary' : 'btn-light' ?>">
      💧 Pakai Pestisida
     </a>


        <!-- Dropdown View -->
        <div class="btn-group">
          <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Lihat Tabel</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="data_pestisida.php?view=utama">Tabel Utama Pestisida</a></li>
            <li><a class="dropdown-item" href="data_pestisida.php?view=dipakai">Tabel Pestisida Dipakai</a></li>
          </ul>
        </div>

        <a href="riwayat_pemakaian.php" class="btn btn-light btn-sm"><i class="bi bi-clock-history"></i> Riwayat</a>
      </div>
    </div>

    <div class="card-body">
    <?php
      $warnings = [];
      ?>
   <?php if ($view === 'utama'): ?>

<h5 class="text-center fw-bold mb-3">📦 Tabel Utama Stok Pestisida</h5>

<?php
// ===============================
// Kumpulkan warnings SEBELUM tampil
// ===============================
$warnings = [];
$sql_warning = mysqli_query($koneksi, "SELECT * FROM pestisida ORDER BY id_pestisida DESC");

while ($rw = mysqli_fetch_assoc($sql_warning)) {

    $botol = (int)$rw['jumlah_botol'];
    $sisa_ml = (float)$rw['sisa_ml'];
    $nama = htmlspecialchars($rw['nama_pestisida']);
    $tipe = strtolower($rw['tipe']);

    $isChemical = ($tipe == "chemical" || $tipe == "pestisida");

    // ===============================
    // FORMAT WARNING CHEMICAL
    // ===============================
    if ($isChemical) {

        if ($botol <= 0 && $sisa_ml <= 0) {
            $warnings[] = "Stok <b>$nama</b> sudah <span class='text-danger fw-bold'>HABIS</span>!";
        }
        elseif ($botol <= 1) {
            $warnings[] = "Stok <b>$nama</b> sangat kritis! Sisa <b>$botol botol</b> (<b>$sisa_ml ml</b>).";
        }
        elseif ($botol <= 3) {
            $warnings[] = "Stok <b>$nama</b> hampir habis! Sisa <b>$botol botol</b> (<b>$sisa_ml ml</b>).";
        }
        elseif ($botol <= 5) {
            $warnings[] = "Stok <b>$nama</b> menipis. Sisa <b>$botol botol</b> (<b>$sisa_ml ml</b>).";
        }
        elseif ($botol <= 10) {
            $warnings[] = "Stok <b>$nama</b> mulai sedikit. Sisa <b>$botol botol</b> (<b>$sisa_ml ml</b>).";
        }

    }

    // ===============================
    // FORMAT WARNING NON-CHEMICAL
    // ===============================
    else {

        if ($botol <= 0) {
            $warnings[] = "Stok <b>$nama</b> sudah <span class='text-danger fw-bold'>HABIS</span>!";
        }
        elseif ($botol <= 1) {
            $warnings[] = "Stok <b>$nama</b> sangat kritis! Sisa <b>$botol botol/Pcs</b>.";
        }
        elseif ($botol <= 3) {
            $warnings[] = "Stok <b>$nama</b> hampir habis! Sisa <b>$botol botol/Pcs</b>.";
        }
        elseif ($botol <= 5) {
            $warnings[] = "Stok <b>$nama</b> menipis. Sisa <b>$botol botol/Pcs</b>.";
        }
        elseif ($botol <= 10) {
            $warnings[] = "Stok <b>$nama</b> mulai sedikit. Sisa <b>$botol botol/Pcs</b>.";
        }
    }
}
?>


<?php if (!empty($warnings)): ?>
<div class="alert alert-danger mt-3">
    <h5 class="fw-bold mb-2">
        <i class="bi bi-exclamation-triangle-fill"></i> Peringatan Stok Rendah
    </h5>
    <ul class="mb-0">
        <?php foreach ($warnings as $w): ?>
            <li><?= $w ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<?php
// 🧾 Logika tombol tambah stok
// 🧾 Logika tombol tambah stok (versi sesuai input user)
// 🧾 Logika tombol tambah stok
if (isset($_POST['tambah_stok'])) {
  $id_pestisida = (int)$_POST['id_pestisida'];
  $jumlah_tambah = (int)$_POST['jumlah_tambah'];

  $cek = mysqli_query($koneksi, "SELECT * FROM pestisida WHERE id_pestisida='$id_pestisida'");
  $data = mysqli_fetch_assoc($cek);

  if ($data) {
      $jumlah_lama = (int)$data['jumlah_botol'];
      $jumlah_baru = $jumlah_lama + $jumlah_tambah;

      $isi_per_botol = (float)$data['isi_per_botol_ml'];
      $tipe_lower = strtolower($data['tipe']);

      // Hitung sisa aktif (ml)
      $sisa_lama = (float)$data['sisa_ml'];
      if ($tipe_lower === 'pestisida' || $tipe_lower === 'chemical') {
          $sisa_tambah = $jumlah_tambah * $isi_per_botol; // ml yang ditambahkan
          $sisa_baru = $sisa_lama + $sisa_tambah;
      } else {
          $sisa_tambah = 0;
          $sisa_baru = 0;
      }

      // Update stok utama + tanggal_update
      mysqli_query($koneksi, "
          UPDATE pestisida 
          SET jumlah_botol='$jumlah_baru', sisa_ml='$sisa_baru', tanggal_update=CURDATE()
          WHERE id_pestisida='$id_pestisida'
      ") or die('Gagal update pestisida: '.mysqli_error($koneksi));

      // Catat riwayat — pakai status dan aktivitas seragam
      $nama_pestisida = mysqli_real_escape_string($koneksi, $data['nama_pestisida']);
      $keterangan = "Menambah stok pestisida $nama_pestisida sebanyak $jumlah_tambah botol (+" . $sisa_tambah . " ml). Total stok sekarang $jumlah_baru botol.";

      mysqli_query($koneksi, "
        INSERT INTO riwayat_pestisida 
        (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
        VALUES 
        ('$id_pestisida', '".mysqli_real_escape_string($koneksi, $data['tipe'])."', 
        'tambah_stok', '-', '-', 
         '$jumlah_tambah', 'botol', CURDATE(), 
         '".mysqli_real_escape_string($koneksi, $keterangan)."', 
         'stok ditambah', '$sisa_tambah')
      ") or die('Gagal insert riwayat (tambah_stok): '.mysqli_error($koneksi));

      echo "<script>
        alert('✅ Stok pestisida berhasil ditambahkan dan dicatat ke riwayat.');
        window.location='data_pestisida.php';
      </script>";
      exit;
  }
}
?>

<table class="table table-bordered table-striped align-middle">
  <thead class="table-dark text-center">
    <tr>
      <th>No</th>
      <th>Nama Pestisida</th>
      <th>Tipe</th>
      <th>Jumlah Botol</th>
      <th>Isi per Botol (ml)</th>
      <th>Sisa Aktif (ml)</th>
      <th>Tanggal Update</th>
      <th width="320">Aksi</th>
    </tr>
  </thead>
  <tbody>
    <?php
    $no = 1;
    $sql = mysqli_query($koneksi, "SELECT * FROM pestisida ORDER BY id_pestisida DESC");
    if (mysqli_num_rows($sql) > 0) {
      while ($row = mysqli_fetch_assoc($sql)) {
        $botol = (int)$row['jumlah_botol'];
$sisa_ml = (float)$row['sisa_ml'];
$nama = htmlspecialchars($row['nama_pestisida']);

// Peringatan stok berlaku untuk SEMUA BARANG
if ($botol <= 0 && $sisa_ml <= 0) {
    $warnings[] = "Stok <b>$nama</b> sudah <span class='text-danger fw-bold'>HABIS</span>!";
}
elseif ($botol <= 1) {
    $warnings[] = "Stok <b>$nama</b> sangat kritis! Sisa <b>$botol botol</b> (<b>$sisa_ml ml</b>).";
}
elseif ($botol <= 3) {
    $warnings[] = "Stok <b>$nama</b> hampir habis! Sisa <b>$botol botol</b>.";
}
elseif ($botol <= 5) {
    $warnings[] = "Stok <b>$nama</b> menipis. Sisa <b>$botol botol</b>.";
}
elseif ($botol <= 10) {
    $warnings[] = "Stok <b>$nama</b> mulai sedikit. Sisa <b>$botol botol</b>.";
}

$isChemical = in_array(strtolower($row['tipe']), ['chemical','pestisida']);
    ?>
    <tr>
      <td class="text-center"><?= $no++ ?></td>
      <td><?= htmlspecialchars($row['nama_pestisida']) ?></td>
      <td class="text-center text-capitalize"><?= htmlspecialchars($row['tipe']) ?></td>
        <td class="text-center">
        <span class="badge bg-secondary">
            <?= $row['jumlah_botol'] ?>
          </span>
        </td>
        <td class="text-center">
        <span class="badge bg-secondary">
            <?= $isChemical ? $row['isi_per_botol_ml'] : '-' ?>
          </span>
        </td>
        <td class="text-center">
        <span class="badge bg-secondary">
            <?= $isChemical ? $row['sisa_ml'] : '-' ?>
          </span>
        </td>
              <td class="text-center"><?= $row['tanggal_update'] ?></td>
      <td class="text-center">
        <div class="d-flex justify-content-center gap-2 flex-wrap">
          <!-- Tombol Edit & Hapus -->
          <a href="edit_pestisida.php?id=<?= $row['id_pestisida'] ?>" class="btn btn-warning btn-sm">✏️ Edit</a>
          <a href="hapus_pestisida.php?id=<?= $row['id_pestisida'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin menghapus pestisida ini?')">🗑️ Hapus</a>

          <!-- Form Tambah Stok -->
          <form method="post" action="" class="d-flex align-items-center mt-1" onsubmit="return confirm('Apakah Anda yakin ingin menambahkan stok pestisida ini?');">
            <input type="hidden" name="id_pestisida" value="<?= $row['id_pestisida'] ?>">
            <div class="input-group input-group-sm" style="width:100px;">
              <button type="button" class="btn btn-outline-secondary btn-minus">-</button>
              <input type="number" name="jumlah_tambah" class="form-control text-center" value="1" min="1">
              <button type="button" class="btn btn-outline-secondary btn-plus">+</button>
            </div>
            <button type="submit" name="tambah_stok" class="btn btn-success btn-sm ms-2">💾 Simpan</button>
          </form>
        </div>
      </td>
    </tr>
    <?php 
      }
    } else {
      echo "<tr><td colspan='8' class='text-center text-muted'>Belum ada data pestisida.</td></tr>";
    } 
    ?>
  </tbody>
</table>

<?php elseif ($view === 'pakai'): ?>
  <h5 class="text-center fw-bold mb-3">💧 Input Pemakaian Pestisida</h5>

  <form action="pakai_pestisida_simpan.php" method="post">
    <div class="form-section">
      <div class="row mb-3">
        <div class="col-md-4">
          <label class="form-label fw-bold">Customer</label>
          <select id="customerSelect" name="customer_type" class="form-select" onchange="toggleCustomerInput()">
            <option value="lama">Customer Lama</option>
            <option value="baru">Customer Baru</option>
          </select>
          <select id="customerOld" name="nama_customer_old" class="form-select mt-2">
            <option value="">-- Pilih Customer Lama --</option>
            <?php while ($c = mysqli_fetch_assoc($customers)) { ?>
              <option value="<?= htmlspecialchars($c['nama_customer']) ?>">
                <?= htmlspecialchars($c['nama_customer']) ?>
              </option>
            <?php } ?>
          </select>
          <input id="customerNew" type="text" name="nama_customer_new" class="form-control mt-2" placeholder="Ketik nama baru..." style="display:none;">
        </div>

        <div class="col-md-4">
          <label class="form-label fw-bold">Nama Teknisi</label>
          <input type="text" name="nama_teknisi" class="form-control" required>
        </div>

        <div class="col-md-4">
          <label class="form-label fw-bold">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
        </div>
      </div>
    </div>

    <table class="table table-bordered" id="pestisidaTable">
  <thead class="table-dark text-center">
    <tr>
      <th>Pestisida</th>
      <th>Jumlah (Botol)</th>
      <th>Keterangan</th>
      <th>Aksi</th>
    </tr>
  </thead>
  <tbody id="pestisidaRows">
    <tr>
      <td>
        <select name="id_pestisida[]" class="form-select select-pestisida" required>
          <option value="">-- Pilih Pestisida --</option>
          <?php
          if (!empty($pestisidas_arr)) {
            foreach ($pestisidas_arr as $p) {
              $id = (int)$p['id_pestisida'];
              $nama = htmlspecialchars($p['nama_pestisida']);
              echo "<option value='{$id}'>{$nama}</option>";
            }
          }
          ?>
        </select>
      </td>
      <td><input type="number" name="jumlah_pakai[]" class="form-control" min="1" required></td>
      <td><input type="text" name="keterangan[]" class="form-control" placeholder="Opsional"></td>
      <td class="text-center">
        <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">🗑️</button>
      </td>
    </tr>
  </tbody>
</table>


<button type="button" class="btn btn-outline-primary btn-sm" onclick="tambahBaris()">+ Tambah Pestisida</button>

<div class="mt-3 text-center">
  <button type="submit" class="btn btn-success px-4 py-2">
    💾 Simpan Pemakaian Pestisida
  </button>
</div>
<script>
function tambahBaris() {
  const table = document.getElementById('pestisidaRows');
  const row = document.createElement('tr');
  row.innerHTML = `
    <td>
      <select name="id_pestisida[]" class="form-select select-pestisida" required>
        <option value="">-- Pilih Pestisida --</option>
        <?php
        if (!empty($pestisidas_arr)) {
          foreach ($pestisidas_arr as $p) {
            $id = (int)$p['id_pestisida'];
            $nama = htmlspecialchars($p['nama_pestisida']);
            echo "<option value='{$id}'>{$nama}</option>";
          }
        }
        ?>
      </select>
    </td>
    <td><input type="number" name="jumlah_pakai[]" class="form-control" min="1" required></td>
    <td><input type="text" name="keterangan[]" class="form-control" placeholder="Opsional"></td>
    <td class="text-center">
      <button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">🗑️</button>
    </td>`;
  table.appendChild(row);
}

function hapusBaris(btn) {
  btn.closest('tr').remove();
}
</script>


      <?php elseif ($view === 'dipakai'): 
        // --- Ambil filter ---
$customer = $_GET['customer'] ?? 'Semua';

// --- Ambil daftar customer untuk dropdown ---
$listCustomer = mysqli_query($koneksi, "
  SELECT DISTINCT nama_customer 
  FROM pakai_pestisida 
  WHERE nama_customer <> '' 
  ORDER BY nama_customer ASC
");

// --- Query utama ---
$where = [];
if ($customer !== 'Semua') {
  $where[] = "pk.nama_customer = '$customer'";
}
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = mysqli_query($koneksi, "
  SELECT pk.*, p.nama_pestisida, p.sisa_ml 
  FROM pakai_pestisida pk
  JOIN pestisida p ON p.id_pestisida = pk.id_pestisida
  $whereSQL
  ORDER BY pk.nama_customer ASC, pk.tanggal DESC, pk.id_pakai DESC
");
?>

<div class="card-body">
  <h5 class="text-center fw-bold mb-3">💧 Daftar Pemakaian Pestisida</h5>

  <!-- 🔍 Filter Customer -->
  <form method="GET" class="row g-2 mb-4">
    <div class="col-md-4">
      <label class="form-label">Nama Customer</label>
      <select name="customer" class="form-select">
        <option value="Semua">Semua</option>
        <?php while($c = mysqli_fetch_assoc($listCustomer)) {
          $sel = ($customer == $c['nama_customer']) ? 'selected' : '';
          echo "<option value='{$c['nama_customer']}' $sel>{$c['nama_customer']}</option>";
        } ?>
      </select>
    </div>
    <div class="col-md-4 d-flex align-items-end gap-2">
      <button type="submit" class="btn btn-primary"><i class="bi bi-search"></i> Tampil</button>
      <a href="data_pestisida.php?view=dipakai" class="btn btn-secondary"><i class="bi bi-arrow-repeat"></i> Reset</a>
    </div>
  </form>

  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>Nama Customer</th>
        <th>Tanggal</th>
        <th>Nama Teknisi</th>
        <th>Nama Pestisida</th>
        <th>Jumlah Botol/Pcs</th>
        <th>Sisa Aktif (ml)</th>
        <th>Tanggal Update</th>
        <th width="150">Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $query = mysqli_query($koneksi, "
      SELECT pk.*, p.nama_pestisida, p.sisa_ml 
      FROM pakai_pestisida pk
      JOIN pestisida p ON p.id_pestisida = pk.id_pestisida
      ORDER BY pk.nama_customer ASC, pk.tanggal DESC, pk.id_pakai DESC
    ");

    
    if (mysqli_num_rows($query) > 0) {
        $last_customer = "";
        while ($row = mysqli_fetch_assoc($query)) {
    ?>
    <tr>
      <?php if ($row['nama_customer'] !== $last_customer): ?>
        <td rowspan="1" class="align-middle fw-bold"><?= htmlspecialchars($row['nama_customer']) ?></td>
        <?php $last_customer = $row['nama_customer']; ?>
      <?php else: ?>
        <td></td>
      <?php endif; ?>
    
      <td class="text-center align-middle"><?= htmlspecialchars($row['tanggal']) ?></td>
      <td><?= htmlspecialchars($row['nama_teknisi']) ?></td>
      <td><?= htmlspecialchars($row['nama_pestisida']) ?></td>
      <td class="text-center"><?= htmlspecialchars($row['jumlah_pakai']) ?></td>
      <td class="text-center"><?= htmlspecialchars($row['sisa_aktif_ml'] ?? $row['sisa_ml'] ?? '-') ?></td>
      <td class="text-center"><?= htmlspecialchars($row['tanggal_update'] ?? '-') ?></td>
      <td class="text-center">
        <div class="d-flex justify-content-center gap-2 mb-2">
          <a href="edit_pakai.php?id=<?= $row['id_pakai'] ?>" class="btn btn-warning btn-sm px-3 py-1">✏️ Edit</a>
          <a href="hapus_pakai.php?id=<?= $row['id_pakai'] ?>" class="btn btn-danger btn-sm px-3 py-1"
             onclick="return confirm('Yakin ingin menghapus data ini?')">🗑️ Hapus</a>
        </div>
    
        <div class="btn-group">
          <button type="button" class="btn btn-success btn-sm dropdown-toggle px-3 py-1" data-bs-toggle="dropdown">
            🔥 Pakai
          </button>
          <ul class="dropdown-menu p-3 shadow" style="width: 220px;">
          <form action="pakai_pestisida_aksi.php" method="POST">
          <input type="hidden" name="id_pakai" value="<?= $row['id_pakai'] ?>">
          <input type="hidden" name="id_pestisida" value="<?= $row['id_pestisida'] ?>">
          <input type="hidden" name="nama_pestisida" value="<?= htmlspecialchars($row['nama_pestisida']) ?>">
          <div class="mb-2">
            <label class="form-label small mb-1">Jumlah (ml):</label>
            <input type="number" class="form-control form-control-sm" name="jumlah_pakai_ml" min="1" placeholder="contoh: 500">
          </div>
          <button type="submit" name="pakai_ml" class="btn btn-primary btn-sm w-100 mb-1">Pakai ML</button>
        </form>

        <form action="pakai_pestisida_aksi.php" method="POST">
          <input type="hidden" name="id_pakai" value="<?= $row['id_pakai'] ?>">
          <input type="hidden" name="id_pestisida" value="<?= $row['id_pestisida'] ?>">
          <input type="hidden" name="nama_pestisida" value="<?= htmlspecialchars($row['nama_pestisida']) ?>">
          <div class="mb-2">
            <label class="form-label small mb-1">Jumlah Botol:</label>
            <input type="number" class="form-control form-control-sm" name="jumlah_pakai_botol" min="1" placeholder="contoh: 1">
          </div>
          <button type="submit" name="pakai_botol" class="btn btn-success btn-sm w-100">Pakai Botol/Pcs</button>
        </form>
          </ul>
        </div>
      </td>
    </tr>
    <?php
        }
    } else {
        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada data pemakaian pestisida.</td></tr>";
    }
    ?>

    </tbody>
  </table>

</div>
<?php endif; ?>

    </div>
  </div>

  <a href="index.php" class="btn btn-secondary btn-sm mt-3"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function toggleCustomerInput() {
  const type = document.getElementById('customerSelect').value;
  document.getElementById('customerOld').style.display = type === 'lama' ? 'block' : 'none';
  document.getElementById('customerNew').style.display = type === 'baru' ? 'block' : 'none';
}

function tambahBaris() {
  const tbody = document.getElementById('pestisidaRows');
  const clone = tbody.rows[0].cloneNode(true);
  clone.querySelectorAll('input').forEach(el => el.value = '');
  tbody.appendChild(clone);
}

function hapusBaris(btn) {
  const tbody = document.getElementById('pestisidaRows');
  if (tbody.rows.length > 1) btn.closest('tr').remove();
}
</script>

<script>
function updateSisaAktif(select) {
  const selectedOption = select.options[select.selectedIndex];
  const sisaValue = selectedOption.getAttribute('data-sisa') || 0;
  const row = select.closest('tr');
  const sisaInput = row.querySelector('.sisa-input');
  sisaInput.value = sisaValue;
}
</script>

<script>
const pestisidasData = <?php echo json_encode($pestisidas_arr, JSON_HEX_TAG|JSON_HEX_APOS|JSON_HEX_QUOT); ?>;

function tambahBaris() {
  const tbody = document.getElementById('pestisidaRows');
  const tr = document.createElement('tr');

  // Pilihan pestisida
  const td1 = document.createElement('td');
  const sel = document.createElement('select');
  sel.name = "id_pestisida[]";
  sel.className = "form-select select-pestisida";
  sel.required = true;

  let opt = document.createElement('option');
  opt.value = "";
  opt.text = "-- Pilih Pestisida --";
  sel.appendChild(opt);

  pestisidasData.forEach(p => {
    let o = document.createElement('option');
    o.value = p.id_pestisida;
    o.text = p.nama_pestisida;
    sel.appendChild(o);
  });
  td1.appendChild(sel);

  // Jumlah pakai (botol)
  const td2 = document.createElement('td');
  td2.innerHTML = '<input type="number" name="jumlah_pakai[]" class="form-control" min="1" required>';

  // Keterangan
  const td3 = document.createElement('td');
  td3.innerHTML = '<input type="text" name="keterangan[]" class="form-control" placeholder="Opsional">';

  // Aksi hapus
  const td4 = document.createElement('td');
  td4.className = 'text-center';
  td4.innerHTML = '<button type="button" class="btn btn-danger btn-sm" onclick="hapusBaris(this)">🗑️</button>';

  tr.appendChild(td1);
  tr.appendChild(td2);
  tr.appendChild(td3);
  tr.appendChild(td4);

  tbody.appendChild(tr);
}
</script>

<script>
document.querySelectorAll('.btn-plus').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = this.parentElement.querySelector('input[type=number]');
    input.value = parseInt(input.value || 0) + 1;
  });
});
document.querySelectorAll('.btn-minus').forEach(btn => {
  btn.addEventListener('click', function() {
    const input = this.parentElement.querySelector('input[type=number]');
    const current = parseInt(input.value || 0);
    if (current > 1) input.value = current - 1;
  });
});
</script>

<script>
document.querySelectorAll("form button[name='pakai_ml']").forEach(btn => {
  btn.addEventListener("click", function(e) {
    const tipe = this.closest("ul").querySelector("input[name='nama_pestisida']").value.toLowerCase();
    
    // Cek dari database tipe (chemical/pestisida = boleh ml)
    const id = this.closest("ul").querySelector("input[name='id_pestisida']").value;

    // AJAX cepat ambil tipe
    fetch("get_tipe.php?id=" + id)
        .then(r => r.json())
        .then(res => {
            if (res.tipe != "chemical" && res.tipe != "pestisida") {
                alert("Produk non-chemical tidak boleh dipakai ML");
                e.preventDefault();
            }
        });
  });
});
</script>


</body>
</html>
