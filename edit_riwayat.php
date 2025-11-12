<?php
include "koneksi.php";

if (!isset($_GET['id'])) {
    die("ID riwayat tidak ditemukan!");
}

$id = (int)$_GET['id'];
$q = mysqli_query($koneksi, "
    SELECT r.*, p.nama_pestisida, p.tipe 
    FROM riwayat_pestisida r 
    LEFT JOIN pestisida p ON r.id_pestisida = p.id_pestisida
    WHERE id_riwayat='$id'
");

if (!$q || mysqli_num_rows($q) == 0) {
    die("Data tidak ditemukan!");
}

$data = mysqli_fetch_assoc($q);

// Update data riwayat
if (isset($_POST['simpan'])) {
    $jumlah = (int)$_POST['jumlah'];
    $satuan = $_POST['satuan'];
    $keterangan = mysqli_real_escape_string($koneksi, $_POST['keterangan']);
    $status = mysqli_real_escape_string($koneksi, $_POST['status']);
    $nama_teknisi = mysqli_real_escape_string($koneksi, $_POST['nama_teknisi']);
    $nama_customer = mysqli_real_escape_string($koneksi, $_POST['nama_customer']);
    $tanggal = mysqli_real_escape_string($koneksi, $_POST['tanggal']);

    // Ambil jumlah lama untuk menyesuaikan stok
    $lama = (int)$data['jumlah'];
    $selisih = $jumlah - $lama;

    // Update stok utama
    if ($selisih != 0) {
        mysqli_query($koneksi, "
            UPDATE pestisida 
            SET jumlah_botol = jumlah_botol + ($selisih) 
            WHERE id_pestisida = '{$data['id_pestisida']}'
        ");
    }

    // Update tabel riwayat
    $update = mysqli_query($koneksi, "
        UPDATE riwayat_pestisida SET 
        jumlah = '$jumlah',
        satuan = '$satuan',
        tanggal = '$tanggal',
        nama_teknisi = '$nama_teknisi',
        nama_customer = '$nama_customer',
        keterangan = '$keterangan',
        status = '$status'
        WHERE id_riwayat='$id'
    ");

    if ($update) {
        echo "<script>alert('✅ Data riwayat berhasil diperbarui dan stok disesuaikan.'); window.location='riwayat_pemakaian.php';</script>";
    } else {
        echo "<script>alert('❌ Gagal memperbarui data!');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Riwayat Pestisida</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="card shadow">
        <div class="card-header bg-warning">
            <h4 class="mb-0">✏️ Edit Riwayat Pestisida</h4>
        </div>
        <div class="card-body">
            <form method="post">
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Pestisida</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($data['nama_pestisida']) ?>" readonly>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Tanggal</label>
                        <input type="date" name="tanggal" class="form-control" value="<?= $data['tanggal'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Nama Teknisi</label>
                        <input type="text" name="nama_teknisi" class="form-control" value="<?= htmlspecialchars($data['nama_teknisi']) ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Nama Customer</label>
                        <input type="text" name="nama_customer" class="form-control" value="<?= htmlspecialchars($data['nama_customer']) ?>">
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Jumlah</label>
                        <input type="number" name="jumlah" class="form-control" value="<?= $data['jumlah'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Satuan</label>
                        <select name="satuan" class="form-select">
                            <option value="ml" <?= ($data['satuan']=='ml'?'selected':'') ?>>ml</option>
                            <option value="botol" <?= ($data['satuan']=='botol'?'selected':'') ?>>botol</option>
                            <option value="pcs" <?= ($data['satuan']=='pcs'?'selected':'') ?>>pcs</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Status Aktivitas</label>
                        <select name="status" class="form-select">
                            <option value="Ditambahkan" <?= ($data['status']=='Ditambahkan'?'selected':'') ?>>Penambahan Stok</option>
                            <option value="Jatah Customer" <?= ($data['status']=='Jatah Customer'?'selected':'') ?>>Jatah Customer</option>
                            <option value="Dipakai" <?= ($data['status']=='Dipakai'?'selected':'') ?>>Pakai Lapangan</option>
                        </select>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan</label>
                    <textarea name="keterangan" class="form-control" rows="3"><?= htmlspecialchars($data['keterangan']) ?></textarea>
                </div>
                <button type="submit" name="simpan" class="btn btn-success">💾 Simpan Perubahan</button>
                <a href="riwayat_pemakaian.php" class="btn btn-secondary">⬅ Kembali</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
