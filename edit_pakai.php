<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Pastikan ada ID
if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}
$id_pakai = (int)$_GET['id'];

// Ambil data pemakaian dan pestisida terkait
$q = mysqli_query($koneksi, "
    SELECT pk.*, p.nama_pestisida, p.tipe, p.jumlah_botol, p.sisa_ml, p.isi_per_botol_ml 
    FROM pakai_pestisida pk
    JOIN pestisida p ON p.id_pestisida = pk.id_pestisida
    WHERE pk.id_pakai = '$id_pakai'
");
$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("Data tidak ditemukan!");
}
$nama_pestisida = mysqli_real_escape_string($koneksi, $data['nama_pestisida']);
$nama_teknisi = mysqli_real_escape_string($koneksi, $data['nama_teknisi']);
$nama_customer = mysqli_real_escape_string($koneksi, $data['nama_customer']);


// Simpan perubahan
if (isset($_POST['simpan'])) {
    $jumlah_baru = (float)$_POST['jumlah_pakai'];
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

    $id_pestisida = $data['id_pestisida'];
    $jumlah_lama = (float)$data['jumlah_pakai'];
    $isi_per_botol = (float)$data['isi_per_botol_ml'];
    $tipe = strtolower($data['tipe']);

    // Hitung sisa aktif baru berdasarkan perubahan jumlah
    $sisa_aktif_baru = ($tipe === 'pestisida' || $tipe === 'chemical') 
        ? $jumlah_baru * $isi_per_botol 
        : $jumlah_baru; // untuk non-chemical (pcs)

    // Hitung selisih ml dan botol
    $selisih_ml = 0;
    if ($tipe === 'pestisida' || $tipe === 'chemical') {
        $selisih_ml = ($jumlah_baru - $jumlah_lama) * $isi_per_botol;
    }

    $stok_botol = (float)$data['jumlah_botol'];
    $stok_sisa_ml = (float)$data['sisa_ml'];

    // 🔧 Jika jumlah baru lebih besar → stok berkurang
    if ($jumlah_baru > $jumlah_lama) {
        $selisih_botol = $jumlah_baru - $jumlah_lama;
        $stok_baru_botol = max($stok_botol - $selisih_botol, 0);
        $stok_baru_ml = max($stok_sisa_ml - $selisih_ml, 0);
    } 
    // 🔧 Jika jumlah baru lebih kecil → stok bertambah
    elseif ($jumlah_baru < $jumlah_lama) {
        $selisih_botol = $jumlah_lama - $jumlah_baru;
        $stok_baru_botol = $stok_botol + $selisih_botol;
        $stok_baru_ml = $stok_sisa_ml + abs($selisih_ml);
    } 
    else {
        $stok_baru_botol = $stok_botol;
        $stok_baru_ml = $stok_sisa_ml;
    }

    // 🔹 Update stok utama
    mysqli_query($koneksi, "
    UPDATE pestisida 
    SET jumlah_botol='$stok_baru_botol', sisa_ml='$stok_baru_ml', tanggal_update=NOW()
    WHERE id_pestisida='$id_pestisida'    
    ") or die("Gagal update stok: " . mysqli_error($koneksi));

    // 🔹 Update data pemakaian + perbarui sisa aktif
    mysqli_query($koneksi, "
        UPDATE pakai_pestisida 
        SET jumlah_pakai='$jumlah_baru', 
            sisa_aktif_ml='$sisa_aktif_baru', 
            tanggal='$tanggal', 
            keterangan='$keterangan'
        WHERE id_pakai='$id_pakai'
    ") or die("Gagal update data pemakaian: " . mysqli_error($koneksi));

    // 🔹 Catat ke riwayat
    $status = "edit_pakai";
    $satuan = 'botol';
    $keterangan_riwayat = "Data pemakaian pestisida {$data['nama_pestisida']} oleh {$data['nama_teknisi']} "
        . "untuk {$data['nama_customer']} diperbarui dari $jumlah_lama menjadi $jumlah_baru $satuan.";

    mysqli_query($koneksi, "
    INSERT INTO riwayat_pestisida 
    (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
    VALUES 
    ('$id_pestisida', '$tipe', 'edit_pakai', '{$data['nama_teknisi']}', '{$data['nama_customer']}', 
    '$jumlah_baru', '$satuan', NOW(), '$keterangan_riwayat', 'edit_pakai', '$sisa_aktif_baru')    
    ") or die("Gagal mencatat riwayat: " . mysqli_error($koneksi));

    echo "<script>
        alert('✅ Data pemakaian berhasil diperbarui dan stok serta sisa aktif disesuaikan.');
        window.location='data_pestisida.php?view=dipakai';
    </script>";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Edit Pemakaian Pestisida</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-warning text-dark">
      <h4 class="mb-0">✏️ Edit Pemakaian Pestisida</h4>
    </div>
    <div class="card-body">
      <form method="post">
        <div class="mb-3">
          <label class="form-label fw-bold">Nama Pestisida</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pestisida']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Nama Customer</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_customer']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Nama Teknisi</label>
          <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_teknisi']) ?>" readonly>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Jumlah Pakai (botol/ml)</label>
          <input type="number" name="jumlah_pakai" class="form-control" value="<?= $data['jumlah_pakai'] ?>" min="1" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Tanggal</label>
          <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
        </div>

        <div class="mb-3">
          <label class="form-label fw-bold">Keterangan</label>
          <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>
        </div>

        <button type="submit" name="simpan" class="btn btn-success">💾 Simpan Perubahan</button>
        <a href="data_pestisida.php?view=dipakai" class="btn btn-secondary">⬅ Kembali</a>
      </form>
    </div>
  </div>
</div>

</body>
</html>
