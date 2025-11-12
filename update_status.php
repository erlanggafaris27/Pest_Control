<?php
include "koneksi.php";

$id = $_GET['id'];
$status = $_GET['status'];
$tanggal = date('Y-m-d');

// 🔹 Cek data alat
$q = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id'");
$alat = mysqli_fetch_assoc($q);
if (!$alat) {
    die("Data alat tidak ditemukan.");
}

// ==============================
// 🛠️ STATUS: MASUK PERBAIKAN
// ==============================
if ($status === 'perbaikan') {
    // Cek apakah alat sudah ada riwayat perbaikan aktif
    $cek = mysqli_query($koneksi, "
        SELECT * FROM riwayat_alat 
        WHERE id_alat='$id' AND status='perbaikan' 
        ORDER BY id_riwayat DESC LIMIT 1
    ");
    if (mysqli_num_rows($cek) == 0) {
        // Tambahkan data baru ke riwayat
        mysqli_query($koneksi, "
            INSERT INTO riwayat_alat 
            (id_alat, jenis, status, tanggal_mulai, lama_perbaikan, nama_teknisi, keterangan)
            VALUES 
            ('$id', 'perbaikan', 'perbaikan', '$tanggal', 0, '', 'Alat masuk perbaikan')
        ");
    }

    // Update status alat di tabel utama
    mysqli_query($koneksi, "UPDATE alat SET status='perbaikan' WHERE id_alat='$id'");

    $pesan = "🛠️ Alat berhasil ditandai masuk perbaikan.";
}

// ==============================
// ✅ STATUS: SELESAI PERBAIKAN
// ==============================
elseif ($status === 'selesai' || $status === 'aktif') {
    // Cari riwayat terakhir perbaikan aktif
    $r = mysqli_query($koneksi, "
        SELECT * FROM riwayat_alat 
        WHERE id_alat='$id' AND status='perbaikan'
        ORDER BY id_riwayat DESC LIMIT 1
    ");
    $riw = mysqli_fetch_assoc($r);

    if ($riw) {
        // Hitung lama perbaikan otomatis
        $tglMulai = new DateTime($riw['tanggal_mulai']);
        $tglSelesai = new DateTime($tanggal);
        $lama = $tglMulai->diff($tglSelesai)->days;

        // Update data lama perbaikan di baris yang lama (biar tanggal selesai masuk)
        mysqli_query($koneksi, "
            UPDATE riwayat_alat 
            SET tanggal_selesai='$tanggal', lama_perbaikan='$lama' 
            WHERE id_riwayat='{$riw['id_riwayat']}'
        ");

        // Tambahkan riwayat baru sebagai selesai_perbaikan (riwayat pelengkap)
        mysqli_query($koneksi, "
            INSERT INTO riwayat_alat 
            (id_alat, jenis, status, tanggal_mulai, tanggal_selesai, lama_perbaikan, nama_teknisi, keterangan)
            VALUES 
            ('$id', 'selesai_perbaikan', 'selesai_perbaikan', '{$riw['tanggal_mulai']}', '$tanggal', '$lama', '', 'Perbaikan selesai dan alat dikembalikan')
        ");
    }

    // Update status alat di tabel utama
    mysqli_query($koneksi, "UPDATE alat SET status='aktif' WHERE id_alat='$id'");

    $pesan = "✅ Perbaikan selesai dan alat telah dikembalikan ke stok utama.";
}

// ==============================
// ⚠️ STATUS TAK DIKENAL
// ==============================
else {
    $pesan = "❗ Status tidak dikenali.";
}

// ==============================
// 🔁 REDIRECT KE HALAMAN YANG SESUAI
// ==============================
if ($status === 'perbaikan') {
    header("Location: alat.php?view=perbaikan&msg=" . urlencode($pesan));
} else {
    header("Location: alat.php?view=utama&msg=" . urlencode($pesan));
}
exit;
?>
