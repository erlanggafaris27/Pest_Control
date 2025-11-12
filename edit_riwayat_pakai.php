<?php
include "koneksi.php";

if (!isset($_GET['id_pakai'])) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='riwayat_pakai.php';</script>";
    exit;
}

$id_pakai = (int) $_GET['id_pakai'];

// Ambil data berdasarkan ID
$q = mysqli_query($koneksi, "
    SELECT p.*, a.nama_alat 
    FROM pakai_alat p
    JOIN alat a ON p.id_alat = a.id_alat
    WHERE p.id_pakai = $id_pakai
    LIMIT 1
");

if (!$q || mysqli_num_rows($q) == 0) {
    echo "<script>alert('Data tidak ditemukan!'); window.location='riwayat_pakai.php';</script>";
    exit;
}

$edit = mysqli_fetch_assoc($q);

// Jika tombol update ditekan
if (isset($_POST['update'])) {
    $tanggal     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $jumlah_baru = (int) $_POST['jumlah'];
    $keterangan  = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $nama_teknisi = mysqli_real_escape_string($koneksi, $_POST['nama_teknisi']);

    mysqli_query($koneksi, "
        UPDATE pakai_alat 
        SET tanggal = '$tanggal', 
            jumlah_pakai = $jumlah_baru, 
            nama_teknisi = '$nama_teknisi',
            keterangan = '$keterangan'
        WHERE id_pakai = $id_pakai
    ");

    echo "<script>alert('✅ Data riwayat berhasil diperbarui!'); window.location='riwayat_pakai.php';</script>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Riwayat Pemakaian Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-5">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">✏ Edit Riwayat Pemakaian Alat</h5>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label">Nama Alat</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($edit['nama_alat'] ?? '') ?>" readonly>
                </div>

                <div class="mb-3">
                    <label class="form-label">Tanggal</label>
                    <input type="date" name="tanggal" value="<?= htmlspecialchars($edit['tanggal'] ?? '') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Nama Teknisi</label>
                    <input type="text" name="nama_teknisi" value="<?= htmlspecialchars($edit['nama_teknisi'] ?? '') ?>" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Jumlah</label>
                    <input type="number" name="jumlah" value="<?= intval($edit['jumlah_pakai'] ?? 1) ?>" class="form-control" min="1" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Keterangan</label>
                    <input type="text" name="keterangan" value="<?= htmlspecialchars($edit['keterangan'] ?? '') ?>" class="form-control">
                </div>

                <div class="d-flex justify-content-between">
                    <a href="riwayat_pakai.php" class="btn btn-secondary">⬅ Kembali</a>
                    <button type="submit" name="update" class="btn btn-success">💾 Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
