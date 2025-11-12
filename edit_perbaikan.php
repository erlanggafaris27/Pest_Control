<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat='$id'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data tidak ditemukan.'); window.location='alat.php?view=perbaikan';</script>";
    exit;
}
$data = mysqli_fetch_assoc($q);

if (isset($_POST['update'])) {
    $id_alat_baru = (int)$_POST['id_alat'];
    $tgl_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
    $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    // 🔹 Tetap ambil tanggal selesai dari data lama
    $tanggal_selesai = $data['tanggal_selesai'];

    // 🔹 Hitung lama perbaikan (kalau tanggal_selesai sudah ada)
    $lama = 0;
    if (!empty($tgl_mulai) && !empty($tanggal_selesai)) {
        $tgl1 = new DateTime($tgl_mulai);
        $tgl2 = new DateTime($tanggal_selesai);
        $lama = $tgl1->diff($tgl2)->days;
    }

    // 🔹 Update data perbaikan dengan aman (tidak hapus riwayat)
    $sql = "
        UPDATE riwayat_alat 
        SET 
            id_alat = '$id_alat_baru',
            tanggal_mulai = '$tgl_mulai',
            keterangan = '$ket',
            lama_perbaikan = '$lama',
            status = 'perbaikan',
            jenis = 'perbaikan'
        WHERE id_riwayat = '$id'
    ";

    if (mysqli_query($koneksi, $sql)) {
        echo "<script>
            alert('✅ Data perbaikan berhasil diperbarui.');
            window.location='alat.php?view=perbaikan';
        </script>";
    } else {
        echo '<pre>❌ SQL Error: ' . mysqli_error($koneksi) . '</pre>';
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Perbaikan</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">
<div class="card shadow">
  <div class="card-header bg-warning fw-bold">✏️ Edit Data Perbaikan</div>
  <div class="card-body">
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Pilih Alat</label>
        <select name="id_alat" class="form-select" required>
          <?php
          $alat_q = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY kode_alat ASC");
          while ($a = mysqli_fetch_assoc($alat_q)) {
              $sel = ($a['id_alat'] == $data['id_alat']) ? 'selected' : '';
              echo "<option value='{$a['id_alat']}' $sel>{$a['kode_alat']} - {$a['nama_alat']}</option>";
          }
          ?>
        </select>
      </div>

      <div class="mb-3">
        <label class="form-label">Tanggal Mulai</label>
        <input type="date" name="tanggal_mulai" class="form-control" 
               value="<?= htmlspecialchars($data['tanggal_mulai']) ?>" required>
      </div>

      <div class="mb-3">
        <label class="form-label">Keterangan</label>
        <textarea name="keterangan" class="form-control" rows="2"><?= htmlspecialchars($data['keterangan']) ?></textarea>
      </div>

      <button type="submit" name="update" class="btn btn-primary">💾 Simpan</button>
      <a href="alat.php?view=perbaikan" class="btn btn-secondary">⬅ Kembali</a>
    </form>
  </div>
</div>
</body>
</html>
