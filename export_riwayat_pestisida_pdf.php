<?php
require('fpdf/fpdf.php');
include "koneksi.php";

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

$pdf = new FPDF('L','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'Laporan Riwayat Pestisida',0,1,'C');
$pdf->SetFont('Arial','B',10);
$pdf->Cell(10,8,'No',1);
$pdf->Cell(30,8,'Tanggal',1);
$pdf->Cell(40,8,'Pestisida',1);
$pdf->Cell(35,8,'Customer',1);
$pdf->Cell(35,8,'Teknisi',1);
$pdf->Cell(20,8,'Jumlah',1);
$pdf->Cell(30,8,'Sisa Aktif',1);
$pdf->Cell(25,8,'Status',1);
$pdf->Cell(60,8,'Keterangan',1);
$pdf->Ln();

$pdf->SetFont('Arial','',9);
$no=1;
while($r=mysqli_fetch_assoc($q)){
  $pdf->Cell(10,8,$no,1);
  $pdf->Cell(30,8,$r['tanggal'],1);
  $pdf->Cell(40,8,$r['nama_pestisida'],1);
  $pdf->Cell(35,8,$r['nama_customer'],1);
  $pdf->Cell(35,8,$r['nama_teknisi'],1);
  $pdf->Cell(20,8,$r['jumlah'],1,0,'C');
  $pdf->Cell(30,8,$r['sisa_aktif_ml'],1,0,'C');
  $pdf->Cell(25,8,$r['status'],1,0,'C');
  $pdf->Cell(60,8,$r['keterangan'],1);
  $pdf->Ln();
  $no++;
}

$pdf->Output('I','Riwayat_Pestisida.pdf');
?>
