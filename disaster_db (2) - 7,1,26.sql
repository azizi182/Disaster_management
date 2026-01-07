-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 04:48 PM
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
(1, 4, 'Flood Warning', 'alert', 'Heavy rain expected. Please prepare.', '2025-12-30', 'Kampung A', '2025-12-30 00:00:00'),
(2, 4, 'Community Meeting', 'community', 'Monthly meeting at the community hall.', '2025-12-31', 'Kampung A', '2025-12-30 01:00:00'),
(3, 4, 'Road Repair', 'info', 'Road repair starts tomorrow morning.', '2025-12-30', 'Kampung B', '2025-12-30 02:00:00'),
(4, 5, 'Village Festival', 'event', 'Join us for the annual village festival.', '2025-12-31', 'Kampung C', '2025-12-30 03:00:00'),
(5, 5, 'Water Supply Alert', 'alert', 'Temporary water supply interruption.', '2025-12-30', 'Kampung C', '2025-12-30 04:00:00'),
(6, 5, 'Health Awareness', 'info', 'Free health check-up for villagers.', '2025-12-31', 'Kampung D', '2025-12-30 05:00:00');

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
  `report_feedback` varchar(255) NOT NULL,
  `report_status` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketua_report`
--

INSERT INTO `ketua_report` (`kt_report_id`, `ketua_id`, `penghulu_id`, `report_title`, `report_desc`, `report_location`, `report_latitude`, `report_longitude`, `report_feedback`, `report_status`, `created_at`) VALUES
(1, 4, 6, 'Ketua Report 1', 'Pending report by ketua1', 'Kampung A', 6.40000000, 100.20000000, 'penghulu to ketua kampung', 'Approved', '2025-12-30 02:00:00'),
(2, 4, 7, 'Ketua Report 2', 'Pending report by ketua1', 'Kampung B', 6.40100000, 100.20100000, '', 'Pending', '2025-12-30 02:05:00'),
(3, 5, 8, 'Ketua Report 1', 'Pending report by ketua2', 'Kampung C', 6.40200000, 100.20200000, '', 'Pending', '2025-12-30 02:10:00'),
(4, 5, 6, 'Ketua Report 2', 'Pending report by ketua2', 'Kampung D', 6.40300000, 100.20300000, '', 'Pending', '2025-12-30 02:15:00');

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
(1, 1, 0, '', 6.44516210, 100.20708847, 'Sent', '2025-12-26 10:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_kampung`
--

CREATE TABLE `tbl_kampung` (
  `kampung_id` int(11) NOT NULL,
  `kampung_name` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_kampung`
--

INSERT INTO `tbl_kampung` (`kampung_id`, `kampung_name`) VALUES
(1, 'Kampung Baru'),
(2, 'Kampung Selamat'),
(3, 'Kampung Bahagia');

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
  `kampung_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_role`, `kampung_id`, `created_at`) VALUES
(1, 'azizi', 'az@gmail.com', '$2y$10$J8fjoS8pW5Z3nhsj0t5YnO6zazvR66anWDjNHsg9gN0qbh2VSQFmK', 'villager', 1, '2026-01-07 13:25:50'),
(2, 'poji', 'poji@gmail.com', '$2y$10$TYOzEBSko48dDBqkL19om.R3woxNwlHTkNba5LGZEMlyqD5AyY82O', 'villager', 2, '2026-01-07 13:28:45'),
(3, 'ketua baru', 'ketuabaru@gmail.com', '$2y$10$Yj3CmkRxtGW4DP8sOPSK8.y3fdpVE7SHnmeTrHguj/J5OpTAfZlri', 'ketuakampung', 1, '2026-01-07 13:59:49'),
(4, 'ketua selamat', 'ketuaselamat@gmail.com', '$2y$10$MYgV0lhn9s6rO84Lv3p/IOpFHbCz8tqgOdP8tiv.yyih2wAKHLtFS', 'ketuakampung', 2, '2026-01-07 14:00:11'),
(5, 'ketua baru 2', 'ketuabaru2@gmail.com', '$2y$10$eI1R8wdB/xy2BhlJbiP4CezA0/TpTGEzcE4O/oY8QkmxTjY1JkjrO', 'ketuakampung', 1, '2026-01-07 14:00:30');

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
(1, 1, 3, 'OR 1=1; -- ', 'Road Damage', 'jalan rosak', '0172922723', '2026-01-07', 'jalan baru2', 6.43765668, 100.19421387, 'error', '', '2026-01-07 15:09:25'),
(2, 1, 3, 'OR 1=1; -- ', 'Road Damage', 'OR 1=1; -- ', '0172922723', '2026-01-07', 'OR 1=1; -- ', 6.43868015, 100.19558716, 'error', '', '2026-01-07 15:10:53'),
(3, 1, 3, 'OR 1=1; -- ', 'Road Damage', 'asasa', '0172922723', '2026-01-07', 'jalan', 6.44362690, 100.23472595, 'error', '', '2026-01-07 15:12:42'),
(4, 1, 3, 'jalan', 'Road Damage', 'jalan', '123', '2026-01-07', 'jalan', 6.43714494, 100.19490051, 'Pending', '', '2026-01-07 15:35:55');

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
-- Indexes for table `tbl_kampung`
--
ALTER TABLE `tbl_kampung`
  ADD PRIMARY KEY (`kampung_id`);

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
  MODIFY `announce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `ketua_report`
--
ALTER TABLE `ketua_report`
  MODIFY `kt_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `sos_villager`
--
ALTER TABLE `sos_villager`
  MODIFY `sos_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_kampung`
--
ALTER TABLE `tbl_kampung`
  MODIFY `kampung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `villager_report`
--
ALTER TABLE `villager_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
