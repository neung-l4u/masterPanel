-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 03, 2024 at 07:45 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `db_localforyou`
--

-- --------------------------------------------------------

--
-- Table structure for table `passwordmanager`
--

CREATE TABLE `passwordmanager` (
  `id` tinyint(4) NOT NULL,
  `pwName` varchar(255) NOT NULL,
  `pwLink` varchar(255) NOT NULL,
  `pwUser` varchar(255) NOT NULL,
  `pwPass` text NOT NULL,
  `pwLevel` tinyint(1) NOT NULL,
  `pwNote` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `passwordmanager`
--

INSERT INTO `passwordmanager` (`id`, `pwName`, `pwLink`, `pwUser`, `pwPass`, `pwLevel`, `pwNote`) VALUES
(4, 'Dreamscape', 'https://reseller.ds.network/', 'admin@localforyou.com', 'admin2022', 2, ''),
(5, 'Gloria Foods (All Shopping cart)ADA', '', 'admin@localforyou.com', 'L4U@2017:)', 2, ''),
(6, 'Facebook: Aaliyah', 'https://facebook.com/', 'localforyou1@gmail.com', 'Localforyou2023!', 2, 'ติด 2FA ผ่านสมาร์ทโฟน'),
(7, 'Email Admin (Official)', 'https://gmail.com/', 'admin@localforyou.com', 'L4U@2017:)', 2, ''),
(8, 'CRM admin', '', 'admin@localforyou.com', 'L4U@2017:)', 2, ''),
(9, 'Restaurant order taking demo', '', 'demoeats@localforyou.com', 'L4U@2017:)', 2, ''),
(10, 'Shopping Cart', 'https://www.localforyoucart.com/', 'demoeats@localforyou.com', 'L4U@2017:)', 2, ''),
(12, 'Demoeat Thai (Shopping cart)', 'https://www.localforyoucart.com/', 'demoeatsthai@localforyou.com', 'L4U@2017:)', 2, ''),
(13, 'Email Sales', 'https://gmail.com/', 'sales@localforyou.com', 'L4U@2017:)', 2, ''),
(14, 'Respond io Sale', 'https://respond.io/', 'sales@localforyou.com', 'L4u@2017:)', 2, ''),
(15, 'Admin payment update', '', 'admin@localforyou.com', 'Localforyou2021', 2, 'ไม่รู้เข้าที่ไหน'),
(16, 'All Massage Wordpress', '', 'admin@localforyou.com', 'L4U=New@min', 2, 'บางอันยังเป็นรหัสเก่า: Localforyou2023!'),
(17, 'Youcanbookme Sales', 'https://youcanbook.me/', 'sales@localforyou.com', 'L4u@2017:)!!', 2, ''),
(18, 'Docusign', 'https://www.docusign.com/', 'sales@localforyou.com', 'Localforyou2022', 2, '');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `passwordmanager`
--
ALTER TABLE `passwordmanager`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `passwordmanager`
--
ALTER TABLE `passwordmanager`
  MODIFY `id` tinyint(4) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
