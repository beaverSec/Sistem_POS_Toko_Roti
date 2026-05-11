-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 10, 2026 at 09:38 AM
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
`harga` int
,`id_transaksi` varchar(10)
,`jumlah` int
,`nama_menu` varchar(50)
,`subtotal` int
);
no
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
('0011001', 'MN01', 'TR0011', 1, 15000),
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
  `jabatan` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `karyawan`
--

INSERT INTO `karyawan` (`id_karyawan`, `nama_karyawan`, `jabatan`, `username`, `password`, `is_active`) VALUES
('K001', 'Pedro Pascal', 'Kasir', 'pedro.pascal', 'd3ce9efea6244baa7bf718f12dd0c331', 1),
('K002', 'Theo James', 'Kasir', 'theo.james', '478d5000594ef50c56e98681961aee6d', 1),
('K003', 'Paul Mescal', 'Manajer', 'paul.mescal', '2e69f107d4be5f743461cb66d55d5e6e', 1);

-- --------------------------------------------------------

--
-- Table structure for table `kategori`
--

CREATE TABLE `kategori` (
  `id_kategori` varchar(10) NOT NULL,
  `nama_kategori` varchar(50) NOT NULL,
  `icon_kategori` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `kategori`
--

INSERT INTO `kategori` (`id_kategori`, `nama_kategori`, `icon_kategori`) VALUES
('KTG01', 'Roti', ''),
('KTG02', 'Pastry', ''),
('KTG03', 'Kue Basah', ''),
('KTG04', 'Kue Kering', '');

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `id_menu` varchar(10) NOT NULL,
  `nama_menu` varchar(50) NOT NULL,
  `stok` int NOT NULL,
  `harga` int NOT NULL,
  `gambar` varchar(255) DEFAULT NULL,
  `id_kategori` varchar(10) NOT NULL,
  `is_deleted` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`id_menu`, `nama_menu`, `stok`, `harga`, `gambar`, `id_kategori`, `is_deleted`) VALUES
('MN01', 'Roti Tawar', 9, 15000, 'roti_tawar.jpeg', 'KTG01', 0),
('MN02', 'Birthday Cake', 7, 200000, 'birthday_cake.jpeg', 'KTG03', 0),
('MN03', 'Cheesecake', 5, 25000, 'cheesecake.jpeg', 'KTG03', 0),
('MN04', 'Croissant', 20, 15000, 'croissant.jpeg', 'KTG02', 0),
('MN05', 'Eclair', 23, 10000, 'eclair.jpeg', 'KTG02', 0),
('MN06', 'Nastar', 10, 60000, 'nastar.jpeg', 'KTG04', 0),
('MN07', 'Chocolate Cookies', 10, 45000, 'chocolate_cookies.jpeg', 'KTG04', 0),
('MN08', 'Dadar Gulung', 15, 5000, 'dadar_gulung.jpeg', 'KTG03', 0),
('MN09', 'Lemper', 35, 5000, 'lemper.jpeg', 'KTG03', 0),
('MN10', 'Pain au Chocolat', 20, 15000, 'pain_au_chocolat.jpeg', 'KTG02', 0);

-- --------------------------------------------------------

--
-- Stand-in structure for view `menubasedonkategori`
-- (See below for the actual view)
--
CREATE TABLE `menubasedonkategori` (
`harga` int
,`nama_kategori` varchar(50)
,`nama_menu` varchar(50)
,`stok` int
);

-- --------------------------------------------------------

--
-- Table structure for table `transaksi`
--

CREATE TABLE `transaksi` (
  `id_transaksi` varchar(10) NOT NULL,
  `waktu_transaksi` datetime NOT NULL,
  `total_bayar` int NOT NULL,
  `uang_bayar` int DEFAULT '0',
  `kembalian` int DEFAULT '0',
  `metode_bayar` varchar(20) DEFAULT 'Cash',
  `status` varchar(20) DEFAULT 'Selesai',
  `id_karyawan` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `transaksi`
--

INSERT INTO `transaksi` (`id_transaksi`, `waktu_transaksi`, `total_bayar`, `uang_bayar`, `kembalian`, `metode_bayar`, `status`, `id_karyawan`) VALUES
('TR0001', '2026-01-30 00:00:00', 30000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0002', '2026-02-02 00:00:00', 200000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0003', '2026-02-16 00:00:00', 75000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0004', '2026-03-08 00:00:00', 50000, 0, 0, 'Cash', 'Selesai', 'K002'),
('TR0005', '2026-03-24 20:45:19', 50000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0006', '2026-03-24 20:50:30', 15000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0008', '2026-03-24 20:52:53', 10000, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0010', '2026-04-24 13:27:22', 0, 0, 0, 'Cash', 'Selesai', 'K001'),
('TR0011', '2026-05-10 12:20:57', 15000, 20000, 5000, 'Cash', 'Selesai', 'K001');

-- --------------------------------------------------------

--
-- Stand-in structure for view `transaksilengkap`
-- (See below for the actual view)
--
CREATE TABLE `transaksilengkap` (
`id_karyawan` varchar(10)
,`id_transaksi` varchar(10)
,`nama_karyawan` varchar(50)
,`status` varchar(20)
,`subtotal` int
,`waktu_transaksi` datetime
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

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `menubasedonkategori`  AS SELECT `kat`.`nama_kategori` AS `nama_kategori`, `m`.`nama_menu` AS `nama_menu`, `m`.`stok` AS `stok`, `m`.`harga` AS `harga` FROM (`kategori` `kat` left join `menu` `m` on((`kat`.`id_kategori` = `m`.`id_kategori`))) WHERE (`m`.`is_deleted` = 0) ORDER BY `kat`.`nama_kategori` ASC  ;

-- --------------------------------------------------------

--
-- Structure for view `transaksilengkap`
--
DROP TABLE IF EXISTS `transaksilengkap`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `transaksilengkap`  AS SELECT `t`.`id_transaksi` AS `id_transaksi`, `t`.`waktu_transaksi` AS `waktu_transaksi`, `t`.`id_karyawan` AS `id_karyawan`, `k`.`nama_karyawan` AS `nama_karyawan`, `t`.`total_bayar` AS `subtotal`, `t`.`status` AS `status` FROM (`transaksi` `t` join `karyawan` `k` on((`t`.`id_karyawan` = `k`.`id_karyawan`)))  ;

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
