-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Jun 16, 2026 at 10:23 PM
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
-- Table structure for table `poll_options`
--

CREATE TABLE `poll_options` (
  `id` int(11) NOT NULL,
  `poll_id` int(11) NOT NULL,
  `content` text NOT NULL,
  `vote_count` varchar(11) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `poll_options`
--

INSERT INTO `poll_options` (`id`, `poll_id`, `content`, `vote_count`) VALUES
(1, 1, 'Red', '3'),
(2, 1, 'Blue', '2'),
(3, 1, 'Green', '0'),
(4, 1, 'Other', '0'),
(5, 2, '1', '0'),
(6, 2, '2', '0'),
(7, 2, '3', '0'),
(8, 3, '1', '0'),
(9, 3, '2', '0'),
(10, 3, '3', '0'),
(11, 4, '112', '2'),
(12, 4, '1212', '1'),
(13, 4, '123123', '0'),
(14, 5, 'testtwe', '0'),
(15, 5, 'hsbshs', '1'),
(16, 5, 'nsnsn', '2'),
(17, 6, '700', '1'),
(18, 6, '800', '2'),
(19, 6, '900', '1'),
(20, 6, '1000', '1'),
(21, 7, 'y', '1'),
(22, 7, 'yy', '0'),
(23, 7, 'yyy', '1'),
(24, 7, 'yyyy', '2'),
(25, 8, 'test 1', '0'),
(26, 8, 'test 2', '1'),
(27, 8, 'test 3', '3'),
(28, 9, '1', '0'),
(29, 9, '2', '0'),
(30, 9, '3', '1'),
(31, 10, 'yes', '0'),
(32, 10, 'no', '1'),
(33, 11, 'ty', '0'),
(34, 11, 'eeee', '0'),
(35, 12, 'see', '1'),
(36, 12, 'see', '0'),
(37, 13, 'Winter', '1'),
(38, 13, 'Summer', '1'),
(39, 13, 'Spring', '2'),
(40, 13, 'Autumn', '1'),
(41, 14, 'g', '1'),
(42, 14, 'f', '1'),
(43, 15, '.', '0'),
(44, 15, '..', '0'),
(45, 15, '...', '1'),
(46, 16, 'test', '0'),
(47, 16, 'test1', '0'),
(48, 17, 'اب', '2'),
(49, 17, 'ابب', '0'),
(50, 18, 'الصحارى الشائعة و المناظر الطبيعية الفريدة', '0'),
(51, 18, 'الجبال و المرتفعات الخلابة', '2'),
(52, 18, 'السواحل و البحار الجميلة', '2'),
(53, 19, 'g', '0'),
(54, 19, 'nm', '1'),
(55, 20, 'نعم', '1'),
(56, 20, 'لا', '0'),
(57, 20, 'ربما', '1'),
(58, 20, 'محتمل', '0'),
(59, 21, 'ممم', '0'),
(60, 21, 'نننن', '2'),
(61, 22, 'الليلة الأولى', '0'),
(62, 22, 'ليلة القدر', '4'),
(63, 22, 'ليلة السابع عشر', '1'),
(64, 23, 'test', '0'),
(65, 23, 'est', '1'),
(66, 24, 'الليلة الأولى', '1'),
(67, 24, 'ليلة القدر', '9'),
(68, 24, 'ليلة السابع عشر', '1'),
(69, 25, 'سورة الزلزلة', '0'),
(70, 25, 'سورة النصر', '0'),
(71, 25, 'سورة العصر', '0'),
(72, 25, 'سورة الكوثر', '0'),
(73, 26, 'نن', '1'),
(74, 26, 'وة', '0'),
(75, 27, 'عيس عليه السلام', '0'),
(76, 27, 'موسى عليه السلام', '0'),
(77, 27, 'سليمان عليه السلام', '0'),
(78, 28, 'سلميان عليه السلام 🌿', '0'),
(79, 28, 'موسى عليه السلام 🌿', '0'),
(80, 28, 'عيسى عليه السلام 🌿', '0');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `poll_options`
--
ALTER TABLE `poll_options`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `poll_options`
--
ALTER TABLE `poll_options`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=81;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
