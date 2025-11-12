<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $nama_teknisi = mysqli_real_escape_string($koneksi, $_POST['nama_teknisi']);
    $customer_type = $_POST['customer_type'];
    $nama_customer = ($customer_type == 'baru')
        ? mysqli_real_escape_string($koneksi, $_POST['nama_customer_new'])
        : mysqli_real_escape_string($koneksi, $_POST['nama_customer_old']);

    $id_pestisida_arr = $_POST['id_pestisida'] ?? [];
    $jumlah_pakai_arr = $_POST['jumlah_pakai'] ?? [];
    $satuan_arr = $_POST['satuan'] ?? [];
    $keterangan_arr = $_POST['keterangan'] ?? [];

    if (empty($id_pestisida_arr)) {
        echo "<script>alert('Tidak ada data pestisida yang dimasukkan.'); window.history.back();</script>";
        exit;
    }

    foreach ($id_pestisida_arr as $i => $id_pestisida) {
        $id_pestisida = (int)$id_pestisida;
        $jumlah_pakai = (float)$jumlah_pakai_arr[$i];
        $satuan = mysqli_real_escape_string($koneksi, $satuan_arr[$i]);
        $keterangan = mysqli_real_escape_string($koneksi, $keterangan_arr[$i]);

        // Ambil data stok utama
        $stok = mysqli_fetch_assoc(mysqli_query($koneksi, "SELECT * FROM pestisida WHERE id_pestisida='$id_pestisida'"));
        if (!$stok) continue;

        $nama_pestisida = $stok['nama_pestisida'];
        $isi_per_botol = (float)$stok['isi_per_botol_ml'];
        $jumlah_botol = (int)$stok['jumlah_botol'];
        $sisa_ml = (float)$stok['sisa_ml'];

        // Hitung pengurangan stok utama
        if ($satuan == 'botol' || $satuan == 'pcs') {
            $total_ml = $jumlah_pakai * $isi_per_botol;
            $jumlah_baru = max($jumlah_botol - $jumlah_pakai, 0);
            $sisa_baru = max($sisa_ml - $total_ml, 0);
        } else { // satuan ml
            $jumlah_baru = $jumlah_botol;
            $sisa_baru = max($sisa_ml - $jumlah_pakai, 0);
        }

        mysqli_query($koneksi, "
            UPDATE pestisida 
            SET jumlah_botol = '$jumlah_baru',
                sisa_ml = '$sisa_baru',
                tanggal_update = CURDATE()
            WHERE id_pestisida = '$id_pestisida'
        ");

        // Hitung sisa aktif untuk pemakaian customer
        $sisa_aktif_ml = ($satuan == 'botol' || $satuan == 'pcs')
            ? $jumlah_pakai * $isi_per_botol
            : $jumlah_pakai;

        // Masukkan ke tabel pemakaian (jatah customer)
        mysqli_query($koneksi, "
            INSERT INTO pakai_pestisida 
            (id_pestisida, nama_customer, nama_teknisi, jumlah_pakai, sisa_aktif_ml, tanggal)
            VALUES 
            ('$id_pestisida', '$nama_customer', '$nama_teknisi', '$jumlah_pakai', '$sisa_aktif_ml', '$tanggal')
        ");

        // Catat ke riwayat
        mysqli_query($koneksi, "
            INSERT INTO riwayat_pestisida 
            (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status)
            VALUES
            ('$id_pestisida', 'chemical', 'alokasi', '$nama_teknisi', '$nama_customer', '$jumlah_pakai', '$satuan', '$tanggal',
            'Alokasi $jumlah_pakai $satuan pestisida $nama_pestisida untuk $nama_customer oleh $nama_teknisi', 'Dialokasikan')
        ");
    }

    echo "<script>alert('✅ Data pemakaian pestisida berhasil disimpan dan stok utama berkurang.'); 
          window.location='data_pestisida.php?view=dipakai';</script>";
}
?>
