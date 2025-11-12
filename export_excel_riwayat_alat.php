<?php
include "koneksi.php";

$bulan  = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$tahun  = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

$where = [];
if ($bulan > 0) $where[] = "MONTH(COALESCE(r.tanggal_mulai, r.tanggal_selesai)) = $bulan";
if ($tahun > 0) $where[] = "YEAR(COALESCE(r.tanggal_mulai, r.tanggal_selesai)) = $tahun";
if ($status != '') $where[] = "(r.status = '" . mysqli_real_escape_string($koneksi, $status) . "' OR r.jenis = '" . mysqli_real_escape_string($koneksi, $status) . "')";

$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

$sql = mysqli_query($koneksi, "
    SELECT 
        a.kode_alat,
        a.nama_alat,
        r.tanggal_mulai,
        r.tanggal_selesai,
        r.lama_perbaikan,
        r.status,
        r.keterangan
    FROM riwayat_alat r
    LEFT JOIN alat a ON r.id_alat = a.id_alat
    $whereSql
    ORDER BY COALESCE(r.tanggal_mulai, r.tanggal_selesai) DESC, r.id_riwayat DESC
");

$filename = "riwayat_alat_" . date('Ymd_His') . ".xls";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=\"$filename\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "No\tTanggal Mulai\tTanggal Selesai\tKode Alat\tNama Alat\tLama (Hari)\tKeterangan\tStatus\n";

$no = 1;
if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        echo $no++ . "\t" .
            ($row['tanggal_mulai'] ?: '-') . "\t" .
            ($row['tanggal_selesai'] ?: '-') . "\t" .
            ($row['kode_alat'] ?: '-') . "\t" .
            ($row['nama_alat'] ?: '-') . "\t" .
            ($row['lama_perbaikan'] ?: '0') . "\t" .
            ($row['keterangan'] ?: '-') . "\t" .
            ucfirst($row['status'] ?: '-') . "\n";
    }
} else {
    echo "Tidak ada data untuk filter ini\n";
}
exit;
?>
