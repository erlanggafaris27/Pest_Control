<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// ======== Tambahkan fungsi helper di atas ========

// Cek apakah tabel ada di database
function tableExists($conn, $table) {
    $safeTable = mysqli_real_escape_string($conn, $table);
    $result = mysqli_query($conn, "SHOW TABLES LIKE '$safeTable'");
    return $result && mysqli_num_rows($result) > 0;
}

// Cek apakah kolom ada di tabel
function columnExists($conn, $table, $column) {
    $safeTable = mysqli_real_escape_string($conn, $table);
    $safeColumn = mysqli_real_escape_string($conn, $column);
    $result = mysqli_query($conn, "SHOW COLUMNS FROM `$safeTable` LIKE '$safeColumn'");
    return $result && mysqli_num_rows($result) > 0;
}

// =================================================


$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : 'all';
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

$data = [];
$totals = [
    'jumlah_botol' => 0,
    'terpakai_ml'  => 0,
    'sisa_ml'      => 0
];
$error = '';

// =======================
// QUERY DATA SESUAI JENIS
// =======================
if ($jenis === 'pestisida') {
    if (!tableExists($koneksi, 'pestisida')) {
        $error = "Tabel 'pestisida' tidak ditemukan di database.";
    } else {
        // jika di form kamu memakai 'all' untuk semua bulan -> handle itu
        $whereParts = [];
        if (columnExists($koneksi, 'pestisida', 'tanggal_update')) {
            if (!empty($tahun)) $whereParts[] = "YEAR(tanggal_update) = '" . mysqli_real_escape_string($koneksi, $tahun) . "'";
            if ($bulan !== '' && $bulan !== 'all') $whereParts[] = "MONTH(tanggal_update) = '" . mysqli_real_escape_string($koneksi, $bulan) . "'";
        }
        $whereSql = count($whereParts) ? "WHERE " . implode(' AND ', $whereParts) : "";

        $orderBy = columnExists($koneksi, 'pestisida', 'tanggal_update') ? "tanggal_update DESC" : "id_pestisida DESC";
        $q = "SELECT * FROM pestisida $whereSql ORDER BY $orderBy";
        $res = mysqli_query($koneksi, $q);
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $data[] = $r;
                $totals['jumlah_botol'] += (int) ($r['jumlah_botol'] ?? 0);
                // kalau nama kolom sisa berbeda coba cek: sisa_ml atau sisa_botol_aktif_ml
                $totals['sisa_ml'] += (int) ($r['sisa_ml'] ?? $r['sisa_botol_aktif_ml'] ?? 0);
                $totals['terpakai_ml'] += (int) ($r['terpakai_ml'] ?? 0);
            }
        } else {
            $error = "Query gagal (pestisida): " . mysqli_error($koneksi);
        }
    }
} elseif ($jenis === 'alat') {
    if (!tableExists($koneksi, 'alat')) {
        $error = "Tabel 'alat' tidak ditemukan di database.";
    } else {
        $whereParts = [];
        if (columnExists($koneksi, 'alat', 'tanggal_update')) {
            if (!empty($tahun)) $whereParts[] = "YEAR(tanggal_update) = '" . mysqli_real_escape_string($koneksi, $tahun) . "'";
            if ($bulan !== '' && $bulan !== 'all') $whereParts[] = "MONTH(tanggal_update) = '" . mysqli_real_escape_string($koneksi, $bulan) . "'";
        }
        $whereSql = count($whereParts) ? "WHERE " . implode(' AND ', $whereParts) . " OR tanggal_update IS NULL" : "";

        $q = "SELECT * FROM alat $whereSql ORDER BY id_alat DESC";
        $res = mysqli_query($koneksi, $q);
        if ($res) {
            while ($r = mysqli_fetch_assoc($res)) {
                $data[] = $r;
                // kolom alat di DB: jumlah, jumlah_pakai, jumlah_perbaikan
                $totals['jumlah_botol'] += (int) ($r['jumlah'] ?? 0);
                $totals['terpakai_ml']  += (int) ($r['jumlah_pakai'] ?? 0);
            }
        } else {
            $error = "Query gagal (alat): " . mysqli_error($koneksi);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Stok Alat & Pestisida</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="container mt-4">

<h2 class="text-center mb-4">📊 Laporan Stok Alat & Pestisida</h2>

<form method="get" class="bg-light p-4 rounded shadow-sm mb-4">
  <div class="row g-3 align-items-end">
    <div class="col-md-3">
      <label class="form-label fw-semibold">Pilih Jenis Data</label>
      <select name="jenis" class="form-select" required>
        <option value="">-- Pilih --</option>
        <option value="alat" <?= $jenis=='alat'?'selected':'' ?>>Data Alat</option>
        <option value="pestisida" <?= $jenis=='pestisida'?'selected':'' ?>>Data Pestisida</option>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold">Bulan</label>
      <select name="bulan" class="form-select">
        <?php
        $bulan_nama = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];
        foreach ($bulan_nama as $num => $nama) {
          echo "<option value='$num' ".($bulan==$num?'selected':'').">$nama</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label class="form-label fw-semibold">Tahun</label>
      <select name="tahun" class="form-select">
        <?php
        $th_now = date('Y');
        for ($t=$th_now; $t>=2020; $t--) {
          echo "<option value='$t' ".($tahun==$t?'selected':'').">$t</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <button type="submit" class="btn btn-primary w-100">🔍 Tampilkan</button>
    </div>
  </div>
</form>

<?php if ($error): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<?php if ($jenis === 'alat'): ?>
  <div class="card">
    <div class="card-header bg-primary text-white fw-bold">
      🧰 Laporan Bulanan Alat (<?= $bulan=='all' ? 'Semua Bulan' : date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahun ?>)
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Kode</th>
            <th>Nama Alat</th>
            <th>Dipakai </th>
            <th>Perbaikan </th>
            <th>Lama Perbaikan (hari)</th>
            <th>Kondisi Akhir</th>
            <th>Update Terakhir</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $alatData = mysqli_query($koneksi, "SELECT * FROM alat ORDER BY nama_alat ASC");
          $no = 1;
          while ($alat = mysqli_fetch_assoc($alatData)):
            $id = $alat['id_alat'];
            $pakai = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT COUNT(*) AS total 
              FROM riwayat_alat 
              WHERE id_alat='$id' AND status='dipakai' 
              AND MONTH(tanggal_mulai)='$bulan' AND YEAR(tanggal_mulai)='$tahun'
            "))['total'] ?? 0;

            $perbaikan = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT COUNT(*) AS total 
              FROM riwayat_alat 
              WHERE id_alat='$id' AND status='perbaikan' 
              AND MONTH(tanggal_mulai)='$bulan' AND YEAR(tanggal_mulai)='$tahun'
            "))['total'] ?? 0;

            $lama = mysqli_fetch_assoc(mysqli_query($koneksi, "
            SELECT SUM(lama_perbaikan) AS total_hari 
            FROM riwayat_alat 
            WHERE id_alat='$id'
            AND status = 'selesai_perbaikan'
            AND tanggal_selesai IS NOT NULL
            AND MONTH(tanggal_selesai) = '$bulan'
            AND YEAR(tanggal_selesai) = '$tahun'            
            "))['total_hari'] ?? 0;

            $kondisi = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT status FROM riwayat_alat 
              WHERE id_alat='$id' ORDER BY tanggal_mulai DESC LIMIT 1
            "))['status'] ?? '-';

            $update = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT MAX(tanggal_mulai) AS terakhir FROM riwayat_alat 
              WHERE id_alat='$id'
            "))['terakhir'] ?? '-';
          ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($alat['kode_alat']) ?></td>
            <td class="text-start"><?= htmlspecialchars($alat['nama_alat']) ?></td>
            <td><?= $pakai ?></td>
            <td><?= $perbaikan ?></td>
            <td><?= $lama ?></td>
            <td><?= ucwords($kondisi) ?></td>
            <td><?= $update ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>


  <?php elseif ($jenis === 'pestisida'): ?>
  <div class="card">
    <div class="card-header bg-success text-white fw-bold">
      🧴 Laporan Bulanan Pestisida (<?= $bulan=='all' ? 'Semua Bulan' : date('F', mktime(0,0,0,$bulan,1)) ?> <?= $tahun ?>)
    </div>
    <div class="card-body">
      <table class="table table-bordered table-striped text-center align-middle">
        <thead class="table-dark">
          <tr>
            <th>No</th>
            <th>Nama Pestisida</th>
            <th>Jenis</th>
            <th>Penambahan (Botol)</th>
            <th>Penambahan (ml)</th>
            <th>Pemakaian (Botol)</th>
            <th>Pemakaian (ml)</th>
            <th>Total Akhir (Botol)</th>
            <th>Total Akhir (ml)</th>
          </tr>
        </thead>
        <tbody>
          <?php
          $pestisidaData = mysqli_query($koneksi, "SELECT * FROM pestisida ORDER BY nama_pestisida ASC");
          $no = 1;
          while ($row = mysqli_fetch_assoc($pestisidaData)):
            $id = $row['id_pestisida'];

            $tambah = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT SUM(jumlah) AS botol, SUM(sisa_aktif_ml) AS ml
              FROM riwayat_pestisida 
              WHERE id_pestisida='$id' AND status='stok ditambah'
              AND MONTH(tanggal)='$bulan' AND YEAR(tanggal)='$tahun'
            "));
            $tambahBotol = $tambah['botol'] ?? 0;
            $tambahML = $tambah['ml'] ?? 0;

          // ✅ Hitung total alokasi pestisida (jangka panjang)
// ✅ Ini menjumlah semua alokasi dari riwayat, seiring waktu bertambah
$alokasi = mysqli_fetch_assoc(mysqli_query($koneksi, "
SELECT 
    SUM(jumlah) AS total_botol,
    SUM(sisa_aktif_ml) AS total_ml
FROM riwayat_pestisida
WHERE id_pestisida = '$id'
  AND status = 'alokasi'
  AND MONTH(tanggal) = '$bulan'
  AND YEAR(tanggal) = '$tahun'
"));

// hasilnya
$pakaiBotol = $alokasi['total_botol'] ?? 0;
$pakaiML    = $alokasi['total_ml'] ?? 0;

            $stok = mysqli_fetch_assoc(mysqli_query($koneksi, "
              SELECT jumlah_botol, sisa_ml FROM pestisida WHERE id_pestisida='$id'
            "));
            $totalBotol = $stok['jumlah_botol'] ?? 0;
            $totalML = $stok['sisa_ml'] ?? 0;
          ?>
          <tr>
            <td><?= $no++ ?></td>
            <td><?= htmlspecialchars($row['nama_pestisida']) ?></td>
            <td><?= htmlspecialchars($row['tipe']) ?></td>
            <td><?= $tambahBotol ?></td>
            <td><?= $tambahML ?></td>
            <td><?= $pakaiBotol ?></td>
            <td><?= $pakaiML ?></td>
            <td><?= $totalBotol ?></td>
            <td><?= $totalML ?></td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
<?php else: ?>
  <div class="alert alert-info text-center">Silakan pilih jenis data (Alat atau Pestisida) untuk ditampilkan.</div>
<?php endif; ?>

<div class="d-flex justify-content-between align-items-center mt-4">
  <a href="index.php" class="btn btn-secondary btn-sm">⬅ Kembali</a>

  <div class="d-flex gap-2">
  <a href="export_pdf.php?jenis=<?= urlencode($jenis) ?>&bulan=<?= urlencode($bulan) ?>&tahun=<?= urlencode($tahun) ?>" 
     target="_blank" class="btn btn-danger btn-sm">
    📄 Export PDF
  </a>
  <a href="export_excel.php?jenis=<?= urlencode($jenis) ?>&bulan=<?= urlencode($bulan) ?>&tahun=<?= urlencode($tahun) ?>" 
     target="_blank" class="btn btn-success btn-sm">
    📊 Export Excel
  </a>
</div>
</div>
</body>
</html>
