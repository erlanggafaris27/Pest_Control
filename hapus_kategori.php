<?php
include "koneksi.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Cek apakah kategori dipakai di tabel pestisida
    $cek = mysqli_query($koneksi, "SELECT COUNT(*) as jumlah FROM pestisida WHERE kategori_id='$id'");
    $data = mysqli_fetch_assoc($cek);

    if ($data['jumlah'] > 0) {
        echo "<script>alert('Kategori tidak bisa dihapus karena masih ada pestisida yang menggunakannya!');
              window.location='data_pestisida.php';</script>";
    } else {
        mysqli_query($koneksi, "DELETE FROM kategori_pestisida WHERE id_kategori='$id'");
        echo "<script>alert('Kategori berhasil dihapus!');
              window.location='data_pestisida.php';</script>";
    }
}
?>
