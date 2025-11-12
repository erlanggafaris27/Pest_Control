<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ambil data riwayat dipakai
$q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat = '$id'");
if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('❌ Data riwayat tidak ditemukan!'); window.location='alat.php?view=dipakai';</script>";
    exit;
}
$data = mysqli_fetch_assoc($q);
$id_alat = $data['id_alat'];
$teknisi = $data['nama_teknisi'] ?? 'Tidak Diketahui';
$keterangan = $data['keterangan'] ?? '-';

// ✅ Tambahkan log baru (tidak ubah status lama)
mysqli_query($koneksi, "
    INSERT INTO riwayat_alat (id_alat, nama_teknisi, keterangan, status, tanggal_mulai, tanggal_selesai)
    VALUES ('$id_alat', '$teknisi', 'Alat dikembalikan ke gudang. ($keterangan)', 'selesai', CURDATE(), CURDATE())
");

// ✅ Update stok alat
mysqli_query($koneksi, "
    UPDATE alat 
    SET jumlah = jumlah + 1, jumlah_pakai = GREATEST(jumlah_pakai - 1, 0)
    WHERE id_alat = '$id_alat'
");

echo "<script>alert('✅ Alat berhasil dikembalikan dan riwayat tersimpan.'); window.location='alat.php?view=utama';</script>";
exit;
?>
