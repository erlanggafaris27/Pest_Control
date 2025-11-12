<?php
include "koneksi.php";

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// 🔹 Cek apakah data alat ada
$cek = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id'");
if (!$cek || mysqli_num_rows($cek) == 0) {
    echo "<script>alert('❌ Data alat tidak ditemukan!'); window.location='alat.php';</script>";
    exit;
}

$alat = mysqli_fetch_assoc($cek);
$nama = htmlspecialchars($alat['nama_alat']);
$kode = htmlspecialchars($alat['kode_alat']);

// 🔹 Jika konfirmasi sudah diset (user klik "Ya")
if (isset($_GET['konfirmasi']) && $_GET['konfirmasi'] == 1) {
    // Catat ke riwayat sebelum dihapus
    mysqli_query($koneksi, "
    INSERT INTO riwayat_alat (id_alat, aktivitas, keterangan, status, tanggal_mulai)
    VALUES ('$id', 'hapus_alat', 'Data alat $kode - $nama dihapus dari sistem', 'hapus', CURDATE())    
    ");

    // Hapus relasi terkait alat
    mysqli_query($koneksi, "DELETE FROM pakai_alat WHERE id_alat='$id'");
    mysqli_query($koneksi, "DELETE FROM riwayat_alat WHERE id_alat='$id'");
    mysqli_query($koneksi, "DELETE FROM alat WHERE id_alat='$id'");

    echo "<script>alert('✅ Data alat dan seluruh riwayat berhasil dihapus.'); window.location='alat.php';</script>";
    exit;
}

// 🔹 Jika alat masih dipakai, beri warning konfirmasi
if (isset($alat['jumlah_pakai']) && $alat['jumlah_pakai'] > 0) {
    echo "
    <script>
        if (confirm('⚠️ Alat \"$nama\" (Kode: $kode) masih dipakai oleh teknisi sejumlah {$alat['jumlah_pakai']} unit.\\nApakah Anda yakin ingin menghapus alat ini beserta seluruh riwayat pemakaian dan perbaikan?')) {
            window.location = 'hapus_alat.php?id={$id}&konfirmasi=1';
        } else {
            window.location = 'alat.php';
        }
    </script>
    ";
    exit;
}

// 🔹 Jika alat tidak sedang dipakai, konfirmasi biasa
echo "
<script>
    if (confirm('Yakin ingin menghapus alat \"$nama\" (Kode: $kode)?\\nTindakan ini tidak dapat dibatalkan.')) {
        window.location = 'hapus_alat.php?id={$id}&konfirmasi=1';
    } else {
        window.location = 'alat.php';
    }
</script>
";
?>
