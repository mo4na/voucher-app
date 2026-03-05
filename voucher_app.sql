-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 25, 2026 at 09:42 AM
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
-- Database: `voucher_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(140) NOT NULL,
  `entity` varchar(60) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `entity`, `entity_id`, `details`, `created_at`) VALUES
(1, 1, 'Create voucher', 'voucher', 1, 'VoucherNo=VCH-000001', '2026-02-25 13:41:43'),
(2, 1, 'Create user', 'user', 2, 'role=END_USER', '2026-02-25 13:42:47'),
(3, 1, 'Create user', 'user', 3, 'role=BUDGET', '2026-02-25 13:43:01'),
(4, 2, 'Create voucher', 'voucher', 2, 'VoucherNo=VCH-000002', '2026-02-25 13:44:06'),
(5, 2, 'Edit voucher', 'voucher', 2, NULL, '2026-02-25 13:44:12'),
(6, 2, 'Release voucher', 'voucher', 2, 'To=BUDGET', '2026-02-25 13:44:15'),
(7, 3, 'Receive voucher', 'voucher', 2, 'Office=BUDGET', '2026-02-25 13:44:57'),
(8, 3, 'Forward voucher', 'voucher', 2, 'To=ACCOUNTING', '2026-02-25 13:45:06'),
(9, 1, 'Edit voucher', 'voucher', 1, NULL, '2026-02-25 13:45:47'),
(10, 1, 'Create user', 'user', 4, 'role=CASHIER', '2026-02-25 14:07:41'),
(11, 1, 'Receive voucher', 'voucher', 2, 'Office=ACCOUNTING', '2026-02-25 14:07:57'),
(12, 1, 'Forward voucher', 'voucher', 2, 'To=CASHIER', '2026-02-25 14:08:05'),
(13, 1, 'Receive voucher', 'voucher', 2, 'Office=CASHIER', '2026-02-25 14:08:20'),
(14, 1, 'Mark paid', 'voucher', 2, NULL, '2026-02-25 14:08:32'),
(15, 1, 'Toggle user active', 'user', 4, NULL, '2026-02-25 14:10:57'),
(16, 1, 'Toggle user active', 'user', 4, NULL, '2026-02-25 14:10:58'),
(17, 1, 'Create user', 'user', 5, 'role=ACCOUNTING', '2026-02-25 14:17:41'),
(18, 1, 'Release voucher', 'voucher', 1, 'To=BUDGET', '2026-02-25 14:19:33'),
(19, 1, 'Receive voucher', 'voucher', 1, 'Office=BUDGET', '2026-02-25 14:19:49'),
(20, 1, 'Forward voucher', 'voucher', 1, 'To=ACCOUNTING', '2026-02-25 14:20:04'),
(21, 1, 'Receive voucher', 'voucher', 1, 'Office=ACCOUNTING', '2026-02-25 14:20:13'),
(22, 1, 'Forward voucher', 'voucher', 1, 'To=CASHIER', '2026-02-25 14:20:19'),
(23, 1, 'Receive voucher', 'voucher', 1, 'Office=CASHIER', '2026-02-25 14:20:24'),
(24, 1, 'Mark paid', 'voucher', 1, NULL, '2026-02-25 14:20:33'),
(25, 1, 'Lock user', 'user', 4, 'minutes=15', '2026-02-25 16:22:42'),
(26, 1, 'Unlock user', 'user', 4, NULL, '2026-02-25 16:22:47'),
(27, 1, 'Toggle user active', 'user', 2, NULL, '2026-02-25 16:23:01'),
(28, 1, 'Toggle user active', 'user', 2, NULL, '2026-02-25 16:23:02'),
(29, 1, 'Bulk disable users', 'user', 0, 'count=4', '2026-02-25 16:24:52'),
(30, 1, 'Bulk lock users', 'user', 0, 'minutes=2 count=4', '2026-02-25 16:24:59'),
(31, 1, 'Bulk enable users', 'user', 0, 'count=4', '2026-02-25 16:25:05'),
(32, 1, 'Bulk unlock users', 'user', 0, 'count=4', '2026-02-25 16:25:27'),
(33, 1, 'Create user', 'user', 6, 'role=BUDGET', '2026-02-25 16:26:12'),
(34, 1, 'Soft delete user', 'user', 6, NULL, '2026-02-25 16:26:17'),
(35, 1, 'Bulk soft delete users', 'user', 0, 'count=4', '2026-02-25 16:29:25'),
(36, 1, 'Restore all users', 'user', 0, NULL, '2026-02-25 16:32:40'),
(37, 1, 'Bulk soft delete users', 'user', 0, 'count=2', '2026-02-25 16:38:28'),
(38, 1, 'Bulk restore users', 'user', 0, 'count=2', '2026-02-25 16:38:35');

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`) VALUES
(4, 'ACCOUNTING'),
(3, 'BUDGET'),
(5, 'CASHIER'),
(2, 'END_USER'),
(1, 'SUPER_ADMIN');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `username` varchar(80) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role_id` int(11) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `locked_until` datetime DEFAULT NULL,
  `deleted_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `full_name`, `username`, `password_hash`, `role_id`, `is_active`, `locked_until`, `deleted_at`, `created_at`) VALUES
(1, 'WIlliam Francisco', 'xiiimarky', '$2y$10$OallITdzGtEcSB88zfwTreiiRD4cIjqWVtrxXbon9wDVuTdbZM07y', 1, 1, NULL, NULL, '2026-02-25 13:39:17'),
(2, 'Mitsue Janeno', 'mitsue', '$2y$10$7bFOrwqJ25mF4AhXfzId0u1jlPzgwnYL8UJfYzsu21J9p55hHHfQK', 2, 1, NULL, NULL, '2026-02-25 13:42:47'),
(3, 'gondiks', 'gondi', '$2y$10$jQBb95vUOL4CtFc3HsF9a.yYhJiXxZ3iFsxoYlSoE81X8u0oDT2M2', 3, 1, NULL, NULL, '2026-02-25 13:43:01'),
(4, 'wasd', 'asdf', '$2y$10$60cRxOmNtwpPQixhDGdsieIP5qKmXzf0YoA3z8la31jgEmyC5pp56', 5, 1, NULL, NULL, '2026-02-25 14:07:41'),
(5, '123', '123', '$2y$10$jch4OX7.oIB/5DrkMHCHrexE.xhTmUFqrtCTjPoUtgZ9/ZhJd50hG', 4, 1, NULL, NULL, '2026-02-25 14:17:41'),
(6, '646456', '3534534534', '$2y$10$e0qje22YnIuXSeRpJQ21Q.O1z/LpmwrbcDez6o3OK4X9JRS6oNMBK', 3, 1, NULL, NULL, '2026-02-25 16:26:12');

-- --------------------------------------------------------

--
-- Table structure for table `vouchers`
--

CREATE TABLE `vouchers` (
  `id` int(11) NOT NULL,
  `voucher_number` varchar(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `payee_type` enum('INTERNAL','EXTERNAL') NOT NULL DEFAULT 'INTERNAL',
  `payee` varchar(150) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `particulars` text NOT NULL,
  `amount` decimal(12,2) NOT NULL DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_by_admin` int(11) DEFAULT NULL,
  `current_office` enum('END_USER','BUDGET','ACCOUNTING','CASHIER') NOT NULL DEFAULT 'END_USER',
  `status` varchar(50) NOT NULL DEFAULT 'DRAFT',
  `date_paid` datetime DEFAULT NULL,
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `vouchers`
--

INSERT INTO `vouchers` (`id`, `voucher_number`, `date_created`, `payee_type`, `payee`, `address`, `particulars`, `amount`, `remarks`, `created_by`, `created_by_admin`, `current_office`, `status`, `date_paid`, `updated_at`) VALUES
(1, 'VCH-000001', '2026-02-25 13:41:43', 'INTERNAL', 'das', 'dad', 'asd', 121212.00, 'dada', 1, NULL, 'CASHIER', 'PAID', '2026-02-25 14:20:33', '2026-02-25 14:20:33'),
(2, 'VCH-000002', '2026-02-25 13:44:06', 'INTERNAL', 'me', '123', 'adasda', 11111.00, 'dada', 2, NULL, 'CASHIER', 'PAID', '2026-02-25 14:08:32', '2026-02-25 14:08:32');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_history`
--

CREATE TABLE `voucher_history` (
  `id` int(11) NOT NULL,
  `voucher_id` int(11) NOT NULL,
  `from_status` varchar(50) DEFAULT NULL,
  `to_status` varchar(50) NOT NULL,
  `from_office` varchar(20) DEFAULT NULL,
  `to_office` varchar(20) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `acted_by` int(11) NOT NULL,
  `acted_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voucher_history`
--

INSERT INTO `voucher_history` (`id`, `voucher_id`, `from_status`, `to_status`, `from_office`, `to_office`, `remarks`, `acted_by`, `acted_at`) VALUES
(1, 2, 'DRAFT', 'FOR_RECEIVING_BUDGET', 'END_USER', 'BUDGET', NULL, 2, '2026-02-25 13:44:15'),
(2, 2, 'FOR_RECEIVING_BUDGET', 'RECEIVED_BUDGET', 'BUDGET', 'BUDGET', NULL, 3, '2026-02-25 13:44:57'),
(3, 2, 'RECEIVED_BUDGET', 'FOR_RECEIVING_ACCOUNTING', 'BUDGET', 'ACCOUNTING', NULL, 3, '2026-02-25 13:45:06'),
(4, 2, 'FOR_RECEIVING_ACCOUNTING', 'RECEIVED_ACCOUNTING', 'ACCOUNTING', 'ACCOUNTING', NULL, 1, '2026-02-25 14:07:57'),
(5, 2, 'RECEIVED_ACCOUNTING', 'FOR_RECEIVING_CASHIER', 'ACCOUNTING', 'CASHIER', NULL, 1, '2026-02-25 14:08:05'),
(6, 2, 'FOR_RECEIVING_CASHIER', 'RECEIVED_CASHIER', 'CASHIER', 'CASHIER', NULL, 1, '2026-02-25 14:08:20'),
(7, 2, 'RECEIVED_CASHIER', 'PAID', 'CASHIER', 'CASHIER', NULL, 1, '2026-02-25 14:08:32'),
(8, 1, 'DRAFT', 'FOR_RECEIVING_BUDGET', 'END_USER', 'BUDGET', NULL, 1, '2026-02-25 14:19:33'),
(9, 1, 'FOR_RECEIVING_BUDGET', 'RECEIVED_BUDGET', 'BUDGET', 'BUDGET', NULL, 1, '2026-02-25 14:19:49'),
(10, 1, 'RECEIVED_BUDGET', 'FOR_RECEIVING_ACCOUNTING', 'BUDGET', 'ACCOUNTING', NULL, 1, '2026-02-25 14:20:04'),
(11, 1, 'FOR_RECEIVING_ACCOUNTING', 'RECEIVED_ACCOUNTING', 'ACCOUNTING', 'ACCOUNTING', NULL, 1, '2026-02-25 14:20:13'),
(12, 1, 'RECEIVED_ACCOUNTING', 'FOR_RECEIVING_CASHIER', 'ACCOUNTING', 'CASHIER', NULL, 1, '2026-02-25 14:20:19'),
(13, 1, 'FOR_RECEIVING_CASHIER', 'RECEIVED_CASHIER', 'CASHIER', 'CASHIER', NULL, 1, '2026-02-25 14:20:24'),
(14, 1, 'RECEIVED_CASHIER', 'PAID', 'CASHIER', 'CASHIER', NULL, 1, '2026-02-25 14:20:33');

-- --------------------------------------------------------

--
-- Table structure for table `voucher_sequences`
--

CREATE TABLE `voucher_sequences` (
  `id` int(11) NOT NULL DEFAULT 1,
  `last_number` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `voucher_sequences`
--

INSERT INTO `voucher_sequences` (`id`, `last_number`) VALUES
(1, 2);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `role_id` (`role_id`),
  ADD KEY `idx_users_locked_until` (`locked_until`),
  ADD KEY `idx_users_deleted_at` (`deleted_at`);

--
-- Indexes for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `voucher_number` (`voucher_number`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `fk_vouchers_created_by_admin` (`created_by_admin`);

--
-- Indexes for table `voucher_history`
--
ALTER TABLE `voucher_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `voucher_id` (`voucher_id`),
  ADD KEY `acted_by` (`acted_by`);

--
-- Indexes for table `voucher_sequences`
--
ALTER TABLE `voucher_sequences`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `vouchers`
--
ALTER TABLE `vouchers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `voucher_history`
--
ALTER TABLE `voucher_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD CONSTRAINT `activity_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `vouchers`
--
ALTER TABLE `vouchers`
  ADD CONSTRAINT `fk_vouchers_created_by_admin` FOREIGN KEY (`created_by_admin`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `vouchers_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `voucher_history`
--
ALTER TABLE `voucher_history`
  ADD CONSTRAINT `voucher_history_ibfk_1` FOREIGN KEY (`voucher_id`) REFERENCES `vouchers` (`id`),
  ADD CONSTRAINT `voucher_history_ibfk_2` FOREIGN KEY (`acted_by`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
