-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 09, 2026 at 02:25 AM
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
-- Database: `log_stream`
--

-- --------------------------------------------------------

--
-- Table structure for table `apps`
--

CREATE TABLE `apps` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `app_token` varchar(64) NOT NULL,
  `app_name` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `apps`
--

INSERT INTO `apps` (`id`, `user_id`, `app_token`, `app_name`, `created_at`) VALUES
(6, 4, '2c7a3f5a2fd238b3085ccba67993b4402fbb4e1479a1200835d71b27dd084dc5', 'kalayar', '2026-08-18 16:15:52'),
(12, 4, '8686b10ec99dd551a33d10852ee4733b', 'app_test', '2026-09-05 06:12:21'),
(13, 4, 'ba6c9ddab7efb389e25568c82648206b', 'app_test2', '2026-09-05 06:12:23'),
(14, 4, '33e1ddcb25e7dc05d3215a11f0db655b', 'AppleNote', '2026-09-05 06:12:27'),
(15, 4, '1fdf182f1919447220b5994f5b5cd610', 'app_test11', '2026-09-05 06:12:35');

-- --------------------------------------------------------

--
-- Table structure for table `devices`
--

CREATE TABLE `devices` (
  `id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `client_identifier` varchar(255) NOT NULL,
  `os_type` varchar(50) NOT NULL,
  `os_version` varchar(50) NOT NULL,
  `device_model` varchar(100) NOT NULL,
  `user_agent` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `devices`
--

INSERT INTO `devices` (`id`, `app_id`, `client_identifier`, `os_type`, `os_version`, `device_model`, `user_agent`, `created_at`, `updated_at`) VALUES
(1, 6, 'c09d65ec204', 'android', '15', 'samsung a14', 'bruno-runtime/4.0.0', '2026-08-19 08:40:58', '2026-08-23 14:27:23'),
(8, 6, 'c09d65ec203', 'android', '15', 'samsung a11', 'HTTPie', '2026-08-19 14:18:52', '2026-08-20 09:44:51'),
(17, 6, 'c09d65ec200', 'ios', '13', 'apple iphone 13', 'HTTPie', '2026-08-20 08:50:59', '2026-08-20 08:50:59'),
(18, 6, 'c09d65ec201', 'ios', '15', 'apple iphone 15', 'HTTPie', '2026-08-20 08:51:12', '2026-08-20 08:51:12'),
(19, 6, 'c09d65ec202', 'ios', '15', 'apple iphone 13', 'HTTPie', '2026-08-20 08:51:22', '2026-08-20 08:51:22'),
(20, 6, 'c09d65ec205', 'linux', '6.1', 'lenovo e1', 'HTTPie', '2026-08-20 08:51:49', '2026-08-20 08:51:49'),
(21, 6, 'c09d65ec206', 'windows', '10', 'lenovo e1', 'HTTPie', '2026-08-20 08:52:03', '2026-08-20 08:52:03'),
(22, 6, 'c09d65ec207', 'linux', '5.8', 'lenovo e1', 'HTTPie', '2026-08-20 08:52:07', '2026-08-20 09:39:03'),
(24, 6, 'c09d65ec208', 'linux', '5.8', 'lenovo e2', 'HTTPie', '2026-08-20 09:39:17', '2026-08-20 09:39:17'),
(25, 6, 'c09d65ec209', 'linux', '5.7', 'lenovo e3', 'HTTPie', '2026-08-20 09:39:26', '2026-08-20 09:39:26'),
(26, 6, 'c09d65ec210', 'windows', '7', 'lenovo e2', 'HTTPie', '2026-08-20 09:39:55', '2026-08-20 09:41:04'),
(27, 6, 'c09d65ec211', 'windows', '8', 'lenovo e2', 'HTTPie', '2026-08-20 09:40:00', '2026-08-20 09:40:52'),
(28, 6, 'c09d65ec212', 'windows', '11', 'lenovo e3', 'HTTPie', '2026-08-20 09:40:05', '2026-08-20 09:40:33'),
(36, 6, 'c09d65ec221', 'android', '15', 'samsung a12', 'HTTPie', '2026-08-20 09:45:18', '2026-08-20 09:45:18'),
(37, 6, 'c09d65ec222', 'android', '17', 'samsung a12', 'HTTPie', '2026-08-20 09:45:33', '2026-08-20 09:45:33'),
(38, 6, 'c09d65ec223', 'android', '13', 'samsung a17', 'HTTPie', '2026-08-20 09:45:42', '2026-08-20 09:45:42'),
(39, 6, 'c09d65ec224', 'ios', '17', 'apple iphone 14', 'HTTPie', '2026-08-20 09:46:40', '2026-08-20 09:46:40'),
(40, 6, 'c09d65ec225', 'ios', '17', 'apple iphone 14', 'HTTPie', '2026-08-20 09:46:42', '2026-08-20 09:46:42'),
(41, 6, 'c09d65ec226', 'ios', '13', 'apple iphone 13', 'HTTPie', '2026-08-20 09:46:53', '2026-08-20 09:46:53'),
(42, 6, 'c09d65ec227', 'ios', '14', 'apple iphone 14', 'HTTPie', '2026-08-20 09:47:02', '2026-08-20 09:47:02'),
(43, 6, 'c09d65ec228', 'mac', '9', 'mac air 1', 'HTTPie', '2026-08-20 09:47:33', '2026-08-20 09:47:33'),
(44, 6, 'c09d65ec229', 'mac', '9', 'mac air 3', 'HTTPie', '2026-08-20 09:47:41', '2026-08-20 09:47:44'),
(46, 6, 'c09d65ec230', 'mac', '9', 'mac air 2', 'HTTPie', '2026-08-20 09:47:54', '2026-08-20 09:47:54'),
(47, 6, 'c09d65ec231', 'mac', '9.2', 'mac air 3', 'HTTPie', '2026-08-20 09:48:02', '2026-08-20 09:48:02'),
(48, 6, 'c09d65ec232', 'mac', '9.2', 'mac air 2', 'HTTPie', '2026-08-20 09:48:06', '2026-08-20 09:48:06'),
(49, 6, 'c09d65ec233', 'mac', '9.2', 'mac air 1', 'HTTPie', '2026-08-20 09:48:12', '2026-08-20 09:48:12'),
(50, 6, 'c09d65ec234', 'mac', '9.3', 'mac air 4', 'HTTPie', '2026-08-20 09:48:29', '2026-08-20 09:48:29'),
(51, 6, 'c09d65ec235', 'android', '16', 'samsung a35', 'HTTPie', '2026-08-20 09:48:34', '2026-08-21 17:47:21'),
(56, 6, 'c09d65ec236', 'android', '14', 'samsung a35', 'HTTPie', '2026-08-21 17:47:36', '2026-08-21 17:47:36'),
(61, 6, 'c09d65ec237', 'android', '14', 'samsung a35', 'HTTPie', '2026-08-23 14:15:15', '2026-08-23 14:15:15'),
(62, 6, 'c09d65ec238', 'android', '15', 'samsung a14', 'bruno-runtime/4.0.0', '2026-08-23 14:16:04', '2026-08-23 14:27:27');

-- --------------------------------------------------------

--
-- Table structure for table `logs`
--

CREATE TABLE `logs` (
  `id` int(11) NOT NULL,
  `app_id` int(11) NOT NULL,
  `log_uuid` varchar(255) NOT NULL,
  `client_identifier` varchar(255) NOT NULL,
  `tag` varchar(255) NOT NULL DEFAULT 'general',
  `message` text NOT NULL,
  `ip_address` varchar(45) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `logs`
--

INSERT INTO `logs` (`id`, `app_id`, `log_uuid`, `client_identifier`, `tag`, `message`, `ip_address`, `created_at`) VALUES
(310, 6, '0049a0da007295aad96ba226e00b7b5a', 'c09d65ec204', 'emergency', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:52:48'),
(311, 6, '23d544775c390b8c3f597df66f2f9e15', 'c09d65ec204', 'critical', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:52:54'),
(312, 6, 'cb86481464893e205c35ac064c236052', 'c09d65ec204', 'error', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:52:58'),
(313, 6, '6ad63658094eeab388cf56f32fb9faf0', 'c09d65ec204', 'alert', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:53:03'),
(314, 6, '99fac907cf647ed310faf8f2dbae0ab2', 'c09d65ec204', 'warning', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:53:09'),
(315, 6, '577a81d96f1b8e2e8337dbcce910d5e4', 'c09d65ec204', 'notice', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:53:13'),
(316, 6, '48df0dae312d2a5a6ca0b28eff1a13ed', 'c09d65ec204', 'debug', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:53:17'),
(317, 6, '603e198a2bde924da4e63e2634129691', 'c09d65ec204', 'info', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 07:53:21'),
(318, 6, 'd489b99f9f0d303989eec111cf3e6171', 'c09d65ec204', 'custom', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 17:39:45'),
(319, 6, '41fba80aec1176834f529e346ce9611f', 'c09d65ec203', 'custom', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 17:40:10'),
(320, 6, '082433b52d00cdaf08fbaaa5b83551d0', 'c09d65ec203', 'custom', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 17:40:11'),
(321, 6, '96fd9163839c52c6ac4685e677c8c0fc', 'c09d65ec203', 'error', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-08-21 17:40:15'),
(322, 6, '7452aa97ae82f23088206a9d0e599a9a', 'c09d65ec203', 'error', '[digikala] [MainPage] user can not login with number', '127.0.0.1', '2026-08-21 17:42:11'),
(323, 6, 'be0c9d05fee354a03703955cb1426a9d', 'c09d65ec203', 'error', '[digikala] [MainPage] user can not login with number', '127.0.0.1', '2026-08-21 17:45:07'),
(324, 6, '96a93056c4699a7146f58bc80aaa2cd7', 'user-123', 'info', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 09:01:15'),
(325, 6, '872195d5f465bae79ac67a63b32fc61e', 'user-123', 'info', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 13:57:00'),
(326, 6, '5c5364fd70d3637dcb40c907f6f10612', 'user-123', 'general', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 13:57:36'),
(327, 6, 'b2d4089670522ba410db93de98f9869a', 'user-123', 'general', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 13:57:37'),
(328, 6, '9cbc4e46fdc2fc4e04601593bd17835b', 'user-123', 'general', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 13:57:38'),
(329, 6, '41c3dab72ce8f5aa37d9627bd18ed062', '', 'info', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 13:59:13'),
(330, 6, 'd24608ffe1a7252ec27f7dd02ec483d9', 'user-123', 'info', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-23 14:03:39'),
(331, 6, '71f1aaf8b85a2b91ad178337d136dc59', 'c09d65ec203', 'error', '[digikala] [MainPage] user can not login with number', '127.0.0.1', '2026-08-23 14:33:36'),
(332, 6, '1d055571d7fcda8843937f591c5e60b7', 'user-123', 'info', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:19:18'),
(333, 6, '30003f031653a07d0a6cb66eb4c14a92', 'user-123', 'debug', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:19:40'),
(334, 6, '0acfeed0b5795a0e429b59fe88d24286', 'user-123', 'warning', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:19:48'),
(335, 6, 'bc92c9b7f440fc1273f084e0f0e44762', 'user-123', 'error', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:20:24'),
(336, 6, '6003c306da5e7bcad8cf2dd75f906bf5', 'user-123', 'emergency', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:20:34'),
(337, 6, '347ed4a51f68f1dbb54525c2f0e084e8', 'user-123', 'alert', 'عملیات با موفقیت انجام شد', '127.0.0.1', '2026-08-26 15:20:43'),
(338, 12, '51e1bb726c21a63e7d51827eb8dc73d7', 'c09d65ec204', 'debug', '[digikala] [MainPage] user can not login with email', '127.0.0.1', '2026-09-05 06:54:52');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `user_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `user_token`, `created_at`) VALUES
(4, 'admin', '$2y$10$uiFSlaO24j4m/Z6FS.IKfOrZjnylv5rRWpwJBTvDlhTcs6LV6yJZ.', '', '7bc067a0a55fd1da498ca0f67a8b85d82b71b37c878f7e396b477e5547c2af28', '2026-08-18 16:15:07'),
(5, 'admin2', '$2y$10$plg6/mZ8jITFCamModMnoudeHvsevWSfnGwcS3tMVhdUmECIIvO9G', '', '9f637fcd8e24bf6fc28f3a0230b16687ca4a97654560359f0d78825a923a518d', '2026-08-20 22:10:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `apps`
--
ALTER TABLE `apps`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `app_uuid` (`app_token`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `devices`
--
ALTER TABLE `devices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_device_per_app_user` (`app_id`,`client_identifier`);

--
-- Indexes for table `logs`
--
ALTER TABLE `logs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `log_uuid` (`log_uuid`),
  ADD KEY `app_id` (`app_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `apps`
--
ALTER TABLE `apps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `devices`
--
ALTER TABLE `devices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `logs`
--
ALTER TABLE `logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=339;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `apps`
--
ALTER TABLE `apps`
  ADD CONSTRAINT `apps_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `devices`
--
ALTER TABLE `devices`
  ADD CONSTRAINT `devices_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `logs`
--
ALTER TABLE `logs`
  ADD CONSTRAINT `logs_ibfk_1` FOREIGN KEY (`app_id`) REFERENCES `apps` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
