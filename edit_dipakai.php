<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data riwayat
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat='$id' AND status='dipakai'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data tidak ditemukan atau bukan status dipakai.'); window.location='alat.php?view=dipakai';</script>";
    exit;
}
$data = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
    $id_alat_baru = (int)$_POST['id_alat'];
    $teknisi = mysqli_real_escape_string($koneksi, $_POST['teknisi']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // Jika alat diganti, perbaiki stok dua alat
    if ($id_alat_baru != $data['id_alat']) {
        // kembalikan stok alat lama
        mysqli_query($koneksi, "
            UPDATE alat 
            SET jumlah = jumlah + 1, jumlah_pakai = GREATEST(jumlah_pakai - 1, 0)
            WHERE id_alat = '{$data['id_alat']}'
        ");

        // kurangi stok alat baru
        mysqli_query($koneksi, "
            UPDATE alat 
            SET jumlah = jumlah - 1, jumlah_pakai = jumlah_pakai + 1
            WHERE id_alat = '$id_alat_baru'
        ");
    }

    // update data riwayat
    mysqli_query($koneksi, "
        UPDATE riwayat_alat 
        SET id_alat = '$id_alat_baru',
            nama_teknisi = '$teknisi',
            keterangan = '$ket'
        WHERE id_riwayat = '$id'
    ");

    echo "<script>alert('✅ Data pemakaian berhasil diperbarui.'); window.location='alat.php?view=dipakai';</script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Alat Dipakai</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">

<div class="card shadow">
  <div class="card-header bg-warning text-dark fw-bold">
    ✏️ Edit Data Pemakaian Alat
  </div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Pilih Alat</label>
        <select name="id_alat" class="form-select" required>
          <?php
          $alat_q = mysqli_query($koneksi, "SELECT id_alat, kode_alat, nama_alat FROM alat ORDER BY kode_alat ASC");
          while ($alat = mysqli_fetch_assoc($alat_q)) {
              $sel = ($alat['id_alat'] == $data['id_alat']) ? 'selected' : '';
              echo "<option value='{$alat['id_alat']}' $sel>{$alat['kode_alat']} - {$alat['nama_alat']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Nama Teknisi</label>
        <input type="text" name="teknisi" class="form-control" value="<?= htmlspecialchars($data['nama_teknisi']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control"><?= htmlspecialchars($data['keterangan']) ?></textarea>
      </div>

      <button type="submit" name="update" class="btn btn-primary">💾 Simpan Perubahan</button>
      <a href="alat.php?view=dipakai" class="btn btn-secondary">⬅ Kembali</a>
    </form>
  </div>
</div>

</body>
</html>
