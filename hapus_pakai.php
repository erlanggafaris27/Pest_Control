<?php
include "koneksi.php";

$id_pakai = (int)$_GET['id'];

// 🔹 Ambil data pemakaian
$q = mysqli_query($koneksi, "
    SELECT pk.*, p.nama_pestisida, p.jumlah_botol, p.sisa_ml, p.isi_per_botol_ml, p.tipe
    FROM pakai_pestisida pk
    JOIN pestisida p ON pk.id_pestisida = p.id_pestisida
    WHERE pk.id_pakai = '$id_pakai'
");
$data = mysqli_fetch_assoc($q);

if ($data) {
    $id_pestisida   = $data['id_pestisida'];
    $nama_pestisida = mysqli_real_escape_string($koneksi, $data['nama_pestisida']);
    $jumlah_dipakai = (float)$data['jumlah_pakai'];
    $sisa_aktif_ml  = (float)$data['sisa_aktif_ml'];
    $isi_per_botol  = (float)$data['isi_per_botol_ml'];
    $tipe           = strtolower($data['tipe']);
    $nama_customer  = mysqli_real_escape_string($koneksi, $data['nama_customer']);
    $nama_teknisi   = mysqli_real_escape_string($koneksi, $data['nama_teknisi']);

    // 1️⃣ Kembalikan stok ke tabel utama
    $jumlah_baru = $data['jumlah_botol'] + $jumlah_dipakai;

    if ($tipe === 'pestisida' || $tipe === 'chemical') {
        $sisa_baru = $data['sisa_ml'] + $sisa_aktif_ml;
    } else {
        $sisa_baru = $data['sisa_ml']; // non-chemical tidak dihitung per ml
    }

    mysqli_query($koneksi, "
        UPDATE pestisida 
        SET jumlah_botol = '$jumlah_baru',
            sisa_ml = '$sisa_baru',
            tanggal_update = NOW()
        WHERE id_pestisida = '$id_pestisida'
    ") or die('Gagal update stok: ' . mysqli_error($koneksi));

    // 2️⃣ Catat ke tabel riwayat — gunakan kolom `jenis` juga
    mysqli_query($koneksi, "
        INSERT INTO riwayat_pestisida
        (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
        VALUES
        (
            '$id_pestisida',
            '$tipe',
            'hapus_pakai',
            '$nama_teknisi',
            '$nama_customer',
            '$jumlah_dipakai',
            'botol',
            NOW(),
            'Data pemakaian pestisida $nama_pestisida oleh teknisi $nama_teknisi untuk customer $nama_customer dihapus. Stok sebanyak $jumlah_dipakai botol dikembalikan ke gudang.',
            'dihapus (stok kembali)',
            '$sisa_aktif_ml'
        )
    ") or die('Gagal mencatat riwayat hapus_pakai: ' . mysqli_error($koneksi));

    // 3️⃣ Hapus data pemakaian
    mysqli_query($koneksi, "DELETE FROM pakai_pestisida WHERE id_pakai='$id_pakai'")
        or die('Gagal menghapus pemakaian: ' . mysqli_error($koneksi));

    echo "<script>
        alert('✅ Data pemakaian pestisida berhasil dihapus dan stok dikembalikan.');
        window.location='data_pestisida.php?view=dipakai';
    </script>";
} else {
    echo "<script>
        alert('❌ Data pemakaian tidak ditemukan.');
        window.location='data_pestisida.php?view=dipakai';
    </script>";
}
?>
