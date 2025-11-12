<?php
include "koneksi.php";

$id = (int)$_GET['id'];
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat='$id' AND status='perbaikan'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data tidak ditemukan atau sudah selesai.'); window.location='alat.php?view=perbaikan';</script>";
    exit;
}
$data = mysqli_fetch_assoc($q);
$id_alat = $data['id_alat'];

// hapus riwayat
mysqli_query($koneksi, "DELETE FROM riwayat_alat WHERE id_riwayat='$id'");

// kembalikan stok
mysqli_query($koneksi, "
    UPDATE alat 
    SET jumlah = jumlah + 1, jumlah_perbaikan = GREATEST(jumlah_perbaikan - 1, 0)
    WHERE id_alat='$id_alat'
");

echo "<script>alert('🗑 Data perbaikan dihapus dan alat dikembalikan ke stok utama.'); window.location='alat.php?view=utama';</script>";
?>
