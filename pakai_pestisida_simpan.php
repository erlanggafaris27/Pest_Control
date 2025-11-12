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
    $keterangan_arr = $_POST['keterangan'] ?? [];

    if (empty($id_pestisida_arr)) {
        echo "<script>alert('Tidak ada data pestisida yang dimasukkan.'); window.history.back();</script>";
        exit;
    }

    for ($i = 0; $i < count($id_pestisida_arr); $i++) {
        $id_pestisida = (int)$id_pestisida_arr[$i];
        $jumlah_pakai = (float)$jumlah_pakai_arr[$i];
        $keterangan = mysqli_real_escape_string($koneksi, $keterangan_arr[$i] ?? '');

        // 🔹 Ambil data pestisida dari tabel utama
        $q = mysqli_query($koneksi, "SELECT * FROM pestisida WHERE id_pestisida='$id_pestisida'");
        $p = mysqli_fetch_assoc($q);
        if (!$p) continue;

        $nama_pestisida = mysqli_real_escape_string($koneksi, $p['nama_pestisida']);
        $tipe = strtolower($p['tipe']);
        $isi_per_botol = (float)$p['isi_per_botol_ml'];

        $total_ml_dialokasikan = $jumlah_pakai * $isi_per_botol; // total ml yang diberikan ke customer

        // Kurangi stok utama
        $jumlah_baru = max($p['jumlah_botol'] - $jumlah_pakai, 0);
        $sisa_ml_baru = max($p['sisa_ml'] - $total_ml_dialokasikan, 0);

        mysqli_query($koneksi, "
            UPDATE pestisida 
            SET jumlah_botol = '$jumlah_baru',
                sisa_ml = '$sisa_ml_baru',
                tanggal_update = CURDATE()
            WHERE id_pestisida = '$id_pestisida'
        ");

        // setelah update pestisida (stok)
$total_ml_dialokasikan = $jumlah_pakai * $isi_per_botol; // ml yang dialokasikan ke customer

        // ============================================================
        // 🔧 Tambahkan data ke tabel pemakaian
        // ============================================================
        // insert ke pakai_pestisida (simpan sisa_aktif_ml = total_ml_dialokasikan)
       // insert ke pakai_pestisida (simpan sisa_aktif_ml = total_ml_dialokasikan)
        mysqli_query($koneksi, "
        INSERT INTO pakai_pestisida 
        (id_pestisida, nama_customer, nama_teknisi, tanggal, jumlah_pakai, satuan, sisa_aktif_ml, keterangan)
        VALUES 
        ('$id_pestisida', '$nama_customer', '$nama_teknisi', '$tanggal', '$jumlah_pakai', 'botol', '$total_ml_dialokasikan', '$keterangan')
        ") or die('Gagal insert pakai_pestisida: ' . mysqli_error($koneksi));

        // ============================================================
        // 🔧 Catat ke riwayat
        // ============================================================
        $satuan_for_history = ($tipe === 'chemical' || $tipe === 'pestisida') ? 'botol' : 'pcs';
        $insert_riwayat = "
        INSERT INTO riwayat_pestisida 
        (id_pestisida, jenis, aktivitas, nama_teknisi, nama_customer, jumlah, satuan, tanggal, keterangan, status, sisa_aktif_ml)
        VALUES 
        ('$id_pestisida', '$tipe', 'alokasi', '$nama_teknisi', '$nama_customer', '$jumlah_pakai', 'botol', '$tanggal',
        CONCAT('Alokasi ', '$jumlah_pakai', ' botol ', '$nama_pestisida', ' untuk ', '$nama_customer', ' oleh ', '$nama_teknisi'),
        'alokasi', '$total_ml_dialokasikan')
    ";
    
    mysqli_query($koneksi, $insert_riwayat) or die('Gagal insert riwayat: ' . mysqli_error($koneksi));    
    } // 🔸 ini penutup loop for

    // 🔹 Setelah semua data diproses, baru redirect
    echo "<script>
        alert('✅ Alokasi pestisida berhasil disimpan dan tercatat di riwayat.');
        window.location='data_pestisida.php?view=dipakai';
    </script>";
    exit;
} // 🔸 ini penutup if
?>
