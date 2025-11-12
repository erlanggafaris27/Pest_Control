<?php
include "koneksi.php";

if (!isset($_GET['id'])) {
    die("ID tidak ditemukan.");
}

$id = (int)$_GET['id'];

// Ambil data riwayat
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_pestisida WHERE id_riwayat='$id'");
if (!$q || mysqli_num_rows($q) == 0) {
    die("Data riwayat tidak ditemukan.");
}

$data = mysqli_fetch_assoc($q);
$id_pestisida = $data['id_pestisida'];
$jumlah = (int)$data['jumlah'];
$status = strtolower($data['status']);

// Kembalikan stok utama jika aktivitasnya pakai atau jatah customer
if (in_array($status, ['dipakai', 'pakai pestisida', 'jatah customer'])) {
    mysqli_query($koneksi, "
        UPDATE pestisida 
        SET jumlah_botol = jumlah_botol + $jumlah 
        WHERE id_pestisida = '$id_pestisida'
    ");
}

// Hapus data riwayat
$hapus = mysqli_query($koneksi, "DELETE FROM riwayat_pestisida WHERE id_riwayat='$id'");

if ($hapus) {
    echo "<script>alert('✅ Data riwayat berhasil dihapus dan stok disesuaikan.'); window.location='riwayat_pemakaian.php';</script>";
} else {
    echo "<script>alert('❌ Gagal menghapus data riwayat.'); window.location='riwayat_pemakaian.php';</script>";
}
?>
