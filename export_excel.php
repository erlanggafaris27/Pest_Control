<?php
include "koneksi.php";

header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=laporan_stok.xls");

date_default_timezone_set('Asia/Jakarta');

$jenis = isset($_GET['jenis']) ? $_GET['jenis'] : '';
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Filter tanggal (bulan & tahun)
$whereBulan = ($bulan > 0)
    ? "MONTH(r.tanggal_mulai)=$bulan AND YEAR(r.tanggal_mulai)=$tahun"
    : "YEAR(r.tanggal_mulai)=$tahun";

// Judul laporan
echo "<center><h2>Laporan Bulanan " . strtoupper($jenis) . " - $tahun</h2></center>";

// =============================================================
// 🧰 LAPORAN ALAT
// =============================================================
if ($jenis == 'alat') {
    echo "<table border='1' cellspacing='0' cellpadding='5'>
        <tr style='background:#ddd; font-weight:bold; text-align:center'>
            <th>No</th>
            <th>Kode Alat</th>
            <th>Nama Alat</th>
            <th>Dipakai (x)</th>
            <th>Perbaikan (x)</th>
            <th>Total Hari Perbaikan</th>
            <th>Kondisi</th>
            <th>Terakhir Update</th>
        </tr>";

    $sql = mysqli_query($koneksi, "
        SELECT 
            a.kode_alat, 
            a.nama_alat,
            COALESCE(SUM(CASE WHEN r.status='dipakai' THEN 1 ELSE 0 END),0) AS total_pakai,
            COALESCE(SUM(CASE WHEN r.status='perbaikan' THEN 1 ELSE 0 END),0) AS total_perbaikan,
            COALESCE(SUM(CASE WHEN r.status='perbaikan' THEN r.lama_perbaikan ELSE 0 END),0) AS total_hari_perbaikan,
            COALESCE(a.status, '-') AS kondisi,
            a.tanggal_update
        FROM alat a
        LEFT JOIN riwayat_alat r ON a.id_alat = r.id_alat 
            AND $whereBulan
        GROUP BY a.id_alat
        ORDER BY a.nama_alat ASC
    ") or die("Query alat error: " . mysqli_error($koneksi));

    $no = 1;
    while ($row = mysqli_fetch_assoc($sql)) {
        echo "<tr>
            <td align='center'>{$no}</td>
            <td>{$row['kode_alat']}</td>
            <td>{$row['nama_alat']}</td>
            <td align='center'>{$row['total_pakai']}</td>
            <td align='center'>{$row['total_perbaikan']}</td>
            <td align='center'>{$row['total_hari_perbaikan']}</td>
            <td align='center'>{$row['kondisi']}</td>
            <td align='center'>{$row['tanggal_update']}</td>
        </tr>";
        $no++;
    }
    echo "</table>";
}

// =============================================================
// 🌿 LAPORAN PESTISIDA
// =============================================================
elseif ($jenis == 'pestisida') {
    echo "<table border='1' cellspacing='0' cellpadding='5'>
        <tr style='background:#ddd; font-weight:bold; text-align:center'>
            <th>No</th>
            <th>Nama Pestisida</th>
            <th>Tipe</th>
            <th>Total Tambah (Botol)</th>
            <th>Total Tambah (ml)</th>
            <th>Total Pakai (Botol)</th>
            <th>Total Pakai (ml)</th>
            <th>Jumlah Akhir (Botol)</th>
            <th>Sisa Akhir (ml)</th>
            <th>Terakhir Update</th>
        </tr>";

    $sql = mysqli_query($koneksi, "
        SELECT 
            p.nama_pestisida, p.tipe,
            COALESCE(SUM(CASE WHEN r.aktivitas='tambah_stok' THEN r.jumlah ELSE 0 END), 0) AS total_tambah_botol,
            COALESCE(SUM(CASE WHEN r.aktivitas='tambah_stok' THEN r.sisa_aktif_ml ELSE 0 END), 0) AS total_tambah_ml,
            COALESCE(SUM(CASE WHEN r.status='alokasi' THEN r.jumlah ELSE 0 END), 0) AS total_pakai_botol,
            COALESCE(SUM(CASE WHEN r.status='alokasi' THEN r.sisa_aktif_ml ELSE 0 END), 0) AS total_pakai_ml,            
            p.jumlah_botol AS stok_akhir_botol,
            p.sisa_ml AS stok_akhir_ml,
            p.tanggal_update
        FROM pestisida p
        LEFT JOIN riwayat_pestisida r ON p.id_pestisida = r.id_pestisida 
            AND MONTH(r.tanggal)=$bulan 
            AND YEAR(r.tanggal)=$tahun
        GROUP BY p.id_pestisida
        ORDER BY p.nama_pestisida ASC
    ") or die("Query pestisida error: " . mysqli_error($koneksi));

    $no = 1;
    while ($row = mysqli_fetch_assoc($sql)) {
        echo "<tr>
            <td align='center'>{$no}</td>
            <td>{$row['nama_pestisida']}</td>
            <td align='center'>".ucfirst($row['tipe'])."</td>
            <td align='right'>{$row['total_tambah_botol']}</td>
            <td align='right'>{$row['total_tambah_ml']}</td>
            <td align='right'>{$row['total_pakai_botol']}</td>
            <td align='right'>{$row['total_pakai_ml']}</td>
            <td align='right'>{$row['stok_akhir_botol']}</td>
            <td align='right'>{$row['stok_akhir_ml']}</td>
            <td align='center'>{$row['tanggal_update']}</td>
        </tr>";
        $no++;
    }
    echo "</table>";
}

// =============================================================
// ⚠️ PARAMETER SALAH
// =============================================================
else {
    echo "<h3>❌ Parameter jenis tidak ditemukan! (alat/pestisida)</h3>";
}
?>
