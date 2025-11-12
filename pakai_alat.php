<?php
include "koneksi.php";

$id_alat = isset($_GET['id_alat']) ? (int)$_GET['id_alat'] : 0;

// ambil data alat
$q = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id_alat'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data alat tidak ditemukan!'); window.location='alat.php';</script>";
    exit;
}
$alat = mysqli_fetch_assoc($q);

if (isset($_POST['simpan'])) {
    $teknisi = mysqli_real_escape_string($koneksi, $_POST['teknisi']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // masukkan ke riwayat
        mysqli_query($koneksi, "
        INSERT INTO riwayat_alat (id_alat, aktivitas, nama_teknisi, keterangan, status, tanggal_mulai)
        VALUES ('$id_alat', 'pakai_alat', '$teknisi', '$ket', 'dipakai', CURDATE())
    ");

    // update status alat (anggap dipakai = 1)
    mysqli_query($koneksi, "
        UPDATE alat 
        SET jumlah_pakai = jumlah_pakai + 1, jumlah = jumlah - 1, tanggal_update = CURDATE()
        WHERE id_alat = '$id_alat'
    ");

    echo "<script>alert('✅ Alat berhasil ditandai sedang dipakai.'); window.location='alat.php?view=dipakai';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Pakai Alat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<div class="card shadow">
  <div class="card-header bg-primary text-white fw-bold">
    🚀 Form Pakai Alat
  </div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Kode Alat</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($alat['kode_alat']) ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Alat</label>
        <input type="text" class="form-control" value="<?= htmlspecialchars($alat['nama_alat']) ?>" readonly>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Teknisi</label>
        <input type="text" name="teknisi" class="form-control" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2"></textarea>
      </div>

      <button type="submit" name="simpan" class="btn btn-success">✅ Simpan</button>
      <a href="alat.php" class="btn btn-secondary">⬅ Kembali</a>
    </form>
  </div>
</div>

</body>
</html>
