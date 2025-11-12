<?php
// riwayat_pakai.php (perbaikan lengkap)
error_reporting(E_ALL);
ini_set('display_errors', 1);
include "koneksi.php";

// ==========================
// FILTER DATA
// ==========================
$bulan   = isset($_GET['bulan']) ? $_GET['bulan'] : '';
$tahun   = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$status  = isset($_GET['status']) ? $_GET['status'] : '';
$teknisi = isset($_GET['teknisi']) ? trim($_GET['teknisi']) : '';

$where = [];

// filter bulan/tahun menggunakan tanggal_mulai / tanggal_selesai (karena tidak ada kolom 'tanggal')
if ($bulan != '')  $where[] = "MONTH(COALESCE(p.tanggal_mulai, p.tanggal_selesai)) = " . (int)$bulan;
if ($tahun != '')  $where[] = "YEAR(COALESCE(p.tanggal_mulai, p.tanggal_selesai)) = " . (int)$tahun;

// filter status (bisa berada di kolom status atau kolom jenis)
if ($status != '') $where[] = "(p.status = '" . mysqli_real_escape_string($koneksi, $status) . "' OR p.jenis = '" . mysqli_real_escape_string($koneksi, $status) . "')";

// filter teknisi
if ($teknisi != '') $where[] = "p.nama_teknisi IS NOT NULL AND p.nama_teknisi <> '' AND p.nama_teknisi LIKE '%" . mysqli_real_escape_string($koneksi, $teknisi) . "%'";

$hideAktif = "1"; // tampilkan semua, kecuali kalau kamu memang ingin sembunyikan 'aktif'
if ($where) {
    $whereSql = "WHERE " . implode(" AND ", $where);
} else {
    $whereSql = ""; // tanpa filter, tampil semua
}


// ==========================================
// HAPUS RIWAYAT (pakai id_riwayat dari riwayat_alat)
// ==========================================
if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    mysqli_query($koneksi, "DELETE FROM riwayat_alat WHERE id_riwayat = $id");
    echo "<div class='alert alert-success'>Data riwayat berhasil dihapus.</div>";
}

// ==========================================
// UPDATE RIWAYAT (edit sederhana — menyesuaikan form edit_riwayat_pakai.php bila ada)
// ==========================================
if (isset($_POST['update'])) {
  $id_alat_baru = (int)$_POST['id_alat'];
  $tgl_mulai = mysqli_real_escape_string($koneksi, $_POST['tanggal_mulai']);
  $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan']);

  // ✅ pastikan data alat valid
  if ($id_alat_baru <= 0) {
      echo "<script>alert('ID alat tidak valid.');</script>";
      exit;
  }

  // ✅ jika alat diganti, sesuaikan stok
  if ($id_alat_baru != $data['id_alat']) {
      mysqli_query($koneksi, "UPDATE alat SET jumlah = jumlah + 1, jumlah_perbaikan = GREATEST(jumlah_perbaikan - 1, 0) WHERE id_alat='{$data['id_alat']}'");
      mysqli_query($koneksi, "UPDATE alat SET jumlah = jumlah - 1, jumlah_perbaikan = jumlah_perbaikan + 1 WHERE id_alat='$id_alat_baru'");
  }

  // ✅ hitung lama perbaikan (jika tanggal_selesai sudah ada)
  $tanggal_selesai = $data['tanggal_selesai'];
  $lama = 0;
  if (!empty($tgl_mulai) && !empty($tanggal_selesai)) {
      $lama = (new DateTime($tgl_mulai))->diff(new DateTime($tanggal_selesai))->days;
  }

  // ✅ pastikan status dan jenis tetap 'perbaikan'
  $sql_update = "
      UPDATE riwayat_alat 
      SET 
          id_alat = '$id_alat_baru',
          tanggal_mulai = '$tgl_mulai',
          keterangan = '$ket',
          jenis = 'perbaikan',
          status = 'perbaikan',
          lama_perbaikan = '$lama'
      WHERE id_riwayat = '$id'
  ";

  if (mysqli_query($koneksi, $sql_update)) {
      echo "<script>
          alert('✅ Data perbaikan berhasil diperbarui.');
          window.location='alat.php?view=perbaikan';
      </script>";
  } else {
      echo "<script>alert('❌ Gagal memperbarui: " . mysqli_error($koneksi) . "');</script>";
  }
  exit;

  echo "<div class='alert alert-info'>Data riwayat berhasil diperbarui dan lama perbaikan dihitung otomatis.</div>";
}


?>
<!DOCTYPE html>
<html>
<head>
    <title>Riwayat Pemakaian Alat</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style> form select, form input { min-width: 120px; } .table td, .table th { vertical-align: middle; } </style>
</head>
<body class="container mt-4">

<h2>📖 Riwayat Alat</h2>

<form method="get" class="row row-cols-lg-auto g-3 align-items-end mb-4 bg-light p-3 rounded shadow-sm">
  <div class="col">
    <label class="form-label small">Bulan</label>
    <select name="bulan" class="form-select form-select-sm">
      <option value="">Semua</option>
      <?php
      $nama_bulan = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
      foreach ($nama_bulan as $num => $nama) {
          $sel = ($bulan==$num)?'selected':'';
          echo "<option value='$num' $sel>$nama</option>";
      }
      ?>
    </select>
  </div>

  <div class="col">
    <label class="form-label small">Tahun</label>
    <select name="tahun" class="form-select form-select-sm">
      <?php
      $now = date('Y');
      for ($t=$now; $t>=2020; $t--) {
          $sel = ($tahun==$t)?'selected':'';
          echo "<option value='$t' $sel>$t</option>";
      }
      ?>
    </select>
  </div>

  <div class="col">
    <label class="form-label small">Status</label>
    <select name="status" class="form-select form-select-sm">
      <option value="">Semua</option>
      <option value="ditambahkan" <?= $status=='ditambahkan'?'selected':''; ?>>Ditambahkan</option>
      <option value="dipakai" <?= $status=='dipakai'?'selected':''; ?>>Dipakai</option>
      <option value="perbaikan" <?= $status=='perbaikan'?'selected':''; ?>>Perbaikan</option>
      <option value="selesai" <?= $status=='selesai'?'selected':''; ?>>Selesai (Dikembalikan)</option>
      <option value="selesai_perbaikan" <?= $status=='selesai_perbaikan'?'selected':''; ?>>Selesai Perbaikan</option>
    </select>
  </div>

  <div class="col">
    <label class="form-label small">Teknisi</label>
    <select name="teknisi" class="form-select form-select-sm">
      <option value="">Semua</option>
      <?php
      // ambil nama teknisi unik dari riwayat_alat (kalau di DB pakai pakai_alat yg berbeda, ganti sesuai)
      $teknisi_q = mysqli_query($koneksi, "SELECT DISTINCT nama_teknisi FROM riwayat_alat WHERE nama_teknisi <> '' ORDER BY nama_teknisi ASC");
      while ($t = mysqli_fetch_assoc($teknisi_q)) {
          $selected = ($teknisi == $t['nama_teknisi']) ? 'selected' : '';
          echo "<option value='".htmlspecialchars($t['nama_teknisi'])."' $selected>".htmlspecialchars($t['nama_teknisi'])."</option>";
      }
      ?>
    </select>
  </div>

  <div class="col">
    <button type="submit" class="btn btn-primary btn-sm mt-2 mt-lg-4">🔍 Tampil</button>
  </div>

  <div class="col">
    <a href="riwayat_pakai.php" class="btn btn-secondary btn-sm mt-2 mt-lg-4">↻ Reset</a>
  </div>
</form>

<table class="table table-bordered table-striped align-middle mt-3">
    <thead class="table-dark text-center">
        <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Kode Alat</th>
            <th>Nama Alat</th>
            <th>Nama Teknisi</th>
            <th>Lama Perbaikan</th>
            <th>Keterangan</th>
            <th>Status</th>
            <th width="150">Aksi</th>
        </tr>
    </thead>
    <tbody>
<?php
// ==========================
// QUERY DATA (pakai riwayat_alat)
$sql_q = "
SELECT p.*, a.kode_alat, a.nama_alat 
FROM riwayat_alat p 
LEFT JOIN alat a ON p.id_alat = a.id_alat
$whereSql
ORDER BY COALESCE(p.tanggal_mulai, p.tanggal_selesai) DESC, p.id_riwayat DESC
";
$sql = mysqli_query($koneksi, $sql_q);
$no = 1;
if ($sql && mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
      // Hitung lama perbaikan otomatis jika ada tanggal mulai & selesai
    $lama_perbaikan = 0;
    if (!empty($row['tanggal_mulai']) && !empty($row['tanggal_selesai'])) {
        $tgl_mulai = new DateTime($row['tanggal_mulai']);
        $tgl_selesai = new DateTime($row['tanggal_selesai']);
        $selisih = $tgl_mulai->diff($tgl_selesai);
        $lama_perbaikan = $selisih->days; // hasilnya dalam satuan hari
    } elseif (!empty($row['lama_perbaikan'])) {
        $lama_perbaikan = (int)$row['lama_perbaikan'];
    }
        $status_row = strtolower($row['status'] ?? $row['jenis'] ?? '');
        $badge = 'secondary';
        switch ($status_row) {
            case 'ditambahkan': $badge = 'info'; break;
            case 'dipakai': $badge = 'primary'; break;
            case 'perbaikan': $badge = 'warning'; break;
            case 'selesai': $badge = 'success'; break;
            case 'selesai_perbaikan': $badge = 'dark'; break;
        }

        echo "<tr>";
        echo "<td class='text-center'>{$no}</td>";
            // Jika status selesai_perbaikan, tampilkan tanggal_selesai, selain itu tampilkan tanggal_mulai
      $tanggal_tampil = '-';
      if (!empty($row['status']) && strtolower($row['status']) == 'selesai_perbaikan') {
          $tanggal_tampil = $row['tanggal_selesai'] ?: '-';
      } else {
          $tanggal_tampil = $row['tanggal_mulai'] ?: $row['tanggal_selesai'] ?: '-';
      }
        echo "<td>" . htmlspecialchars($tanggal_tampil) . "</td>";
        echo "<td>".htmlspecialchars($row['kode_alat'] ?? '-')."</td>";
        echo "<td>".htmlspecialchars($row['nama_alat'] ?? '-')."</td>";
        echo "<td>".htmlspecialchars($row['nama_teknisi'] ?? '-')."</td>";
        echo "<td class='text-center'>{$lama_perbaikan} hari</td>";
        echo "<td>".htmlspecialchars($row['keterangan'] ?? '-')."</td>";
        echo "<td class='text-center'><span class='badge bg-{$badge}'>".ucfirst($status_row)."</span></td>";

        echo "<td class='text-center'>
                <div class='d-flex justify-content-center gap-1'>
                    <a href='edit_riwayat_pakai.php?id_riwayat={$row['id_riwayat']}' class='btn btn-warning btn-sm'>✏ Edit</a>
                    <a href='?hapus={$row['id_riwayat']}' class='btn btn-danger btn-sm' onclick=\"return confirm('Yakin hapus?')\">🗑 Hapus</a>
                </div>
              </td>";

        echo "</tr>";
        $no++;
    }
} else {
    echo "<tr><td colspan='9' class='text-center text-muted'>Belum ada data riwayat.</td></tr>";
}
?>
    </tbody>
</table>

<div class="d-flex justify-content-between align-items-center mt-4">
  <a href="alat.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>
  <div class="d-flex gap-2">
  <a href="export_pdf_riwayat_alat.php?bulan=<?= urlencode($bulan) ?>&tahun=<?= urlencode($tahun) ?>&status=<?= urlencode($status) ?>" class="btn btn-danger btn-sm">
    📄 Export PDF
</a>
    <a href="export_excel_riwayat_alat.php?bulan=<?= urlencode($bulan) ?>&tahun=<?= urlencode($tahun) ?>&status=<?= urlencode($status) ?>" class="btn btn-success btn-sm">📊 Export Excel</a>
  </div>
</div>

</body>
</html>
