-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 12:54 AM
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

--
-- Truncate table before insert `passwordmanager`
--

TRUNCATE TABLE `passwordmanager`;
--
-- Dumping data for table `passwordmanager`
--

INSERT INTO `passwordmanager` (`id`, `pwName`, `pwLink`, `pwUser`, `pwPass`, `pwLevel`, `pwTeam`, `pwType`, `pwNote`, `pwCreateBy`, `pwCreateAt`, `pwUpdateBy`, `pwUpdateAt`, `pwDeleteBy`, `pwDeleteAt`) VALUES
(4, 'Dreamscape', 'https://reseller.ds.network/', 'admin@localforyou.com', 'admin2022', 4, 5, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(5, 'Gloria Foods (All Shopping cart)ADA', '', 'admin@localforyou.com', 'L4U@2017:)', 3, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(6, 'Facebook: Aaliyah', 'https://facebook.com/', 'localforyou1@gmail.com', 'Localforyou2023!', 3, 0, 1, 'ติด 2FA ผ่านสมาร์ทโฟน', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(7, 'Email Admin (Official)', 'https://gmail.com/', 'admin@localforyou.com', 'L4U@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(8, 'CRM admin', '', 'admin@localforyou.com', 'L4U@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(9, 'Restaurant order taking demo', '', 'demoeats@localforyou.com', 'L4U@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(10, 'Shopping Cart', 'https://www.localforyoucart.com/', 'demoeats@localforyou.com', 'L4U@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(12, 'Demoeat Thai (Shopping cart)', 'https://www.localforyoucart.com/', 'demoeatsthai@localforyou.com', 'L4U@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(13, 'Email Sales', 'https://gmail.com/', 'sales@localforyou.com', 'L4U@2017:)', 3, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(14, 'Respond io Sale', 'https://respond.io/', 'sales@localforyou.com', 'L4u@2017:)', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(15, 'Admin payment update', '', 'admin@localforyou.com', 'Localforyou2021', 3, 0, 1, 'ไม่รู้เข้าที่ไหน', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(16, 'All Massage Wordpress', '', 'admin@localforyou.com', 'L4U=New@min', 4, 5, 1, 'บางอันยังเป็นรหัสเก่า: Localforyou2023!', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(17, 'Youcanbookme Sales', 'https://youcanbook.me/', 'sales@localforyou.com', 'L4u@2017:)!!', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(18, 'Docusign', 'https://www.docusign.com/', 'sales@localforyou.com', 'Localforyou2022', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(22, 'Email Admin (old)', 'https://gmail.com/', 'localforyou1@gmail.com', 'L4U2021!', 4, 0, 1, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(23, 'Signature Thai Massage & Wellness - USA', '', 'Client01 - Account Manager', 'Client01 - AM', 4, 2, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(24, 'True Thai - AU', '', 'Client02 - Account Manager', 'Client01 - AM', 4, 2, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(25, 'Chai Thai (Murrumbeena) - AU', '', 'Client01 - Customer Support', 'Client01 - CS', 4, 1, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(26, 'A Day in Bkk', '', 'Client02 - Customer Support', 'Client02 - CS', 4, 1, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(27, 'Mr.Hen', '', 'Client01 - Sale', 'Client01 - SE', 4, 3, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(28, 'Mrs.Mai', '', 'Client02 - Sale', 'Client02 - SE', 4, 3, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL),
(29, 'Prapa Thai Remedial', '', 'Client01 - Information Technology', 'Client01 - IT', 4, 5, 2, '', NULL, '2024-12-12 13:19:09', NULL, NULL, NULL, NULL);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
