-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 23, 2026 at 01:55 AM
-- Server version: 10.4.32-MariaDB-log
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_prakerin_smk`
--

-- --------------------------------------------------------

--
-- Table structure for table `industri`
--
-- Error reading structure for table db_prakerin_smk.industri: #1932 - Table &#039;db_prakerin_smk.industri&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.industri: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`industri`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `jurnal`
--
-- Error reading structure for table db_prakerin_smk.jurnal: #1932 - Table &#039;db_prakerin_smk.jurnal&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.jurnal: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`jurnal`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `log_aktivitas`
--
-- Error reading structure for table db_prakerin_smk.log_aktivitas: #1932 - Table &#039;db_prakerin_smk.log_aktivitas&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.log_aktivitas: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`log_aktivitas`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `log_penempatan`
--
-- Error reading structure for table db_prakerin_smk.log_penempatan: #1932 - Table &#039;db_prakerin_smk.log_penempatan&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.log_penempatan: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`log_penempatan`&#039; at line 1

-- --------------------------------------------------------

--
-- Table structure for table `penempatan`
--
-- Error reading structure for table db_prakerin_smk.penempatan: #1932 - Table &#039;db_prakerin_smk.penempatan&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.penempatan: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`penempatan`&#039; at line 1

--
-- Triggers `penempatan`
--
DELIMITER $$
CREATE TRIGGER `AfterInsertPenempatan` AFTER INSERT ON `penempatan` FOR EACH ROW BEGIN
    INSERT INTO log_penempatan (keterangan, waktu_kejadian)
    VALUES (
        CONCAT(
            'Siswa dengan NIS ',
            NEW.nis,
            ' ditempatkan di Industri ID ',
            NEW.id_industri
        ),
        NOW()
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `AfterUpdatePenempatan` AFTER UPDATE ON `penempatan` FOR EACH ROW BEGIN
    INSERT INTO log_aktivitas (aksi, keterangan, waktu)
    VALUES (
        'UPDATE PENEMPATAN',
        CONCAT('Status prakerin NIS ', OLD.nis, ' diperbarui'),
        NOW()
    );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `AfterUpdateStatus` AFTER UPDATE ON `penempatan` FOR EACH ROW BEGIN
    IF NEW.status = 'Selesai' THEN
        INSERT INTO log_penempatan(keterangan, waktu_kejadian)
        VALUES (
            CONCAT(
                'Penempatan NIS ',
                OLD.nis,
                ' Telah Selesai'
            ),
            NOW()
        );
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `siswa`
--
-- Error reading structure for table db_prakerin_smk.siswa: #1932 - Table &#039;db_prakerin_smk.siswa&#039; doesn&#039;t exist in engine
-- Error reading data for table db_prakerin_smk.siswa: #1064 - You have an error in your SQL syntax; check the manual that corresponds to your MariaDB server version for the right syntax to use near &#039;FROM `db_prakerin_smk`.`siswa`&#039; at line 1

--
-- Triggers `siswa`
--
DELIMITER $$
CREATE TRIGGER `AfterInsertSiswa` AFTER INSERT ON `siswa` FOR EACH ROW BEGIN
    
    INSERT INTO log_aktivitas (aksi, keterangan, waktu) 
    VALUES ('INSERT SISWA', CONCAT('Siswa baru ditambahkan: ', NEW.nama_siswa), NOW());
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_laporan_prakerin`
-- (See below for the actual view)
--
CREATE TABLE `view_laporan_prakerin` (
);

-- --------------------------------------------------------

--
-- Stand-in structure for view `view_rekap_jurnal`
-- (See below for the actual view)
--
CREATE TABLE `view_rekap_jurnal` (
);

-- --------------------------------------------------------

--
-- Structure for view `view_laporan_prakerin`
--
DROP TABLE IF EXISTS `view_laporan_prakerin`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_laporan_prakerin`  AS SELECT `s`.`nis` AS `nis`, `s`.`nama_siswa` AS `nama_siswa`, `s`.`kelas` AS `kelas`, `i`.`nama_perusahaan` AS `nama_perusahaan`, `p`.`tanggal_mulai` AS `tanggal_mulai`, `p`.`status` AS `status` FROM ((`penempatan` `p` join `siswa` `s` on(`p`.`nis` = `s`.`nis`)) join `industri` `i` on(`p`.`id_industri` = `i`.`id_industri`)) ;

-- --------------------------------------------------------

--
-- Structure for view `view_rekap_jurnal`
--
DROP TABLE IF EXISTS `view_rekap_jurnal`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `view_rekap_jurnal`  AS SELECT `s`.`nis` AS `nis`, `s`.`nama_siswa` AS `nama_siswa`, `i`.`nama_perusahaan` AS `nama_perusahaan`, `j`.`kegiatan` AS `kegiatan` FROM (((`jurnal` `j` join `penempatan` `p` on(`j`.`id_penempatan` = `p`.`id_penempatan`)) join `siswa` `s` on(`p`.`nis` = `s`.`nis`)) join `industri` `i` on(`p`.`id_industri` = `i`.`id_industri`)) ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;