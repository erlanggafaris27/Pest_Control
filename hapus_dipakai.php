<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Ambil data riwayat
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat='$id' AND status='dipakai'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data pemakaian tidak ditemukan atau sudah dikembalikan.'); window.location='alat.php?view=dipakai';</script>";
    exit;
}
$data = mysqli_fetch_assoc($q);
$id_alat = $data['id_alat'];

// Hapus riwayat yang salah
mysqli_query($koneksi, "DELETE FROM riwayat_alat WHERE id_riwayat='$id'");

// Kembalikan stok alat (anggap pemakaian dibatalkan)
mysqli_query($koneksi, "
    UPDATE alat 
    SET jumlah = jumlah + 1, 
        jumlah_pakai = GREATEST(jumlah_pakai - 1, 0)
    WHERE id_alat = '$id_alat'
");

echo "<script>alert('✅ Data pemakaian berhasil dihapus dan alat dikembalikan ke stok utama.'); 
window.location='alat.php?view=utama';</script>";
exit;
?>
