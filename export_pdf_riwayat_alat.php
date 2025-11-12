<?php
require('fpdf/fpdf.php');
include "koneksi.php";

// Ambil filter dari URL
$bulan  = isset($_GET['bulan']) ? (int)$_GET['bulan'] : 0;
$tahun  = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');
$status = isset($_GET['status']) ? trim($_GET['status']) : '';

// Buat kondisi filter
$where = [];
if ($bulan > 0) $where[] = "MONTH(COALESCE(r.tanggal_mulai, r.tanggal_selesai)) = $bulan";
if ($tahun > 0) $where[] = "YEAR(COALESCE(r.tanggal_mulai, r.tanggal_selesai)) = $tahun";
if ($status != '') $where[] = "(r.status = '" . mysqli_real_escape_string($koneksi, $status) . "' OR r.jenis = '" . mysqli_real_escape_string($koneksi, $status) . "')";

$whereSql = count($where) ? "WHERE " . implode(" AND ", $where) : "";

// Query data riwayat alat
$sql = mysqli_query($koneksi, "
    SELECT 
        r.id_riwayat,
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

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// Judul tanpa emoji
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, 'Laporan Riwayat Alat', 0, 1, 'C');

// Periode
$periodeText = "Semua bulan " . $tahun;
if ($bulan > 0) {
    $nama_bulan = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
        7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];
    $periodeText = $nama_bulan[$bulan] . " " . $tahun;
}
if ($status != '') $periodeText .= " - Status: " . ucfirst($status);

$pdf->SetFont('Arial', '', 11);
$pdf->Cell(0, 8, "Tanggal cetak: " . date('d-m-Y H:i'), 0, 1, 'C');
$pdf->Cell(0, 8, "Periode: $periodeText", 0, 1, 'C');
$pdf->Ln(3);

// Header tabel
$pdf->SetFont('Arial', 'B', 10);
$pdf->Cell(10, 8, 'No', 1, 0, 'C');
$pdf->Cell(30, 8, 'Tanggal Mulai', 1, 0, 'C');
$pdf->Cell(30, 8, 'Tanggal Selesai', 1, 0, 'C');
$pdf->Cell(25, 8, 'Kode Alat', 1, 0, 'C');
$pdf->Cell(45, 8, 'Nama Alat', 1, 0, 'C');
$pdf->Cell(25, 8, 'Lama (Hari)', 1, 0, 'C');
$pdf->Cell(75, 8, 'Keterangan', 1, 0, 'C');
$pdf->Cell(30, 8, 'Status', 1, 1, 'C');

// Isi tabel
$pdf->SetFont('Arial', '', 10);
$no = 1;

if (mysqli_num_rows($sql) > 0) {
    while ($row = mysqli_fetch_assoc($sql)) {
        $pdf->Cell(10, 8, $no++, 1, 0, 'C');
        $pdf->Cell(30, 8, $row['tanggal_mulai'] ?: '-', 1, 0, 'C');
        $pdf->Cell(30, 8, $row['tanggal_selesai'] ?: '-', 1, 0, 'C');
        $pdf->Cell(25, 8, $row['kode_alat'] ?: '-', 1, 0, 'C');
        $pdf->Cell(45, 8, $row['nama_alat'] ?: '-', 1, 0, 'L');
        $pdf->Cell(25, 8, $row['lama_perbaikan'] . " hari", 1, 0, 'C');
        $pdf->Cell(75, 8, substr($row['keterangan'] ?: '-', 0, 60), 1, 0, 'L');
        $pdf->Cell(30, 8, ucfirst($row['status'] ?: '-'), 1, 1, 'C');
    }
} else {
    $pdf->Cell(270, 10, 'Tidak ada data untuk filter ini.', 1, 1, 'C');
}

// Output PDF
$nama_file = "riwayat_alat_" . date('Ymd_His') . ".pdf";
$pdf->Output('D', $nama_file);
?>
