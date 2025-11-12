-- MariaDB dump 10.19  Distrib 10.4.28-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: pest_control
-- ------------------------------------------------------
-- Server version	10.4.28-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alat`
--

DROP TABLE IF EXISTS `alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `alat` (
  `id_alat` int(11) NOT NULL AUTO_INCREMENT,
  `kode_alat` varchar(20) DEFAULT NULL,
  `nama_alat` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT 0,
  `status` enum('baik','rusak','perbaikan') DEFAULT 'baik',
  `jumlah_perbaikan` int(11) DEFAULT 0,
  `jumlah_pakai` int(11) DEFAULT 0,
  `tanggal_update` date DEFAULT curdate(),
  PRIMARY KEY (`id_alat`),
  UNIQUE KEY `kode_alat` (`kode_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=20 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alat`
--

LOCK TABLES `alat` WRITE;
/*!40000 ALTER TABLE `alat` DISABLE KEYS */;
INSERT INTO `alat` VALUES (15,'A004','ColdFogg',1,'',1,1,'2025-11-11'),(16,'A005','ColdFogg',1,'',0,0,'2025-11-11'),(17,'A001','Fogging',2,'',0,0,'2025-10-29'),(18,'F002','Fogging',1,'',0,0,'2025-11-11'),(19,'A003','Spraying',0,'',0,1,'2025-11-11');
/*!40000 ALTER TABLE `alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pakai_alat`
--

DROP TABLE IF EXISTS `pakai_alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pakai_alat` (
  `id_pakai` int(11) NOT NULL AUTO_INCREMENT,
  `id_alat` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah_pakai` int(11) DEFAULT NULL,
  `nama_teknisi` varchar(100) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `jenis` varchar(50) DEFAULT NULL,
  `tipe_riwayat` enum('pakai','perbaikan','rusak','selesai_perbaikan','dikembalikan') DEFAULT NULL,
  `lama_perbaikan` int(11) DEFAULT 0,
  `tanggal_selesai` date DEFAULT NULL,
  PRIMARY KEY (`id_pakai`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `pakai_alat_ibfk_1` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id_alat`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pakai_alat`
--

LOCK TABLES `pakai_alat` WRITE;
/*!40000 ALTER TABLE `pakai_alat` DISABLE KEYS */;
/*!40000 ALTER TABLE `pakai_alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pakai_pestisida`
--

DROP TABLE IF EXISTS `pakai_pestisida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pakai_pestisida` (
  `id_pakai` int(11) NOT NULL AUTO_INCREMENT,
  `id_pestisida` int(11) NOT NULL,
  `nama_customer` varchar(100) DEFAULT NULL,
  `nama_teknisi` varchar(100) DEFAULT NULL,
  `tanggal` date NOT NULL,
  `jumlah_pakai` int(11) NOT NULL,
  `satuan` enum('ml','botol','pcs') DEFAULT 'ml',
  `keterangan` varchar(255) DEFAULT NULL,
  `sisa_aktif_ml` int(11) DEFAULT 0,
  PRIMARY KEY (`id_pakai`),
  KEY `pakai_pestisida_ibfk_1` (`id_pestisida`),
  CONSTRAINT `pakai_pestisida_ibfk_1` FOREIGN KEY (`id_pestisida`) REFERENCES `pestisida` (`id_pestisida`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=93 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pakai_pestisida`
--

LOCK TABLES `pakai_pestisida` WRITE;
/*!40000 ALTER TABLE `pakai_pestisida` DISABLE KEYS */;
INSERT INTO `pakai_pestisida` VALUES (75,23,'Ridwan','Ujang','2025-11-10',0,'botol','',0),(89,28,'Ridwan','Agus','2025-11-10',4,'botol','',4000),(90,28,'Siti','Asep','2025-11-11',2,'botol','',2500),(91,27,'Siti','Sandi','2025-11-11',4,'botol','',4800),(92,27,'Ridwan','Udin','2025-11-11',5,'botol','',6000);
/*!40000 ALTER TABLE `pakai_pestisida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemakaian_alat`
--

DROP TABLE IF EXISTS `pemakaian_alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemakaian_alat` (
  `id_pemakaian` int(11) NOT NULL AUTO_INCREMENT,
  `id_alat` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pemakaian`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `pemakaian_alat_ibfk_1` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id_alat`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemakaian_alat`
--

LOCK TABLES `pemakaian_alat` WRITE;
/*!40000 ALTER TABLE `pemakaian_alat` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemakaian_alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pemakaian_pestisida`
--

DROP TABLE IF EXISTS `pemakaian_pestisida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pemakaian_pestisida` (
  `id_pemakaian` int(11) NOT NULL AUTO_INCREMENT,
  `id_pestisida` int(11) DEFAULT NULL,
  `tanggal` date DEFAULT NULL,
  `jumlah_ml` int(11) DEFAULT NULL,
  PRIMARY KEY (`id_pemakaian`),
  KEY `id_pestisida` (`id_pestisida`),
  CONSTRAINT `pemakaian_pestisida_ibfk_1` FOREIGN KEY (`id_pestisida`) REFERENCES `pestisida` (`id_pestisida`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pemakaian_pestisida`
--

LOCK TABLES `pemakaian_pestisida` WRITE;
/*!40000 ALTER TABLE `pemakaian_pestisida` DISABLE KEYS */;
/*!40000 ALTER TABLE `pemakaian_pestisida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pestisida`
--

DROP TABLE IF EXISTS `pestisida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `pestisida` (
  `id_pestisida` int(11) NOT NULL AUTO_INCREMENT,
  `nama_customer` varchar(100) DEFAULT NULL,
  `nama_teknisi` varchar(100) DEFAULT NULL,
  `jenis_kontrol` varchar(50) DEFAULT NULL,
  `target` varchar(100) DEFAULT NULL,
  `kategori` enum('insect control','rodent') NOT NULL,
  `tipe` enum('non chemical','pestisida') NOT NULL,
  `nama_pestisida` varchar(100) DEFAULT NULL,
  `metode` varchar(100) DEFAULT NULL,
  `nama` varchar(100) NOT NULL,
  `jumlah_botol` int(11) DEFAULT 0,
  `isi_per_botol_ml` int(11) DEFAULT 0,
  `sisa_ml` int(11) DEFAULT 0,
  `tanggal` date DEFAULT curdate(),
  `tanggal_update` date DEFAULT curdate(),
  PRIMARY KEY (`id_pestisida`)
) ENGINE=InnoDB AUTO_INCREMENT=29 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pestisida`
--

LOCK TABLES `pestisida` WRITE;
/*!40000 ALTER TABLE `pestisida` DISABLE KEYS */;
INSERT INTO `pestisida` VALUES (22,NULL,NULL,NULL,NULL,'insect control','pestisida','Fipronil',NULL,'',45,1500,31500,'2025-11-02','2025-11-10'),(23,NULL,NULL,NULL,NULL,'insect control','non chemical','Rat box',NULL,'',5,0,0,'2025-11-02','2025-11-10'),(24,NULL,NULL,NULL,NULL,'insect control','pestisida','Lamda',NULL,'',10,800,8000,'2025-11-02','2025-11-11'),(27,NULL,NULL,NULL,NULL,'insect control','pestisida','Cypermethrin',NULL,'',50,1200,60000,'2025-11-03','2025-11-11'),(28,NULL,NULL,NULL,NULL,'insect control','pestisida','Agita',NULL,'',25,1000,25000,'2025-11-04','2025-11-11');
/*!40000 ALTER TABLE `pestisida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_alat`
--

DROP TABLE IF EXISTS `riwayat_alat`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_alat` (
  `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
  `id_alat` int(11) DEFAULT NULL,
  `aktivitas` varchar(50) DEFAULT NULL,
  `jenis` enum('pakai','perbaikan','rusak','selesai_perbaikan','dikembalikan') DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `nama_teknisi` varchar(100) DEFAULT NULL,
  `keterangan` varchar(255) DEFAULT NULL,
  `tanggal_mulai` date DEFAULT NULL,
  `tanggal_masuk` date DEFAULT NULL,
  `tanggal_selesai` date DEFAULT NULL,
  `lama_perbaikan` int(11) DEFAULT 0,
  `status` enum('ditambahkan','dipakai','perbaikan','selesai','selesai_perbaikan') DEFAULT 'dipakai',
  `tanggal_update` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id_riwayat`),
  KEY `id_alat` (`id_alat`),
  CONSTRAINT `riwayat_alat_ibfk_1` FOREIGN KEY (`id_alat`) REFERENCES `alat` (`id_alat`)
) ENGINE=InnoDB AUTO_INCREMENT=179 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_alat`
--

LOCK TABLES `riwayat_alat` WRITE;
/*!40000 ALTER TABLE `riwayat_alat` DISABLE KEYS */;
INSERT INTO `riwayat_alat` VALUES (52,15,NULL,NULL,NULL,'Admin','Penambahan alat baru','2025-10-21',NULL,NULL,0,'ditambahkan','2025-10-21 16:58:10'),(53,16,NULL,NULL,NULL,'Admin','Penambahan alat baru','2025-10-22',NULL,NULL,0,'ditambahkan','2025-10-21 17:25:34'),(103,17,NULL,NULL,NULL,'Admin','Penambahan alat baru','2025-10-28',NULL,NULL,0,'ditambahkan','2025-10-28 15:03:44'),(119,18,NULL,NULL,NULL,'Admin','Penambahan alat baru','2025-10-29',NULL,NULL,0,'ditambahkan','2025-10-29 13:18:23'),(120,19,NULL,NULL,NULL,'Admin','Penambahan alat baru','2025-10-29',NULL,NULL,0,'ditambahkan','2025-10-29 13:18:43'),(130,18,NULL,NULL,NULL,NULL,'','2025-10-29',NULL,'2025-10-30',1,'perbaikan','2025-10-30 13:14:48'),(162,18,NULL,NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-10-29',NULL,'2025-10-30',1,'selesai_perbaikan','2025-10-30 13:14:48'),(163,15,NULL,'perbaikan',NULL,NULL,'','2025-10-29',NULL,'2025-10-30',1,'perbaikan','2025-10-30 13:53:33'),(164,15,NULL,NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-10-29',NULL,'2025-10-30',1,'selesai_perbaikan','2025-10-30 13:53:33'),(165,18,NULL,NULL,NULL,NULL,'','2025-10-30',NULL,'2025-11-03',4,'perbaikan','2025-11-03 08:35:26'),(166,18,NULL,NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-10-30',NULL,'2025-11-03',4,'selesai_perbaikan','2025-11-03 08:35:26'),(167,19,NULL,NULL,NULL,'Ujang','','2025-11-03',NULL,NULL,0,'dipakai','2025-11-03 10:20:06'),(168,15,'pakai_alat',NULL,NULL,'Agung','','2025-11-11',NULL,NULL,0,'dipakai','2025-11-11 13:42:04'),(169,19,NULL,NULL,NULL,'Ujang','Alat dikembalikan ke gudang. ()','2025-11-11',NULL,'2025-11-11',0,'selesai','2025-11-11 13:42:59'),(170,18,'perbaikan','perbaikan',NULL,NULL,'','2025-11-08',NULL,'2025-11-11',3,'perbaikan','2025-11-11 13:44:26'),(171,16,'perbaikan','perbaikan',NULL,NULL,'','2025-11-10',NULL,'2025-11-11',1,'perbaikan','2025-11-11 13:45:20'),(172,18,'selesai_perbaikan',NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-11-08',NULL,'2025-11-11',3,'selesai_perbaikan','2025-11-11 13:44:26'),(173,16,'selesai_perbaikan',NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-11-10',NULL,'2025-11-11',1,'selesai_perbaikan','2025-11-11 13:45:20'),(174,16,'perbaikan','perbaikan',NULL,NULL,'','2025-11-09',NULL,'2025-11-11',2,'perbaikan','2025-11-11 13:46:18'),(175,16,'selesai_perbaikan',NULL,NULL,NULL,'Perbaikan selesai dan alat dikembalikan','2025-11-09',NULL,'2025-11-11',2,'selesai_perbaikan','2025-11-11 13:46:18'),(176,15,NULL,NULL,NULL,'Agung','Alat dikembalikan ke gudang. ()','2025-11-11',NULL,'2025-11-11',0,'selesai','2025-11-11 13:58:36'),(177,15,'pakai_alat',NULL,NULL,'Rio','','2025-11-11',NULL,NULL,0,'dipakai','2025-11-11 13:58:45'),(178,19,'pakai_alat',NULL,NULL,'Wahyu','','2025-11-11',NULL,NULL,0,'dipakai','2025-11-11 15:30:02');
/*!40000 ALTER TABLE `riwayat_alat` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `riwayat_pestisida`
--

DROP TABLE IF EXISTS `riwayat_pestisida`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `riwayat_pestisida` (
  `id_riwayat` int(11) NOT NULL AUTO_INCREMENT,
  `id_pestisida` int(11) DEFAULT NULL,
  `jenis` enum('chemical','non-chemical') DEFAULT NULL,
  `aktivitas` enum('tambah','tambah_stok','pakai','selesai_pakai','hapus') DEFAULT 'tambah',
  `nama_teknisi` varchar(100) DEFAULT NULL,
  `nama_customer` varchar(100) DEFAULT NULL,
  `jumlah` int(11) DEFAULT NULL,
  `sisa_aktif_ml` float DEFAULT 0,
  `satuan` varchar(50) DEFAULT 'botol',
  `tanggal` date DEFAULT curdate(),
  `keterangan` text DEFAULT NULL,
  `status` varchar(50) DEFAULT NULL,
  PRIMARY KEY (`id_riwayat`),
  KEY `id_pestisida` (`id_pestisida`),
  CONSTRAINT `riwayat_pestisida_ibfk_1` FOREIGN KEY (`id_pestisida`) REFERENCES `pestisida` (`id_pestisida`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=361 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `riwayat_pestisida`
--

LOCK TABLES `riwayat_pestisida` WRITE;
/*!40000 ALTER TABLE `riwayat_pestisida` DISABLE KEYS */;
INSERT INTO `riwayat_pestisida` VALUES (236,22,'','','-','-',30,45000,'botol','2025-11-02','Menambahkan pestisida baru Fipronil sebanyak 30 botol/pcs dengan isi 1500 ml per botol.','Pestisida Baru'),(237,22,'','tambah_stok','-','-',10,60000,'botol','2025-11-02','Menambah stok pestisida Fipronil sebanyak 10 botol (total sekarang 40 botol).','Stok Ditambah'),(238,22,'','tambah_stok','-','-',5,7500,'botol','2025-11-02','Menambah stok pestisida Fipronil sebanyak 5 botol (+7500 ml). Total stok sekarang 45 botol.','Stok Ditambah'),(239,22,'chemical','','-','-',40,60000,'botol','2025-11-02','Data pestisida Fipronil telah diperbarui. Jumlah sekarang 40 botol/pcs dengan isi 1500 ml per botol (total aktif 60000 ml).','Update Pestisida'),(240,22,'','','Agus','Ahmad',10,15000,'botol','2025-11-02','Alokasi 10 botol Fipronil untuk Ahmad oleh Agus','Dialokasikan'),(241,22,'','','Agus','Ahmad',15,22500,'botol','2025-11-02','Data pemakaian pestisida Fipronil oleh Agus untuk Ahmad diperbarui dari 10 menjadi 15 botol.','Pemakaian Diperbarui'),(242,22,'','','Agus','Ahmad',20,30000,'botol','2025-11-02','Data pemakaian pestisida Fipronil oleh Agus untuk Ahmad diperbarui dari 15 menjadi 20 botol.','Pemakaian Diperbarui'),(243,22,'','','Agus','Ahmad',10,15000,'botol','2025-11-02','Data pemakaian pestisida Fipronil oleh Agus untuk Ahmad diperbarui dari 20 menjadi 10 botol.','Pemakaian Diperbarui'),(245,23,'','','-','-',40,0,'botol','2025-11-02','Menambahkan pestisida baru Rat box sebanyak 40 botol/pcs.','Pestisida Baru'),(247,24,'','','-','-',40,32000,'botol','2025-11-02','Menambahkan pestisida baru Lamda sebanyak 40 botol/pcs dengan isi 800 ml per botol.','Pestisida Baru'),(248,23,'','','Asep','Ahmad',4,0,'pcs','2025-11-02','Alokasi 4 pcs Rat box untuk Ahmad oleh Asep','Dialokasikan'),(249,24,'','','Asep','Ahmad',3,2400,'botol','2025-11-02','Alokasi 3 botol Lamda untuk Ahmad oleh Asep','Dialokasikan'),(250,22,'','','Rudi','Ridwan',4,6000,'botol','2025-11-02','Alokasi 4 botol Fipronil untuk Ridwan oleh Rudi','Dialokasikan'),(253,23,NULL,'','Asep','Ahmad',4,0,'botol','2025-11-03','Penghapusan data pemakaian Rat box oleh Asep untuk Ahmad. Stok dikembalikan ke gudang.','Dihapus (Kembali ke Stok)'),(258,27,'','','-','-',50,60000,'botol','2025-11-03','Menambahkan pestisida baru Cypermethrin sebanyak 50 botol/pcs dengan isi 1200 ml per botol.','Pestisida Baru'),(259,24,'','tambah_stok','-','-',3,2400,'botol','2025-11-03','Menambah stok pestisida Lamda sebanyak 3 botol (+2400 ml). Total stok sekarang 40 botol.','Stok Ditambah'),(260,23,'','tambah_stok','-','-',10,0,'botol','2025-11-03','Menambah stok pestisida Rat box sebanyak 10 botol (+0 ml). Total stok sekarang 50 botol.','stok ditambah'),(263,22,'','','Agus','Ahmad',4,6000,'botol','2025-11-04','Alokasi 4 botol Fipronil untuk Ahmad oleh Agus','alokasi'),(264,28,'','tambah_stok','-','-',30,30000,'botol','2025-11-04','Menambahkan pestisida baru Agita sebanyak 30 botol/pcs dengan isi 1000 ml per botol.','stok ditambah'),(265,28,'','','Ujang','Ahmad',5,5000,'botol','2025-11-04','Alokasi 5 botol Agita untuk Ahmad oleh Ujang','alokasi'),(275,28,'chemical','','-','-',25,25000,'botol','2025-11-05','Data pestisida Agita telah diperbarui. Jumlah sekarang 25 botol/pcs dengan isi 1000 ml per botol (total aktif 25000 ml).','Update Pestisida'),(276,28,'','tambah_stok','-','-',5,5000,'botol','2025-11-05','Menambah stok pestisida Agita sebanyak 5 botol (+5000 ml). Total stok sekarang 30 botol.','stok ditambah'),(281,23,'','','Ujang','Ridwan',5,0,'botol','2025-11-10','Alokasi 5 botol Rat box untuk Ridwan oleh Ujang','alokasi'),(282,23,NULL,'','Ujang','Ridwan',2,0,'botol','2025-11-10','Pemakaian Rat box sebanyak 2 botol/pcs oleh Ujang untuk Ridwan. Sisa aktif 0 ml.','Dipakai'),(283,23,NULL,'','Ujang','Ridwan',3,0,'botol','2025-11-10','Pemakaian Rat box sebanyak 3 botol/pcs oleh Ujang untuk Ridwan. Sisa aktif 0 ml.','Dipakai'),(284,22,'','','Agus','Ahmad',0,0,'botol','2025-11-10','Data pemakaian pestisida Fipronil oleh teknisi Agus untuk customer Ahmad dihapus. Stok sebanyak 0 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(285,22,'','','Agus','Ahmad',11,0,'botol','2025-11-10','Data pemakaian pestisida Fipronil oleh teknisi Agus untuk customer Ahmad dihapus. Stok sebanyak 11 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(286,28,'','','Ujang','Ahmad',0,0,'botol','2025-11-10','Data pemakaian pestisida Agita oleh teknisi Ujang untuk customer Ahmad dihapus. Stok sebanyak 0 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(287,24,'','','Asep','Ahmad',0,0,'botol','2025-11-10','Data pemakaian pestisida Lamda oleh teknisi Asep untuk customer Ahmad dihapus. Stok sebanyak 0 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(288,22,NULL,'','Rudi','Ridwan',2,3000,'botol','2025-11-10','Pakai 2 botol Fipronil. Sisa sekarang: 3000 ml.','Dipakai'),(289,22,NULL,'','Rudi','Ridwan',2,0,'botol','2025-11-10','Pakai 2 botol Fipronil. Sisa sekarang: 0 ml.','Dipakai'),(290,28,'','','Bayu','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Bayu','alokasi'),(291,28,NULL,'','Bayu','Ridwan',2,3000,'botol','2025-11-10','Pakai 2 botol Agita. Sisa sekarang: 3000 ml.','Dipakai'),(292,28,NULL,'','Bayu','Ridwan',1,2000,'botol','2025-11-10','Pakai 1 botol Agita. Sisa sekarang: 2000 ml.','Dipakai'),(293,28,NULL,'','Bayu','Ridwan',1000,1000,'ml','2025-11-10','Pakai 1000 ml Agita. Sisa sekarang: 1000 ml.','Dipakai'),(294,28,NULL,'','Bayu','Ridwan',1,0,'botol','2025-11-10','Pakai 1 botol Agita. Sisa sekarang: 0 ml.','Dipakai'),(295,28,'','','Asep','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Asep','alokasi'),(296,28,NULL,'','Asep','Ridwan',1,4000,'botol','2025-11-10','Pakai 1 botol Agita. Sisa sekarang: 4000 ml.','Dipakai'),(297,28,NULL,'','Asep','Ridwan',1000,3000,'ml','2025-11-10','Pakai 1000 ml Agita. Sisa sekarang: 3000 ml.','Dipakai'),(298,28,NULL,'','Asep','Ridwan',1,0,'botol','2025-11-10','Pakai 1 botol Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(299,28,NULL,'','Asep','Ridwan',1000,0,'ml','2025-11-10','Pakai 1000 ml Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(300,28,NULL,'','Asep','Ridwan',1,0,'botol','2025-11-10','Pakai 1 botol Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(301,28,'','','Bayu','',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk  oleh Bayu','alokasi'),(302,23,'','','Agus','Ridwan',5,0,'botol','2025-11-10','Alokasi 5 botol Rat box untuk Ridwan oleh Agus','alokasi'),(303,28,NULL,'','Bayu','',1500,0,'ml','2025-11-10','Pakai 1500 ml Agita oleh Bayu untuk .','Pakai Lapangan'),(304,28,NULL,'','Bayu','',0,0,'ml','2025-11-10','Pakai  ml Agita oleh Bayu untuk .','Pakai Lapangan'),(305,28,'','','Ujang','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Ujang','alokasi'),(306,28,'chemical','','-','-',10,10000,'botol','2025-11-10','Data pestisida Agita telah diperbarui. Jumlah sekarang 10 botol/pcs dengan isi 1000 ml per botol (total aktif 10000 ml).','Update Pestisida'),(307,28,NULL,'','Ujang','Ridwan',0,0,'ml','2025-11-10','Pakai  ml Agita oleh Ujang untuk Ridwan.','Pakai Lapangan'),(308,28,'','','Rudi','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Rudi','alokasi'),(309,28,NULL,'','Rudi','Ridwan',0,0,'ml','2025-11-10','Pakai  ml Agita oleh Rudi untuk Ridwan.','Pakai Lapangan'),(310,28,'','tambah_stok','-','-',10,10000,'botol','2025-11-10','Menambah stok pestisida Agita sebanyak 10 botol (+10000 ml). Total stok sekarang 15 botol.','stok ditambah'),(311,28,'','','Asep','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Asep','alokasi'),(312,28,NULL,'','Asep','Ridwan',1,1000,'botol','2025-11-10','Pakai 1 botol (1000 ml) Agita oleh Asep.','Pakai Lapangan'),(313,28,NULL,'','Asep','Ridwan',1000,0,'ml','2025-11-10','Pakai 1000 ml Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(314,28,NULL,'','Asep','Ridwan',1500,0,'ml','2025-11-10','Pakai 1500 ml Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(315,28,NULL,'','Asep','Ridwan',1,1000,'botol','2025-11-10','Pakai 1 botol (1000 ml) Agita oleh Asep.','Pakai Lapangan'),(316,28,NULL,'','Asep','Ridwan',500,0,'ml','2025-11-10','Pakai 500 ml Agita oleh Asep untuk Ridwan.','Pakai Lapangan'),(317,23,NULL,'','Agus','Ridwan',1,0,'botol','2025-11-10','Pakai 1 botol (0 ml) Rat box oleh Agus.','Pakai Lapangan'),(318,28,'','','Sandi','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Sandi','alokasi'),(319,28,NULL,'','Sandi','Ridwan',1,0,'ml','2025-11-10','Pakai 1000 ml (1 botol + 0 ml) Agita oleh Sandi.','Pakai Lapangan'),(320,28,NULL,'','Sandi','Ridwan',1,1000,'botol','2025-11-10','Pakai 1 botol (1000 ml) Agita oleh Sandi.','Pakai Lapangan'),(321,28,NULL,'','Sandi','Ridwan',1,500,'ml','2025-11-10','Pakai 1500 ml (1 botol + 500 ml) Agita oleh Sandi.','Pakai Lapangan'),(322,28,NULL,'','Sandi','Ridwan',2,0,'ml','2025-11-10','Pakai 1500 ml (1.5 botol) Agita oleh Sandi.','Pakai Lapangan'),(323,28,'','','Agus','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Agus','alokasi'),(324,28,NULL,'','Agus','Ridwan',1,1000,'botol','2025-11-10','Pakai 1 botol (1000 ml) Agita oleh Agus.','Pakai Lapangan'),(325,28,NULL,'','Agus','Ridwan',1,3000,'ml','2025-11-10','Pakai 1000 ml (1.0 botol) Agita oleh Agus.','Pakai Lapangan'),(326,28,NULL,'','Agus','Ridwan',2,1500,'ml','2025-11-10','Pakai 1500 ml (1.5 botol) Agita oleh Agus.','Pakai Lapangan'),(327,28,NULL,'','Agus','Ridwan',2,1500,'ml','2025-11-10','Pakai 1500 ml (1.5 botol) Agita oleh Agus.','Pakai Lapangan'),(328,28,NULL,'','Agus','Ridwan',2,2000,'botol','2025-11-10','Pakai 2 botol (2000 ml) Agita oleh Agus.','Pakai Lapangan'),(329,28,'','tambah_stok','-','-',20,20000,'botol','2025-11-10','Menambah stok pestisida Agita sebanyak 20 botol (+20000 ml). Total stok sekarang 20 botol.','stok ditambah'),(330,28,'','','Agus','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Agus','alokasi'),(331,28,NULL,'','Agus','Ridwan',1,1000,'botol','2025-11-10','Pakai 1 botol (1000 ml) Agita oleh Agus.','Pakai Lapangan'),(332,28,NULL,'','Agus','Ridwan',1,1000,'ml','2025-11-10','Pakai 1000 ml (1.0 botol) Agita oleh Agus.','Pakai Lapangan'),(333,28,'','','Agus','Ridwan',7,0,'botol','2025-11-10','Data pemakaian pestisida Agita oleh teknisi Agus untuk customer Ridwan dihapus. Stok sebanyak 7 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(334,28,'chemical','','-','-',15,15000,'botol','2025-11-10','Data pestisida Agita telah diperbarui. Jumlah sekarang 15 botol/pcs dengan isi 1000 ml per botol (total aktif 15000 ml).','Update Pestisida'),(335,28,'','','Rudi','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Rudi','alokasi'),(336,28,NULL,'','Rudi','Ridwan',1,1000,'ml','2025-11-10','Pakai 1000 ml (1.0 botol) Agita oleh Rudi.','Pakai Lapangan'),(337,28,NULL,'','Rudi','Ridwan',2,1500,'ml','2025-11-10','Pakai 1500 ml (1.5 botol) Agita oleh Rudi.','Pakai Lapangan'),(338,28,NULL,'','Rudi','Ridwan',2,2000,'botol','2025-11-10','Pakai 2 botol (2000 ml) Agita oleh Rudi.','Pakai Lapangan'),(339,28,NULL,'','Rudi','Ridwan',1,500,'ml','2025-11-10','Pakai 500 ml (0.5 botol) Agita oleh Rudi.','Pakai Lapangan'),(340,23,NULL,'','Agus','Ridwan',4,0,'botol','2025-11-10','Pakai 4 botol (0 ml) Rat box oleh Agus.','Pakai Lapangan'),(341,22,'','','Rudi','Ridwan',1,1500,'botol','2025-11-10','Alokasi 1 botol Fipronil untuk Ridwan oleh Rudi','alokasi'),(342,22,NULL,'','Rudi','Ridwan',1,1500,'botol','2025-11-10','Pakai 1 botol (1500 ml) Fipronil oleh Rudi.','Pakai Lapangan'),(343,28,'','','Sandi','Ridwan',0,0,'botol','2025-11-10','Data pemakaian pestisida Agita oleh teknisi Sandi untuk customer Ridwan dihapus. Stok sebanyak 0 botol dikembalikan ke gudang.','dihapus (stok kembali)'),(344,28,'','','Bayu','Ridwan',5,5000,'botol','2025-11-10','Alokasi 5 botol Agita untuk Ridwan oleh Bayu','alokasi'),(345,28,NULL,'','Bayu','Ridwan',3,2500,'ml','2025-11-10','Pakai 2500 ml (2.5 botol) Agita oleh Bayu.','Pakai Lapangan'),(346,28,NULL,'','Bayu','Ridwan',2,2000,'botol','2025-11-10','Pakai 2 botol (2000 ml) Agita oleh Bayu.','Pakai Lapangan'),(347,28,NULL,'','Bayu','Ridwan',1,500,'ml','2025-11-10','Pakai 500 ml (0.5 botol) Agita oleh Bayu.','Pakai Lapangan'),(348,24,'chemical','','-','-',40,32000,'botol','2025-11-10','Data pestisida Lamda telah diperbarui. Jumlah sekarang 40 botol/pcs dengan isi 800 ml per botol (total aktif 32000 ml).','Update Pestisida'),(349,28,'','','Agus','Ridwan',4,4000,'botol','2025-11-10','Alokasi 4 botol Agita untuk Ridwan oleh Agus','alokasi'),(350,23,'','','-','-',5,0,'botol','2025-11-10','Data pestisida Rat box telah diperbarui. Jumlah sekarang 5 botol/pcs.','Update Pestisida'),(351,24,'chemical','','-','-',10,8000,'botol','2025-11-11','Data pestisida Lamda telah diperbarui. Jumlah sekarang 10 botol/pcs dengan isi 800 ml per botol (total aktif 8000 ml).','Update Pestisida'),(352,28,'','tambah_stok','-','-',30,30000,'botol','2025-11-11','Menambah stok pestisida Agita sebanyak 30 botol (+30000 ml). Total stok sekarang 31 botol.','stok ditambah'),(353,28,'','','Asep','Siti',6,6000,'botol','2025-11-11','Alokasi 6 botol Agita untuk Siti oleh Asep','alokasi'),(354,28,NULL,'','Asep','Siti',1,1000,'botol','2025-11-11','Pakai 1 botol (1000 ml) Agita oleh Asep.','Pakai Lapangan'),(355,28,NULL,'','Asep','Siti',1,1000,'ml','2025-11-11','Pakai 1000 ml (1.0 botol) Agita oleh Asep.','Pakai Lapangan'),(356,28,NULL,'','Asep','Siti',2,1500,'ml','2025-11-11','Pakai 1500 ml (1.5 botol) Agita oleh Asep.','Pakai Lapangan'),(357,27,'','tambah_stok','-','-',10,12000,'botol','2025-11-11','Menambah stok pestisida Cypermethrin sebanyak 10 botol (+12000 ml). Total stok sekarang 60 botol.','stok ditambah'),(358,27,'','','Sandi','Siti',5,6000,'botol','2025-11-11','Alokasi 5 botol Cypermethrin untuk Siti oleh Sandi','alokasi'),(359,27,NULL,'','Sandi','Siti',1,1200,'botol','2025-11-11','Pakai 1 botol (1200 ml) Cypermethrin oleh Sandi.','Pakai Lapangan'),(360,27,'','','Udin','Ridwan',5,6000,'botol','2025-11-11','Alokasi 5 botol Cypermethrin untuk Ridwan oleh Udin','alokasi');
/*!40000 ALTER TABLE `riwayat_pestisida` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stok_gudang`
--

DROP TABLE IF EXISTS `stok_gudang`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `stok_gudang` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `nama_barang` varchar(100) NOT NULL,
  `metode` varchar(100) DEFAULT NULL,
  `jenis` enum('alat','pestisida','non_chemical') NOT NULL,
  `kategori` enum('maintenance','preparasi','habis_pakai') DEFAULT NULL,
  `isi_botol_ml` int(11) DEFAULT 0,
  `jumlah_botol` int(11) DEFAULT 0,
  `stok_sekarang` int(11) DEFAULT 0,
  `terpakai_ml` int(11) DEFAULT 0,
  `sisa_ml` int(11) DEFAULT 0,
  `tanggal` date NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stok_gudang`
--

LOCK TABLES `stok_gudang` WRITE;
/*!40000 ALTER TABLE `stok_gudang` DISABLE KEYS */;
INSERT INTO `stok_gudang` VALUES (6,'Fogging',NULL,'alat',NULL,0,20,0,0,0,'2025-10-16'),(7,'Spraying',NULL,'alat',NULL,0,5,0,0,0,'2025-10-20'),(8,'Fogging',NULL,'alat',NULL,0,1,0,0,0,'2025-10-21'),(9,'Spraying',NULL,'alat',NULL,0,1,0,0,0,'2025-10-21'),(10,'Fogging',NULL,'alat',NULL,0,1,0,0,0,'2025-10-21'),(11,'Fogging',NULL,'alat',NULL,0,1,0,0,0,'2025-10-21'),(12,'ColdFogg',NULL,'alat',NULL,0,1,0,0,0,'2025-10-21'),(13,'ColdFogg',NULL,'alat',NULL,0,1,0,0,0,'2025-10-22'),(14,'Fogging',NULL,'alat',NULL,0,1,0,0,0,'2025-10-28'),(15,'Fogging',NULL,'alat',NULL,0,1,0,0,0,'2025-10-29'),(16,'Spraying',NULL,'alat',NULL,0,1,0,0,0,'2025-10-29');
/*!40000 ALTER TABLE `stok_gudang` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-11-12 14:43:31
