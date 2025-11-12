<?php
include "koneksi.php";

$id = $_GET['id'] ?? 0;
$q = mysqli_query($koneksi, "SELECT * FROM pestisida WHERE id_pestisida='$id'");
if (!$q || mysqli_num_rows($q) == 0) {
  echo "<script>alert('Data tidak ditemukan!'); window.location='data_pestisida.php';</script>";
  exit;
}
$data = mysqli_fetch_assoc($q);

if (isset($_POST['simpan'])) {
  $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pestisida']);
  $tipe = mysqli_real_escape_string($koneksi, $_POST['tipe']);
  $jumlah = (int)$_POST['jumlah_botol'];
  $isi = (int)$_POST['isi_per_botol_ml'];

  // 🔹 Hitung ulang sisa aktif hanya untuk chemical/pestisida
  if (strtolower($tipe) !== 'non chemical') {
      $sisa = $jumlah * $isi;
  } else {
      $sisa = 0;
  }

  // 🔹 Update data utama
  $update = mysqli_query($koneksi, "
    UPDATE pestisida 
    SET nama_pestisida='$nama', tipe='$tipe', jumlah_botol='$jumlah', 
        isi_per_botol_ml='$isi', sisa_ml='$sisa', tanggal_update=CURDATE()
    WHERE id_pestisida='$id'
  ");

  if ($update) {
      // 🔹 Catat ke tabel riwayat_pestisida
      $tipe_lower = strtolower($tipe);
      $jenis = ($tipe_lower === 'pestisida' || $tipe_lower === 'chemical') ? 'chemical' : 'non chemical';

      $keterangan = mysqli_real_escape_string($koneksi,
          "Data pestisida $nama telah diperbarui. Jumlah sekarang $jumlah botol/pcs" .
          (($isi > 0) ? " dengan isi $isi ml per botol (total aktif $sisa ml)." : ".")
      );

      mysqli_query($koneksi, "
        INSERT INTO riwayat_pestisida 
        (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
        VALUES 
        ('$id', '$jenis', 'update_data', '-', '-', '$jumlah', 'botol', CURDATE(),
         '$keterangan', 'Update Pestisida', '$sisa')
      ") or die('Gagal mencatat riwayat update: ' . mysqli_error($koneksi));

      echo "<script>alert('✅ Data berhasil diperbarui dan tercatat di riwayat.'); window.location='data_pestisida.php';</script>";
  } else {
      echo "<script>alert('❌ Gagal memperbarui data!');</script>";
  }
  exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Pestisida</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
<div class="card shadow">
  <div class="card-header bg-warning fw-bold">✏ Edit Data Pestisida</div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Nama Pestisida</label>
        <input type="text" name="nama_pestisida" value="<?= htmlspecialchars($data['nama_pestisida']) ?>" class="form-control" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Tipe</label>
        <select name="tipe" class="form-select" required>
          <option value="pestisida" <?= $data['tipe']=='pestisida'?'selected':'' ?>>Chemical</option>
          <option value="non chemical" <?= $data['tipe']=='non chemical'?'selected':'' ?>>Non Chemical</option>
        </select>
      </div>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Jumlah Botol / Pcs</label>
          <input type="number" name="jumlah_botol" value="<?= $data['jumlah_botol'] ?>" class="form-control" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Isi per Botol (ml)</label>
          <input type="number" name="isi_per_botol_ml" value="<?= $data['isi_per_botol_ml'] ?>" class="form-control">
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Sisa Aktif (ml)</label>
          <input type="number" name="sisa_ml" value="<?= $data['sisa_ml'] ?>" class="form-control" readonly>
        </div>
      </div>
      <button type="submit" name="simpan" class="btn btn-primary">💾 Simpan</button>
      <a href="data_pestisida.php" class="btn btn-secondary">⬅ Kembali</a>
    </form>
  </div>
</div>
</body>
</html>
