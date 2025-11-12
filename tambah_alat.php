<?php 
include "koneksi.php";

// === Generate kode alat otomatis ===
$query = mysqli_query($koneksi, "SELECT MAX(kode_alat) as kode_terbesar FROM alat");
$data = mysqli_fetch_array($query);
$kodeAlat = $data['kode_terbesar'];

if ($kodeAlat) {
    $urutan = (int) substr($kodeAlat, 1, 3);
    $urutan++;
} else {
    $urutan = 1;
}

$huruf = "A";
$kodeBaru = $huruf . sprintf("%03s", $urutan);

// === Simpan data alat baru ===
if (isset($_POST['simpan'])) {
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);

    $cek = mysqli_query($koneksi, "SELECT * FROM alat WHERE kode_alat='$kode'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kode alat sudah digunakan, silakan gunakan kode lain.'); history.back();</script>";
        exit;
    }

    // Insert alat
    mysqli_query($koneksi, "INSERT INTO alat (kode_alat, nama_alat, jumlah) VALUES ('$kode', '$nama', 1)");

    // Ambil ID alat terakhir
    $id_alat = mysqli_insert_id($koneksi);
    if ($id_alat == 0) {
        die('❌ Gagal mendapatkan ID alat. Pastikan kolom id_alat AUTO_INCREMENT.');
    }

    // Catat ke stok_gudang (opsional)
    mysqli_query($koneksi, "INSERT INTO stok_gudang 
        (nama_barang, jenis, isi_botol_ml, jumlah_botol, terpakai_ml, tanggal) 
        VALUES ('$nama', 'alat', 0, 1, 0, CURDATE())");

    // Catat ke riwayat_alat
    $insertRiwayat = mysqli_query($koneksi, "
    INSERT INTO riwayat_alat (id_alat, aktivitas, nama_teknisi, keterangan, status, tanggal_mulai)
    VALUES ('$id_alat', 'tambah_alat', 'Admin', 'Penambahan alat baru', 'ditambahkan', CURDATE())    
    ");

    if (!$insertRiwayat) {
        die('❌ Gagal menambahkan ke riwayat: ' . mysqli_error($koneksi));
    }

    echo "<script>alert('✅ Alat baru berhasil ditambahkan dan tercatat di riwayat.'); window.location='alat.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Tambah Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<div class="card shadow">
    <div class="card-header bg-success text-white">
        <h4 class="mb-0">➕ Tambah Alat Baru</h4>
    </div>
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Kode Alat</label>
                <input type="text" name="kode" class="form-control" 
                       value="<?= $kodeBaru ?>" required>
                <small class="text-muted">Kode otomatis bisa diganti manual jika dibutuhkan.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Alat</label>
                <input type="text" name="nama" class="form-control" placeholder="Masukkan nama alat" required>
            </div>

            <button type="submit" name="simpan" class="btn btn-primary">💾 Simpan</button>
            <a href="alat.php" class="btn btn-secondary">⬅ Kembali</a>
        </form>
    </div>
</div>

</body>
</html>
