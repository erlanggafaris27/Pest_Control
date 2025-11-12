<?php
include "koneksi.php";
date_default_timezone_set('Asia/Jakarta');

// Jika dipanggil dengan id_alat => tampilkan form Masuk Perbaikan (POST menyimpan)
// Jika dipanggil dengan id => proses Selesai Perbaikan (id = id_riwayat)

// -------------------------
// CASE A: Form MASUK PERBAIKAN
// link: perbaikan_alat.php?id_alat=123
// -------------------------
if (isset($_GET['id_alat'])) {
    $id_alat = (int) $_GET['id_alat'];

    // ambil data alat
    $q = mysqli_query($koneksi, "SELECT * FROM alat WHERE id_alat = '$id_alat'");
    if (!$q || mysqli_num_rows($q) == 0) {
        echo "<script>alert('❌ Data alat tidak ditemukan!'); window.location='alat.php';</script>";
        exit;
    }
    $alat = mysqli_fetch_assoc($q);

    // simpan perbaikan
    if (isset($_POST['simpan'])) {
        $ket = mysqli_real_escape_string($koneksi, $_POST['keterangan'] ?? '');
        $tgl_masuk = date('Y-m-d');

        // insert riwayat perbaikan (status = perbaikan), lama_perbaikan = 0 pada awalnya
        mysqli_query($koneksi, "
        INSERT INTO riwayat_alat (id_alat, aktivitas, keterangan, status, tanggal_mulai, lama_perbaikan)
        VALUES ('$id_alat', 'perbaikan', '$ket', 'perbaikan', '$tgl_masuk', 0)        
        ") or die("Gagal insert riwayat: " . mysqli_error($koneksi));

        // update stok alat: kurangi jumlah aktif, tambah jumlah_perbaikan
        mysqli_query($koneksi, "
            UPDATE alat 
            SET jumlah_perbaikan = jumlah_perbaikan + 1,
                jumlah = GREATEST(jumlah - 1, 0),
                tanggal_update = CURDATE(),
                status = 'perbaikan'
            WHERE id_alat = '$id_alat'
        ");

        echo "<script>alert('🛠️ Alat berhasil dimasukkan ke daftar perbaikan.'); window.location='alat.php?view=perbaikan';</script>";
        exit;
    }

    // FORM HTML (keterangan tidak wajib)
    ?>
    <!DOCTYPE html>
    <html lang="id">
    <head>
      <meta charset="utf-8">
      <title>Masuk Perbaikan</title>
      <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body class="container mt-5">
      <div class="card shadow col-md-6 offset-md-3">
        <div class="card-header bg-warning fw-bold">🛠️ Form Masuk Perbaikan</div>
        <div class="card-body">
          <form method="post">
            <div class="mb-3">
              <label class="form-label">Kode Alat</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($alat['kode_alat']) ?>" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Nama Alat</label>
              <input type="text" class="form-control" value="<?= htmlspecialchars($alat['nama_alat']) ?>" readonly>
            </div>
            <div class="mb-3">
              <label class="form-label">Keterangan Perbaikan</label>
              <textarea name="keterangan" class="form-control" rows="3" placeholder="Keluhan / Keterangan (boleh dikosongkan)"></textarea>
            </div>
            <button type="submit" name="simpan" class="btn btn-primary">💾 Simpan</button>
            <a href="alat.php?view=perbaikan" class="btn btn-secondary">⬅ Kembali</a>
          </form>
        </div>
      </div>
    </body>
    </html>
    <?php
    exit;
}

// -------------------------
// CASE B: SELESAI PERBAIKAN
if (isset($_GET['id'])) {
  $id_riwayat = (int) $_GET['id'];

  // Ambil data perbaikan aktif
  $q = mysqli_query($koneksi, "SELECT * FROM riwayat_alat WHERE id_riwayat='$id_riwayat' AND status='perbaikan' LIMIT 1");
  if (!$q || mysqli_num_rows($q) == 0) {
      echo "<script>alert('❌ Data perbaikan tidak ditemukan atau status bukan perbaikan.'); window.location='alat.php?view=perbaikan';</script>";
      exit;
  }

  $r = mysqli_fetch_assoc($q);
  $id_alat = (int)$r['id_alat'];
  $tanggal_mulai = $r['tanggal_mulai'];

  // Gunakan tanggal sistem (saat diselesaikan)
  $today = date('Y-m-d');

  // Hitung lama perbaikan (selisih hari antara mulai & hari ini)
  $lama = 0;
  if (!empty($tanggal_mulai)) {
      $tgl1 = new DateTime($tanggal_mulai);
      $tgl2 = new DateTime($today);
      $lama = max($tgl1->diff($tgl2)->days, 0);
  }

  // 🔹 Update baris lama (status masih 'perbaikan')
  mysqli_query($koneksi, "
      UPDATE riwayat_alat
      SET tanggal_selesai = '$today',
          lama_perbaikan = '$lama'
      WHERE id_riwayat = '$id_riwayat'
  ") or die('Gagal update riwayat lama: ' . mysqli_error($koneksi));

  // 🔹 Tambahkan baris baru dengan status 'selesai_perbaikan'
  mysqli_query($koneksi, "
  INSERT INTO riwayat_alat 
  (id_alat, aktivitas, tanggal_mulai, tanggal_selesai, lama_perbaikan, status, keterangan)
  VALUES 
  ('$id_alat', 'selesai_perbaikan', '$tanggal_mulai', '$today', '$lama', 'selesai_perbaikan', 'Perbaikan selesai dan alat dikembalikan')  
  ") or die('Gagal insert riwayat baru: ' . mysqli_error($koneksi));

  // 🔹 Update stok alat
  mysqli_query($koneksi, "
      UPDATE alat
      SET jumlah_perbaikan = GREATEST(jumlah_perbaikan - 1, 0),
          jumlah = jumlah + 1,
          tanggal_update = CURDATE(),
          status = 'aktif'
      WHERE id_alat = '$id_alat'
  ");

  echo "<script>alert('✅ Perbaikan selesai — lama perbaikan: $lama hari.'); window.location='alat.php?view=perbaikan';</script>";
  exit;
}


// Default: tidak ada parameter valid
echo "<script>alert('Parameter tidak lengkap.'); window.location='alat.php';</script>";
exit;
