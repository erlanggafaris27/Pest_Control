<?php
include "koneksi.php";
// di awal file setelah include koneksi
$view = isset($_GET['view']) ? $_GET['view'] : 'utama';
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Alat</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-box-seam"></i> Data Alat</h4>
      <div class="d-flex gap-2">
        <a href="tambah_alat.php" class="btn btn-success btn-sm"><i class="bi bi-plus-lg"></i> Tambah Alat</a>

        <!-- 🔽 Dropdown pilih tampilan tabel -->
        <div class="btn-group">
          <button type="button" class="btn btn-light btn-sm dropdown-toggle" data-bs-toggle="dropdown">Lihat Tabel</button>
          <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="alat.php?view=utama">Tabel Utama</a></li>
            <li><a class="dropdown-item" href="alat.php?view=dipakai">Alat Dipakai</a></li>
            <li><a class="dropdown-item" href="alat.php?view=perbaikan">Alat Perbaikan</a></li>
          </ul>
        </div>

        <!-- 🔁 Tombol Riwayat -->
        <a href="riwayat_pakai.php" class="btn btn-light btn-sm"><i class="bi bi-clock-history"></i> Riwayat</a>
      </div>
    </div>

    <!-- 🔹 Di sinilah isi tabel diletakkan -->
    <div class="card-body">
<!-- header tetap seperti sekarang -->
<div class="card-body">
<?php if ($view === 'utama'): ?>

  <h5 class="fw-bold mb-3 text-primary">
    📦 Tabel Utama Alat
  </h5>


  <!-- TABEL UTAMA ALAT -->
  <table class="table table-bordered table-hover align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>No</th><th>Kode Alat</th><th>Nama Alat</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
      <?php
      $no = 1;
      $sql = mysqli_query($koneksi, "
      SELECT a.* 
      FROM alat a
      LEFT JOIN (
          SELECT id_alat, MAX(id_riwayat) AS last_riwayat
          FROM riwayat_alat
          GROUP BY id_alat
      ) lr ON a.id_alat = lr.id_alat
      LEFT JOIN riwayat_alat r ON r.id_riwayat = lr.last_riwayat
      WHERE (r.status IS NULL OR r.status NOT IN ('dipakai', 'perbaikan'))
      ORDER BY a.id_alat DESC      
");


      if ($sql && mysqli_num_rows($sql) > 0) {
          while ($r = mysqli_fetch_assoc($sql)) {
              echo "<tr>";
              echo "<td class='text-center'>{$no}</td>";
              echo "<td>".htmlspecialchars($r['kode_alat'] ?? '')."</td>";
              echo "<td>".htmlspecialchars($r['nama_alat'] ?? '')."</td>";
              echo "<td class='text-center'>
                      <div class='d-flex justify-content-center gap-2'>
                        <a href='edit_alat.php?id={$r['id_alat']}' class='btn btn-warning btn-sm'>✏ Edit</a>
                        <a href='hapus_alat.php?id={$r['id_alat']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin ingin menghapus alat ini?')\">🗑 Hapus</a>
                        <!-- tombol cepat: pakai / perbaikan -->
                        <a href='pakai_alat.php?id_alat={$r['id_alat']}' class='btn btn-primary btn-sm'>Pakai</a>
                        <a href='perbaikan_alat.php?id_alat={$r['id_alat']}' class='btn btn-secondary btn-sm'>Perbaikan</a>
                      </div>
                    </td>";
              echo "</tr>";
              $no++;
          }
      } else {
          echo "<tr><td colspan='4' class='text-center text-muted'>Belum ada data alat.</td></tr>";
      }
      ?>
    </tbody>
  </table>

<?php elseif ($view === 'dipakai'): ?>
  <h5 class="fw-bold mb-3 text-success">
    🧰 Daftar Alat yang Sedang Dipakai
  </h5>
  <!-- TABEL ALAT DIPAKAI (ambil dari riwayat_alat atau pakai_alat - adjust nama tabel kamu) -->
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>No</th><th>Tanggal</th><th>Kode Alat</th><th>Nama Alat</th><th>Nama Teknisi</th>
        <th>Keterangan</th><th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php
    // contoh memakai tabel riwayat_alat (sesuaikan nama & kolom bila berbeda)
    $q = "
    SELECT r.*, a.kode_alat, a.nama_alat
    FROM riwayat_alat r
    JOIN (
        SELECT id_alat, MAX(id_riwayat) AS last_riwayat
        FROM riwayat_alat
        GROUP BY id_alat
    ) lr ON r.id_riwayat = lr.last_riwayat
    JOIN alat a ON r.id_alat = a.id_alat
    WHERE r.status = 'dipakai'
    ORDER BY r.tanggal_mulai DESC, r.id_riwayat DESC
  ";  
    $res = mysqli_query($koneksi, $q);
    $no = 1;
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            echo "<tr>";
            echo "<td class='text-center'>{$no}</td>";
            echo "<td>".htmlspecialchars($row['tanggal_mulai'] ?? $row['tanggal'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($row['kode_alat'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($row['nama_alat'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($row['nama_teknisi'] ?? '')."</td>";
            echo "<td>".htmlspecialchars($row['keterangan'] ?? '')."</td>";
            // aksi: kembalikan (mengubah alat -> jumlah +1, jumlah_pakai -1; update riwayat jadi selesai_pakai)
            echo "<td class='text-center'>
                    <a href='kembalikan_alat.php?id={$row['id_riwayat']}' class='btn btn-success btn-sm' onclick=\"return confirm('Konfirmasi: kembalikan alat ini?')\">↩ Kembalikan</a>
                    <a href='edit_dipakai.php?id={$row['id_riwayat']}' class='btn btn-warning btn-sm'>✏ Edit</a>
                    <a href='hapus_dipakai.php?id={$row['id_riwayat']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin hapus riwayat ini?')\">🗑 Hapus</a>
                  </td>";
            echo "</tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada alat sedang dipakai.</td></tr>";
    }
    ?>
    </tbody>
  </table>

  <?php elseif ($view === 'perbaikan'): ?>
    <h5 class="fw-bold mb-3 text-danger">
    🛠️ Daftar Alat yang Sedang Diperbaiki
  </h5>
  <!-- TABEL PERBAIKAN -->
  <table class="table table-bordered table-striped align-middle">
    <thead class="table-dark text-center">
      <tr>
        <th>No</th>
        <th>Tanggal Masuk</th>
        <th>Kode Alat</th>
        <th>Nama Alat</th>
        <th>Lama (Hari)</th>
        <th>Keterangan</th>
        <th>Aksi</th>
      </tr>
    </thead>
    <tbody>
    <?php
    $q = "
      SELECT r.*, a.kode_alat, a.nama_alat,
             DATEDIFF(CURDATE(), r.tanggal_mulai) AS lama_hari
      FROM riwayat_alat r
      JOIN alat a ON r.id_alat = a.id_alat
      WHERE r.status = 'perbaikan'
      AND (r.tanggal_selesai IS NULL OR r.tanggal_selesai = '0000-00-00')
      ORDER BY r.tanggal_mulai DESC, r.id_riwayat DESC
    ";
    $res = mysqli_query($koneksi, $q);
    $no = 1;
    if ($res && mysqli_num_rows($res) > 0) {
        while ($row = mysqli_fetch_assoc($res)) {
            $lama = $row['lama_hari'] ?? 0;
            echo "<tr>";
            echo "<td class='text-center'>{$no}</td>";
            echo "<td>".htmlspecialchars($row['tanggal_mulai'] ?? '-')."</td>";
            echo "<td>".htmlspecialchars($row['kode_alat'] ?? '-')."</td>";
            echo "<td>".htmlspecialchars($row['nama_alat'] ?? '-')."</td>";
            echo "<td class='text-center'>{$lama} Hari</td>";
            echo "<td>".htmlspecialchars($row['keterangan'] ?? '-')."</td>";
            echo "<td class='text-center'>
                    <a href='perbaikan_alat.php?id={$row['id_riwayat']}' class='btn btn-success btn-sm' onclick=\"return confirm('Tandai alat ini sudah selesai diperbaiki dan dikembalikan ke gudang?')\">✔ Selesai</a>
                    <a href='edit_perbaikan.php?id={$row['id_riwayat']}' class='btn btn-warning btn-sm'>✏ Edit</a>
                    <a href='hapus_perbaikan.php?id={$row['id_riwayat']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin hapus data perbaikan ini? Data riwayat juga akan terhapus dan alat kembali ke stok utama.')\">🗑 Hapus</a>
                  </td>";
            echo "</tr>";
            $no++;
        }
    } else {
        echo "<tr><td colspan='7' class='text-center text-muted'>Belum ada alat sedang diperbaiki.</td></tr>";
    }
    ?>
    </tbody>
  </table>
<?php endif; ?>

</div>

<a href="index.php" class="btn btn-secondary btn-sm mt-3"><i class="bi bi-arrow-left"></i> Kembali</a>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
