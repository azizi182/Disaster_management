-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 29, 2025 at 06:21 PM
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

DROP TABLE IF EXISTS ketua_announce;
DROP TABLE IF EXISTS ketua_report;
DROP TABLE IF EXISTS sos_villager;
DROP TABLE IF EXISTS tbl_users;
DROP TABLE IF EXISTS villager_report;

CREATE TABLE `ketua_announce` (
  `announce_id` int(11) NOT NULL AUTO_INCREMENT,
  `ketua_id` int(11) NOT NULL,
  `announce_title` varchar(50) NOT NULL,
  `announce_type` enum('alert','info','community','event') NOT NULL,
  `announce_desc` varchar(100) NOT NULL,
  `announce_date` date NOT NULL,
  `announce_location` varchar(100) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`announce_id`);
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketua_announce`
--

INSERT INTO `ketua_announce` (`announce_id`, `ketua_id`, `announce_title`, `announce_type`, `announce_desc`, `announce_date`, `announce_location`, `created_date`) VALUES
(1, 4, 'Flood Warning', 'alert', 'Heavy rain expected. Please prepare.', '2025-12-30', 'Kampung A', '2025-12-30 08:00:00'),
(2, 4, 'Community Meeting', 'community', 'Monthly meeting at the community hall.', '2025-12-31', 'Kampung A', '2025-12-30 09:00:00'),
(3, 4, 'Road Repair', 'info', 'Road repair starts tomorrow morning.', '2025-12-30', 'Kampung B', '2025-12-30 10:00:00'),
(4, 5, 'Village Festival', 'event', 'Join us for the annual village festival.', '2025-12-31', 'Kampung C', '2025-12-30 11:00:00'),
(5, 5, 'Water Supply Alert', 'alert', 'Temporary water supply interruption.', '2025-12-30', 'Kampung C', '2025-12-30 12:00:00'),
(6, 5, 'Health Awareness', 'info', 'Free health check-up for villagers.', '2025-12-31', 'Kampung D', '2025-12-30 13:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `ketua_report`
--

CREATE TABLE `ketua_report` (
  `kt_report_id` int(11) NOT NULL AUTO_INCREMENT,
  `ketua_id` int(11) NOT NULL,
  `penghulu_id` int(11) NOT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_desc` text NOT NULL,
  `report_location` varchar(200) NOT NULL,
  `report_latitude` decimal(10,8) NOT NULL,
  `report_longitude` decimal(11,8) NOT NULL,
  `report_feedback` varchar(255) NOT NULL,
  `report_status` varchar(200) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`kt_report_id`);
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `ketua_report`
--

INSERT INTO `ketua_report` (`kt_report_id`, `ketua_id`, `penghulu_id`, `report_title`, `report_desc`, `report_location`, `report_latitude`, `report_longitude`, `report_feedback`, `report_status`, `created_at`) VALUES
(1, 4, 6, 'Ketua Report 1', 'Pending report by ketua1', 'Kampung A', 6.40000000, 100.20000000, '', 'Pending', '2025-12-30 10:00:00'),
(2, 4, 7, 'Ketua Report 2', 'Pending report by ketua1', 'Kampung B', 6.40100000, 100.20100000, '', 'Pending', '2025-12-30 10:05:00'),
(3, 5, 8, 'Ketua Report 1', 'Pending report by ketua2', 'Kampung C', 6.40200000, 100.20200000, '', 'Pending', '2025-12-30 10:10:00'),
(4, 5, 6, 'Ketua Report 2', 'Pending report by ketua2', 'Kampung D', 6.40300000, 100.20300000, '', 'Pending', '2025-12-30 10:15:00');

-- --------------------------------------------------------

--
-- Table structure for table `sos_villager`
--

CREATE TABLE `sos_villager` (
  `sos_id` int(11) NOT NULL AUTO_INCREMENT,
  `villager_id` int(11) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `sos_msg` varchar(100) NOT NULL,
  `latitude` decimal(10,8) NOT NULL,
  `longitude` decimal(11,8) NOT NULL,
  `sos_status` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`sos_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sos_villager`
--

INSERT INTO `sos_villager` (`sos_id`, `villager_id`, `ketua_id`, `sos_msg`, `latitude`, `longitude`, `sos_status`, `created_at`) VALUES
(7, 1, 0, '', 6.44516210, 100.20708847, 'Sent', '2025-12-26 18:10:54');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_users`
--

CREATE TABLE `tbl_users` (
  `user_id` int(11) NOT NULL AUTO_INCREMENT,
  `user_name` varchar(100) NOT NULL,
  `user_email` varchar(100) NOT NULL,
  `user_password` varchar(100) NOT NULL,
  `user_role` enum('villager','ketuakampung','penghulu','pejabatdaerah','kplbhq') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_users`
--

INSERT INTO `tbl_users` (`user_id`, `user_name`, `user_email`, `user_password`, `user_role`, `created_at`) VALUES
(1, 'villager1', 'villager1@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'villager', '2025-12-16 08:58:09'),
(2, 'villager2', 'villager2@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'villager', '2025-12-16 08:59:08'),
(3, 'villager3', 'villager3@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'villager', '2025-12-16 09:00:00'),
(4, 'ketua1', 'ketua1@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'ketuakampung', '2025-12-16 09:01:00'),
(5, 'ketua2', 'ketua2@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'ketuakampung', '2025-12-16 09:02:00'),
(6, 'penghulu1', 'penghulu1@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'penghulu', '2025-12-16 09:03:00'),
(7, 'penghulu2', 'penghulu2@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'penghulu', '2025-12-16 09:04:00'),
(8, 'penghulu3', 'penghulu3@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'penghulu', '2025-12-16 09:05:00'),
(9, 'pejabat', 'pejabat@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'pejabatdaerah', '2025-12-16 09:06:00'),
(10, 'kplbhq', 'kplbhq@gmail.com', '8cb2237d0679ca88db6464eac60da96345513964', 'kplbhq', '2025-12-16 09:07:00');

-- --------------------------------------------------------

--
-- Table structure for table `villager_report`
--

CREATE TABLE `villager_report` (
  `report_id` int(11) NOT NULL AUTO_INCREMENT,
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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`report_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `villager_report`
--

INSERT INTO `villager_report` (`report_id`, `villager_id`, `ketua_id`, `report_title`, `report_type`, `report_desc`, `report_phone`, `report_date`, `report_location`, `latitude`, `longitude`, `report_status`, `report_feedback`, `created_at`) VALUES
(1, 1, 4, 'Villager1 Report 1', 'Road Damage', 'Pending report', '0123456789', '2025-12-30', 'Kampung A', 6.41000000, 100.21000000, 'Pending', '', '2025-12-30 11:00:00'),
(2, 1, 5, 'Villager1 Report 2', 'Flood', 'Pending report', '0123456789', '2025-12-30', 'Kampung B', 6.41100000, 100.21100000, 'Pending', '', '2025-12-30 11:05:00'),
(3, 2, 4, 'Villager2 Report 1', 'Power Failure', 'Pending report', '0123456789', '2025-12-30', 'Kampung C', 6.41200000, 100.21200000, 'Pending', '', '2025-12-30 11:10:00'),
(4, 2, 5, 'Villager2 Report 2', 'Other', 'Pending report', '0123456789', '2025-12-30', 'Kampung D', 6.41300000, 100.21300000, 'Pending', '', '2025-12-30 11:15:00'),
(5, 3, 4, 'Villager3 Report 1', 'Road Damage', 'Pending report', '0123456789', '2025-12-30', 'Kampung A', 6.41400000, 100.21400000, 'Pending', '', '2025-12-30 11:20:00'),
(6, 3, 5, 'Villager3 Report 2', 'Flood', 'Pending report', '0123456789', '2025-12-30', 'Kampung B', 6.41500000, 100.21500000, 'Pending', '', '2025-12-30 11:25:00');

--
-- Indexes for dumped tables
--

-- AUTO_INCREMENT for dumped tables
--

--
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
