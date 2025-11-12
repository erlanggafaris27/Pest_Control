<?php
include "koneksi.php";

if (isset($_POST['simpan'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pestisida']);
  $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
  $jumlah = (int)$_POST['jumlah_botol'];
  $isi = ($tipe === 'non_chemical') ? 0 : (int)$_POST['isi_per_botol_ml'];

  // Hitung total sisa aktif dalam ml
  $sisa = ($tipe === 'non_chemical') ? 0 : ($jumlah * $isi);

  // Cegah duplikasi nama pestisida
  $cek = mysqli_query($koneksi, "SELECT id_pestisida FROM pestisida WHERE nama_pestisida='$nama'");
  if (mysqli_num_rows($cek) > 0) {
    echo "<script>alert('⚠️ Nama pestisida sudah ada!'); window.history.back();</script>";
    exit;
  }
  // Simpan ke tabel utama pestisida
  $sql = "
    INSERT INTO pestisida (nama_pestisida, tipe, jumlah_botol, isi_per_botol_ml, sisa_ml, tanggal, tanggal_update)
    VALUES ('$nama', '$tipe', $jumlah, $isi, $sisa, CURDATE(), CURDATE())
  ";

  if (mysqli_query($koneksi, $sql)) {
    // Ambil id pestisida terakhir
    $last_id = mysqli_insert_id($koneksi);

    // Siapkan keterangan untuk riwayat
    $keterangan = "Menambahkan pestisida baru $nama sebanyak $jumlah botol/pcs" .
              (($isi > 0) ? " dengan isi $isi ml per botol." : ".");

   // Catat ke tabel riwayat_pestisida
   // Siapkan keterangan deskriptif untuk riwayat
$keterangan_riwayat = "Menambahkan pestisida baru $nama sebanyak $jumlah botol/pcs" .
(($isi > 0) ? " dengan isi $isi ml per botol." : ".");

// Catat ke tabel riwayat_pestisida
$insert_riwayat = "
INSERT INTO riwayat_pestisida
(id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
VALUES
('$last_id', '$tipe', 'tambah_stok', '-', '-', '$jumlah', 'botol', CURDATE(),
'$keterangan_riwayat', 'stok ditambah', '$sisa')
";
mysqli_query($koneksi, $insert_riwayat) or die('Gagal insert riwayat (pestisida baru): ' . mysqli_error($koneksi));

// Update tanggal_update untuk pencatatan terakhir
mysqli_query($koneksi, "UPDATE pestisida SET tanggal_update = CURDATE() WHERE id_pestisida = '$last_id'");

    echo "<script>
      alert('✅ Pestisida baru berhasil ditambahkan dan tercatat di riwayat.');
      window.location='data_pestisida.php';
    </script>";
  } else {
    echo "<script>alert('❌ Gagal menambah data pestisida!');</script>";
  }
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Tambah Pestisida</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <script>
    // Sembunyikan kolom Chemical jika pilih Non-Chemical
    function toggleFields() {
      const tipe = document.getElementById('tipe').value;
      const chemicalFields = document.getElementById('chemicalFields');
      if (tipe === 'pestisida') {
        chemicalFields.style.display = 'block';
      } else {
        chemicalFields.style.display = 'none';
        document.querySelector('input[name="isi_per_botol_ml"]').value = 0;
      }
    }
  </script>
</head>

<body class="container mt-5">

<div class="card shadow col-md-6 offset-md-3">
  <div class="card-header bg-success text-white fw-bold">➕ Tambah Pestisida Baru</div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Nama Pestisida</label>
        <input type="text" name="nama_pestisida" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Tipe</label>
        <select name="tipe" id="tipe" class="form-select" onchange="toggleFields()" required>
          <option value="">-- Pilih Tipe --</option>
          <option value="pestisida">Chemical (Pestisida)</option>
          <option value="non chemical">Non-Chemical</option>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Jumlah Botol / Pcs</label>
        <input type="number" name="jumlah_botol" class="form-control" min="1" required>
      </div>

      <!-- Kolom khusus Chemical -->
      <div id="chemicalFields" style="display:none;">
        <div class="mb-3">
          <label class="form-label">Isi per Botol (ml)</label>
          <input type="number" name="isi_per_botol_ml" class="form-control" min="0" value="0">
        </div>
      </div>

      <button type="submit" name="simpan" class="btn btn-success">💾 Simpan</button>
      <a href="data_pestisida.php" class="btn btn-secondary">⬅ Kembali</a>
    </form>
  </div>
</div>

</body>
</html>
