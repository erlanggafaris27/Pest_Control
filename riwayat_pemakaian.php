<?php
include "koneksi.php";

// --- Ambil filter pencarian ---
$bulan = $_GET['bulan'] ?? 'Semua';
$tahun = $_GET['tahun'] ?? date('Y');
$status = $_GET['status'] ?? 'Semua';
$customer = $_GET['customer'] ?? 'Semua';

// --- Query dasar ---
$where = [];
if ($bulan !== 'Semua') $where[] = "MONTH(r.tanggal) = '$bulan'";
if ($tahun !== 'Semua') $where[] = "YEAR(r.tanggal) = '$tahun'";
if ($status !== 'Semua') $where[] = "r.status = '$status'";
if ($customer !== 'Semua') $where[] = "r.nama_customer = '$customer'";
$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$query = "
  SELECT r.*, p.nama_pestisida, p.tipe, p.sisa_ml, p.isi_per_botol_ml
  FROM riwayat_pestisida r
  LEFT JOIN pestisida p ON r.id_pestisida = p.id_pestisida
  $whereSQL
  ORDER BY r.tanggal DESC, r.id_riwayat DESC
";
$result = mysqli_query($koneksi, $query);

// --- Ambil data untuk dropdown ---
$listCustomer = mysqli_query($koneksi, "SELECT DISTINCT nama_customer FROM riwayat_pestisida WHERE nama_customer <> '-' AND nama_customer <> '' ORDER BY nama_customer ASC");
$listStatus = mysqli_query($koneksi, "SELECT DISTINCT status FROM riwayat_pestisida ORDER BY status ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Riwayat Aktivitas Pestisida</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="bg-light">

<div class="container mt-4">
  <div class="card shadow">
    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
      <h4 class="mb-0"><i class="bi bi-clock-history"></i> Riwayat Aktivitas Pestisida</h4>
      <a href="data_pestisida.php" class="btn btn-light btn-sm"><i class="bi bi-arrow-left"></i> Kembali</a>
    </div>

    <div class="card-body">
      <!-- 🔍 Filter dan Tombol Ekspor -->
      <div class="d-flex justify-content-between align-items-end flex-wrap mb-3">
        <form method="GET" class="row g-2 align-items-end flex-grow-1">
          <div class="col-md-2">
            <label class="form-label">Bulan</label>
            <select name="bulan" class="form-select form-select-sm">
              <option value="Semua">Semua</option>
              <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>" <?= ($bulan == $i) ? 'selected' : '' ?>>
                  <?= date('F', mktime(0,0,0,$i,1)) ?>
                </option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-2">
            <label class="form-label">Tahun</label>
            <select name="tahun" class="form-select form-select-sm">
              <?php for ($y = date('Y'); $y >= date('Y')-5; $y--): ?>
                <option value="<?= $y ?>" <?= ($tahun == $y) ? 'selected' : '' ?>><?= $y ?></option>
              <?php endfor; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Status</label>
            <select name="status" class="form-select form-select-sm">
              <option value="Semua">Semua</option>
              <?php while ($s = mysqli_fetch_assoc($listStatus)): ?>
                <option value="<?= $s['status'] ?>" <?= ($status == $s['status']) ? 'selected' : '' ?>>
                  <?= ucwords($s['status']) ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-3">
            <label class="form-label">Customer</label>
            <select name="customer" class="form-select form-select-sm">
              <option value="Semua">Semua</option>
              <?php while ($c = mysqli_fetch_assoc($listCustomer)): ?>
                <option value="<?= $c['nama_customer'] ?>" <?= ($customer == $c['nama_customer']) ? 'selected' : '' ?>>
                  <?= $c['nama_customer'] ?>
                </option>
              <?php endwhile; ?>
            </select>
          </div>
          <div class="col-md-2 d-flex gap-2">
            <button type="submit" class="btn btn-primary btn-sm w-100"><i class="bi bi-search"></i> Tampil</button>
            <a href="riwayat_pestisida.php" class="btn btn-secondary btn-sm w-100"><i class="bi bi-arrow-repeat"></i> Reset</a>
          </div>
        </form>

        <div class="d-flex justify-content-between mb-2">
  <div></div>
  <div>
    <a href="export_riwayat_pestisida_excel.php?<?= http_build_query($_GET) ?>" class="btn btn-success btn-sm me-2">
      <i class="bi bi-file-earmark-excel"></i> Excel
    </a>
    <a href="export_riwayat_pestisida_pdf.php?<?= http_build_query($_GET) ?>" class="btn btn-danger btn-sm">
      <i class="bi bi-file-earmark-pdf"></i> PDF
    </a>
  </div>
</div>



      <!-- 📋 Tabel Riwayat -->
      <table class="table table-bordered table-striped align-middle">
        <thead class="table-dark text-center">
          <tr>
            <th>No</th>
            <th>Tanggal</th>
            <th>Nama Pestisida</th>
            <th>Nama Customer</th>
            <th>Nama Teknisi</th>
            <th>Jumlah</th>
            <th>Sisa Aktif (ml)</th>
            <th>Status</th>
            <th>Keterangan</th>
            <th width="120">Aksi</th>
          </tr>
        </thead>
        <tbody>
          <?php
          if (mysqli_num_rows($result) > 0) {
            $no = 1;
            while ($row = mysqli_fetch_assoc($result)) {
              $status = strtolower($row['status']);
              if ($status === 'pestisida baru') $badge = '<span class="badge bg-info text-dark">🧪 Pestisida Baru</span>';
              elseif ($status === 'stok ditambah') $badge = '<span class="badge bg-success">📦 Stok Ditambah</span>';
              elseif (str_contains($status, 'alokasi')) $badge = '<span class="badge bg-warning text-dark">📤 Dialokasikan</span>';
              elseif (str_contains($status, 'pakai')) $badge = '<span class="badge bg-primary">🧴 Pakai Lapangan</span>';
              elseif ($status === 'hapus_pakai') {
                $badge = '<span class="badge bg-danger">🗑️ Hapus Pemakaian</span>';
            } elseif ($status === 'hapus_stok') {
                $badge = '<span class="badge bg-dark">💀 Hapus Stok Pestisida</span>';
            }            
              else $badge = '<span class="badge bg-secondary">'.htmlspecialchars($row['status']).'</span>';

              echo "
                <tr>
                  <td class='text-center'>{$no}</td>
                  <td class='text-center'>".htmlspecialchars($row['tanggal'])."</td>
                  <td>".htmlspecialchars($row['nama_pestisida'] ?? '-')."</td>
                  <td>".htmlspecialchars($row['nama_customer'] ?? '-')."</td>
                  <td>".htmlspecialchars($row['nama_teknisi'] ?? '-')."</td>
                  <td class='text-center'>".htmlspecialchars($row['jumlah'])."</td>
                  <td class='text-center'>".htmlspecialchars($row['sisa_aktif_ml'])."</td>
                  <td class='text-center'>{$badge}</td>
                  <td>".htmlspecialchars($row['keterangan'])."</td>
                  <td class='text-center'>
                    <a href='edit_riwayat.php?id={$row['id_riwayat']}' class='btn btn-warning btn-sm'>✏️</a>
                    <a href='hapus_riwayat.php?id={$row['id_riwayat']}' class='btn btn-danger btn-sm'
                       onclick=\"return confirm('Yakin ingin menghapus data riwayat ini?');\">🗑️</a>
                  </td>
                </tr>
              ";
              $no++;
            }
          } else {
            echo "<tr><td colspan='10' class='text-center text-muted'>Belum ada data riwayat pestisida.</td></tr>";
          }
          ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

</body>
</html>
