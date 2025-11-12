<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if (!isset($_POST['pakai_ml']) && !isset($_POST['pakai_botol'])) {
    die("Akses tidak valid.");
}

$id_pakai = (int)$_POST['id_pakai'];
$jumlah_ml_input = floatval($_POST['jumlah_pakai_ml'] ?? 0);
$jumlah_botol_input = intval($_POST['jumlah_pakai_botol'] ?? 0);

$q = mysqli_query($koneksi,"
    SELECT pk.*, p.nama_pestisida, p.isi_per_botol_ml, p.tipe
    FROM pakai_pestisida pk
    JOIN pestisida p ON p.id_pestisida = pk.id_pestisida
    WHERE pk.id_pakai = '$id_pakai' LIMIT 1
");

$data = mysqli_fetch_assoc($q);
if (!$data) {
    die("<script>alert('Data tidak ditemukan');history.back();</script>");
}

$id_pestisida   = $data['id_pestisida'];
$nama_pestisida = $data['nama_pestisida'];
$isi_botol      = floatval($data['isi_per_botol_ml']);
$sisa_ml        = floatval($data['sisa_aktif_ml']);
$jumlah_botol   = intval($data['jumlah_pakai']);
$nama_customer  = $data['nama_customer'];
$nama_teknisi   = $data['nama_teknisi'];
$tipe           = strtolower($data['tipe']);

$isChemical = ($tipe == "chemical" || $tipe == "pestisida");


// ========================
// CEK NON CHEMICAL TIDAK BOLEH ML
// ========================
if (!$isChemical && isset($_POST['pakai_ml'])) {
    echo "<script>alert('Barang NON-CHEMICAL tidak boleh memakai ML');history.back();</script>";
    exit;
}

// ========================
// PEMAKAIAN ML (BENAR)
// ========================
if (isset($_POST['pakai_ml']) && $jumlah_ml_input > 0) {

    $diambil_ml = $jumlah_ml_input;

    // Total ml sebelum dipakai
    $total_ml_sebelumnya = $sisa_ml;

   // Total ml sesudah dipakai
$total_ml_baru = max($sisa_ml - $diambil_ml, 0);

// Hitung botol sisa
$jumlah_baru = floor($total_ml_baru / $isi_botol);

// Hitung sisa ml sisa
$sisa_baru = $total_ml_baru;

    // Hitung botol terpakai (untuk RIWAYAT)
    $botol_terpakai_decimal = $diambil_ml / $isi_botol;
    $botol_terpakai_rounded = ceil($botol_terpakai_decimal);
    $botol_terpakai_tampil = number_format($botol_terpakai_decimal, 1);

   // Jika total ml tersisa kurang dari 1 botol → hapus baris
if ($total_ml_baru < $isi_botol) {
    mysqli_query($koneksi, "DELETE FROM pakai_pestisida WHERE id_pakai='$id_pakai'");
} else {
    mysqli_query($koneksi,"
        UPDATE pakai_pestisida
        SET jumlah_pakai='$jumlah_baru',
            sisa_aktif_ml='$sisa_baru',
            tanggal=CURDATE()
        WHERE id_pakai='$id_pakai'
    ");
}

    // Catat RIWAYAT: tampilkan nilai INPUT, BUKAN sisa tabel
    mysqli_query($koneksi,"
        INSERT INTO riwayat_pestisida
        (id_pestisida, aktivitas, nama_teknisi, nama_customer, jumlah, sisa_aktif_ml,
         satuan, tanggal, keterangan, status)
        VALUES
        ('$id_pestisida','pakai_pestisida','$nama_teknisi','$nama_customer',
         '$botol_terpakai_rounded', '$diambil_ml', 'ml', CURDATE(),
         'Pakai $diambil_ml ml ($botol_terpakai_tampil botol) $nama_pestisida oleh $nama_teknisi.',
         'Pakai Lapangan')
    ");

    echo "<script>alert('Pemakaian ML berhasil');window.location='data_pestisida.php?view=dipakai';</script>";
    exit;
}

// ========================
// PEMAKAIAN BOTOL
// ========================
if (isset($_POST['pakai_botol']) && $jumlah_botol_input > 0) {

    $diambil_botol = $jumlah_botol_input;
    $diambil_ml = $diambil_botol * $isi_botol;

    if ($isChemical) {
        $sisa_baru = max($sisa_ml - $diambil_ml, 0);
        $botol_baru = ($sisa_baru > 0) ? floor($sisa_baru / $isi_botol) : 0;
    } else {
        // Non chemical → hanya hitung botol
        $botol_baru = max($jumlah_botol - $diambil_botol, 0);
        $sisa_baru = 0;
    }

    // Update atau hapus baris jika habis
    if ($botol_baru <= 0 && $sisa_baru <= 0) {
        mysqli_query($koneksi,"DELETE FROM pakai_pestisida WHERE id_pakai='$id_pakai'");
    } else {
        mysqli_query($koneksi,"
            UPDATE pakai_pestisida SET 
                jumlah_pakai = '$botol_baru',
                sisa_aktif_ml = '$sisa_baru',
                tanggal = CURDATE()
            WHERE id_pakai='$id_pakai'
        ");
    }

    // Catat RIWAYAT — menampilkan INPUT (jumlah botol asli)
    mysqli_query($koneksi,"
        INSERT INTO riwayat_pestisida
        (id_pestisida, aktivitas, nama_teknisi, nama_customer, jumlah, sisa_aktif_ml,
         satuan, tanggal, keterangan, status)
        VALUES
        ('$id_pestisida','pakai_pestisida','$nama_teknisi','$nama_customer',
         '$diambil_botol','$diambil_ml','botol',CURDATE(),
         'Pakai $diambil_botol botol ($diambil_ml ml) $nama_pestisida oleh $nama_teknisi.',
         'Pakai Lapangan')
    ");

    echo "<script>alert('Pemakaian botol berhasil');window.location='data_pestisida.php?view=dipakai';</script>";
    exit;
}
?>
