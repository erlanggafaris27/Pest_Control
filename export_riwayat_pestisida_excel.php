<?php
include "koneksi.php";
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=riwayat_pestisida.xls");

$bulan = $_GET['bulan'] ?? 'Semua';
$tahun = $_GET['tahun'] ?? date('Y');
$status = $_GET['status'] ?? 'Semua';
$customer = $_GET['customer'] ?? 'Semua';

$where = [];
if ($bulan !== 'Semua') $where[] = "MONTH(r.tanggal) = '$bulan'";
if ($tahun !== 'Semua') $where[] = "YEAR(r.tanggal) = '$tahun'";
if ($status !== 'Semua') $where[] = "r.status = '$status'";
if ($customer !== 'Semua') $where[] = "r.nama_customer = '$customer'";

$whereSQL = $where ? 'WHERE ' . implode(' AND ', $where) : '';

$q = mysqli_query($koneksi, "
  SELECT r.*, p.nama_pestisida
  FROM riwayat_pestisida r
  LEFT JOIN pestisida p ON p.id_pestisida = r.id_pestisida
  $whereSQL
  ORDER BY r.tanggal DESC
");
echo "<table border='1'>
<tr><th>No</th><th>Tanggal</th><th>Nama Pestisida</th><th>Customer</th><th>Teknisi</th><th>Jumlah</th><th>Sisa Aktif (ml)</th><th>Status</th><th>Keterangan</th></tr>";
$no=1;
while($r=mysqli_fetch_assoc($q)){
  echo "<tr>
  <td>$no</td>
  <td>{$r['tanggal']}</td>
  <td>{$r['nama_pestisida']}</td>
  <td>{$r['nama_customer']}</td>
  <td>{$r['nama_teknisi']}</td>
  <td>{$r['jumlah']}</td>
  <td>{$r['sisa_aktif_ml']}</td>
  <td>{$r['status']}</td>
  <td>{$r['keterangan']}</td>
  </tr>";
  $no++;
}
echo "</table>";
?>
