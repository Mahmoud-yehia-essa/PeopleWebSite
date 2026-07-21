-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2026 at 10:09 PM
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
-- Table structure for table `polls`
--

CREATE TABLE `polls` (
  `id` int(11) NOT NULL,
  `post_id` int(11) NOT NULL,
  `question` text NOT NULL,
  `total_votes` varchar(11) DEFAULT '0',
  `is_multiple_choice` tinyint(1) DEFAULT 0,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `polls`
--

INSERT INTO `polls` (`id`, `post_id`, `question`, `total_votes`, `is_multiple_choice`, `created_at`, `expires_at`) VALUES
(1, 2, 'This is a question?', '5', 0, '2025-06-05 08:13:49', '2025-12-31 23:59:59'),
(2, 18, 'Test 1', '0', 0, '2025-06-10 08:57:38', '2025-06-11 00:00:00'),
(3, 19, 'test 2', '0', 0, '2025-06-10 08:57:58', '2025-06-11 00:00:00'),
(4, 20, 'new poll', '2', 0, '2025-06-10 08:59:17', '2025-06-11 00:00:00'),
(5, 23, 'amc', '3', 0, '2025-06-11 08:04:30', '2025-06-12 00:00:00'),
(6, 25, 'how much is your salary', '5', 0, '2025-06-11 08:39:18', '2025-06-20 00:00:00'),
(7, 27, 'hey', '4', 0, '2025-06-11 11:45:23', '2025-06-12 00:00:00'),
(8, 30, 'Hello', '6', 0, '2025-06-16 08:36:15', '2025-06-17 00:00:00'),
(9, 39, 'test', '1', 0, '2025-06-17 05:57:16', '2025-06-18 00:00:00'),
(10, 69, 'let\'s see', '1', 0, '2025-06-23 07:42:12', '2025-06-23 00:00:00'),
(11, 101, 't', '0', 0, '2025-06-27 13:11:23', '2025-06-28 00:00:00'),
(12, 107, 'test', '1', 0, '2025-06-30 11:05:04', '2025-07-02 00:00:00'),
(13, 124, 'when is your birthday?', '5', 0, '2025-07-04 06:40:32', '2025-07-08 00:00:00'),
(14, 125, 'vg', '2', 0, '2025-07-06 12:19:50', '2025-07-08 00:00:00'),
(15, 136, 'test', '1', 0, '2025-07-08 11:10:12', '2025-07-10 00:00:00'),
(16, 141, 'test', '0', 0, '2025-07-16 12:38:37', '2025-07-19 00:00:00'),
(17, 150, 'مهند', '2', 0, '2025-07-23 10:54:33', '2025-07-25 00:00:00'),
(18, 174, 'ما اكثر ما يعجبك في جغرافيا الشرق الأوسط؟', '4', 0, '2025-08-06 06:35:09', '2025-08-27 00:00:00'),
(19, 182, 'bvc', '1', 0, '2025-08-14 20:14:48', '2025-08-21 00:00:00'),
(20, 575, 'هل يُعد الذكاء الاصطناعي تهديداً للوظائف التقليدية؟', '2', 0, '2025-10-21 13:07:38', '2025-10-22 21:00:00'),
(21, 1414, 'اااتيسي', '2', 0, '2026-01-13 10:10:35', '2026-01-14 21:00:00'),
(22, 1858, 'ما هي أفضل ليلة في شهر رمضان؟', '5', 0, '2026-02-18 11:52:04', '2026-02-20 21:00:00'),
(23, 1870, 't', '1', 0, '2026-02-18 13:12:34', '2026-02-18 21:00:00'),
(24, 1874, 'ما هي أفضل ليلة في شهر رمضان؟', '11', 0, '2026-02-19 07:33:38', '2026-02-19 21:00:00'),
(25, 1908, 'ماهي السورة التي أنزلها الله تعالى تشير إلى اقتراب وفاة الرسول صلى الله عليه وسلم', '0', 0, '2026-02-20 05:14:37', NULL),
(26, 1922, 'و', '1', 0, '2026-02-20 19:15:23', '2026-02-20 21:00:00'),
(27, 2694, 'من هو كليم الله', '0', 0, '2026-03-27 11:40:50', '2026-03-30 21:00:00'),
(28, 2734, 'من هو كليم الله 🌿 🌸', '0', 0, '2026-03-30 11:39:18', '2026-03-30 21:00:00');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `polls`
--
ALTER TABLE `polls`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `polls`
--
ALTER TABLE `polls`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
