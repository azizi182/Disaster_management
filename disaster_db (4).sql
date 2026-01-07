-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 07, 2026 at 11:14 PM
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
(1, 7, 1, 'test', 'event', 'tets', '2026-01-08', 'test', '2026-01-07 18:56:44');

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
(1, 7, 6, 'report to penghulu', 'saasasa', 'location', 0.00000000, 0.00000000, 'tetsttt', 'Approved', '2026-01-07 19:14:08'),
(2, 7, 6, 'test2', 'test ketua to penghulu', '1212', 0.00000000, 0.00000000, '', 'Pending', '2026-01-07 19:14:59'),
(3, 9, 6, 'test ketua2', 'ketua2', '1212', 0.00000000, 0.00000000, '', 'Pending', '2026-01-07 19:20:41');

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
(1, 13, 'water', 14, 'test', 'test', '2026-01-08', 'jalan', '2026-01-08 05:29:20'),
(2, 13, 'transportation', 6, 'test', 'test', '2026-01-08', 'jalan', '2026-01-08 05:52:37');

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
(1, 6, 13, 'like &lt;script&gt;alert(&#039;XSS&#039;);&lt;/script&gt;', 'test', 'test', 'Pending', '', '2026-01-07 20:52:31');

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
(1, 1, 0, '', 6.44345633, 100.20502853, 'Sent', '2026-01-07 19:31:02'),
(2, 1, 0, '', 6.43663320, 100.21961975, 'Sent', '2026-01-07 19:31:10'),
(3, 1, 0, '', 6.43663320, 100.21961975, 'Sent', '2026-01-07 19:31:14'),
(4, 1, 0, '', 6.43663320, 100.21961975, 'Sent', '2026-01-07 19:34:07'),
(5, 1, 0, '', 6.44345633, 100.20502853, 'Sent', '2026-01-07 19:34:59'),
(6, 1, 0, '', 6.43731552, 100.20485687, 'Sent', '2026-01-07 19:35:07'),
(7, 1, 0, '', 6.44277402, 100.21223831, 'Sent', '2026-01-07 19:36:30');

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
(1, 'azizi', 'azizi@gmail.com', '$2y$10$3l3bds1I37hqn2pZGNPOiu1EaT6VyrdPZJDLaXK.gZRuYmo2c5G1S', 'villager', '2026-01-07 18:34:57'),
(2, 'nabil', 'nabil@gmail.com', '$2y$10$xfpLLZGye2eAfsSqn8qAzO1PXzL9xIayfo5WpToubhglvebPDYWJK', 'villager', '2026-01-07 18:37:38'),
(3, 'nafis', 'nafis@gmail.com', '$2y$10$feJWa8AWW4Mr/cW632qHxuxvMvK6yAo9hFm7XkY5UD0Y38ZlEjgY2', 'villager', '2026-01-07 18:42:17'),
(4, 'megat', 'megat@gmail.com', '$2y$10$fmXQkP3Tc4E1LQS8wtDuyuIOQdxi2GcI44Cc3ajfOgtM8MEZJ.shq', 'villager', '2026-01-07 18:43:19'),
(5, 'fadil', 'fadil@gmail.com', '$2y$10$aJNxozo6gy9PhhfPZkvKN.i4ksn9j8YBOHR1unOMcwEJqdYlpzr9K', 'villager', '2026-01-07 18:45:06'),
(6, 'penghulu', 'penghulu@gmail.com', '$2y$10$Pda5tqJbfQRU7nLUOWAs6.UI1rCtoeYT.s7H37cKxJvMSuHPH2N6.', 'penghulu', '2026-01-07 18:45:25'),
(7, 'ketua', 'ketua@gmail.com', '$2y$10$7TLYBZgG1N5TGMgEpp31qe/b2ggmFpnAavsvuFGAdE.D9Xo37Th5y', 'ketuakampung', '2026-01-07 18:46:08'),
(8, 'aisar', 'aisar@gmail.com', '$2y$10$xtxlTyBne9W1/k96F8bBnu5PXrKYIBEJA/vnhtNbhxa.izknOehce', 'villager', '2026-01-07 18:59:53'),
(9, 'ketua2', 'ketua2@gmail.com', '$2y$10$LXYgRykjOCvkXPeZbh0ZiOOhuBfRylGd41ixIKidktMsZHsygmmIG', 'ketuakampung', '2026-01-07 19:16:18'),
(10, 'irfan', 'irfan@gmail.com', '$2y$10$ZuUuJh1AXpS08Ae.lWh2veaqewgM9ctEJfx0qc87ySES9uSldS9yS', 'ketuakampung', '2026-01-07 20:16:01'),
(11, 'din', 'din@gmail.com', '$2y$10$.lLK0fCuCgIH12yNn6EDVOeksKJBH3PNDfOKS5/hVSfcEqqgQJIKK', 'ketuakampung', '2026-01-07 20:17:46'),
(12, 'nain', 'nain@gmail.com', '$2y$10$DRMgFT0WXUaEsaNXxzayF.fDyoQvGGrgLMOEwcgQZD0XJdEN.G3Oa', 'villager', '2026-01-07 20:18:06'),
(13, 'pejabat', 'pejabat@gmail.com', '$2y$10$gt6tw0Gks5DaZ2wzT3Wsce1nFZ74ZZhzPH/56IgL.HKyOvmgEgCbq', 'pejabatdaerah', '2026-01-07 20:51:41'),
(14, 'penghulu2', 'penghulu2@gmail.com', '$2y$10$lCOHABii4wJYYvPo1hxFOuo0YtWSoxBS.900zOySOmkqMe6Co15ga', 'penghulu', '2026-01-07 21:28:40'),
(15, 'kplb', 'kplb@gmail.com', '$2y$10$jykkeGLodk8CTowLdNvCde28N4iqWD22AFhXt8ut6Mg4ZTevEKptq', 'kplbhq', '2026-01-07 21:44:37');

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
(3, 1),
(3, 2),
(4, 1),
(4, 2),
(5, 2),
(6, 1),
(6, 2),
(7, 1),
(8, 1),
(9, 1),
(10, 2),
(11, 3),
(12, 3),
(14, 1),
(14, 2),
(14, 3),
(15, 1);

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
(1, 1, 7, 'try submit report', 'Road Damage', 'try submit report', '12121', '2026-01-08', 'jalan baru', 6.44328575, 100.19781876, 'Approved', 'as', '2026-01-07 19:25:26'),
(2, 1, 9, 'azizi report', 'Road Damage', 'azizi report', '12112', '2026-01-08', 'azizi report', 6.42895708, 100.19335556, 'Approved', 'test', '2026-01-07 19:30:03'),
(3, 1, 9, 'tes', 'Road Damage', 'tes', '2121', '2026-01-08', '12121', 6.42946882, 100.20760346, 'Pending', '', '2026-01-07 19:37:41'),
(4, 1, 7, 'estss', 'Road Damage', 'wewwew', '12121', '2026-01-08', '12121', 6.42622776, 100.19181061, 'Approved', '<script>alert(\'XSS\');</script> ', '2026-01-07 19:50:58'),
(5, 1, 7, 'azizi', 'Road Damage', 'azizi', '123', '2026-01-08', 'jalan', 6.42946882, 100.19610214, 'Approved', '<script>alert(\'XSS\');</script> ', '2026-01-07 19:55:39'),
(6, 1, 7, 'azizi', 'Road Damage', 'azizi', '12345', '2026-01-08', '12121', 6.43833899, 100.22442627, 'Pending', '', '2026-01-07 19:59:40'),
(7, 2, 10, 'nabil submit report', 'Road Damage', '1212', '12121', '2026-01-08', '12121', 6.44430921, 100.18185425, 'Pending', '', '2026-01-07 20:16:46'),
(8, 12, 11, 'nain', 'Road Damage', 'nain', '2121212', '2026-01-08', '12121', 6.44345633, 100.18013764, 'Pending', '', '2026-01-07 20:18:35'),
(9, 1, 9, 'test', 'Road Damage', 'test', '12345', '2026-01-08', 'jalan', 6.43407451, 100.18374252, 'Pending', '', '2026-01-07 22:02:11');

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
-- Indexes for table `user_kampung`
--
ALTER TABLE `user_kampung`
  ADD PRIMARY KEY (`user_id`,`kampung_id`),
  ADD KEY `kampung_id` (`kampung_id`);

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
  MODIFY `announce_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `ketua_report`
--
ALTER TABLE `ketua_report`
  MODIFY `kt_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `pejabatdaerah_aid_distribution`
--
ALTER TABLE `pejabatdaerah_aid_distribution`
  MODIFY `aid_distribution_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `penghulu_report`
--
ALTER TABLE `penghulu_report`
  MODIFY `penghulu_report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `sos_villager`
--
ALTER TABLE `sos_villager`
  MODIFY `sos_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tbl_kampung`
--
ALTER TABLE `tbl_kampung`
  MODIFY `kampung_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tbl_users`
--
ALTER TABLE `tbl_users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `villager_report`
--
ALTER TABLE `villager_report`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `user_kampung`
--
ALTER TABLE `user_kampung`
  ADD CONSTRAINT `user_kampung_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tbl_users` (`user_id`),
  ADD CONSTRAINT `user_kampung_ibfk_2` FOREIGN KEY (`kampung_id`) REFERENCES `tbl_kampung` (`kampung_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
