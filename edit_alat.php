<?php
include "koneksi.php";

$id = $_GET['id'];
$sql = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat='$id'");
$data = mysqli_fetch_assoc($sql);

if (isset($_POST['update'])) {
    $kode = trim($_POST['kode']);
    $nama = trim($_POST['nama']);

    // Cek apakah kode sudah dipakai alat lain
    $cek = mysqli_query($koneksi, "SELECT * FROM alat WHERE kode_alat='$kode' AND id_alat!='$id'");
    if (mysqli_num_rows($cek) > 0) {
        echo "<script>alert('Kode alat sudah digunakan oleh alat lain!'); history.back();</script>";
        exit;
    }

    // Update data alat
    mysqli_query($koneksi, "
        UPDATE alat 
        SET kode_alat = '$kode', 
            nama_alat = '$nama',
            tanggal_update = CURDATE()
        WHERE id_alat = '$id'
    ");

    // Catat ke riwayat_alat
    mysqli_query($koneksi, "
        INSERT INTO riwayat_alat (id_alat, jenis, keterangan, tanggal_mulai)
        VALUES ('$id', 'edit', 'Perubahan data alat', CURDATE())
    ");

    header("Location: alat.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Edit Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<div class="card shadow">
    <div class="card-header bg-warning text-white">
        <h4 class="mb-0">✏️ Edit Data Alat</h4>
    </div>

    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label class="form-label">Kode Alat</label>
                <input type="text" name="kode" class="form-control" 
                       value="<?= htmlspecialchars($data['kode_alat']) ?>" required>
                <small class="text-muted">Kode alat bisa diubah jika dibutuhkan.</small>
            </div>

            <div class="mb-3">
                <label class="form-label">Nama Alat</label>
                <input type="text" name="nama" class="form-control" 
                       value="<?= htmlspecialchars($data['nama_alat']) ?>" required>
            </div>

            <button type="submit" name="update" class="btn btn-success">💾 Update</button>
            <a href="alat.php" class="btn btn-secondary">⬅ Kembali</a>
        </form>
    </div>
</div>

</body>
</html>
