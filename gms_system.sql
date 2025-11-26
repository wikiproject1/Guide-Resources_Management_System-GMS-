-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Nov 26, 2025 at 09:38 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `gms_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_log`
--

CREATE TABLE `activity_log` (
  `id` int(11) NOT NULL,
  `action` varchar(255) NOT NULL,
  `details` text DEFAULT NULL,
  `admin_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `borrow_items`
--

CREATE TABLE `borrow_items` (
  `id` int(11) NOT NULL,
  `borrow_record_id` int(11) NOT NULL,
  `resource_id` int(11) NOT NULL,
  `quantity_borrowed` int(11) NOT NULL DEFAULT 0,
  `quantity_returned` int(11) NOT NULL DEFAULT 0,
  `is_returned` tinyint(1) DEFAULT 0,
  `return_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_items`
--

INSERT INTO `borrow_items` (`id`, `borrow_record_id`, `resource_id`, `quantity_borrowed`, `quantity_returned`, `is_returned`, `return_date`, `created_at`, `updated_at`) VALUES
(22, 9, 54, 1, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(23, 9, 52, 2, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(24, 9, 2, 1, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(25, 9, 50, 2, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(26, 9, 9, 2, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(27, 9, 24, 1, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(28, 9, 21, 4, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(29, 9, 53, 1, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(30, 9, 8, 2, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(31, 9, 7, 2, 0, 1, '2025-08-23 07:20:41', '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(32, 10, 50, 4, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(33, 10, 20, 1, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(34, 10, 2, 1, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(35, 10, 9, 2, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(36, 10, 7, 2, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(37, 10, 8, 2, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(38, 10, 21, 2, 0, 1, '2025-08-27 07:14:51', '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(39, 11, 8, 2, 0, 1, '2025-08-27 07:14:35', '2025-08-16 08:05:07', '2025-08-27 07:14:35'),
(40, 11, 7, 1, 0, 1, '2025-08-27 07:14:35', '2025-08-16 08:05:07', '2025-08-27 07:14:35'),
(41, 12, 34, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(42, 12, 42, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(43, 12, 61, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(44, 12, 38, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(45, 12, 56, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(46, 12, 59, 6, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(47, 12, 63, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(48, 12, 60, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(49, 12, 32, 2, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(50, 12, 43, 7, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(51, 12, 41, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(52, 12, 58, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(53, 12, 57, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(54, 12, 38, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(55, 12, 39, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(56, 12, 40, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(57, 12, 15, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(58, 12, 19, 3, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(59, 12, 33, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(60, 12, 14, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(61, 12, 28, 2, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(62, 12, 12, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(63, 12, 44, 2, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(64, 12, 13, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(65, 12, 23, 7, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(66, 12, 17, 1, 0, 1, '2025-08-22 11:21:10', '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(67, 13, 66, 1, 0, 0, NULL, '2025-08-23 06:27:23', '2025-08-23 07:27:23'),
(68, 14, 66, 1, 0, 0, NULL, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(69, 14, 53, 2, 0, 0, NULL, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(70, 14, 24, 1, 0, 0, NULL, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(71, 14, 21, 6, 0, 0, NULL, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(72, 14, 22, 2, 0, 0, NULL, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(73, 15, 34, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(74, 15, 67, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(75, 15, 72, 10, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(76, 15, 50, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(77, 15, 2, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(78, 15, 71, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(79, 15, 39, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(80, 15, 57, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(81, 15, 68, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(82, 15, 59, 9, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(83, 15, 54, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(84, 15, 43, 9, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(85, 15, 60, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(86, 15, 56, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(87, 15, 69, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(88, 15, 38, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(89, 15, 70, 1, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(90, 15, 73, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(91, 15, 63, 2, 0, 1, '2025-09-01 13:08:29', '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(92, 16, 34, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(93, 16, 72, 10, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(94, 16, 50, 2, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(95, 16, 56, 2, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(96, 16, 38, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(97, 16, 6, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(98, 16, 57, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(99, 16, 44, 3, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(100, 16, 2, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(101, 16, 39, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(102, 16, 43, 12, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(103, 16, 60, 1, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(104, 16, 59, 10, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(105, 16, 63, 2, 0, 0, NULL, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(106, 17, 9, 7, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(107, 17, 1, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(108, 17, 10, 2, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(109, 17, 6, 5, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(110, 17, 3, 2, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(111, 17, 7, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(112, 17, 4, 7, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(113, 17, 2, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(114, 17, 8, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(115, 17, 11, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(116, 17, 5, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05'),
(117, 17, 12, 1, 0, 0, NULL, '2025-09-22 10:49:05', '2025-09-22 11:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `borrow_records`
--

CREATE TABLE `borrow_records` (
  `id` int(11) NOT NULL,
  `guide_id` int(11) NOT NULL,
  `borrow_date` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expected_return_date` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `actual_return_date` timestamp NULL DEFAULT NULL,
  `status` enum('Borrowed','Returned','Overdue','Missing') DEFAULT 'Borrowed',
  `notes` text DEFAULT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `borrow_records`
--

INSERT INTO `borrow_records` (`id`, `guide_id`, `borrow_date`, `expected_return_date`, `actual_return_date`, `status`, `notes`, `created_by`, `created_at`, `updated_at`) VALUES
(9, 25, '2025-08-23 07:20:41', '2025-08-26 20:59:59', '2025-08-23 07:20:41', 'Returned', 'all things are good', 1, '2025-08-15 12:43:33', '2025-08-23 07:20:41'),
(10, 28, '2025-08-27 07:14:51', '2025-08-24 20:59:59', '2025-08-27 07:14:51', 'Returned', '', 1, '2025-08-15 13:07:31', '2025-08-27 07:14:51'),
(11, 28, '2025-08-27 07:14:35', '2025-08-23 20:59:59', '2025-08-27 07:14:35', 'Returned', '', 1, '2025-08-16 08:05:07', '2025-08-27 07:14:35'),
(12, 26, '2025-08-22 11:21:10', '2025-08-23 20:59:59', '2025-08-22 11:21:10', 'Returned', 'all are fine', 1, '2025-08-16 12:20:07', '2025-08-22 11:21:10'),
(13, 29, '2025-08-23 06:27:23', '2025-08-23 20:59:59', NULL, 'Borrowed', 'I GPS FOR COMPANY CAR', 1, '2025-08-23 06:27:23', '2025-08-23 07:27:23'),
(14, 28, '2025-08-23 10:55:06', '2025-08-29 20:59:59', NULL, 'Borrowed', 'FOR MISS RUSHAO', 1, '2025-08-23 10:55:06', '2025-08-23 11:55:06'),
(15, 30, '2025-09-01 13:08:29', '2025-09-01 20:59:59', '2025-09-01 13:08:29', 'Returned', 'All good', 1, '2025-08-27 06:21:04', '2025-09-01 13:08:29'),
(16, 31, '2025-09-08 05:05:54', '2025-09-13 20:59:59', NULL, 'Borrowed', 'THIS IS FOR MISS GU GROUP', 1, '2025-09-08 05:05:54', '2025-09-08 06:05:54'),
(17, 30, '2025-09-22 10:49:04', '2025-10-06 20:59:59', NULL, 'Borrowed', 'FOR MISS YONGZAI', 1, '2025-09-22 10:49:04', '2025-09-22 11:49:04');

-- --------------------------------------------------------

--
-- Table structure for table `guides`
--

CREATE TABLE `guides` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `type` enum('Mountain Guide','Safari Guide') NOT NULL,
  `contact_info` text DEFAULT NULL,
  `status` enum('Active','Inactive') DEFAULT 'Active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `guides`
--

INSERT INTO `guides` (`id`, `name`, `type`, `contact_info`, `status`, `created_at`, `updated_at`) VALUES
(25, 'KELVIN MASENGO', 'Safari Guide', '+255 789 773 572', 'Active', '2025-08-15 10:04:10', '2025-08-15 11:42:27'),
(26, 'WILLY ADVENTURE', 'Mountain Guide', '+255 653 808 586', 'Active', '2025-08-15 11:45:01', '2025-08-15 12:45:01'),
(28, 'VENANCE', 'Safari Guide', '+255 769 124 295', 'Active', '2025-08-15 13:03:51', '2025-08-15 14:03:51'),
(29, 'WALTER AMOS KIWELU', 'Safari Guide', '+255 746 379 453', 'Active', '2025-08-23 06:26:27', '2025-08-23 07:26:27'),
(30, 'OMARY KILIMANJARO GUIDE', 'Mountain Guide', '0681 180 860', 'Active', '2025-08-27 05:28:47', '2025-08-27 06:28:47'),
(31, 'ISACK KILI GUIDE', 'Mountain Guide', '0752369766', 'Active', '2025-09-08 05:00:57', '2025-09-08 06:00:57');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `category` enum('Mandatory','Optional') NOT NULL,
  `quantity_total` int(11) NOT NULL DEFAULT 0,
  `quantity_available` int(11) NOT NULL DEFAULT 0,
  `description` text DEFAULT NULL,
  `status` enum('Available','Borrowed','Missing') DEFAULT 'Available',
  `min_stock_level` int(11) DEFAULT 0,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `name`, `category`, `quantity_total`, `quantity_available`, `description`, `status`, `min_stock_level`, `location`, `created_at`, `updated_at`) VALUES
(1, 'ALL IN ONE TENT', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 05:56:26', '2025-09-22 11:49:05'),
(2, 'OXYMETER', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 05:56:59', '2025-09-22 11:49:05'),
(3, 'LIGHTS`', 'Mandatory', 2, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 07:49:14', '2025-09-22 11:49:05'),
(4, 'T-SHIRTS', 'Mandatory', 7, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 07:50:34', '2025-09-22 11:49:05'),
(5, 'MATRESS', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 07:52:59', '2025-09-22 11:49:05'),
(6, 'KIT BAGS', 'Mandatory', 5, 0, 'FOR MISS YONGZAI', 'Available', 0, 'GILMANS STORE', '2025-09-22 07:56:27', '2025-09-22 11:49:05'),
(7, 'TOILET FULLY', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'GILMANS STORE', '2025-09-22 10:27:24', '2025-09-22 11:49:05'),
(8, 'OXYGEN', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'GILMANS STORE', '2025-09-22 10:27:45', '2025-09-22 11:49:05'),
(9, 'COVER BAGS', 'Mandatory', 7, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 10:28:57', '2025-09-22 11:49:05'),
(10, 'CHINA + COMPANY FLAGS', 'Mandatory', 2, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 10:29:13', '2025-09-22 11:49:05'),
(11, 'BED', 'Mandatory', 1, 0, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 10:31:41', '2025-09-22 11:49:05'),
(12, 'TABLE + CHAIR', 'Mandatory', 2, 1, 'FOR MISS YONGZAI', 'Available', 0, 'WEST WILD ADVENTURE', '2025-09-22 10:32:10', '2025-09-22 11:49:05');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('Admin') DEFAULT 'Admin',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password_hash`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@gms.com', '$2y$10$alFTlsUtun2kPud40cc4seyVI8am9e27myjd34ObFXQvaXLIEWck6', 'Admin', 1, '2025-11-26 08:16:56', '2025-08-13 12:00:26', '2025-11-26 08:16:56');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `admin_id` (`admin_id`);

--
-- Indexes for table `borrow_items`
--
ALTER TABLE `borrow_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `borrow_record_id` (`borrow_record_id`),
  ADD KEY `resource_id` (`resource_id`);

--
-- Indexes for table `borrow_records`
--
ALTER TABLE `borrow_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `guide_id` (`guide_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `guides`
--
ALTER TABLE `guides`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_log`
--
ALTER TABLE `activity_log`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `borrow_items`
--
ALTER TABLE `borrow_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `borrow_records`
--
ALTER TABLE `borrow_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `guides`
--
ALTER TABLE `guides`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activity_log`
--
ALTER TABLE `activity_log`
  ADD CONSTRAINT `activity_log_ibfk_1` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_items`
--
ALTER TABLE `borrow_items`
  ADD CONSTRAINT `borrow_items_ibfk_1` FOREIGN KEY (`borrow_record_id`) REFERENCES `borrow_records` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_items_ibfk_2` FOREIGN KEY (`resource_id`) REFERENCES `resources` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `borrow_records`
--
ALTER TABLE `borrow_records`
  ADD CONSTRAINT `borrow_records_ibfk_1` FOREIGN KEY (`guide_id`) REFERENCES `guides` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `borrow_records_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
