<?php
ob_start();
require('fpdf/fpdf.php');
include "koneksi.php";

date_default_timezone_set('Asia/Jakarta');

$jenis = $_GET['jenis'] ?? '';
$bulan = (int)($_GET['bulan'] ?? 0);
$tahun = (int)($_GET['tahun'] ?? date('Y'));

$pdf = new FPDF('L', 'mm', 'A4');
$pdf->AddPage();

// ==============================
// JUDUL
// ==============================
$pdf->SetFont('Arial', 'B', 14);
$pdf->Cell(0, 10, "LAPORAN BULANAN " . strtoupper($jenis) . " - $tahun", 0, 1, 'C');
$pdf->Ln(5);


// ==================================================
// 🧰 LAPORAN ALAT
// ==================================================
if ($jenis == 'alat') {

    $headers = [
        ['No', 10],
        ['Kode Alat', 25],
        ['Nama Alat', 45],
        ['Dipakai (x)', 25],
        ['Perbaikan (x)', 28],
        ['Lama Perbaikan (hari)', 40],
        ['Kondisi Akhir', 35],
        ['Update Terakhir', 35],
    ];

    $pdf->SetFont('Arial','B',10);
    foreach ($headers as $h) {
        $pdf->Cell($h[1], 10, $h[0], 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->SetFont('Arial','',9);

    $sql = mysqli_query($koneksi, "
        SELECT 
            a.id_alat,
            a.kode_alat,
            a.nama_alat,

            -- total dipakai
            (SELECT COUNT(*) 
             FROM riwayat_alat r1
             WHERE r1.id_alat = a.id_alat
             AND r1.status='dipakai'
             AND MONTH(r1.tanggal_mulai)=$bulan
             AND YEAR(r1.tanggal_mulai)=$tahun
            ) AS total_pakai,

            -- total perbaikan
            (SELECT COUNT(*) 
             FROM riwayat_alat r2
             WHERE r2.id_alat = a.id_alat
             AND r2.status='perbaikan'
             AND MONTH(r2.tanggal_mulai)=$bulan
             AND YEAR(r2.tanggal_mulai)=$tahun
            ) AS total_perbaikan,

            -- lama perbaikan
            (SELECT SUM(DATEDIFF(r3.tanggal_selesai, r3.tanggal_mulai))
             FROM riwayat_alat r3
             WHERE r3.id_alat = a.id_alat
             AND r3.aktivitas='perbaikan'
             AND r3.tanggal_selesai IS NOT NULL
             AND MONTH(r3.tanggal_mulai)=$bulan
             AND YEAR(r3.tanggal_mulai)=$tahun
            ) AS total_hari_perbaikan,

            a.status AS kondisi_akhir,
            a.tanggal_update

        FROM alat a
        ORDER BY a.nama_alat ASC
    ") or die(mysqli_error($koneksi));

    $no = 1;
    while ($row = mysqli_fetch_assoc($sql)) {
        $pdf->Cell(10, 8, $no++, 1);
        $pdf->Cell(25, 8, $row['kode_alat'], 1);
        $pdf->Cell(45, 8, $row['nama_alat'], 1);
        $pdf->Cell(25, 8, $row['total_pakai'], 1, 0, 'C');
        $pdf->Cell(28, 8, $row['total_perbaikan'], 1, 0, 'C');
        $pdf->Cell(40, 8, $row['total_hari_perbaikan'] ?? 0, 1, 0, 'C');
        $pdf->Cell(35, 8, $row['kondisi_akhir'], 1, 0, 'C');
        $pdf->Cell(35, 8, $row['tanggal_update'], 1, 0, 'C');
        $pdf->Ln();
    }

}

// ==================================================
// 🌿 LAPORAN PESTISIDA
// ==================================================
elseif ($jenis == 'pestisida') {

    $headers = [
        ['No', 10],
        ['Nama Pestisida', 40],
        ['Tipe', 25],
        ['Total Tambah (Botol)', 30],
        ['Total Tambah (ml)', 30],
        ['Total Pakai (Botol)', 30],
        ['Total Pakai (ml)', 30],
        ['Jumlah Akhir (Botol)', 30],
        ['Sisa Akhir (ml)', 30],
        ['Update Terakhir', 30],
    ];

    $pdf->SetFont('Arial','B',10);
    foreach ($headers as $h) {
        $pdf->Cell($h[1], 10, $h[0], 1, 0, 'C');
    }
    $pdf->Ln();

    $pdf->SetFont('Arial','',9);

    $sql = mysqli_query($koneksi, "
        SELECT 
            p.nama_pestisida, p.tipe,
            SUM(CASE WHEN r.aktivitas='tambah_stok' THEN r.jumlah ELSE 0 END) AS total_tambah_botol,
            SUM(CASE WHEN r.aktivitas='tambah_stok' THEN r.sisa_aktif_ml ELSE 0 END) AS total_tambah_ml,
            SUM(CASE WHEN r.status='alokasi' THEN r.jumlah ELSE 0 END) AS total_pakai_botol,
            SUM(CASE WHEN r.status='alokasi' THEN r.sisa_aktif_ml ELSE 0 END) AS total_pakai_ml,
            p.jumlah_botol AS stok_akhir_botol,
            p.sisa_ml AS stok_akhir_ml,
            p.tanggal_update
        FROM pestisida p
        LEFT JOIN riwayat_pestisida r ON p.id_pestisida = r.id_pestisida
            AND MONTH(r.tanggal)=$bulan
            AND YEAR(r.tanggal)=$tahun
        GROUP BY p.id_pestisida
        ORDER BY p.nama_pestisida ASC
    ");

    $no = 1;
    while ($row = mysqli_fetch_assoc($sql)) {
        $pdf->Cell(10, 8, $no++, 1);
        $pdf->Cell(40, 8, $row['nama_pestisida'], 1);
        $pdf->Cell(25, 8, ucfirst($row['tipe']), 1);
        $pdf->Cell(30, 8, $row['total_tambah_botol'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['total_tambah_ml'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['total_pakai_botol'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['total_pakai_ml'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['stok_akhir_botol'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['stok_akhir_ml'], 1, 0, 'R');
        $pdf->Cell(30, 8, $row['tanggal_update'], 1);
        $pdf->Ln();
    }

}

// ==================================================
ob_end_clean();
$pdf->Output('I', "Laporan_{$jenis}_{$bulan}-{$tahun}.pdf");
