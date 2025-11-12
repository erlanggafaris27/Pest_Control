<?php
include "koneksi.php";

$id = (int)$_GET['id'];

// 🔹 Ambil data pestisida
$q = mysqli_query($koneksi, "SELECT * FROM pestisida WHERE id_pestisida='$id'");
$data = mysqli_fetch_assoc($q);

if ($data) {
    $nama   = mysqli_real_escape_string($koneksi, $data['nama_pestisida']);
    $jumlah = (float)$data['jumlah_botol'];
    $sisa   = (float)$data['sisa_ml'];
    $jenis  = mysqli_real_escape_string($koneksi, $data['tipe']); // 🔹 tambahkan jenis

    // ✅ 1️⃣ Nonaktifkan foreign key constraint sementara
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=0");

    // ✅ 2️⃣ Catat dulu ke tabel riwayat
    $insert = "
        INSERT INTO riwayat_pestisida
        (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
        VALUES
        (
            '$id',
            '$jenis',
            'hapus_stok',
            '-',
            '-',
            '$jumlah',
            'botol',
            CURDATE(),
            'Data pestisida $nama dihapus dari master stok dengan sisa aktif $sisa ml.',
            'stok dihapus',
            '$sisa'
        )
    ";
    mysqli_query($koneksi, $insert) or die('❌ Gagal mencatat riwayat: ' . mysqli_error($koneksi));

    // ✅ 3️⃣ Hapus semua pemakaian terkait agar FK aman
    mysqli_query($koneksi, "DELETE FROM pakai_pestisida WHERE id_pestisida='$id'") or die('Gagal hapus pakai: '.mysqli_error($koneksi));

    // ✅ 4️⃣ Hapus dari tabel utama
    mysqli_query($koneksi, "DELETE FROM pestisida WHERE id_pestisida='$id'") or die('Gagal hapus pestisida: '.mysqli_error($koneksi));

    // ✅ 5️⃣ Aktifkan kembali foreign key constraint
    mysqli_query($koneksi, "SET FOREIGN_KEY_CHECKS=1");

    echo "<script>
        alert('✅ Data pestisida berhasil dihapus dan tercatat di riwayat dengan status stok dihapus.');
        window.location='data_pestisida.php';
    </script>";
} else {
    echo "<script>
        alert('❌ Data pestisida tidak ditemukan.');
        window.location='data_pestisida.php';
    </script>";
}
?>
