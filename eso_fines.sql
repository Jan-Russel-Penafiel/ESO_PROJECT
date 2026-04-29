-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 28, 2026 at 05:13 PM
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
-- Database: `eso_fines`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED DEFAULT NULL,
  `action` varchar(80) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `description`, `created_at`) VALUES
(1, 2, 'login', 'User logged in: juan', '2026-04-27 14:00:48'),
(2, 2, 'logout', 'User logged out', '2026-04-27 14:00:50'),
(3, 1, 'login', 'User logged in: admin', '2026-04-27 14:00:55'),
(4, 1, 'fine_issue', 'Issued fine of ₱25.00 to student #1', '2026-04-27 14:01:02'),
(5, 1, 'logout', 'User logged out', '2026-04-27 14:01:04'),
(6, 2, 'login', 'User logged in: juan', '2026-04-27 14:01:09'),
(7, 2, 'payment_initiated', 'Payment ESO-383A8007-260427220111 for fine F-1', '2026-04-27 14:01:11'),
(8, 2, 'logout', 'User logged out', '2026-04-27 14:05:13'),
(9, 1, 'login', 'User logged in: admin', '2026-04-27 14:05:21'),
(10, 1, 'payment_verify', 'Verified payment #1', '2026-04-27 14:11:26'),
(11, 1, 'logout', 'User logged out', '2026-04-27 14:12:44'),
(12, 2, 'login', 'User logged in: juan', '2026-04-27 14:12:49'),
(13, 2, 'logout', 'User logged out', '2026-04-27 14:12:54'),
(14, 1, 'login', 'User logged in: admin', '2026-04-27 14:13:00'),
(15, 1, 'fine_issue', 'Issued fine of ₱50.00 to student #1', '2026-04-27 14:13:05'),
(16, 1, 'logout', 'User logged out', '2026-04-27 14:13:20'),
(17, 2, 'login', 'User logged in: juan', '2026-04-27 14:13:34'),
(18, 2, 'payment_initiated', 'Payment ESO-12DFA0DA-260427221340 for fine F-2', '2026-04-27 14:13:40'),
(19, 2, 'payment_ref_submitted', 'Student submitted GCash ref \'1111111111111\' for payment #2', '2026-04-27 14:38:29'),
(20, 2, 'logout', 'User logged out', '2026-04-27 14:38:41'),
(21, 1, 'login', 'User logged in: admin', '2026-04-27 14:39:01'),
(22, 1, 'payment_verify', 'Verified payment #2', '2026-04-27 14:39:17'),
(23, 1, 'fine_issue', 'Issued fine of ₱50.00 to student #1', '2026-04-27 14:39:35'),
(24, 1, 'logout', 'User logged out', '2026-04-27 14:41:58'),
(25, 2, 'login', 'User logged in: juan', '2026-04-27 14:42:05'),
(26, 2, 'payment_initiated', 'Payment ESO-EF73D622-260427224209 for fine F-3', '2026-04-27 14:42:09'),
(27, 2, 'payment_ref_submitted', 'Student submitted GCash ref \'2222222222222\' for payment #3', '2026-04-27 14:42:59'),
(28, 2, 'logout', 'User logged out', '2026-04-27 14:43:05'),
(29, 2, 'login', 'User logged in: juan', '2026-04-27 14:43:13'),
(30, 2, 'logout', 'User logged out', '2026-04-27 14:43:23'),
(31, 1, 'login', 'User logged in: admin', '2026-04-27 14:43:31'),
(32, 1, 'logout', 'User logged out', '2026-04-27 14:44:08'),
(33, 2, 'login', 'User logged in: juan', '2026-04-27 14:44:12'),
(34, 2, 'logout', 'User logged out', '2026-04-27 14:44:33'),
(35, 1, 'login', 'User logged in: admin', '2026-04-27 14:44:38'),
(36, 1, 'fine_issue', 'Issued fine of ₱25.00 to student #1', '2026-04-27 14:44:47'),
(37, 1, 'logout', 'User logged out', '2026-04-27 14:44:50'),
(38, 2, 'login', 'User logged in: juan', '2026-04-27 14:44:58'),
(39, 2, 'payment_initiated', 'Payment ESO-09AD7C48-260427224501 for fine F-4', '2026-04-27 14:45:01'),
(40, 2, 'logout', 'User logged out', '2026-04-27 14:56:59'),
(41, 1, 'login', 'User logged in: admin', '2026-04-27 14:57:12'),
(42, 1, 'login', 'User logged in: admin', '2026-04-28 00:35:21'),
(43, 2, 'login', 'User logged in: juan', '2026-04-28 01:40:25'),
(44, 2, 'logout', 'User logged out', '2026-04-28 01:45:35'),
(45, 1, 'login', 'User logged in: admin', '2026-04-28 01:45:45'),
(46, 1, 'logout', 'User logged out', '2026-04-28 01:47:50'),
(47, 2, 'login', 'User logged in: juan', '2026-04-28 01:48:05'),
(48, 2, 'logout', 'User logged out', '2026-04-28 01:55:53'),
(49, 1, 'login', 'User logged in: admin', '2026-04-28 01:56:18'),
(50, 1, 'login', 'User logged in: admin', '2026-04-28 05:26:51'),
(51, 1, 'logout', 'User logged out', '2026-04-28 05:36:05'),
(52, 1, 'login', 'User logged in: admin', '2026-04-28 05:36:35'),
(53, 1, 'logout', 'User logged out', '2026-04-28 05:37:04'),
(54, 1, 'login', 'User logged in: admin', '2026-04-28 05:38:30'),
(55, 1, 'logout', 'User logged out', '2026-04-28 05:39:59'),
(56, 1, 'login', 'User logged in: admin', '2026-04-28 05:40:38'),
(57, 1, 'logout', 'User logged out', '2026-04-28 05:43:30'),
(58, 1, 'login', 'User logged in: admin', '2026-04-28 05:43:39'),
(59, 1, 'logout', 'User logged out', '2026-04-28 05:45:00'),
(60, 1, 'login', 'User logged in: admin', '2026-04-28 05:45:23'),
(61, 1, 'logout', 'User logged out', '2026-04-28 05:50:52'),
(62, 2, 'login', 'User logged in: juan', '2026-04-28 05:50:58'),
(63, 2, 'payment_ref_submitted', 'Student submitted GCash ref \'1111111111111\' for payment #4', '2026-04-28 05:51:20'),
(64, 2, 'logout', 'User logged out', '2026-04-28 05:51:25'),
(65, 1, 'login', 'User logged in: admin', '2026-04-28 05:51:39'),
(66, 1, 'payment_verify', 'Verified payment #4', '2026-04-28 05:51:46'),
(67, 1, 'logout', 'User logged out', '2026-04-28 05:51:49'),
(68, 2, 'login', 'User logged in: juan', '2026-04-28 05:51:55'),
(69, 2, 'logout', 'User logged out', '2026-04-28 06:02:33'),
(70, 2, 'login', 'User logged in: juan', '2026-04-28 06:03:00'),
(71, 2, 'logout', 'User logged out', '2026-04-28 06:55:57'),
(72, 1, 'login', 'User logged in: admin', '2026-04-28 06:56:02'),
(73, 1, 'logout', 'User logged out', '2026-04-28 06:56:08'),
(74, 2, 'login', 'User logged in: juan', '2026-04-28 06:56:15'),
(75, 2, 'logout', 'User logged out', '2026-04-28 07:02:13'),
(76, 1, 'login', 'User logged in: admin', '2026-04-28 07:02:20'),
(77, 1, 'logout', 'User logged out', '2026-04-28 07:08:01'),
(78, 2, 'login', 'User logged in: juan', '2026-04-28 07:08:07'),
(79, 2, 'logout', 'User logged out', '2026-04-28 07:08:33'),
(80, 1, 'login', 'User logged in: admin', '2026-04-28 07:08:40'),
(81, 1, 'logout', 'User logged out', '2026-04-28 07:09:09'),
(82, 2, 'login', 'User logged in: juan', '2026-04-28 07:09:14'),
(83, 2, 'logout', 'User logged out', '2026-04-28 07:12:24'),
(84, 1, 'login', 'User logged in: admin', '2026-04-28 07:12:29'),
(85, 1, 'logout', 'User logged out', '2026-04-28 07:13:58'),
(86, 2, 'login', 'User logged in: juan', '2026-04-28 07:14:04'),
(87, 2, 'logout', 'User logged out', '2026-04-28 07:31:35'),
(88, 1, 'login', 'User logged in: admin', '2026-04-28 10:50:32'),
(89, 1, 'login', 'User logged in: admin', '2026-04-28 11:12:14'),
(90, 1, 'logout', 'User logged out', '2026-04-28 11:12:18'),
(91, 1, 'login', 'User logged in: admin', '2026-04-28 12:35:40'),
(92, 1, 'logout', 'User logged out', '2026-04-28 12:57:32'),
(93, 2, 'login', 'User logged in: juan', '2026-04-28 12:57:40'),
(94, 2, 'logout', 'User logged out', '2026-04-28 13:00:34'),
(95, 1, 'login', 'User logged in: admin', '2026-04-28 13:00:43'),
(96, 1, 'logout', 'User logged out', '2026-04-28 13:07:18'),
(97, 2, 'login', 'User logged in: juan', '2026-04-28 13:07:24'),
(98, 2, 'logout', 'User logged out', '2026-04-28 13:11:42'),
(99, 1, 'login', 'User logged in: admin', '2026-04-28 13:12:51'),
(100, 1, 'student_update', 'Updated student #1', '2026-04-28 13:14:31'),
(101, 1, 'logout', 'User logged out', '2026-04-28 13:19:53'),
(102, 1, 'login', 'User logged in: admin', '2026-04-28 13:20:09'),
(103, 1, 'fine_issue', 'Issued fine of ₱50.00 to student #1', '2026-04-28 13:20:15'),
(104, 1, 'logout', 'User logged out', '2026-04-28 13:20:17'),
(105, 2, 'login', 'User logged in: juan', '2026-04-28 13:20:24'),
(106, 2, 'logout', 'User logged out', '2026-04-28 13:21:07'),
(107, 1, 'login', 'User logged in: admin', '2026-04-28 13:21:13'),
(108, 1, 'logout', 'User logged out', '2026-04-28 13:24:16'),
(109, 2, 'login', 'User logged in: juan', '2026-04-28 13:24:24'),
(110, 2, 'payment_initiated', 'Payment ESO-51B15AA6-260428212428 for fine F-5', '2026-04-28 13:24:28'),
(111, 2, 'logout', 'User logged out', '2026-04-28 13:25:17'),
(112, 1, 'login', 'User logged in: admin', '2026-04-28 13:25:23'),
(113, 1, 'logout', 'User logged out', '2026-04-28 13:29:14'),
(114, 2, 'login', 'User logged in: juan', '2026-04-28 13:29:31'),
(115, 2, 'logout', 'User logged out', '2026-04-28 13:30:18'),
(116, 1, 'login', 'User logged in: admin', '2026-04-28 13:32:18'),
(117, 1, 'logout', 'User logged out', '2026-04-28 13:33:36'),
(118, 2, 'login', 'User logged in: juan', '2026-04-28 13:33:45'),
(119, 2, 'logout', 'User logged out', '2026-04-28 13:41:58'),
(120, 1, 'login', 'User logged in: admin', '2026-04-28 13:42:06'),
(121, 1, 'logout', 'User logged out', '2026-04-28 13:42:53'),
(122, 2, 'login', 'User logged in: juan', '2026-04-28 13:43:00'),
(123, 2, 'payment_ref_submitted', 'Student uploaded GCash receipt for payment #5', '2026-04-28 13:43:13'),
(124, 2, 'logout', 'User logged out', '2026-04-28 13:43:39'),
(125, 1, 'login', 'User logged in: admin', '2026-04-28 13:44:26'),
(126, 1, 'payment_verify', 'Verified payment #5', '2026-04-28 13:52:33'),
(127, 1, 'logout', 'User logged out', '2026-04-28 13:54:24'),
(128, 2, 'login', 'User logged in: juan', '2026-04-28 13:54:39'),
(129, 2, 'logout', 'User logged out', '2026-04-28 13:59:38'),
(130, 1, 'login', 'User logged in: admin', '2026-04-28 14:22:49'),
(131, 1, 'login', 'User logged in: admin', '2026-04-28 14:28:52'),
(132, 1, 'logout', 'User logged out', '2026-04-28 14:30:06'),
(133, 1, 'login', 'User logged in: admin', '2026-04-28 14:33:16'),
(134, 1, 'logout', 'User logged out', '2026-04-28 14:35:59'),
(135, 1, 'login', 'User logged in: admin', '2026-04-28 14:37:12'),
(136, 1, 'logout', 'User logged out', '2026-04-28 14:37:42'),
(137, 2, 'login', 'User logged in: juan', '2026-04-28 14:38:03'),
(138, 1, 'login', 'User logged in: admin', '2026-04-28 14:44:33'),
(139, 1, 'logout', 'User logged out', '2026-04-28 14:47:32'),
(140, 1, 'login', 'User logged in: admin', '2026-04-28 15:06:02'),
(141, 1, 'logout', 'User logged out', '2026-04-28 15:06:59'),
(142, 1, 'login', 'User logged in: admin', '2026-04-28 15:08:19'),
(143, 1, 'logout', 'User logged out', '2026-04-28 15:08:40');

-- --------------------------------------------------------

--
-- Table structure for table `fines`
--

CREATE TABLE `fines` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `category_id` int(11) UNSIGNED DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) NOT NULL,
  `status` enum('unpaid','pending','paid','cancelled') NOT NULL DEFAULT 'unpaid',
  `issued_by` int(11) UNSIGNED NOT NULL,
  `issued_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fines`
--

INSERT INTO `fines` (`id`, `student_id`, `category_id`, `amount`, `reason`, `status`, `issued_by`, `issued_at`, `paid_at`) VALUES
(1, 1, 5, 25.00, 'Late Attendance', 'paid', 1, '2026-04-27 14:01:02', '2026-04-27 14:11:26'),
(2, 1, 1, 50.00, 'Improper Uniform', 'paid', 1, '2026-04-27 14:13:05', '2026-04-27 14:39:17'),
(3, 1, 1, 50.00, 'Improper Uniform', 'pending', 1, '2026-04-27 14:39:35', NULL),
(4, 1, 5, 25.00, 'Late Attendance', 'paid', 1, '2026-04-27 14:44:47', '2026-04-28 05:51:46'),
(5, 1, 1, 50.00, 'Improper Uniform', 'paid', 1, '2026-04-28 13:20:15', '2026-04-28 13:52:33');

-- --------------------------------------------------------

--
-- Table structure for table `fine_categories`
--

CREATE TABLE `fine_categories` (
  `id` int(11) UNSIGNED NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_amount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fine_categories`
--

INSERT INTO `fine_categories` (`id`, `name`, `default_amount`, `description`, `is_active`) VALUES
(1, 'Improper Uniform', 50.00, 'Wearing uniform that does not comply with school dress code', 1),
(2, 'No ID', 30.00, 'Failure to wear/present school ID inside campus', 1),
(3, 'Littering', 100.00, 'Throwing trash outside designated bins', 1),
(4, 'Smoking on Campus', 500.00, 'Smoking inside school premises', 1),
(5, 'Late Attendance', 25.00, 'Tardiness to school activities or classes', 1);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) UNSIGNED NOT NULL,
  `fine_id` int(11) UNSIGNED NOT NULL,
  `student_id` int(11) UNSIGNED NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reference_no` varchar(60) NOT NULL,
  `receipt_path` varchar(255) DEFAULT NULL,
  `payment_method` varchar(40) NOT NULL DEFAULT 'GCASH',
  `status` enum('initiated','pending','success','failed') NOT NULL DEFAULT 'initiated',
  `qr_payload` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `paid_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `fine_id`, `student_id`, `amount`, `reference_no`, `receipt_path`, `payment_method`, `status`, `qr_payload`, `created_at`, `paid_at`) VALUES
(1, 1, 1, 25.00, 'ESO-383A8007-260427220111', NULL, 'GCASH', 'success', '{\"merchant\":\"ESO OFFICE\",\"gcash_no\":\"09171234567\",\"amount\":25,\"reference\":\"ESO-383A8007-260427220111\",\"callback\":\"http:\\/\\/localhost\\/fine\\/api\\/gcash_callback.php?ref=ESO-383A8007-260427220111\"}', '2026-04-27 14:01:11', '2026-04-27 14:11:26'),
(2, 2, 1, 50.00, 'ESO-12DFA0DA-260427221340', NULL, 'GCASH', 'success', NULL, '2026-04-27 14:13:40', '2026-04-27 14:39:17'),
(3, 3, 1, 50.00, 'ESO-EF73D622-260427224209', NULL, 'GCASH', 'pending', NULL, '2026-04-27 14:42:09', NULL),
(4, 4, 1, 25.00, 'ESO-09AD7C48-260427224501', NULL, 'GCASH', 'success', NULL, '2026-04-27 14:45:01', '2026-04-28 05:51:46'),
(5, 5, 1, 50.00, 'ESO-51B15AA6-260428212428', 'uploads/receipts/rcpt_5_1777383793.png', 'GCASH', 'success', NULL, '2026-04-28 13:24:28', '2026-04-28 13:52:33');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) UNSIGNED NOT NULL,
  `student_no` varchar(30) NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(120) NOT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `course` varchar(80) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `section` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `student_no`, `full_name`, `email`, `contact`, `course`, `year_level`, `section`, `created_at`) VALUES
(1, '2024-0001', 'Juan Dela Cruz', 'juan@student.local', '09171234567', 'BSCPE', '3', 'A', '2026-04-27 14:00:37'),
(2, '2024-0002', 'Maria Santos', 'maria@student.local', '09181234567', 'BSCS', '2', 'B', '2026-04-27 14:00:37');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) UNSIGNED NOT NULL,
  `username` varchar(60) NOT NULL,
  `email` varchar(120) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','student') NOT NULL DEFAULT 'student',
  `student_id` int(11) UNSIGNED DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `role`, `student_id`, `is_active`, `created_at`) VALUES
(1, 'admin', 'admin@eso.local', '$2y$10$cIK5eHUUWchPXVdWkue0QudSOp6OrRhZ9Wbh/jp8/V6yh5yuQ6j9G', 'admin', NULL, 1, '2026-04-27 14:00:37'),
(2, 'juan', 'juan@student.local', '$2y$10$GKodsipa1EpyH/QafsTSgecdt5LSInJ4jlA37t3Cnx4ONULBk0wRG', 'student', 1, 1, '2026-04-27 14:00:37'),
(3, 'maria', 'maria@student.local', '$2y$10$GKodsipa1EpyH/QafsTSgecdt5LSInJ4jlA37t3Cnx4ONULBk0wRG', 'student', 2, 1, '2026-04-27 14:00:37');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user` (`user_id`);

--
-- Indexes for table `fines`
--
ALTER TABLE `fines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `fk_fines_category` (`category_id`),
  ADD KEY `fk_fines_admin` (`issued_by`);

--
-- Indexes for table `fine_categories`
--
ALTER TABLE `fine_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `idx_fine` (`fine_id`),
  ADD KEY `idx_student` (`student_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `student_no` (`student_no`),
  ADD KEY `idx_full_name` (`full_name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=144;

--
-- AUTO_INCREMENT for table `fines`
--
ALTER TABLE `fines`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `fine_categories`
--
ALTER TABLE `fine_categories`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `fines`
--
ALTER TABLE `fines`
  ADD CONSTRAINT `fk_fines_admin` FOREIGN KEY (`issued_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `fk_fines_category` FOREIGN KEY (`category_id`) REFERENCES `fine_categories` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `fk_fines_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `fk_payments_fine` FOREIGN KEY (`fine_id`) REFERENCES `fines` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_payments_student` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
