<?php
include "koneksi.php";

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header("Location: data_pestisida.php");
  exit;
}

$id = (int) ($_POST['id_pestisida'] ?? 0);
$jumlah = max(1, (int) ($_POST['jumlah'] ?? 1));

if ($id <= 0) {
  header("Location: data_pestisida.php");
  exit;
}

// ambil nama pestisida
$q = mysqli_query($koneksi, "SELECT nama_pestisida FROM pestisida WHERE id_pestisida=$id");
$data = mysqli_fetch_assoc($q);
$nama = $data['nama_pestisida'] ?? '';

// update stok
mysqli_query($koneksi, "
  UPDATE pestisida 
  SET jumlah_botol = jumlah_botol + $jumlah,
      tanggal_update = CURDATE()
  WHERE id_pestisida = $id
");

// simpan ke riwayat_pestisida
mysqli_query($koneksi, "
  INSERT INTO riwayat_pestisida (id_pestisida, tanggal, jumlah, keterangan, status)
  VALUES ($id, CURDATE(), $jumlah, 'Penambahan stok pestisida ($nama)', 'tambah_stok')
");

echo "<script>alert('✅ Stok pestisida berhasil diperbarui dan tercatat di riwayat.'); window.location='data_pestisida.php';</script>";
exit;
?>
