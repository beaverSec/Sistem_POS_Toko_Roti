-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 05, 2026 at 04:14 AM
-- Server version: 8.0.43
-- PHP Version: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `tokoroti`
--

-- --------------------------------------------------------

--
-- Stand-in structure for view `detailstruk`
-- (See below for the actual view)
--
CREATE TABLE `detailstruk` (
`id_transaksi` varchar(10)
,`nama_menu` varchar(50)
,`jumlah` int
,`harga` int
,`subtotal` int
);

-- --------------------------------------------------------

--
-- Table structure for table `detail_transaksi`
--

CREATE TABLE `detail_transaksi` (
  `id_detailTransaksi` varchar(10) NOT NULL,
  `id_menu` varchar(10) NOT NULL,
  `id_transaksi` varchar(10) NOT NULL,
  `jumlah` int NOT NULL,
  `subtotal` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `detail_transaksi`
--

INSERT INTO `detail_transaksi` (`id_detailTransaksi`, `id_menu`, `id_transaksi`, `jumlah`, `subtotal`) VALUES
('DT-TR0008', 'MN05', 'TR0008', 1, 10000),
('DT0001', 'MN01', 'TR0001', 2, 30000),
('DT0002', 'MN02', 'TR0002', 1, 200000),
('DT0003', 'MN10', 'TR0003', 5, 75000),
('DT0004', 'MN05', 'TR0004', 5, 50000),
('DT0010', 'MN05', 'TR0010', 1, 10000);

--
-- Triggers `detail_transaksi`
--
DELIMITER $$
CREATE TRIGGER `after_detail_transaksi_insert` AFTER INSERT ON `detail_transaksi` FOR EACH ROW BEGIN
    UPDATE menu 
    SET stok = stok - NEW.jumlah
    WHERE id_menu = NEW.id_menu;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `karyawan`
--

CREATE TABLE `karyawan` (
  `id_karyawan` varchar(10) NOT NULL,
  `nama_karyawan` varchar(50) NOT NULL,
  `jabatan` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `jabatan`) VALUES
('K001', 'Pedro Pascal', 'Kasir'),
('K002', 'Theo James', 'Kasir'),
('K003', 'Paul Mescal', 'Manajer');

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`) VALUES
('KTG01', 'Roti'),
('KTG02', 'Pastry'),
('KTG03', 'Kue Basah'),
('KTG04', 'Kue Kering');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` varchar(10) NOT NULL,
  `nama_menu` varchar(50) NOT NULL,
  `stok` int NOT NULL,
  `harga` int NOT NULL,
  `id_kategori` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `stok`, `harga`, `id_kategori`) VALUES
('MN01', 'Roti Tawar', 10, 15000, 'KTG01'),
('MN02', 'Birthday Cake', 7, 200000, 'KTG03'),
('MN03', 'Cheesecake', 5, 25000, 'KTG03'),
('MN04', 'Croissant', 20, 15000, 'KTG02'),
('MN05', 'Eclair', 23, 10000, 'KTG02'),
('MN06', 'Nastar', 10, 60000, 'KTG04'),
('MN07', 'Chocolate Cookies', 10, 45000, 'KTG04'),
('MN08', 'Dadar Gulung', 15, 5000, 'KTG03'),
('MN09', 'Lemper', 35, 5000, 'KTG03'),
('MN10', 'Pain au Chocolat', 20, 15000, 'KTG02');

-- --------------------------------------------------------

--
-- Stand-in structure for view `menubasedonkategori`
-- (See below for the actual view)
--
CREATE TABLE `menubasedonkategori` (
`nama_kategori` varchar(50)
,`nama_menu` varchar(50)
,`stok` int
,`harga` int
);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(10) NOT NULL,
  `waktu_transaksi` datetime NOT NULL,
  `total_bayar` int NOT NULL,
  `id_karyawan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `waktu_transaksi`, `total_bayar`, `id_karyawan`) VALUES
('TR0001', '2026-01-30 00:00:00', 30000, 'K001'),
('TR0002', '2026-02-02 00:00:00', 200000, 'K001'),
('TR0003', '2026-02-16 00:00:00', 75000, 'K001'),
('TR0004', '2026-03-08 00:00:00', 50000, 'K002'),
('TR0005', '2026-03-24 20:45:19', 50000, 'K001'),
('TR0006', '2026-03-24 20:50:30', 15000, 'K001'),
('TR0008', '2026-03-24 20:52:53', 10000, 'K001'),
('TR0010', '2026-04-24 13:27:22', 0, 'K001');

-- --------------------------------------------------------

--
-- Stand-in structure for view `transaksilengkap`
-- (See below for the actual view)
--
CREATE TABLE `transaksilengkap` (
`id_transaksi` varchar(10)
,`waktu_transaksi` datetime
,`nama_karyawan` varchar(50)
,`nama_menu` varchar(50)
,`jumlah` int
,`harga` int
,`subtotal` int
);

-- --------------------------------------------------------

--
-- Structure for view `detailstruk`
--
DROP TABLE IF EXISTS `detailstruk`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `detailstruk`  AS SELECT `dt`.`id_transaksi` AS `id_transaksi`, `m`.`nama_menu` AS `nama_menu`, `dt`.`jumlah` AS `jumlah`, `m`.`harga` AS `harga`, `dt`.`subtotal` AS `subtotal` FROM (`detail_transaksi` `dt` join `menu` `m` on((`dt`.`id_menu` = `m`.`id_menu`)))  ;

-- --------------------------------------------------------

--
-- Structure for view `menubasedonkategori`
--
DROP TABLE IF EXISTS `menubasedonkategori`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `menubasedonkategori`  AS SELECT `kat`.`nama_kategori` AS `nama_kategori`, `m`.`nama_menu` AS `nama_menu`, `m`.`stok` AS `stok`, `m`.`harga` AS `harga` FROM (`kategori` `kat` left join `menu` `m` on((`kat`.`id_kategori` = `m`.`id_kategori`))) ORDER BY `kat`.`nama_kategori` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `transaksilengkap`
--
DROP TABLE IF EXISTS `transaksilengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transaksilengkap`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`waktu_transaksi` AS `waktu_transaksi`, `k`.`nama_karyawan` AS `nama_karyawan`, `m`.`nama_menu` AS `nama_menu`, `dt`.`jumlah` AS `jumlah`, `m`.`harga` AS `harga`, `dt`.`subtotal` AS `subtotal` FROM (((`transaksi` `t` join `karyawan` `k` on((`t`.`id_karyawan` = `k`.`id_karyawan`))) join `detail_transaksi` `dt` on((`t`.`id_transaksi` = `dt`.`id_transaksi`))) join `menu` `m` on((`dt`.`id_menu` = `m`.`id_menu`))) ORDER BY `t`.`waktu_transaksi` AS `DESCdesc` ASC  ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD PRIMARY KEY (`id_detailTransaksi`),
  ADD KEY `id_menu` (`id_menu`),
  ADD KEY `id_transaksi` (`id_transaksi`);

--
-- Indexes for table `karyawan`
--
ALTER TABLE `karyawan`
  ADD PRIMARY KEY (`id_karyawan`);

--
-- Indexes for table `kategori`
--
ALTER TABLE `kategori`
  ADD PRIMARY KEY (`id_kategori`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`id_menu`),
  ADD KEY `id_kategori` (`id_kategori`);

--
-- Indexes for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD PRIMARY KEY (`id_transaksi`),
  ADD KEY `id_karyawan` (`id_karyawan`);

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_transaksi`
--
ALTER TABLE `detail_transaksi`
  ADD CONSTRAINT `detail_transaksi_ibfk_1` FOREIGN KEY (`id_menu`) REFERENCES `menu` (`id_menu`),
  ADD CONSTRAINT `detail_transaksi_ibfk_2` FOREIGN KEY (`id_transaksi`) REFERENCES `transaksi` (`id_transaksi`);

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_ibfk_1` FOREIGN KEY (`id_kategori`) REFERENCES `kategori` (`id_kategori`);

--
-- Constraints for table `transaksi`
--
ALTER TABLE `transaksi`
  ADD CONSTRAINT `transaksi_ibfk_1` FOREIGN KEY (`id_karyawan`) REFERENCES `karyawan` (`id_karyawan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*Penggunaan password_hash() di PHP*/
mysql> ALTER TABLE karyawan
    -> ADD COLUMN username VARCHAR(50) AFTER nama_karyawan,
    -> ADD COLUMN password VARCHAR(255) AFTER username,
    -> ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER password;
/*Query OK, 0 rows affected, 1 warning (0.09 sec)
Records: 0  Duplicates: 0  Warnings: 1*/

mysql> -- Mengisi data untuk Pedro Pascal
mysql> UPDATE karyawan
    -> SET username = 'pedro.pascal',
    ->     password = MD5('pedro123')
    -> WHERE id_karyawan = 'K001';
Query OK, 1 row affected (0.02 sec)
Rows matched: 1  Changed: 1  Warnings: 0

mysql>
mysql> -- Mengisi data untuk Theo James
mysql> UPDATE karyawan
    -> SET username = 'theo.james',
    ->     password = MD5('theo123')
    -> WHERE id_karyawan = 'K002';

mysql>
mysql> -- Mengisi data untuk Paul Mescal
mysql> UPDATE karyawan
    -> SET username = 'paul.mescal',
    ->     password = MD5('paul123')
    -> WHERE id_karyawan = 'K003';
  

/*Perbaikan View transaksilengkap*/
mysql> CREATE OR REPLACE VIEW transaksilengkap AS
    -> SELECT *
    -> FROM transaksi
    -> -- ... (tambahkan join Anda di sini jika ada)
    -> ORDER BY waktu_transaksi DESC;

mysql> ALTER TABLE menu
    -> ADD COLUMN is_deleted TINYINT(1) DEFAULT 0;
/*Query OK, 0 rows affected, 1 warning (0.04 sec)
Records: 0  Duplicates: 0  Warnings: 1*/

mysql> SELECT * FROM menu WHERE is_deleted = 0;



