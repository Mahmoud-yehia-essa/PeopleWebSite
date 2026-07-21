-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2026 at 11:10 PM
-- Server version: 11.8.6-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u425698010_new_wiselook`
--

-- --------------------------------------------------------

--
-- Table structure for table `groups`
--

CREATE TABLE `groups` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `descriptions` text DEFAULT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `date_created` datetime DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `member_count` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `groups`
--

INSERT INTO `groups` (`id`, `name`, `image`, `descriptions`, `created_by_user_id`, `date_created`, `updated_at`, `member_count`) VALUES
(44, 'Groups 1', '1760335489_1000074604.jpg', 'Groups 1', 17, '2025-09-12 14:56:18', '2026-02-17 11:24:27', 2),
(45, 'Groups 2', '1760335489_1000074604.jpg', 'Groups 2', 17, '2025-09-12 14:57:01', '2026-02-17 11:24:27', 3),
(46, 'Group 3', NULL, 'Group 3', 17, '2025-09-13 11:57:07', '2026-02-17 11:24:27', 2),
(47, '4 Group', '1757929948_monzer.JPG', 'Group 4', 17, '2025-09-15 12:52:28', '2026-02-17 11:24:27', 4),
(48, 'Group 5', NULL, 'Group 5', 17, '2025-09-15 12:58:53', '2026-02-17 11:24:27', 5),
(49, 'Test group', NULL, 'Test group', 17, '2025-09-17 08:20:32', '2026-02-17 11:24:27', 3),
(50, 'test', '1758282573_image_picker_AD344F3A-22D3-481D-86B3-DD523763EAF5-5022-000001204F069ED7.jpg', 'test up', 17, '2025-09-19 14:49:33', '2026-02-17 11:24:27', 3),
(51, 'hala', NULL, 'nothing', 3, '2025-09-29 22:52:28', '2026-02-17 11:24:27', 3),
(52, 'lebanon', NULL, 'descripti', 3, '2025-09-29 23:01:49', '2026-02-17 11:24:27', 2),
(53, 'خاص', NULL, 'مجموعة خاصة', 55, '2025-09-29 23:02:13', '2026-02-17 11:24:27', 3),
(54, 'vv', NULL, 'bb', 40, '2025-10-02 22:25:16', '2026-02-17 11:24:27', 3),
(55, 'test', '1759472922_1000074604.jpg', 'tesy', 17, '2025-10-03 09:28:42', '2026-02-17 11:24:27', 5),
(56, 'gg', NULL, 'hh', 51, '2025-10-12 19:36:17', '2026-02-17 11:24:27', 2),
(57, 'test 1', '1760335489_1000074604.jpg', 'vhcg', 3, '2025-10-13 09:04:49', '2026-02-17 11:24:27', 4),
(58, 'منجرب', '1760820569_1001136146.jpg', 'شغال او لا', 40, '2025-10-18 23:49:29', '2026-02-17 11:24:27', 3),
(59, 'Test monda', '1760940075_monzer.JPG', 'sss', 17, '2025-10-20 09:01:15', '2026-02-17 11:24:27', 5),
(60, 'حكماء', NULL, 'حكماء', 37, '2025-10-21 16:11:48', '2026-02-17 11:24:27', 3),
(64, 'tttt', NULL, 'tttt', 10, '2025-10-27 16:47:01', '2026-02-17 11:24:27', 4),
(65, 'مرحبا', NULL, 'مرحبا بكم', 19, '2025-10-27 20:03:41', '2026-02-17 11:24:27', 5),
(66, 'tes', NULL, 'gxyd', 17, '2025-10-28 09:52:07', '2026-02-17 11:24:27', 3),
(67, 'tes', NULL, 'gxyd', 17, '2025-10-28 09:52:15', '2026-02-23 09:43:30', 2),
(68, 'aaa', '1760940075_monzer.JPG', 'test', 3, '2025-10-28 09:57:58', '2026-02-17 11:24:27', 3),
(69, 'tttt', NULL, 'test', 3, '2025-10-28 10:24:07', '2026-02-17 11:24:27', 3),
(70, 'bbb', NULL, 'bbb', 3, '2025-10-28 10:48:58', '2026-02-17 11:24:27', 3),
(71, 'مجموعة', NULL, 'وصف المجموعة', 3, '2025-12-13 10:13:30', '2026-02-17 11:24:27', 5),
(72, '😎', NULL, 'h', 60, '2025-12-14 23:27:19', '2026-02-17 11:24:27', 3),
(73, 'y', NULL, 't', 17, '2025-12-15 12:37:58', '2026-02-17 11:24:27', 3),
(74, 'y', NULL, 't is', 17, '2025-12-15 12:38:08', '2026-02-17 11:24:27', 3),
(75, 'test', NULL, 'test', 10, '2025-12-15 14:59:04', '2026-02-17 11:24:27', 2),
(76, 'test', NULL, 'test', 17, '2025-12-15 15:13:02', '2026-02-17 11:24:27', 3),
(77, 'فريق الكويت', '1771361187_1001343206.png', 'حكماء العالم', 40, '2026-02-01 16:13:32', '2026-02-17 20:46:27', 6),
(78, '1chees', '1771327616_15260.jpg', 'gdgeyummmm', 4, '2026-02-17 12:54:07', '2026-02-17 11:26:56', 3),
(81, 'test', '1774777380_image_picker_868E3482-B417-4AFF-97F1-EF03CCA6B0F2-62588-000010E81BE9A135.jpg', 'test', 17, '2026-03-29 12:42:17', '2026-03-29 09:43:00', 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `groups`
--
ALTER TABLE `groups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=82;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
