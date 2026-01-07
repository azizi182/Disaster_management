-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 08, 2026 at 12:00 AM
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
-- Table structure for table `authority_announce`
--

CREATE TABLE `authority_announce` (
  `announce_id` int(11) NOT NULL,
  `authority_id` int(11) NOT NULL,
  `announce_title` varchar(50) NOT NULL,
  `announce_type` enum('alert','info','community','event') NOT NULL,
  `announce_desc` varchar(100) NOT NULL,
  `announce_date` date NOT NULL,
  `announce_location` varchar(100) NOT NULL,
  `created_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `authority_announce`
--

INSERT INTO `authority_announce` (`announce_id`, `authority_id`, `announce_title`, `announce_type`, `announce_desc`, `announce_date`, `announce_location`, `created_date`) VALUES
(1, 1, 'Flood Alert', 'alert', 'Heavy rain causing floods', '2026-01-05', 'Changlun', '2026-01-07 22:35:04'),
(2, 2, 'Community Meeting', 'community', 'Discussing disaster preparedness', '2026-01-06', 'Sintok', '2026-01-07 22:35:04'),
(3, 1, 'Event Cleanup', 'event', 'Village cleanup after floods', '2026-01-07', 'Kubang Pasu', '2026-01-07 22:35:04'),
(4, 2, 'Information Session', 'info', 'Disaster response info', '2026-01-08', 'Changlun', '2026-01-07 22:35:04'),
(5, 1, 'Water Supply Alert', 'alert', 'Limited water supply due to floods', '2026-01-09', 'Sintok', '2026-01-07 22:35:04'),
(6, 2, 'Evacuation Drill', 'event', 'Evacuation drill for villagers', '2026-01-10', 'Kubang Pasu', '2026-01-07 22:35:04'),
(7, 1, 'Health Advisory', 'info', 'Check health centers', '2026-01-11', 'Changlun', '2026-01-07 22:35:04'),
(8, 2, 'Community Training', 'community', 'Training for volunteer responders', '2026-01-12', 'Sintok', '2026-01-07 22:35:04'),
(9, 1, 'Flood Alert 2', 'alert', 'River overflow warning', '2026-01-13', 'Kubang Pasu', '2026-01-07 22:35:04'),
(10, 2, 'Aid Distribution Info', 'info', 'Information about aid centers', '2026-01-14', 'Changlun', '2026-01-07 22:35:04');

-- --------------------------------------------------------

--
-- Table structure for table `ketua_announce`
--

CREATE TABLE `ketua_announce` (
  `announce_id` int(11) NOT NULL,
  `ketua_id` int(11) NOT NULL,
  `kampung_id` int(11) NOT NULL,
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

INSERT INTO `ketua_announce` (`announce_id`, `ketua_id`, `kampung_id`, `announce_title`, `announce_type`, `announce_desc`, `announce_date`, `announce_location`, `created_date`) VALUES
(1, 1, 1, 'Village Alert', 'alert', 'Flood warning for Kampung Baru', '2026-01-05', 'Kampung Baru', '2026-01-07 22:35:53'),
(2, 2, 2, 'Community Info', 'info', 'Meeting to discuss safety', '2026-01-06', 'Kampung Selamat', '2026-01-07 22:35:53'),
(3, 3, 3, 'Event Cleanup', 'event', 'Post-flood cleaning', '2026-01-07', 'Kampung Bahagia', '2026-01-07 22:35:53'),
(4, 4, 4, 'Safety Advisory', 'info', 'Health advisory after flood', '2026-01-08', 'Kampung Sejahtera', '2026-01-07 22:35:53'),
(5, 1, 5, 'Flood Drill', 'event', 'Evacuation drill', '2026-01-09', 'Kampung Mewah', '2026-01-07 22:35:53'),
(6, 2, 1, 'Community Alert', 'alert', 'River overflow warning', '2026-01-10', 'Kampung Baru', '2026-01-07 22:35:53'),
(7, 3, 2, 'Event Training', 'event', 'Volunteer training', '2026-01-11', 'Kampung Selamat', '2026-01-07 22:35:53'),
(8, 4, 3, 'Health Info', 'info', 'Health checkup info', '2026-01-12', 'Kampung Bahagia', '2026-01-07 22:35:53'),
(9, 1, 4, 'Alert Update', 'alert', 'Flood update', '2026-01-13', 'Kampung Sejahtera', '2026-01-07 22:35:53'),
(10, 2, 5, 'Community Event', 'community', 'Village community gathering', '2026-01-14', 'Kampung Mewah', '2026-01-07 22:35:53');

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
(1, 1, 1, 'Flooded Street', 'Main street flooded', 'Changlun', 6.45000000, 100.45000000, '', 'Pending', '2026-01-07 22:37:18'),
(2, 2, 1, 'Damaged Roof', 'Roof blown away by wind', 'Sintok', 6.44000000, 100.44000000, '', 'Pending', '2026-01-07 22:37:18'),
(3, 3, 2, 'Blocked Road', 'Tree fell on road', 'Kubang Pasu', 6.43000000, 100.43000000, '', 'Pending', '2026-01-07 22:37:18'),
(4, 4, 2, 'Water Shortage', 'Limited water supply', 'Changlun', 6.45050000, 100.45050000, '', 'Pending', '2026-01-07 22:37:18'),
(5, 1, 3, 'Electricity Outage', 'Power cut due to flood', 'Sintok', 6.44100000, 100.44100000, '', 'Pending', '2026-01-07 22:37:18'),
(6, 2, 3, 'Road Repair', 'Road damaged, needs repair', 'Kubang Pasu', 6.43100000, 100.43100000, '', 'Pending', '2026-01-07 22:37:18'),
(7, 3, 4, 'Flooded Field', 'Rice field flooded', 'Changlun', 6.45070000, 100.45070000, '', 'Pending', '2026-01-07 22:37:18'),
(8, 4, 4, 'Bridge Damage', 'Bridge weakened', 'Sintok', 6.44200000, 100.44200000, '', 'Pending', '2026-01-07 22:37:18'),
(9, 1, 5, 'Evacuation Needed', 'Evacuate villagers', 'Kubang Pasu', 6.43200000, 100.43200000, '', 'Pending', '2026-01-07 22:37:18'),
(10, 2, 5, 'Health Issue', 'Medical assistance required', 'Changlun', 6.45100000, 100.45100000, '', 'Pending', '2026-01-07 22:37:18');

-- --------------------------------------------------------

--
-- Table structure for table `pejabatdaerah_aid_distribution`
--

CREATE TABLE `pejabatdaerah_aid_distribution` (
  `aid_distribution_id` int(11) NOT NULL,
  `pejabatdaerah_id` int(11) NOT NULL,
  `aid_type` varchar(255) NOT NULL,
  `penghulu_id` int(11) NOT NULL,
  `distribution_title` varchar(255) NOT NULL,
  `distribution_desc` varchar(255) NOT NULL,
  `distribution_date` date NOT NULL,
  `distribution_location` varchar(255) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pejabatdaerah_aid_distribution`
--

INSERT INTO `pejabatdaerah_aid_distribution` (`aid_distribution_id`, `pejabatdaerah_id`, `aid_type`, `penghulu_id`, `distribution_title`, `distribution_desc`, `distribution_date`, `distribution_location`, `created_at`) VALUES
(1, 1, 'Food', 1, 'Food Aid 1', 'Distribution of dry food', '2026-01-05', 'Changlun', '2026-01-08 06:37:35'),
(2, 1, 'Water', 2, 'Water Aid', 'Clean water supply', '2026-01-06', 'Sintok', '2026-01-08 06:37:35'),
(3, 2, 'Clothes', 1, 'Clothes Aid', 'Clothing distribution', '2026-01-07', 'Kubang Pasu', '2026-01-08 06:37:35'),
(4, 2, 'Medicine', 2, 'Medical Aid', 'Medical kit distribution', '2026-01-08', 'Changlun', '2026-01-08 06:37:35'),
(5, 1, 'Food', 3, 'Food Aid 2', 'Rice and canned food', '2026-01-09', 'Sintok', '2026-01-08 06:37:35'),
(6, 1, 'Water', 3, 'Water Aid 2', 'Water bottles distribution', '2026-01-10', 'Kubang Pasu', '2026-01-08 06:37:35'),
(7, 2, 'Clothes', 4, 'Clothes Aid 2', 'Winter clothes', '2026-01-11', 'Changlun', '2026-01-08 06:37:35'),
(8, 2, 'Medicine', 4, 'Medical Aid 2', 'First aid kits', '2026-01-12', 'Sintok', '2026-01-08 06:37:35'),
(9, 1, 'Food', 5, 'Food Aid 3', 'Meal packs', '2026-01-13', 'Kubang Pasu', '2026-01-08 06:37:35'),
(10, 1, 'Water', 5, 'Water Aid 3', 'Drinking water', '2026-01-14', 'Changlun', '2026-01-08 06:37:35');

-- --------------------------------------------------------

--
-- Table structure for table `pejabatdaerah_report`
--

CREATE TABLE `pejabatdaerah_report` (
  `pejabatdaerah_report_id` int(11) NOT NULL,
  `pejabatdaerah_id` int(11) NOT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_desc` text NOT NULL,
  `report_location` varchar(200) NOT NULL,
  `report_latitude` decimal(10,8) NOT NULL,
  `report_longitude` decimal(11,8) NOT NULL,
  `report_status` varchar(200) NOT NULL,
  `report_feedback` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pejabatdaerah_report`
--

INSERT INTO `pejabatdaerah_report` (`pejabatdaerah_report_id`, `pejabatdaerah_id`, `report_title`, `report_desc`, `report_location`, `report_latitude`, `report_longitude`, `report_status`, `report_feedback`, `created_at`) VALUES
(1, 1, 'Flood Status', 'Flood status in Changlun', 'Changlun', 6.45000000, 100.45000000, 'Pending', '', '2026-01-07 22:38:18'),
(2, 1, 'Road Status', 'Blocked road report', 'Sintok', 6.44000000, 100.44000000, 'Pending', '', '2026-01-07 22:38:18'),
(3, 2, 'Electricity Status', 'Power outage', 'Kubang Pasu', 6.43000000, 100.43000000, 'Pending', '', '2026-01-07 22:38:18'),
(4, 2, 'Water Supply', 'Limited water availability', 'Changlun', 6.45050000, 100.45050000, 'Pending', '', '2026-01-07 22:38:18'),
(5, 1, 'Bridge Damage', 'Bridge weakened', 'Sintok', 6.44100000, 100.44100000, 'Pending', '', '2026-01-07 22:38:18'),
(6, 1, 'Medical Aid', 'Health emergency', 'Kubang Pasu', 6.43100000, 100.43100000, 'Pending', '', '2026-01-07 22:38:18'),
(7, 2, 'Evacuation Status', 'Evacuation needed', 'Changlun', 6.45070000, 100.45070000, 'Pending', '', '2026-01-07 22:38:18'),
(8, 2, 'Community Info', 'Safety info for villagers', 'Sintok', 6.44200000, 100.44200000, 'Pending', '', '2026-01-07 22:38:18'),
(9, 1, 'Food Distribution', 'Food aid distributed', 'Kubang Pasu', 6.43200000, 100.43200000, 'Pending', '', '2026-01-07 22:38:18'),
(10, 1, 'Clothes Distribution', 'Clothes aid distributed', 'Changlun', 6.45100000, 100.45100000, 'Pending', '', '2026-01-07 22:38:18');

-- --------------------------------------------------------

--
-- Table structure for table `penghulu_report`
--

CREATE TABLE `penghulu_report` (
  `penghulu_report_id` int(11) NOT NULL,
  `penghulu_id` int(11) NOT NULL,
  `pejabat_daerah_id` int(11) NOT NULL,
  `report_title` varchar(200) NOT NULL,
  `report_desc` text NOT NULL,
  `report_location` varchar(200) NOT NULL,
  `report_status` varchar(200) NOT NULL,
  `report_feedback` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `penghulu_report`
--

INSERT INTO `penghulu_report` (`penghulu_report_id`, `penghulu_id`, `pejabat_daerah_id`, `report_title`, `report_desc`, `report_location`, `report_status`, `report_feedback`, `created_at`) VALUES
(1, 1, 1, 'Flood Report', 'Flooded streets', 'Changlun', 'Pending', '', '2026-01-07 22:39:15'),
(2, 2, 1, 'Electricity Cut', 'Power outage reported', 'Sintok', 'Pending', '', '2026-01-07 22:39:15'),
(3, 3, 2, 'Blocked Road', 'Tree fell on road', 'Kubang Pasu', 'Pending', '', '2026-01-07 22:39:15'),
(4, 1, 2, 'Health Issue', 'Medical help needed', 'Changlun', 'Pending', '', '2026-01-07 22:39:15'),
(5, 2, 1, 'Water Shortage', 'Limited clean water', 'Sintok', 'Pending', '', '2026-01-07 22:39:15'),
(6, 3, 1, 'Bridge Damage', 'Bridge weakened by flood', 'Kubang Pasu', 'Pending', '', '2026-01-07 22:39:15'),
(7, 1, 2, 'Evacuation', 'Villagers evacuated', 'Changlun', 'Pending', 'Pending', '2026-01-07 22:39:15'),
(8, 2, 2, 'Community Event', 'Community gathering for aid', 'Sintok', 'Pending', '', '2026-01-07 22:39:15'),
(9, 3, 1, 'Food Distribution', 'Food packs distributed', 'Kubang Pasu', 'Pending', '', '2026-01-07 22:39:15'),
(10, 1, 1, 'Clothes Distribution', 'Clothes aid given', 'Changlun', 'Pending', '', '2026-01-07 22:39:15');

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
(1, 1, 0, 'Need help, flood', 6.45000000, 100.45000000, 'Sent', '2026-01-07 22:41:26'),
(2, 2, 0, 'Medical emergency', 6.44000000, 100.44000000, 'Sent', '2026-01-07 22:41:26'),
(3, 3, 0, 'Blocked road', 6.43000000, 100.43000000, 'Sent', '2026-01-07 22:41:26'),
(4, 4, 0, 'Electricity out', 6.45050000, 100.45050000, 'Sent', '2026-01-07 22:41:26'),
(5, 5, 0, 'Water shortage', 6.44100000, 100.44100000, 'Sent', '2026-01-07 22:41:26'),
(6, 6, 0, 'Bridge damaged', 6.43150000, 100.43150000, 'Sent', '2026-01-07 22:41:26'),
(7, 7, 0, 'Evacuation needed', 6.45070000, 100.45070000, 'Sent', '2026-01-07 22:41:26'),
(8, 8, 0, 'Health assistance', 6.44200000, 100.44200000, 'Sent', '2026-01-07 22:41:26'),
(9, 9, 0, 'Food aid required', 6.43200000, 100.43200000, 'Sent', '2026-01-07 22:41:26'),
(10, 10, 0, 'Clothes aid required', 6.45100000, 100.45100000, 'Sent', '2026-01-07 22:41:26');

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
(3, 'Kampung Bahagia'),
(4, 'Kampung Sejahtera'),
(5, 'Kampung Mewah');

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
(1, 'Ali', 'ali@example.com', 'password1', 'villager', '2026-01-07 22:58:50'),
(2, 'Siti', 'siti@example.com', 'password2', 'villager', '2026-01-07 22:58:50'),
(3, 'Abu', 'abu@example.com', 'password3', 'ketuakampung', '2026-01-07 22:58:50'),
(4, 'Ahmad', 'ahmad@example.com', 'password4', 'penghulu', '2026-01-07 22:58:50'),
(5, 'Halim', 'halim@example.com', 'password5', 'pejabatdaerah', '2026-01-07 22:58:50'),
(6, 'Rashid', 'rashid@example.com', 'password6', 'villager', '2026-01-07 22:58:50'),
(7, 'Aminah', 'aminah@example.com', 'password7', 'ketuakampung', '2026-01-07 22:58:50'),
(8, 'Zain', 'zain@example.com', 'password8', 'penghulu', '2026-01-07 22:58:50'),
(9, 'Lina', 'lina@example.com', 'password9', 'pejabatdaerah', '2026-01-07 22:58:50'),
(10, 'Farid', 'farid@example.com', 'password10', 'villager', '2026-01-07 22:58:50');

-- --------------------------------------------------------

--
-- Table structure for table `user_kampung`
--

CREATE TABLE `user_kampung` (
  `user_id` int(11) NOT NULL,
  `kampung_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_kampung`
--

INSERT INTO `user_kampung` (`user_id`, `kampung_id`) VALUES
(1, 1),
(2, 2),
(3, 3),
(4, 4),
(5, 5),
(6, 1),
(7, 2),
(8, 3),
(9, 4),
(10, 5);

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
(1, 1, 1, 'Flooded Street', 'flood', 'Main street flooded', '0123456789', '2026-01-05', 'Changlun', 6.45000000, 100.45000000, 'Pending', '', '2026-01-07 22:59:53'),
(2, 2, 1, 'Electricity Cut', 'electric', 'Power outage reported', '0123456788', '2026-01-06', 'Sintok', 6.44000000, 100.44000000, 'Pending', '', '2026-01-07 22:59:53'),
(3, 3, 2, 'Blocked Road', 'road', 'Tree fell on road', '0123456787', '2026-01-07', 'Kubang Pasu', 6.43000000, 100.43000000, 'Pending', '', '2026-01-07 22:59:53'),
(4, 4, 2, 'Health Issue', 'health', 'Medical help needed', '0123456786', '2026-01-08', 'Changlun', 6.45050000, 100.45050000, 'Pending', '', '2026-01-07 22:59:53'),
(5, 5, 3, 'Water Shortage', 'water', 'Limited water supply', '0123456785', '2026-01-09', 'Sintok', 6.44100000, 100.44100000, 'Pending', '', '2026-01-07 22:59:53'),
(6, 6, 3, 'Bridge Damage', 'infrastructure', 'Bridge weakened', '0123456784', '2026-01-10', 'Kubang Pasu', 6.43100000, 100.43100000, '', '', '2026-01-07 22:59:53'),
(7, 7, 4, 'Evacuation', 'safety', 'Villagers evacuated', '0123456783', '2026-01-11', 'Changlun', 6.45070000, 100.45070000, 'Pending', '', '2026-01-07 22:59:53'),
(8, 8, 4, 'Food Distribution', 'aid', 'Food packs distributed', '0123456782', '2026-01-12', 'Sintok', 6.44200000, 100.44200000, 'Pending', '', '2026-01-07 22:59:53'),
(9, 9, 5, 'Medical Aid', 'health', 'Medical kit distribution', '0123456781', '2026-01-13', 'Kubang Pasu', 6.43200000, 100.43200000, 'Pending', '', '2026-01-07 22:59:53'),
(10, 10, 5, 'Clothes Distribution', 'aid', 'Clothes aid given', '0123456780', '2026-01-14', 'Changlun', 6.45100000, 100.45100000, 'Pending', '', '2026-01-07 22:59:53');

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
-- Indexes for table `pejabatdaerah_aid_distribution`
--
ALTER TABLE `pejabatdaerah_aid_distribution`
  ADD PRIMARY KEY (`aid_distribution_id`);

--
-- Indexes for table `penghulu_report`
--
ALTER TABLE `penghulu_report`
  ADD PRIMARY KEY (`penghulu_report_id`);

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
  MODIFY `announce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `ketua_report`
--
ALTER TABLE `ketua_report`
  MODIFY `kt_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `pejabatdaerah_aid_distribution`
--
ALTER TABLE `pejabatdaerah_aid_distribution`
  MODIFY `aid_distribution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `penghulu_report`
--
ALTER TABLE `penghulu_report`
  MODIFY `penghulu_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `sos_villager`
--
ALTER TABLE `sos_villager`
  MODIFY `sos_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_kampung`
--
ALTER TABLE `tbl_kampung`
  MODIFY `kampung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `villager_report`
--
ALTER TABLE `villager_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
