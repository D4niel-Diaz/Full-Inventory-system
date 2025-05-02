-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 02, 2025 at 09:23 PM
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
-- Database: `inventory_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `borrowed_items`
--

CREATE TABLE `borrowed_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_borrowed` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_requests`
--

CREATE TABLE `borrow_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `ched_request` varchar(255) DEFAULT NULL,
  `quantity_borrowed` int(11) DEFAULT 0,
  `quantity_returned` int(11) DEFAULT 0,
  `status` enum('Available','Not Available','For Delivery') DEFAULT 'Available',
  `remarks` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quantity` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `sub_category` varchar(50) NOT NULL,
  `quantity` varchar(255) DEFAULT NULL,
  `available_quantity` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `borrowed_quantity` int(11) DEFAULT 0,
  `borrowed` int(11) DEFAULT 0,
  `returned` int(11) DEFAULT 0,
  `remarks` text DEFAULT NULL,
  `unit` varchar(255) NOT NULL DEFAULT 'unit',
  `main_category_id` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `items`
--

INSERT INTO `items` (`id`, `name`, `sub_category`, `quantity`, `available_quantity`, `description`, `department`, `created_at`, `borrowed_quantity`, `borrowed`, `returned`, `remarks`, `unit`, `main_category_id`) VALUES
(9, 'Bell boys cart', 'Tools', '2', '0', NULL, NULL, '2025-05-02 01:56:07', 0, 0, 0, NULL, 'unit', 1),
(10, 'Credit Card Voucher', 'Tools', '1', '0', NULL, NULL, '2025-05-02 01:56:26', 1, 0, 0, NULL, 'unit', 1),
(11, 'Calculator', 'Equipment', '5', '5', NULL, NULL, '2025-05-02 01:56:52', 0, 0, 0, NULL, 'unit', 1),
(12, 'Cash Box Drawer', 'Equipment', '1', '0', NULL, NULL, '2025-05-02 01:57:18', 1, 0, 0, NULL, 'unit', 1),
(13, 'Cash Register', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:57:36', 0, 0, 0, NULL, 'unit', 1),
(14, 'Clocks at least 4 various time zone', 'Equipment', '4', '0', NULL, NULL, '2025-05-02 01:57:53', 3, 0, 0, NULL, 'unit', 1),
(15, 'Computer (with reservation System) PMS', 'Equipment', '2', '2', NULL, NULL, '2025-05-02 01:58:08', 0, 0, 0, NULL, 'unit', 1),
(16, 'Credit card Imprinter', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:58:22', 0, 0, 0, NULL, 'unit', 1),
(18, 'Fake bills Detector', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:58:46', 0, 0, 0, NULL, 'unit', 1),
(19, 'Fax Machine', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:59:00', 0, 0, 0, NULL, 'unit', 1),
(20, 'Front Office Desk', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:59:14', 0, 0, 0, NULL, 'unit', 1),
(24, 'Key Card Verifier', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 01:59:57', 0, 0, 0, NULL, 'unit', 1),
(25, 'Key rack/ keycard holders', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 02:01:43', 0, 0, 0, NULL, 'unit', 1),
(29, 'Telephone system', 'Equipment', '2', '1', NULL, NULL, '2025-05-02 02:02:55', 0, 0, 0, NULL, 'unit', 1),
(30, 'Typewriter', 'Equipment', '1', '1', NULL, NULL, '2025-05-02 02:03:20', 0, 0, 0, NULL, 'unit', 1),
(31, 'Logbook', 'Materials', '1', '1', NULL, NULL, '2025-05-02 02:03:37', 0, 0, 0, NULL, 'unit', 1),
(76, 'asda', 'dasd', 'asdas', '0', NULL, NULL, '2025-04-28 04:44:56', 0, 0, 0, 'adas', 'unit', 1),
(77, 'sad', 'sample', '1 units', '4', NULL, NULL, '2025-04-28 08:30:59', 0, 0, 0, 'To delivery', 'unit', 1),
(82, 'Antistatic Duster', 'Tools and Supplies', '5', '5', NULL, NULL, '2025-05-02 05:31:05', 0, 0, 0, '', 'units', 2),
(83, 'Apple corer', 'Kitchen Tools and Supplies', '8', '8', NULL, NULL, '2025-05-02 09:35:52', 0, 0, 0, '', 'units', 4),
(84, 'Butter knives', 'CUTLERIES / SILVERWARES', '24', '24', NULL, NULL, '2025-05-02 09:37:27', 0, 0, 0, '', 'units', 3);

-- --------------------------------------------------------

--
-- Table structure for table `main_categories`
--

CREATE TABLE `main_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `main_categories`
--

INSERT INTO `main_categories` (`id`, `name`) VALUES
(3, 'FOOD AND BEVERAGE'),
(4, 'FOOD PRODUCTION'),
(1, 'FRONT OFFICE LABORATORY'),
(2, 'HOUSEKEEPING');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `transaction_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `borrowed_quantity` int(11) NOT NULL,
  `returned_quantity` int(11) DEFAULT 0,
  `status` enum('Borrowed','Returned') NOT NULL,
  `transaction_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `transaction_type` enum('borrow','return') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`transaction_id`, `user_id`, `item_id`, `borrowed_quantity`, `returned_quantity`, `status`, `transaction_date`, `transaction_type`) VALUES
(10, 3, 9, 2, 0, 'Borrowed', '2025-05-07 11:45:33', 'borrow'),
(11, 3, 10, 1, 0, 'Borrowed', '2025-04-27 11:46:18', 'borrow'),
(12, 3, 11, 5, 0, 'Borrowed', '2025-04-27 11:46:26', 'borrow'),
(13, 3, 12, 1, 0, 'Borrowed', '2025-04-27 12:10:50', 'borrow'),
(14, 3, 14, 3, 0, 'Borrowed', '2025-04-27 12:10:52', 'borrow'),
(15, 3, 9, 0, 2, 'Returned', '2025-04-27 12:19:38', 'borrow'),
(16, 3, 9, 2, 0, 'Borrowed', '2025-04-27 12:22:55', 'borrow'),
(17, 3, 9, 0, 2, 'Returned', '2025-04-27 12:22:58', 'borrow'),
(18, 3, 9, 1, 0, 'Borrowed', '2025-04-27 12:23:00', 'borrow'),
(19, 3, 9, 1, 0, 'Borrowed', '2025-04-27 12:32:58', 'borrow'),
(20, 3, 9, 0, 2, 'Returned', '2025-04-27 12:35:11', 'borrow'),
(21, 3, 9, 2, 0, 'Borrowed', '2025-04-27 12:41:49', 'borrow'),
(22, 3, 9, 0, 2, 'Returned', '2025-04-27 12:41:56', 'borrow'),
(23, 3, 9, 1, 0, 'Borrowed', '2025-04-27 12:42:01', 'borrow'),
(24, 5, 11, 0, 5, 'Returned', '2025-04-27 12:52:10', 'borrow'),
(25, 3, 9, 1, 0, 'Borrowed', '2025-04-27 13:12:51', 'borrow'),
(26, 3, 9, 0, 2, 'Returned', '2025-04-27 23:02:46', 'borrow'),
(27, 3, 9, 1, 0, 'Borrowed', '2025-04-28 04:26:02', 'borrow'),
(28, 3, 9, 0, 1, 'Returned', '2025-04-28 04:26:12', 'borrow'),
(29, 3, 9, 2, 0, 'Borrowed', '2025-04-28 04:26:25', 'borrow'),
(30, 3, 9, 0, 2, 'Returned', '2025-04-28 04:26:26', 'borrow'),
(31, 3, 9, 2, 0, 'Borrowed', '2025-04-28 04:32:32', 'borrow'),
(32, 3, 11, 5, 0, 'Borrowed', '2025-04-28 04:32:52', 'borrow'),
(33, 3, 76, 222, 0, 'Borrowed', '2025-04-28 04:45:24', 'borrow'),
(34, 3, 76, 0, 222, 'Returned', '2025-04-28 04:45:31', 'borrow'),
(35, 3, 30, 1, 0, 'Borrowed', '2025-04-28 04:47:45', 'borrow'),
(36, 3, 30, 0, 1, 'Returned', '2025-04-28 04:47:58', 'borrow'),
(37, 3, 9, 0, 2, 'Returned', '2025-04-28 04:48:01', 'borrow'),
(38, 3, 9, 2, 0, 'Borrowed', '2025-04-28 04:48:12', 'borrow'),
(39, 3, 9, 0, 2, 'Returned', '2025-04-28 04:48:14', 'borrow'),
(40, 3, 31, 1, 0, 'Borrowed', '2025-04-28 04:50:45', 'borrow'),
(41, 6, 9, 2, 0, 'Borrowed', '2025-04-28 04:51:25', 'borrow'),
(42, 7, 13, 1, 0, 'Borrowed', '2025-04-28 08:18:55', 'borrow'),
(43, 7, 13, 0, 1, 'Returned', '2025-04-28 08:19:01', 'borrow'),
(44, 7, 13, 1, 0, 'Borrowed', '2025-04-28 08:21:55', 'borrow'),
(45, 7, 13, 0, 1, 'Returned', '2025-04-28 08:21:59', 'borrow'),
(46, 7, 14, 1, 0, 'Borrowed', '2025-04-28 08:23:48', 'borrow'),
(47, 7, 13, 1, 0, 'Borrowed', '2025-04-28 08:23:59', 'borrow'),
(48, 7, 13, 0, 1, 'Returned', '2025-04-28 08:24:04', 'borrow'),
(49, 7, 15, 2, 0, 'Borrowed', '2025-04-28 08:24:12', 'borrow'),
(50, 7, 15, 0, 2, 'Returned', '2025-04-28 08:24:17', 'borrow'),
(51, 7, 15, 2, 0, 'Borrowed', '2025-04-28 08:24:23', 'borrow'),
(52, 7, 15, 0, 2, 'Returned', '2025-04-28 08:24:29', 'borrow'),
(53, 7, 16, 1, 0, 'Borrowed', '2025-04-28 08:27:25', 'borrow'),
(54, 7, 16, 0, 1, 'Returned', '2025-04-28 08:29:08', 'borrow'),
(55, 7, 16, 1, 0, 'Borrowed', '2025-04-28 08:29:22', 'borrow'),
(56, 7, 16, 0, 1, 'Returned', '2025-04-28 08:29:25', 'borrow'),
(57, 3, 77, 3, 0, 'Borrowed', '2025-04-28 08:47:13', 'borrow'),
(58, 3, 77, 0, 3, 'Returned', '2025-04-28 08:47:16', 'borrow'),
(59, 3, 77, 3, 0, 'Borrowed', '2025-04-28 08:47:18', 'borrow'),
(60, 3, 77, 0, 3, 'Returned', '2025-04-28 08:47:27', 'borrow'),
(61, 3, 77, 3, 0, 'Borrowed', '2025-04-28 08:47:30', 'borrow'),
(62, 3, 77, 0, 3, 'Returned', '2025-04-28 08:47:31', 'borrow'),
(63, 3, 77, 1, 0, 'Borrowed', '2025-04-28 08:53:53', 'borrow'),
(64, 3, 77, 0, 1, 'Returned', '2025-04-28 08:54:00', 'borrow'),
(65, 3, 31, 0, 1, 'Returned', '2025-04-29 08:33:06', 'borrow'),
(66, 8, 29, 1, 0, 'Borrowed', '2025-04-29 10:16:15', 'borrow'),
(67, 8, 18, 1, 0, 'Borrowed', '2025-04-29 10:16:29', 'borrow'),
(68, 8, 77, 2, 0, 'Borrowed', '2025-04-29 10:40:16', 'borrow'),
(69, 8, 29, 0, 1, 'Returned', '2025-04-29 10:42:51', 'borrow'),
(70, 8, 77, 0, 2, 'Returned', '2025-04-29 10:43:00', 'borrow'),
(71, 8, 77, 1, 0, 'Borrowed', '2025-04-30 07:24:57', 'borrow'),
(72, 8, 77, 1, 0, 'Borrowed', '2025-04-30 07:33:28', 'borrow'),
(73, 8, 77, 3, 0, 'Borrowed', '2025-04-30 07:33:39', 'borrow'),
(74, 8, 18, 0, 1, 'Returned', '2025-04-30 07:33:50', 'borrow'),
(75, 8, 77, 0, 3, 'Returned', '2025-05-01 06:41:53', 'borrow'),
(76, 8, 77, 1, 0, 'Borrowed', '2025-05-01 22:18:35', 'borrow'),
(77, 8, 77, 0, 1, 'Returned', '2025-05-01 22:37:12', 'borrow'),
(78, 8, 77, 2, 0, 'Borrowed', '2025-05-01 22:55:34', 'borrow'),
(79, 8, 77, 0, 2, 'Returned', '2025-05-01 22:56:11', 'borrow'),
(80, 8, 29, 1, 0, 'Borrowed', '2025-05-01 23:26:32', 'borrow'),
(81, 8, 29, 0, 1, 'Returned', '2025-05-01 23:30:49', 'borrow'),
(82, 9, 29, 1, 0, 'Borrowed', '2025-05-02 04:07:09', 'borrow'),
(83, 3, 11, 0, 5, 'Returned', '2025-05-02 08:53:50', 'borrow');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `first_name` varchar(255) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `email` varchar(255) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `department` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `user_type` enum('admin','user') NOT NULL DEFAULT 'user'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `first_name`, `last_name`, `email`, `password`, `department`, `role`, `created_at`, `user_type`) VALUES
(2, 'admin', 'admin', 'admin@gmail.com', '$2y$10$heXRymKFqL6./83WbFty8.07rSLitgZd35kCYv8BLnPF63RICRePm', NULL, 'user', '2025-04-28 03:10:48', 'admin'),
(3, 'test', 'test', 'test@gmail.com', '$2y$10$u3Jh31grXDnbtkzYHTXEX.X6s6gzWShY9ydvCPLuA3A9lVkRnbF3q', NULL, 'user', '2025-04-28 03:19:26', 'user'),
(4, 'Daniel', 'Diaz', 'da@gmail.com', '$2y$10$k.lzS3Z7hFHOD1YYE.BDLei.AAH3tBojeveEXG.rcFaU3BjtMkNqC', NULL, 'user', '2025-04-27 11:57:25', 'user'),
(5, 'dada', 'daaa', 'dada@gmail.com', '$2y$10$Ja/wNIGaEOdNd8MZL.C2rusGRS9DhiJj8oZsZQj5msyH9y0ykVrbu', NULL, 'user', '2025-04-27 12:51:37', 'user'),
(6, 'Daniel', 'Diaz', 'daniel@gmail.com', '$2y$10$46d6QFg5pdQlQ4TVuGzKVuSpYiPABIzfx9xPV2tFzRkQ1LG81yjdG', NULL, 'user', '2025-04-28 04:19:48', 'user'),
(7, 'sample', 'sa', 'sa@gmail.com', '$2y$10$DrJQmGJ0cNDQh5lttVuI3uT500HpZ6C85Q5qySm5DPcwhZ17TkGaq', NULL, 'user', '2025-04-28 08:18:32', 'user'),
(8, 'Miles', 'Morales', 'morales@gmail.com', '$2y$10$TLL9A1q3TWpAfLS5nX6fKO3bymOWrCF5DvJTd4pezA0dQjYWJYSXK', 'BSIS', 'user', '2025-04-29 10:15:36', 'user'),
(9, 'John', 'Doe', 'doe@test.com', '$2y$10$/r9KZl0TZjkuRBP/cmLbQe3usGl84rsZ3JIgJhMPvg4c8MLGxaorK', NULL, 'user', '2025-05-01 23:29:49', 'user');

-- --------------------------------------------------------

--
-- Table structure for table `user_borrowed_items`
--

CREATE TABLE `user_borrowed_items` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `borrowed_at` datetime DEFAULT current_timestamp(),
  `due_date` datetime NOT NULL COMMENT 'User-specified return date',
  `returned_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user_borrowed_items`
--

INSERT INTO `user_borrowed_items` (`id`, `user_id`, `item_id`, `quantity`, `borrowed_at`, `due_date`, `returned_at`) VALUES
(1, 3, 9, 2, '2025-04-28 12:32:32', '0000-00-00 00:00:00', '2025-04-28 12:48:01'),
(2, 3, 11, 5, '2025-04-28 12:32:52', '0000-00-00 00:00:00', '2025-05-02 01:53:50'),
(3, 3, 76, 222, '2025-04-28 12:45:24', '0000-00-00 00:00:00', '2025-04-28 12:45:31'),
(4, 3, 30, 1, '2025-04-28 12:47:45', '0000-00-00 00:00:00', '2025-04-28 12:47:58'),
(5, 3, 9, 2, '2025-04-28 12:48:12', '0000-00-00 00:00:00', '2025-04-28 12:48:14'),
(6, 3, 31, 1, '2025-04-28 12:50:45', '0000-00-00 00:00:00', '2025-04-29 01:33:06'),
(7, 6, 9, 2, '2025-04-28 12:51:25', '0000-00-00 00:00:00', NULL),
(8, 7, 13, 1, '2025-04-28 16:18:55', '0000-00-00 00:00:00', '2025-04-28 16:19:01'),
(9, 7, 13, 1, '2025-04-28 16:21:55', '0000-00-00 00:00:00', '2025-04-28 16:21:59'),
(10, 7, 14, 1, '2025-04-28 16:23:48', '0000-00-00 00:00:00', NULL),
(11, 7, 13, 1, '2025-04-28 16:23:59', '0000-00-00 00:00:00', '2025-04-28 16:24:04'),
(12, 7, 15, 2, '2025-04-28 16:24:12', '0000-00-00 00:00:00', '2025-04-28 16:24:17'),
(13, 7, 15, 2, '2025-04-28 16:24:23', '0000-00-00 00:00:00', '2025-04-28 16:24:29'),
(14, 7, 16, 1, '2025-04-28 16:27:25', '0000-00-00 00:00:00', '2025-04-28 16:29:08'),
(15, 7, 16, 1, '2025-04-28 16:29:22', '0000-00-00 00:00:00', '2025-04-28 16:29:25'),
(16, 3, 77, 3, '2025-04-28 16:47:13', '0000-00-00 00:00:00', '2025-04-28 16:47:16'),
(17, 3, 77, 3, '2025-04-28 16:47:18', '0000-00-00 00:00:00', '2025-04-28 16:47:27'),
(18, 3, 77, 3, '2025-04-28 16:47:30', '0000-00-00 00:00:00', '2025-04-28 16:47:31'),
(19, 3, 77, 1, '2025-04-28 16:53:53', '0000-00-00 00:00:00', '2025-04-28 16:54:00'),
(20, 8, 29, 1, '2025-04-29 03:16:15', '0000-00-00 00:00:00', '2025-04-29 03:42:51'),
(21, 8, 18, 1, '2025-04-29 03:16:29', '0000-00-00 00:00:00', '2025-04-30 00:33:50'),
(22, 8, 77, 2, '2025-04-29 03:40:16', '0000-00-00 00:00:00', '2025-04-29 03:43:00'),
(23, 8, 77, 1, '2025-04-30 00:24:57', '0000-00-00 00:00:00', '2025-04-30 23:41:53'),
(24, 8, 77, 1, '2025-04-30 00:33:28', '0000-00-00 00:00:00', '2025-04-30 23:41:53'),
(25, 8, 77, 3, '2025-04-30 00:33:39', '0000-00-00 00:00:00', '2025-04-30 23:41:53'),
(26, 8, 77, 1, '2025-05-01 15:18:35', '2025-05-09 00:18:35', '2025-05-01 15:37:12'),
(27, 8, 77, 2, '2025-05-01 15:55:34', '2025-05-05 00:00:00', '2025-05-01 15:56:11'),
(28, 8, 29, 1, '2025-05-01 16:26:32', '2025-05-05 00:00:00', '2025-05-01 16:30:49'),
(29, 9, 29, 1, '2025-05-01 21:07:09', '2025-05-06 00:00:00', NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_items_main_category` (`main_category_id`);

--
-- Indexes for table `main_categories`
--
ALTER TABLE `main_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`transaction_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `fk_item_id` (`item_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `user_borrowed_items`
--
ALTER TABLE `user_borrowed_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `item_id` (`item_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=85;

--
-- AUTO_INCREMENT for table `main_categories`
--
ALTER TABLE `main_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `transaction_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=84;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `user_borrowed_items`
--
ALTER TABLE `user_borrowed_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `borrowed_items`
--
ALTER TABLE `borrowed_items`
  ADD CONSTRAINT `borrowed_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrowed_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_requests`
--
ALTER TABLE `borrow_requests`
  ADD CONSTRAINT `borrow_requests_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `borrow_requests_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Constraints for table `items`
--
ALTER TABLE `items`
  ADD CONSTRAINT `fk_items_main_category` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_main_category` FOREIGN KEY (`main_category_id`) REFERENCES `main_categories` (`id`);

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `fk_item_id` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `user_borrowed_items`
--
ALTER TABLE `user_borrowed_items`
  ADD CONSTRAINT `user_borrowed_items_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `user_borrowed_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
