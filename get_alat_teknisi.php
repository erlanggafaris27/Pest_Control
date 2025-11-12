<?php
include "koneksi.php";
header('Content-Type: application/json');

if (isset($_GET['teknisi'])) {
    $teknisi = mysqli_real_escape_string($koneksi, $_GET['teknisi']);
    $sql = mysqli_query($koneksi, "
        SELECT DISTINCT a.id_alat, a.kode_alat, a.nama_alat
        FROM pakai_alat p
        JOIN alat a ON p.id_alat = a.id_alat
        WHERE p.nama_teknisi = '$teknisi' AND p.tipe_riwayat = 'pakai'
    ");
} else {
    $sql = mysqli_query($koneksi, "SELECT id_alat, kode_alat, nama_alat FROM alat ORDER BY nama_alat ASC");
}

$data = [];
while ($row = mysqli_fetch_assoc($sql)) {
    $data[] = $row;
}
echo json_encode($data);
?>
