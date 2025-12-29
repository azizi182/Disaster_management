-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 02:47 AM
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
-- Database: `disaster_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `ketua_announce`
--

CREATE TABLE `ketua_announce` (
  `announce_id` int(11) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `announce_title` varchar(50) NOT NULL,
  `announce_type` enum('alert','info','community','event') NOT NULL,
  `announce_desc` varchar(100) NOT NULL,
  `announce_date` date NOT NULL,
  `announce_location` varchar(100) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketua_announce`
--

INSERT INTO `ketua_announce` (`announce_id`, `ketua_id`, `announce_title`, `announce_type`, `announce_desc`, `announce_date`, `announce_location`, `created_date`) VALUES
(1, 2, 'test by ketua', 'event', 'tesyysysys', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:22:10'),
(2, 2, 'test by ketua', 'event', 'tesyysysys', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:23:31'),
(3, 6, 'alert by ahmad', 'alert', 'asasa', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:24:08'),
(4, 6, 'll', 'event', 'll', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:44:01'),
(5, 6, 'banjir', 'info', 'aaaa', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:44:18'),
(6, 2, 'assa', 'community', 'fg', '2025-12-18', 'kampung fuvking l=lima', '2025-12-17 16:46:26'),
(7, 2, 'banjir', 'alert', '1131', '2025-12-19', '', '2025-12-17 16:46:36');

-- --------------------------------------------------------

--
-- Table structure for table `ketua_report`
--

CREATE TABLE `ketua_report` (
  `kt_report_id` int(11) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `penghulu_id` int(11) NOT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_desc` text NOT NULL,
  `report_location` varchar(200) NOT NULL,
  `report_latitude` decimal(10,8) NOT NULL,
  `report_longitude` decimal(11,8) NOT NULL,
  `report_status` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketua_report`
--

INSERT INTO `ketua_report` (`kt_report_id`, `ketua_id`, `penghulu_id`, `report_title`, `report_desc`, `report_location`, `report_latitude`, `report_longitude`, `report_status`, `created_at`) VALUES
(1, 2, 3, 'report to penghulu', 'test1', 'jalan fucking 5', 0.00000000, 0.00000000, 'Pending', '2025-12-26 11:30:46'),
(2, 2, 3, 'report to penghulu', 'test1', 'jalan fucking 5', 0.00000000, 0.00000000, 'Pending', '2025-12-26 11:30:49'),
(3, 2, 3, 'report to penghulu', 'test2', 'jalan fucking 5', 0.00000000, 0.00000000, 'Pending', '2025-12-26 11:33:23');

-- --------------------------------------------------------

--
-- Table structure for table `sos_villager`
--

CREATE TABLE `sos_villager` (
  `sos_id` int(11) NOT NULL,
  `villager_id` int(11) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `sos_msg` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `sos_status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sos_villager`
--

INSERT INTO `sos_villager` (`sos_id`, `villager_id`, `ketua_id`, `sos_msg`, `latitude`, `longitude`, `sos_status`, `created_at`) VALUES
(1, 1, 6, '', 0.00000000, 0.00000000, 'Resolved', '2025-12-19 14:27:30'),
(2, 1, 2, '', 0.00000000, 0.00000000, 'Resolved', '2025-12-19 14:28:27'),
(3, 5, 2, '', 0.00000000, 0.00000000, 'Resolved', '2025-12-19 14:28:49'),
(4, 1, 2, '', 0.00000000, 0.00000000, 'Resolved', '2025-12-25 15:06:09'),
(5, 1, 2, '', 6.43799783, 100.19421387, 'Resolved', '2025-12-26 14:17:18'),
(6, 1, 2, '', 6.43782726, 100.19387055, 'Resolved', '2025-12-26 17:23:16'),
(7, 1, 0, '', 6.44516210, 100.20708847, 'Sent', '2025-12-26 18:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(100) NOT NULL,
  `user_role` enum('villager','ketuakampung','penghulu','pejabatdaerah','kplbhq') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_role`, `created_at`) VALUES
(1, 'az', 'az@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'villager', '2025-12-16 08:58:09'),
(2, 'ketua', 'ketua@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'ketuakampung', '2025-12-16 08:59:08'),
(3, 'penghulu', 'penghulu@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'penghulu', '2025-12-16 09:08:13'),
(4, 'pejabat', 'pejabat@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'pejabatdaerah', '2025-12-16 09:08:58'),
(5, 'poji', 'poji@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'villager', '2025-12-16 14:43:57'),
(6, 'ahmad', 'ahmad@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'ketuakampung', '2025-12-16 15:27:03');

-- --------------------------------------------------------

--
-- Table structure for table `villager_report`
--

CREATE TABLE `villager_report` (
  `report_id` int(11) NOT NULL,
  `villager_id` int(100) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `report_title` varchar(100) NOT NULL,
  `report_type` varchar(100) NOT NULL,
  `report_desc` varchar(100) NOT NULL,
  `report_phone` varchar(50) NOT NULL,
  `report_date` date NOT NULL,
  `report_location` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `report_status` varchar(30) NOT NULL,
  `report_feedback` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `villager_report`
--

INSERT INTO `villager_report` (`report_id`, `villager_id`, `ketua_id`, `report_title`, `report_type`, `report_desc`, `report_phone`, `report_date`, `report_location`, `latitude`, `longitude`, `report_status`, `report_feedback`, `created_at`) VALUES
(1, 5, 6, 'jalan rosak', 'Road Damage', 'test', '234223', '2025-12-16', 'jalan fucking 5', 0.00000000, 0.00000000, 'Approved', 'test second feedback', '2025-12-16 15:57:36'),
(2, 5, 2, 'yes3', 'Power Failure', 'eweew', '234223', '2025-12-16', 'jalan fucking 5', 0.00000000, 0.00000000, 'Rejected', '', '2025-12-16 16:03:54'),
(4, 1, 2, 'aa', 'Road Damage', 'aa', '0172922723', '2025-12-17', 'jalan fucking 5', 0.00000000, 0.00000000, 'Approved', 'test feedback to az', '2025-12-16 17:56:55'),
(5, 5, 2, 'tesy', 'Road Damage', 'trs', '0172922723', '2025-12-17', 'jalan fucking 5', 0.00000000, 0.00000000, 'Rejected', 'Report rejected by ketua (Ketua Kampung)', '2025-12-16 18:05:27'),
(6, 5, 6, 'wewew', 'Other', 'wewe', '0172922723', '2025-12-17', 'jalan fucking 5', 0.00000000, 0.00000000, 'Rejected', 'Report rejected by ahmad (Ketua Kampung)', '2025-12-16 18:05:40'),
(8, 1, 2, 'sd', 'Flood', 'df', '0172922723', '2025-12-17', 'jalan fucking 5', 0.00000000, 0.00000000, 'Rejected', 'Report rejected by ketua (Ketua Kampung)', '2025-12-16 18:19:39'),
(9, 1, 2, '12', 'Power Failure', 'asasasas', '0172922723', '2025-12-17', 'jalan fucking 5', 0.00000000, 0.00000000, 'Approved', '1234', '2025-12-17 15:46:01'),
(10, 1, 6, 'map', 'Road Damage', 'asas', '0172922723', '2025-12-26', 'jalan fucking 5', 6.47635743, 100.25913620, 'Approved', 'APPROVE MAP', '2025-12-26 13:46:48'),
(11, 1, 6, 'test map', 'Flood', '1345465', '0172922723', '2025-12-26', 'jalan fucking 5', 6.43216934, 100.42896938, 'Approved', 'APPROVE MAP2', '2025-12-26 13:56:30'),
(12, 1, 6, 'tesy', 'Power Failure', 'wrtyt', '0172922723', '2025-12-26', 'jalan fucking 5', 6.43680378, 100.19438553, 'Approved', 'APPROVE MAP3', '2025-12-26 14:17:03'),
(13, 1, 6, 'TTT', 'Road Damage', 'TR', '0172922723', '2025-12-26', 'jalan fucking 5', 6.43816841, 100.19387055, 'Pending', '', '2025-12-26 14:56:07'),
(14, 1, 2, '34', 'Flood', '256', '0172922723', '2025-12-26', 'jalan fucking 5', 6.44396806, 100.22202301, 'Pending', '', '2025-12-26 18:01:50');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ketua_announce`
--
ALTER TABLE `ketua_announce`
  ADD PRIMARY KEY (`announce_id`);

--
-- Indexes for table `ketua_report`
--
ALTER TABLE `ketua_report`
  ADD PRIMARY KEY (`kt_report_id`);

--
-- Indexes for table `sos_villager`
--
ALTER TABLE `sos_villager`
  ADD PRIMARY KEY (`sos_id`);

--
-- Indexes for table `tbl_users`
--
ALTER TABLE `tbl_users`
  ADD PRIMARY KEY (`user_id`);

--
-- Indexes for table `villager_report`
--
ALTER TABLE `villager_report`
  ADD PRIMARY KEY (`report_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ketua_announce`
--
ALTER TABLE `ketua_announce`
  MODIFY `announce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `ketua_report`
--
ALTER TABLE `ketua_report`
  MODIFY `kt_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `sos_villager`
--
ALTER TABLE `sos_villager`
  MODIFY `sos_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `villager_report`
--
ALTER TABLE `villager_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
