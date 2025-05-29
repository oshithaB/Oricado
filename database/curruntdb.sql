-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 21, 2025 at 04:26 PM
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
-- Database: `oricado`
--

-- --------------------------------------------------------

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `type` enum('individual','company') NOT NULL,
  `name` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `mobile` varchar(20) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `tax_number` varchar(50) DEFAULT NULL,
  `website` varchar(100) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT 'profilepic.jpg',
  `tags` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_by` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `type`, `name`, `address`, `phone`, `mobile`, `email`, `tax_number`, `website`, `profile_picture`, `tags`, `created_at`, `created_by`) VALUES
(7, 'individual', 'sameera', 'test', '12345678', '1234567', 'abc@mail.com', '', '', '681090fa1b4da.jpeg', '', '2025-04-22 18:55:38', 3),
(8, 'individual', 'sameeraa', 'test', '07324563', '07634532', 'abc@mail.com', '', '', '6810911fc12a1.jpeg', '', '2025-04-22 19:21:18', 3),
(9, 'individual', 'oshitha', 'piliyandala', '0766961189', '11111133333', 'oxxikala@gmail.com', '', '', '681090e3172ad.jpeg', 'cs', '2025-04-24 10:41:29', 3),
(10, 'company', 'kalhara', '114/7', '0766961234', '123456789', 'oxx@gmail.com', '', '', '681090cc61d37.jpeg', 'good', '2025-04-27 17:25:37', 3);

-- --------------------------------------------------------

--
-- Table structure for table `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `invoice_type` enum('advance','final') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `advance_amount` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `invoices`
--

INSERT INTO `invoices` (`id`, `order_id`, `invoice_type`, `amount`, `advance_amount`, `balance_amount`, `created_by`, `created_at`) VALUES
(1, 69, 'advance', 500.00, 0.00, 9500.00, 3, '2025-05-08 11:14:36'),
(2, 69, '', 9500.00, 500.00, 0.00, 3, '2025-05-08 13:07:53'),
(3, 68, '', 400.00, 0.00, 1580.00, 3, '2025-05-08 13:30:53'),
(4, 68, 'advance', 600.00, 0.00, 1380.00, 3, '2025-05-08 13:41:40'),
(5, 68, '', 1380.00, 600.00, 0.00, 3, '2025-05-08 13:43:03'),
(6, 68, 'advance', 300.00, 0.00, 1680.00, 3, '2025-05-08 13:48:56'),
(7, 68, '', 1680.00, 300.00, 0.00, 3, '2025-05-08 13:50:15'),
(8, 68, 'advance', 500.00, 0.00, 1480.00, 3, '2025-05-08 13:55:24'),
(9, 68, 'final', 1480.00, 500.00, 0.00, 3, '2025-05-08 14:26:51'),
(10, 71, 'advance', 1000.00, 0.00, 19000.00, 3, '2025-05-08 19:03:03'),
(11, 71, 'final', 19000.00, 1000.00, 0.00, 3, '2025-05-08 19:04:11'),
(12, 82, 'advance', 1000.00, 0.00, 4555.56, 3, '2025-05-15 06:05:59'),
(13, 82, 'final', 4555.56, 1000.00, 0.00, 3, '2025-05-15 06:06:23'),
(14, 83, 'advance', 500.00, 0.00, 3055.56, 3, '2025-05-15 16:09:56'),
(15, 83, 'final', 3055.56, 500.00, 0.00, 3, '2025-05-15 16:26:12'),
(16, 84, 'advance', 10000.00, 0.00, 24722.22, 3, '2025-05-15 17:09:12'),
(17, 85, 'advance', 100.00, 0.00, 247.22, 3, '2025-05-15 17:21:44');

-- --------------------------------------------------------

--
-- Table structure for table `materials`
--

CREATE TABLE `materials` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `type` enum('coil','other') NOT NULL,
  `thickness` decimal(4,2) DEFAULT NULL,
  `color` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL,
  `unit` varchar(20) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `materials`
--

INSERT INTO `materials` (`id`, `name`, `type`, `thickness`, `color`, `quantity`, `unit`, `price`) VALUES
(62, 'මෝටර්', 'other', NULL, NULL, 86.00, 'pieces', 100.00),
(63, 'roller door', 'other', NULL, NULL, 63.00, 'sqft', 1000.00),
(64, 'පුලි', 'other', NULL, NULL, 86.00, 'pieces', 100.00),
(65, 'ස්ප්‍රින්ග්', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(66, 'බෝල්ට් ඇන', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(67, 'වොශර්', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(68, 'එල් ඈන්ගල්', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(69, 'U බෝල්ට්', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(70, 'ඇන්කර් බෝල්ට්', 'other', NULL, NULL, 83.00, 'pieces', 0.00),
(71, 'U චැනල් කොයිල්', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(72, 'Sqew nails', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(73, 'Nilon strips', 'other', NULL, NULL, 86.00, 'meters', 0.00),
(74, 'Stepler rails', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(75, 'Tomb bar', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(76, 'Cutting wheel', 'other', NULL, NULL, 83.00, 'pieces', 0.00),
(77, 'Paint', 'other', NULL, NULL, 86.00, 'liters', 0.00),
(78, 'Tiner', 'other', NULL, NULL, 86.00, 'liters', 0.00),
(79, 'Center lock', 'other', NULL, NULL, 84.00, 'pieces', 0.00),
(80, 'Down lock', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(81, 'Side lock', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(82, 'Door hooks', 'other', NULL, NULL, 54.00, 'pieces', 0.00),
(83, 'Covering coils', 'other', NULL, NULL, 83.00, 'sqft', 0.00),
(84, 'Box bar 1x1.2mm', 'other', NULL, NULL, 65.00, 'pieces', 0.00),
(85, 'Box bar 1x1/2mm', 'other', NULL, NULL, 73.00, 'pieces', 0.00),
(86, 'Revert 5/32 x 1/2', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(87, 'Revert 5/32 x 1', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(88, 'Unit box', 'other', NULL, NULL, 83.00, 'pieces', 0.00),
(89, 'Flat iron 1\"x 6mm', 'other', NULL, NULL, 88.00, 'pieces', 0.00),
(90, 'Flat iron 1\" x 1/2 x 6mm', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(91, 'Roll plug 6mm', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(92, 'Roll plug nut', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(93, 'Case', 'other', NULL, NULL, 80.00, 'pieces', 0.00),
(94, 'Steel bar', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(95, 'Eye hole', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(96, 'Letter box', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(97, 'Letter holder', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(98, 'Tomb bar beedin', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(99, 'Gate bush', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(100, 'Saraneru', 'other', NULL, NULL, 80.00, 'pieces', 0.00),
(101, 'Kondipattam', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(102, '12 welding sticks', 'other', 0.00, 'coffee_brown', 50.00, 'pieces', 0.00),
(103, '13A plug top', 'other', NULL, NULL, 67.00, 'pieces', 0.00),
(104, 'Door handle', 'other', NULL, NULL, 99.00, 'pieces', 0.00),
(105, 'Stop bracket', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(106, 'Stop bracket nails', 'other', NULL, NULL, 86.00, 'pieces', 0.00),
(107, 'Dagara kambi 10mm', 'other', NULL, NULL, 84.00, 'pieces', 0.00),
(108, 'Control unit + remote', 'other', NULL, NULL, 84.00, 'pieces', 0.00),
(109, 'Coil', 'coil', 0.60, 'coffee_brown', 80.00, 'sqft', 100.00),
(110, 'Coil', 'coil', 0.60, 'black_shine', 85.00, 'sqft', 100.00),
(111, 'Coil', 'coil', 0.60, 'blue_color', 90.00, 'sqft', 100.00),
(112, 'Coil', 'coil', 0.60, 'butter_milk', 100.00, 'sqft', 100.00),
(113, 'Coil', 'coil', 0.60, 'chocolate_brown', 100.00, 'sqft', 100.00),
(114, 'Coil', 'coil', 0.60, 'black_mate', 100.00, 'sqft', 100.00),
(115, 'Coil', 'coil', 0.60, 'beige', 90.00, 'sqft', 100.00),
(116, 'Coil', 'coil', 0.47, 'coffee_brown', 100.00, 'sqft', 100.00),
(117, 'Coil', 'coil', 0.47, 'black_shine', 290.00, 'sqft', 114.00),
(118, 'Coil', 'coil', 0.47, 'blue_color', 1000.00, 'sqft', 100.00),
(119, 'Coil', 'coil', 0.47, 'butter_milk', 100.00, 'sqft', 100.00),
(120, 'Coil', 'coil', 0.47, 'chocolate_brown', 100.00, 'sqft', 100.00),
(121, 'Coil', 'coil', 0.47, 'black_mate', 95.00, 'sqft', 100.00),
(122, 'Coil', 'coil', 0.47, 'beige', 103.00, 'sqft', 109.49),
(123, 'test 1', 'other', NULL, NULL, 100.00, 'pieces', 200.00),
(124, 'test6', 'other', NULL, NULL, 0.00, NULL, 0.00),
(125, 'test6', 'other', NULL, NULL, 0.00, NULL, 0.00),
(126, 'tst', 'other', NULL, NULL, 3.00, 'sqft', 100.00),
(127, 'test6', 'other', NULL, NULL, 70.00, 'sqft', 26.00),
(128, 'tst10', 'other', NULL, NULL, 40.00, 'pieces', 100.00),
(129, 'tst11', 'other', NULL, NULL, 50.00, 'pieces', 100.00),
(130, 'test50', 'other', NULL, NULL, 100.00, 'meters', 100.00),
(131, 'test20', 'other', NULL, NULL, 2.00, 'meters', 100.00),
(132, 'test80', 'other', NULL, NULL, 30.00, 'sqft', 200.00);

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(100) NOT NULL,
  `customer_contact` varchar(50) NOT NULL,
  `customer_address` text NOT NULL,
  `prepared_by` int(11) DEFAULT NULL,
  `checked_by` int(11) DEFAULT NULL,
  `approved_by` int(11) DEFAULT NULL,
  `status` enum('pending','reviewed','confirmed','completed','done') DEFAULT 'pending',
  `total_price` decimal(10,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `quotation_id` int(11) DEFAULT NULL,
  `total_sqft` decimal(10,2) DEFAULT NULL,
  `admin_approved` tinyint(1) DEFAULT 0,
  `admin_approved_by` int(11) DEFAULT NULL,
  `admin_approved_at` datetime DEFAULT NULL,
  `material_cost` decimal(10,2) DEFAULT 0.00,
  `paid_amount` decimal(10,2) DEFAULT 0.00,
  `balance_amount` decimal(10,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`id`, `customer_name`, `customer_contact`, `customer_address`, `prepared_by`, `checked_by`, `approved_by`, `status`, `total_price`, `created_at`, `quotation_id`, `total_sqft`, `admin_approved`, `admin_approved_by`, `admin_approved_at`, `material_cost`, `paid_amount`, `balance_amount`) VALUES
(1, 'kalhara', '', '', 3, 2, NULL, 'done', 10000.00, '2025-04-09 19:29:39', NULL, NULL, 0, NULL, NULL, 0.00, 0.00, 0.00),
(2, 'iruki chamathka', '076 12345678', '', 3, 2, NULL, 'completed', 300000.00, '2025-04-09 21:14:59', NULL, NULL, 0, NULL, NULL, 0.00, 0.00, 0.00),
(12, 'iruki', '1122334', 'abc', 3, 2, NULL, 'done', 30000.00, '2025-04-21 15:11:31', NULL, NULL, 0, NULL, NULL, 0.00, 0.00, 0.00),
(13, 'iruki2', '134567', 'adsf', 3, 2, NULL, 'confirmed', 10000.00, '2025-04-21 15:52:41', NULL, NULL, 1, 1, '2025-04-23 20:57:23', 2700.00, 0.00, 0.00),
(14, 'iru3', '23456789', 'acdfs', 3, 2, NULL, 'done', 30000.00, '2025-04-21 22:08:03', NULL, NULL, 0, NULL, NULL, 0.00, 0.00, 0.00),
(15, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-22 22:25:59', NULL, NULL, 0, NULL, NULL, 0.00, 0.00, 0.00),
(18, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-23 06:17:01', 20, 162.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(24, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-23 09:33:03', 22, 40.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(25, 'sameera', '1234567', 'test', 3, 2, NULL, 'confirmed', 59388.00, '2025-04-23 09:37:40', 21, 60.00, 1, 1, '2025-04-23 22:37:41', 15000.00, 0.00, 0.00),
(44, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', 499950.00, '2025-04-23 18:52:09', 30, 520.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(45, 'sameera', '1234567', 'test', 3, 2, NULL, 'done', 59388.00, '2025-04-23 18:54:04', 31, 40.00, 1, 1, '2025-04-24 01:04:39', 4000.00, 0.00, 0.00),
(46, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', 90000.00, '2025-04-24 16:23:13', 50, 110.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(47, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 29400.00, '2025-04-24 16:27:48', 51, 40.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(48, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:15:32', 52, 65.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(49, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:19:49', 53, 12.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(50, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:30:35', 49, 21.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(51, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:36:07', 36, 49.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(52, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:46:36', 54, 15.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(53, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 17:56:55', 55, 28.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(54, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 20000.00, '2025-04-24 18:09:04', 33, 20.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(55, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 18:10:54', 56, 42.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(56, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 18:16:56', 57, 14.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(57, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 18:25:01', 58, 4.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(58, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 18:31:30', 59, 4.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(59, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', NULL, '2025-04-24 18:59:51', 60, 10.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(60, 'sameera', '1234567', 'test', 3, 2, NULL, 'confirmed', 10000.00, '2025-04-24 19:05:55', 61, 8.00, 1, 1, '2025-04-25 03:11:50', 2189.80, 0.00, 0.00),
(61, 'sameeraa', '07634532', 'test', 3, 2, NULL, 'confirmed', 18000.00, '2025-04-24 19:14:01', 62, 18.00, 1, 1, '2025-04-25 03:04:30', 1204.39, 0.00, 0.00),
(62, 'sameeraa', '07634532', 'test', 3, 2, NULL, 'done', 19000.00, '2025-04-24 19:16:51', 63, 18.00, 1, 1, '2025-04-25 02:10:03', 2000.00, 0.00, 0.00),
(63, 'sameera', '1234567', 'test', 3, 2, NULL, 'done', 6098.00, '2025-04-27 17:35:17', 67, 6.00, 1, 1, '2025-04-27 23:11:02', 218.98, 0.00, 0.00),
(64, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 9900.00, '2025-04-29 08:57:09', 70, 12.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(65, 'kalhara', '123456789', '114/7', 3, 2, NULL, 'done', 15680.00, '2025-04-29 08:59:38', 69, 16.00, 1, 1, '2025-04-29 14:43:12', 500.00, 0.00, 0.00),
(66, 'kalhara', '123456789', '114/7', 3, 2, NULL, 'done', 27720.00, '2025-04-30 04:34:49', 74, 28.00, 1, 1, '2025-04-30 10:07:44', 500.00, 0.00, 0.00),
(67, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', 9900.00, '2025-05-07 19:58:35', 76, 15.00, 0, NULL, NULL, 0.00, 0.00, 0.00),
(68, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 1980.00, '2025-05-07 20:15:04', 77, 10.00, 0, NULL, NULL, 0.00, 1980.00, 0.00),
(69, 'sameera', '1234567', 'test', 3, NULL, NULL, '', 10000.00, '2025-05-07 20:16:18', 78, 18.00, 0, NULL, NULL, 0.00, 10000.00, 0.00),
(70, 'kalhara', '123456789', '114/7', 3, 2, NULL, 'done', 21000.00, '2025-05-08 11:18:44', 79, 21.00, 1, 1, '2025-05-08 17:12:58', 1000.00, 0.00, 2000.00),
(71, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 20000.00, '2025-05-08 19:00:17', 80, 15.00, 0, NULL, NULL, 0.00, 20000.00, 0.00),
(72, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', 20000.00, '2025-05-12 17:20:43', 82, 0.42, 0, NULL, NULL, 0.00, 0.00, 20000.00),
(73, 'sameeraa', '07634532', 'test', 3, NULL, NULL, 'pending', 30000.00, '2025-05-15 03:11:30', 83, 5.56, 0, NULL, NULL, 0.00, 0.00, 30000.00),
(74, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 20000.00, '2025-05-15 03:24:13', 84, 5.56, 0, NULL, NULL, 0.00, 0.00, 20000.00),
(75, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 10000.00, '2025-05-15 03:34:06', 85, 5.56, 0, NULL, NULL, 0.00, 0.00, 10000.00),
(76, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 17000.00, '2025-05-15 04:22:00', 92, 2.72, 0, NULL, NULL, 0.00, 0.00, 17000.00),
(77, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 17000.00, '2025-05-15 04:53:48', 95, 2.35, 0, NULL, NULL, 0.00, 0.00, 17000.00),
(79, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 6722.22, '2025-05-15 05:04:44', 96, 6.72, 0, NULL, NULL, 0.00, 0.00, 6722.22),
(80, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 14000.00, '2025-05-15 05:52:22', 97, 3.56, 0, NULL, NULL, 0.00, 0.00, 14000.00),
(81, 'kalhara', '123456789', '114/7', 3, NULL, NULL, 'pending', 25000.00, '2025-05-15 05:54:55', 98, 5.56, 0, NULL, NULL, 0.00, 0.00, 25000.00),
(82, 'kalhara', '123456789', '114/7', 3, 2, NULL, 'reviewed', 5555.56, '2025-05-15 06:04:46', 99, 5.56, 1, 1, '2025-05-20 22:18:00', 1094.90, 5555.56, 0.00),
(83, 'sameeraa', '07634532', 'test', 3, 2, NULL, 'done', 3555.56, '2025-05-15 16:09:02', 100, 3.56, 1, 1, '2025-05-15 21:52:14', 2000.00, 3555.56, 0.00),
(84, 'kalhara', '123456789', '114/7', 3, 2, NULL, 'confirmed', 34722.22, '2025-05-15 16:54:51', 101, 34.72, 1, 1, '2025-05-15 22:48:16', 2189.80, 10000.00, 24722.22),
(85, 'sameera', '1234567', 'test', 3, 2, NULL, 'confirmed', 347.22, '2025-05-15 17:21:36', 102, 0.35, 1, 1, '2025-05-15 22:53:50', 2189.80, 100.00, 247.22),
(86, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 4166.67, '2025-05-21 09:35:00', 103, 4.17, 0, NULL, NULL, 0.00, 0.00, 4166.67),
(87, 'sameera', '1234567', 'test', 3, NULL, NULL, 'pending', 6356.47, '2025-05-21 09:36:57', 104, 4.17, 0, NULL, NULL, 0.00, 0.00, 6356.47);

-- --------------------------------------------------------

--
-- Table structure for table `order_materials`
--

CREATE TABLE `order_materials` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `quantity` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_materials`
--

INSERT INTO `order_materials` (`id`, `order_id`, `material_id`, `quantity`) VALUES
(1, 1, 102, 5.00),
(2, 1, 103, 5.00),
(3, 1, 70, 5.00),
(4, 1, 66, 5.00),
(5, 1, 84, 5.00),
(6, 1, 85, 5.00),
(7, 1, 93, 5.00),
(8, 1, 79, 5.00),
(9, 1, 111, 5.00),
(10, 1, 108, 5.00),
(11, 1, 83, 5.00),
(12, 1, 76, 5.00),
(13, 1, 107, 5.00),
(14, 1, 104, 5.00),
(15, 1, 82, 5.00),
(16, 1, 80, 5.00),
(17, 1, 95, 5.00),
(18, 1, 90, 5.00),
(19, 1, 89, 5.00),
(20, 1, 99, 5.00),
(21, 1, 63, 5.00),
(22, 1, 101, 5.00),
(23, 1, 68, 5.00),
(24, 1, 96, 5.00),
(25, 1, 97, 5.00),
(26, 1, 62, 5.00),
(27, 1, 73, 5.00),
(28, 1, 77, 5.00),
(29, 1, 64, 5.00),
(30, 1, 87, 5.00),
(31, 1, 86, 5.00),
(32, 1, 91, 5.00),
(33, 1, 92, 5.00),
(34, 1, 100, 5.00),
(35, 1, 81, 5.00),
(36, 1, 65, 5.00),
(37, 1, 72, 5.00),
(38, 1, 94, 5.00),
(39, 1, 74, 5.00),
(40, 1, 105, 5.00),
(41, 1, 106, 5.00),
(42, 1, 78, 5.00),
(43, 1, 75, 5.00),
(44, 1, 98, 5.00),
(45, 1, 69, 5.00),
(46, 1, 71, 5.00),
(47, 1, 88, 5.00),
(48, 1, 67, 5.00),
(49, 2, 102, 5.00),
(50, 2, 103, 5.00),
(51, 2, 70, 5.00),
(52, 2, 66, 5.00),
(53, 2, 84, 5.00),
(54, 2, 85, 5.00),
(55, 2, 93, 5.00),
(56, 2, 79, 5.00),
(57, 2, 111, 5.00),
(58, 2, 108, 5.00),
(59, 2, 83, 5.00),
(60, 2, 76, 5.00),
(61, 2, 107, 5.00),
(62, 2, 104, 5.00),
(63, 2, 82, 5.00),
(64, 2, 80, 5.00),
(65, 2, 95, 5.00),
(66, 2, 90, 5.00),
(67, 2, 89, 5.00),
(68, 2, 99, 5.00),
(69, 2, 63, 5.00),
(70, 2, 101, 5.00),
(71, 2, 68, 5.00),
(72, 2, 96, 5.00),
(73, 2, 97, 5.00),
(74, 2, 62, 5.00),
(75, 2, 73, 5.00),
(76, 2, 77, 5.00),
(77, 2, 64, 5.00),
(78, 2, 87, 5.00),
(79, 2, 86, 5.00),
(80, 2, 91, 5.00),
(81, 2, 92, 5.00),
(82, 2, 100, 5.00),
(83, 2, 81, 5.00),
(84, 2, 65, 5.00),
(85, 2, 72, 5.00),
(86, 2, 94, 5.00),
(87, 2, 74, 5.00),
(88, 2, 105, 5.00),
(89, 2, 106, 5.00),
(90, 2, 78, 5.00),
(91, 2, 75, 5.00),
(92, 2, 98, 5.00),
(93, 2, 69, 5.00),
(94, 2, 71, 5.00),
(95, 2, 88, 5.00),
(96, 2, 67, 5.00),
(97, 12, 102, 2.00),
(98, 12, 103, 2.00),
(99, 12, 70, 3.00),
(100, 12, 66, 2.00),
(101, 12, 84, 4.00),
(102, 12, 85, 2.00),
(103, 12, 93, 2.00),
(104, 12, 79, 2.00),
(105, 12, 110, 10.00),
(106, 12, 108, 2.00),
(107, 12, 83, 3.00),
(108, 12, 76, 3.00),
(109, 12, 107, 2.00),
(110, 12, 104, 2.00),
(111, 12, 82, 4.00),
(112, 12, 80, 2.00),
(113, 12, 95, 2.00),
(114, 12, 90, 2.00),
(115, 12, 99, 2.00),
(116, 12, 63, 2.00),
(117, 12, 101, 2.00),
(118, 12, 68, 2.00),
(119, 12, 96, 2.00),
(120, 12, 97, 2.00),
(121, 12, 62, 2.00),
(122, 12, 73, 2.00),
(123, 12, 77, 2.00),
(124, 12, 64, 2.00),
(125, 12, 87, 2.00),
(126, 12, 86, 2.00),
(127, 12, 91, 2.00),
(128, 12, 92, 2.00),
(129, 12, 100, 2.00),
(130, 12, 81, 2.00),
(131, 12, 65, 2.00),
(132, 12, 72, 2.00),
(133, 12, 94, 2.00),
(134, 12, 74, 2.00),
(135, 12, 105, 2.00),
(136, 12, 106, 2.00),
(137, 12, 78, 2.00),
(138, 12, 75, 2.00),
(139, 12, 98, 2.00),
(140, 12, 69, 2.00),
(141, 12, 71, 2.00),
(142, 12, 88, 2.00),
(143, 12, 67, 2.00),
(144, 14, 102, 3.00),
(145, 14, 103, 4.00),
(146, 14, 84, 5.00),
(147, 14, 85, 4.00),
(148, 14, 93, 5.00),
(149, 14, 79, 2.00),
(150, 14, 122, 2.00),
(151, 14, 108, 2.00),
(152, 14, 83, 2.00),
(153, 14, 76, 2.00),
(154, 14, 107, 2.00),
(155, 14, 104, 2.00),
(156, 14, 82, 2.00),
(157, 14, 80, 2.00),
(158, 14, 95, 2.00),
(159, 14, 90, 2.00),
(160, 14, 89, 2.00),
(161, 14, 99, 2.00),
(162, 14, 101, 2.00),
(163, 14, 96, 2.00),
(164, 14, 97, 2.00),
(165, 14, 73, 2.00),
(166, 14, 77, 2.00),
(167, 14, 87, 2.00),
(168, 14, 86, 2.00),
(169, 14, 91, 2.00),
(170, 14, 92, 2.00),
(171, 14, 100, 2.00),
(172, 14, 81, 2.00),
(173, 14, 72, 2.00),
(174, 14, 94, 2.00),
(175, 14, 74, 2.00),
(176, 14, 105, 2.00),
(177, 14, 106, 2.00),
(178, 14, 78, 2.00),
(179, 14, 75, 2.00),
(180, 14, 98, 2.00),
(181, 14, 71, 2.00),
(182, 14, 69, 2.00),
(183, 14, 88, 2.00),
(184, 14, 70, 2.00),
(185, 14, 68, 2.00),
(186, 14, 64, 2.00),
(187, 14, 66, 2.00),
(188, 14, 62, 2.00),
(189, 14, 67, 2.00),
(190, 14, 65, 2.00),
(191, 13, 102, 3.00),
(192, 13, 103, 3.00),
(193, 13, 84, 3.00),
(194, 13, 85, 3.00),
(195, 13, 93, 3.00),
(196, 13, 79, 3.00),
(197, 13, 122, 3.00),
(198, 13, 108, 2.00),
(199, 13, 83, 2.00),
(200, 13, 76, 2.00),
(201, 13, 107, 2.00),
(202, 13, 104, 2.00),
(203, 13, 82, 2.00),
(204, 13, 80, 2.00),
(205, 13, 95, 2.00),
(206, 13, 90, 2.00),
(207, 13, 89, 2.00),
(208, 13, 99, 2.00),
(209, 13, 63, 2.00),
(210, 13, 101, 2.00),
(211, 13, 96, 2.00),
(212, 13, 97, 2.00),
(213, 13, 73, 2.00),
(214, 13, 77, 2.00),
(215, 13, 87, 2.00),
(216, 13, 86, 2.00),
(217, 13, 91, 2.00),
(218, 13, 92, 2.00),
(219, 13, 100, 2.00),
(220, 13, 81, 2.00),
(221, 13, 72, 2.00),
(222, 13, 94, 2.00),
(223, 13, 74, 2.00),
(224, 13, 105, 2.00),
(225, 13, 106, 2.00),
(226, 13, 78, 2.00),
(227, 13, 75, 2.00),
(228, 13, 98, 2.00),
(229, 13, 71, 2.00),
(230, 13, 69, 2.00),
(231, 13, 88, 2.00),
(232, 13, 70, 2.00),
(233, 13, 68, 2.00),
(234, 13, 64, 2.00),
(235, 13, 66, 2.00),
(236, 13, 62, 2.00),
(237, 13, 67, 2.00),
(238, 13, 65, 2.00),
(239, 25, 102, 10.00),
(240, 25, 103, 4.00),
(241, 25, 84, 7.00),
(242, 25, 85, 7.00),
(243, 25, 109, 150.00),
(244, 25, 104, 2.00),
(245, 25, 82, 2.00),
(246, 25, 80, 2.00),
(247, 25, 106, 2.00),
(248, 45, 111, 40.00),
(249, 45, 102, 5.00),
(250, 45, 103, 5.00),
(251, 45, 84, 5.00),
(252, 45, 85, 5.00),
(253, 45, 93, 5.00),
(254, 45, 79, 5.00),
(255, 45, 108, 5.00),
(256, 45, 83, 5.00),
(257, 45, 76, 5.00),
(258, 45, 107, 5.00),
(259, 45, 104, 5.00),
(260, 62, 109, 20.00),
(261, 62, 102, 2.00),
(262, 62, 84, 2.00),
(263, 62, 85, 2.00),
(264, 62, 93, 2.00),
(265, 62, 79, 2.00),
(266, 62, 108, 2.00),
(267, 61, 122, 11.00),
(268, 61, 102, 4.00),
(269, 61, 103, 3.00),
(270, 61, 84, 3.00),
(271, 60, 122, 20.00),
(272, 60, 102, 7.00),
(273, 60, 84, 2.00),
(274, 60, 85, 3.00),
(275, 63, 122, 2.00),
(276, 63, 79, 2.00),
(277, 63, 108, 2.00),
(278, 63, 83, 2.00),
(279, 63, 76, 2.00),
(280, 63, 107, 2.00),
(281, 65, 121, 5.00),
(282, 65, 103, 3.00),
(283, 65, 84, 4.00),
(284, 65, 85, 5.00),
(285, 66, 110, 5.00),
(286, 66, 102, 2.00),
(287, 66, 103, 3.00),
(288, 66, 84, 5.00),
(289, 70, 115, 10.00),
(290, 70, 88, 3.00),
(291, 70, 70, 2.00),
(292, 83, 109, 20.00),
(293, 83, 102, 3.00),
(294, 83, 103, 4.00),
(295, 83, 84, 5.00),
(296, 83, 85, 3.00),
(297, 83, 93, 3.00),
(298, 84, 122, 20.00),
(299, 84, 102, 15.00),
(300, 84, 103, 2.00),
(301, 85, 122, 20.00),
(302, 85, 102, 15.00),
(303, 85, 103, 5.00),
(304, 82, 122, 10.00),
(305, 82, 102, 2.00),
(306, 82, 84, 3.00);

-- --------------------------------------------------------

--
-- Table structure for table `quotations`
--

CREATE TABLE `quotations` (
  `id` int(11) NOT NULL,
  `type` enum('raw_materials','order') NOT NULL,
  `quotation_type` enum('sell','buy') DEFAULT 'sell',
  `customer_name` varchar(100) NOT NULL,
  `customer_contact` varchar(50) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `coil_thickness` decimal(4,2) DEFAULT NULL,
  `quotation_text` text DEFAULT NULL,
  `order_id` int(11) DEFAULT NULL,
  `is_updated` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotations`
--

INSERT INTO `quotations` (`id`, `type`, `quotation_type`, `customer_name`, `customer_contact`, `total_amount`, `created_by`, `created_at`, `coil_thickness`, `quotation_text`, `order_id`, `is_updated`) VALUES
(1, 'raw_materials', 'sell', 'sameera', '1234567', 235.00, 3, '2025-04-22 19:18:50', 0.60, '', NULL, 0),
(2, 'raw_materials', 'sell', 'sameera', '1234567', 0.00, 3, '2025-04-22 19:19:08', 0.60, '', NULL, 0),
(3, 'order', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 19:23:54', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(4, 'raw_materials', 'sell', 'sameeraa', '07634532', 200.00, 3, '2025-04-22 19:39:15', 0.60, '', NULL, 0),
(5, 'raw_materials', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 19:44:03', 0.60, '', NULL, 0),
(6, 'order', 'sell', 'sameeraa', '07634532', 100.00, 3, '2025-04-22 19:55:39', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(7, 'order', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 19:56:27', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(8, 'order', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 20:02:11', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(9, 'order', 'sell', 'sameera', '1234567', 296.00, 3, '2025-04-22 20:49:44', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(10, 'order', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 20:59:45', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(11, 'raw_materials', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-22 21:01:28', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(12, 'order', 'sell', 'sameera', '1234567', 441.00, 3, '2025-04-22 21:15:14', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(13, 'order', 'sell', 'sameera', '1234567', 1176.00, 3, '2025-04-22 21:34:36', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(17, 'order', 'sell', 'sameeraa', '07634532', 1039.00, 3, '2025-04-22 22:13:38', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(19, 'order', 'sell', 'sameera', '1234567', 352.00, 3, '2025-04-22 22:37:21', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(20, 'order', 'sell', 'sameera', '1234567', 99960.00, 3, '2025-04-22 22:42:34', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(21, 'order', 'sell', 'sameera', '1234567', 59388.00, 3, '2025-04-23 08:29:47', 0.60, 'Default text for 0.60 thickness quotation...', 25, 1),
(22, 'order', 'sell', 'sameera', '1234567', 39592.00, 3, '2025-04-23 08:51:14', 0.60, 'Default text for 0.60 thickness quotation...', 24, 0),
(23, 'order', 'sell', 'sameera', '1234567', 104000.00, 3, '2025-04-23 09:47:42', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(24, 'order', 'sell', 'sameeraa', '07634532', 83109.60, 3, '2025-04-23 10:02:40', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(25, 'order', 'sell', 'sameeraa', '07634532', 150100.00, 3, '2025-04-23 11:17:04', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(26, 'order', 'sell', 'sameera', '1234567', 150470.00, 3, '2025-04-23 16:19:38', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(27, 'order', 'sell', 'sameera', '1234567', 99930.60, 3, '2025-04-23 17:31:17', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(28, 'order', 'sell', 'sameeraa', '07634532', 129948.00, 3, '2025-04-23 17:38:17', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(29, 'order', 'sell', 'sameera', '1234567', 158304.00, 3, '2025-04-23 18:05:08', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(30, 'order', 'sell', 'sameeraa', '07634532', 499950.00, 3, '2025-04-23 18:24:59', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(31, 'order', 'sell', 'sameera', '1234567', 59388.00, 3, '2025-04-23 18:53:00', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 0),
(32, 'raw_materials', 'sell', 'sameeraa', '07634532', 2600.00, 3, '2025-04-23 20:13:28', 0.60, '', NULL, 0),
(33, 'order', 'sell', 'sameera', '1234567', 20000.00, 3, '2025-04-23 20:15:25', 0.47, 'Default text for 0.47 thickness quotation...', NULL, 0),
(34, 'raw_materials', 'sell', 'sameeraa', '07634532', 10000.00, 3, '2025-04-23 20:18:38', 0.60, '', NULL, 0),
(35, 'raw_materials', 'sell', 'sameera', '1234567', 2000.00, 3, '2025-04-23 20:27:49', 0.60, '', NULL, 0),
(36, 'order', 'sell', 'sameera', '1234567', 51000.00, 3, '2025-04-23 20:34:28', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(37, 'raw_materials', 'sell', 'sameera', '1234567', 25000.00, 3, '2025-04-23 21:21:18', 0.60, '', NULL, 0),
(40, '', 'buy', 'sameera', '1234567', 0.00, 3, '2025-04-24 05:12:40', NULL, NULL, NULL, 0),
(41, '', 'buy', 'sameera', '1234567', 0.00, 3, '2025-04-24 05:14:43', NULL, NULL, NULL, 0),
(43, '', 'buy', 'sameera', '1234567', 0.00, 3, '2025-04-24 05:51:53', NULL, NULL, NULL, 0),
(44, '', 'buy', 'sameeraa', '07634532', 0.00, 3, '2025-04-24 06:02:46', NULL, NULL, NULL, 0),
(45, '', 'buy', 'sameeraa', '07634532', 0.00, 3, '2025-04-24 06:08:24', NULL, NULL, NULL, 0),
(46, '', 'buy', 'sameera', '1234567', 300.00, 3, '2025-04-24 06:10:45', NULL, NULL, NULL, 0),
(47, '', 'buy', 'sameera', '1234567', 8320.00, 3, '2025-04-24 06:25:17', NULL, NULL, NULL, 0),
(48, '', 'buy', 'sameera', '1234567', 6500.00, 3, '2025-04-24 06:42:27', NULL, NULL, NULL, 0),
(49, 'order', 'sell', 'sameeraa', '07634532', 20790.00, 3, '2025-04-24 10:37:08', 0.60, 'Default text for 0.60 thickness quotation...', NULL, 1),
(50, 'order', 'sell', 'sameeraa', '07634532', 90000.00, 3, '2025-04-24 16:18:32', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(51, 'order', 'sell', 'sameera', '1234567', 29400.00, 3, '2025-04-24 16:26:24', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(52, 'order', 'sell', 'sameeraa', '07634532', 67522.00, 3, '2025-04-24 17:09:33', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(53, 'order', 'sell', 'sameera', '1234567', 11880.00, 3, '2025-04-24 17:18:27', 0.60, 'Features of the \r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(54, 'order', 'sell', 'sameera', '1234567', 14998.50, 3, '2025-04-24 17:45:44', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(55, 'order', 'sell', 'sameera', '1234567', 27720.00, 3, '2025-04-24 17:55:24', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(56, 'order', 'sell', 'sameera', '1234567', 42000.00, 3, '2025-04-24 18:09:59', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(57, 'order', 'sell', 'sameera', '1234567', 14000.00, 3, '2025-04-24 18:15:29', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(58, 'order', 'sell', 'sameera', '1234567', 4000.00, 3, '2025-04-24 18:23:29', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(59, 'order', 'sell', 'sameera', '1234567', 4000.00, 3, '2025-04-24 18:30:29', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(60, 'order', 'sell', 'sameeraa', '07634532', 9800.00, 3, '2025-04-24 18:58:55', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(61, 'order', 'sell', 'sameera', '1234567', 10000.00, 3, '2025-04-24 19:05:15', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(62, 'order', 'sell', 'sameeraa', '07634532', 18000.00, 3, '2025-04-24 19:13:19', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(63, 'order', 'sell', 'sameeraa', '07634532', 19000.00, 3, '2025-04-24 19:15:58', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(64, '', 'sell', 'sameera', '1234567', 4328.47, 3, '2025-04-24 20:48:35', NULL, NULL, NULL, 0),
(65, 'order', 'sell', 'sameeraa', '07634532', 98000.00, 3, '2025-04-25 04:47:26', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(66, 'raw_materials', 'sell', 'sameera', '1234567', 321.00, 3, '2025-04-27 17:28:48', 0.60, '', NULL, 0),
(67, 'order', 'sell', 'sameera', '1234567', 6098.00, 3, '2025-04-27 17:31:52', 0.47, 'Features of the Roller Door\r\nPanel: 914mm wide, 0.47mm thick Zinc Aluminum Roller Door Panel\r\nComponents: Includes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminium Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days from the date issued.\r\nAdvance Payment: 50% of the grand total is due within 3 days of the quotation date as a non-refundable, non-transferable advance.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to access the site during office hours for installation.\r\nThe customer or an authorized representative must be present during site visits.\r\nORICADO ROLLER DOORS is not liable for delays or extra costs if access is restricted.\r\nThe customer should ensure the site is ready for installation within 12 working days of the advance payment. Delays in preparation may lead to price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are based on the current government budget and may be revised in case of any government price changes or budget updates.\r\nCurrency Fluctuation: Prices are subject to change due to fluctuations in the US Dollar exchange rate.\r\nExclusion of Taxes: Prices are exclusive of all applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015. /  Ms. Chathuri at +94 74 156 8098.\r\n\r\n​\r\n\r\nWe trust this quotation meets your requirements. ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\n\r\n\r\nPrepared By: ......................................	​​	​​Checked By:.........................................	​	​	​Authorized By:.........................................', NULL, 1),
(68, '', 'sell', 'sameera', '1234567', 22000.00, 3, '2025-04-27 17:49:11', NULL, NULL, NULL, 0),
(69, 'order', 'sell', 'kalhara', '123456789', 15680.00, 3, '2025-04-29 07:27:30', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1);
INSERT INTO `quotations` (`id`, `type`, `quotation_type`, `customer_name`, `customer_contact`, `total_amount`, `created_by`, `created_at`, `coil_thickness`, `quotation_text`, `order_id`, `is_updated`) VALUES
(70, 'order', 'sell', 'kalhara', '123456789', 9900.00, 3, '2025-04-29 08:50:21', 0.47, 'Features of the Roller Door\r\nPanel: 914mm wide, 0.47mm thick Zinc Aluminum Roller Door Panel\r\nComponents: Includes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminium Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days from the date issued.\r\nAdvance Payment: 50% of the grand total is due within 3 days of the quotation date as a non-refundable, non-transferable advance.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to access the site during office hours for installation.\r\nThe customer or an authorized representative must be present during site visits.\r\nORICADO ROLLER DOORS is not liable for delays or extra costs if access is restricted.\r\nThe customer should ensure the site is ready for installation within 12 working days of the advance payment. Delays in preparation may lead to price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are based on the current government budget and may be revised in case of any government price changes or budget updates.\r\nCurrency Fluctuation: Prices are subject to change due to fluctuations in the US Dollar exchange rate.\r\nExclusion of Taxes: Prices are exclusive of all applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015. /  Ms. Chathuri at +94 74 156 8098.\r\n\r\n​\r\n\r\nWe trust this quotation meets your requirements. ORICADO ROLLER DOORS is committed to delivering high-quality products using advanced technology and premium materials.\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\n\r\n\r\nPrepared By: ......................................	​​	​​Checked By:.........................................	​	​	​Authorized By:.........................................', NULL, 0),
(71, 'raw_materials', 'sell', 'sameera', '1234567', 100.00, 3, '2025-04-29 09:35:27', 0.60, '', NULL, 0),
(72, '', 'sell', 'kalhara', '123456789', 1294.90, 3, '2025-04-29 10:00:54', NULL, NULL, NULL, 0),
(73, 'raw_materials', 'sell', 'kalhara', '123456789', 328.00, 3, '2025-04-30 04:21:00', 0.60, '', NULL, 0),
(74, 'order', 'sell', 'kalhara', '123456789', 27720.00, 3, '2025-04-30 04:22:30', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(75, '', 'sell', 'kalhara', '123456789', 10560.00, 3, '2025-04-30 04:43:24', NULL, NULL, NULL, 0),
(76, 'order', 'sell', 'sameeraa', '07634532', 14850.00, 3, '2025-05-07 19:57:49', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(77, 'order', 'sell', 'sameera', '1234567', 9900.00, 3, '2025-05-07 20:08:30', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(78, 'order', 'sell', 'sameera', '1234567', 18000.00, 3, '2025-05-07 20:15:54', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(79, 'order', 'sell', 'kalhara', '123456789', 21000.00, 3, '2025-05-08 11:18:01', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(80, 'order', 'sell', 'kalhara', '123456789', 15000.00, 3, '2025-05-08 18:58:56', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(81, 'raw_materials', 'sell', 'sameera', '1234567', 600.00, 3, '2025-05-11 12:14:33', 0.60, '', NULL, 0),
(82, 'order', 'sell', 'sameeraa', '07634532', 416.67, 3, '2025-05-12 17:19:07', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(83, 'order', 'sell', 'sameeraa', '07634532', 5555.56, 3, '2025-05-15 03:10:28', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(84, 'order', 'sell', 'sameera', '1234567', 5555.56, 3, '2025-05-15 03:23:06', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(85, 'order', 'sell', 'kalhara', '123456789', 5555.56, 3, '2025-05-15 03:33:19', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(86, 'order', 'sell', 'kalhara', '123456789', 5555.56, 3, '2025-05-15 03:48:48', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(87, 'order', 'sell', 'kalhara', '123456789', 1388.89, 3, '2025-05-15 03:51:17', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(88, 'order', 'sell', 'kalhara', '123456789', 868.06, 3, '2025-05-15 03:56:53', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(89, 'order', 'sell', 'kalhara', '123456789', 3125.00, 3, '2025-05-15 04:05:32', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(90, 'order', 'sell', 'sameeraa', '07634532', 1388.89, 3, '2025-05-15 04:10:31', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(91, 'order', 'sell', 'kalhara', '123456789', 1388.89, 3, '2025-05-15 04:17:06', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(92, 'order', 'sell', 'sameera', '1234567', 2722.22, 3, '2025-05-15 04:21:09', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(93, 'order', 'sell', 'kalhara', '123456789', 2722.22, 3, '2025-05-15 04:35:06', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1);
INSERT INTO `quotations` (`id`, `type`, `quotation_type`, `customer_name`, `customer_contact`, `total_amount`, `created_by`, `created_at`, `coil_thickness`, `quotation_text`, `order_id`, `is_updated`) VALUES
(94, 'order', 'sell', 'kalhara', '123456789', 8680.56, 3, '2025-05-15 04:47:13', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(95, 'order', 'sell', 'kalhara', '123456789', 17000.00, 3, '2025-05-15 04:52:58', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(96, 'order', 'sell', 'sameera', '1234567', 6722.22, 3, '2025-05-15 05:00:18', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(97, 'order', 'sell', 'kalhara', '123456789', 14000.00, 3, '2025-05-15 05:51:27', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(98, 'order', 'sell', 'kalhara', '123456789', 25000.00, 3, '2025-05-15 05:53:59', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 0),
(99, 'order', 'sell', 'kalhara', '123456789', 5555.56, 3, '2025-05-15 06:00:52', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(100, 'order', 'sell', 'sameeraa', '07634532', 3555.56, 3, '2025-05-15 16:06:39', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(101, 'order', 'sell', 'kalhara', '123456789', 34722.22, 3, '2025-05-15 16:53:11', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(102, 'order', 'sell', 'sameera', '1234567', 347.22, 3, '2025-05-15 17:20:52', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(103, 'order', 'sell', 'sameera', '1234567', 4166.67, 3, '2025-05-21 08:51:47', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1),
(104, 'order', 'sell', 'sameera', '1234567', 6356.47, 3, '2025-05-21 09:36:02', 0.60, 'Features of the Roller Door\r\n914mm wide, 0.60mm thick powder-coated roller door panel\r\nIncludes Springs, Pulleys, GI Center Bar, Dust Seal, Nylon Strip, Aluminum Bottom Bars, and Side Locks\r\nAvailable Colors\r\nBlack, Buttermilk, Beige, Coffee Brown, Blue, Green, Maroon, Autumn Red, Maroon (sand finished)\r\nWarranty\r\n10-Year Warranty on non-corrosive states of the door panel (Conditions Apply)\r\nWarranty Card issued upon installation after full payment\r\n2-Year Warranty for motor & 1-Year Warranty for remotes (Conditions Apply)\r\nTerms & Conditions\r\nValidity: Quotation valid for 7 days only.\r\nPayment: 50% of the grand total is due as an advance payment within 3 days of the quotation date. This payment is non-refundable and non-transferable.\r\nSite Access:\r\n\r\nThe customer agrees to allow company representatives to visit the installation site during office hours at a mutually convenient time.\r\nThe customer or an authorized representative must be present during site visits.\r\nThe company is not responsible for any delays or additional costs due to restricted access or delays by the customer.\r\nThe customer must prepare the site within 12 working days of the advance payment. Any delays in site preparation may result in price adjustments, and the advance payment will not be refunded.\r\nFinal Payment: Full payment is required prior to delivery and installation. Ownership remains with ORICADO ROLLER DOORS until full payment is received. In case of non-payment, the company reserves the right to claim any damages and costs, and the advance payment will be forfeited.\r\nPrice Adjustments: Prices are subject to change based on government budget updates or exchange rate fluctuations.\r\nTax Exclusion: Prices exclude applicable taxes.\r\nBank Details\r\nAccount Name: RIYON INTERNATIONAL (PVT) LTD\r\nBank: HATTON NATIONAL BANK - MALABE\r\nAccount Number: 1560 1000 9853\r\nFor inquiries, please contact  Ms. Poojani at +94 76 827 4015 /  Ms. Chathuri at +94 74 156 8098.   \r\n\r\nWe are committed to providing high-quality products using the latest technology and premium materials.\r\n\r\nThank you for considering ORICADO ROLLER DOOR. 	​	​	​	​	​\r\n\r\nYours Sincerely,\r\n\r\nORICADO ROLLER DOORS\r\n\r\n\r\n\r\nPrepared By: ......................................	​	​​Checked By:...........................................	​Authorized By:...................................................', NULL, 1);

-- --------------------------------------------------------

--
-- Table structure for table `quotation_items`
--

CREATE TABLE `quotation_items` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `material_id` int(11) DEFAULT NULL,
  `name` varchar(100) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `discount` decimal(5,2) DEFAULT 0.00,
  `price` decimal(10,2) NOT NULL,
  `taxes` decimal(5,2) DEFAULT 0.00,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `quotation_items`
--

INSERT INTO `quotation_items` (`id`, `quotation_id`, `material_id`, `name`, `quantity`, `unit`, `discount`, `price`, `taxes`, `amount`) VALUES
(1, 17, 122, 'Coil - beige (0.47)', 10.00, 'sqft', 1.00, 100.00, 5.00, 1039.50),
(3, 19, 122, 'Coil - beige (0.47)', 2.00, 'sqft', 2.00, 150.00, 20.00, 352.80),
(4, 20, 63, 'roller door', 100.00, 'sqft', 2.00, 1000.00, 2.00, 99960.00),
(5, 21, 63, 'roller door', 60.00, 'sqft', 2.00, 1000.00, 1.00, 59388.00),
(6, 22, 63, 'roller door', 40.00, 'sqft', 2.00, 1000.00, 1.00, 39592.00),
(7, 23, 63, 'roller door', 104.00, 'sqft', 0.00, 1000.00, 0.00, 104000.00),
(8, 24, 63, 'roller door', 84.00, 'sqft', 3.00, 1000.00, 2.00, 83109.60),
(9, 25, 63, 'roller door', 150.00, 'sqft', 0.00, 1000.00, 0.00, 150000.00),
(10, 25, 62, 'මෝටර්', 1.00, 'pieces', 0.00, 100.00, 0.00, 100.00),
(11, 26, 63, 'roller door', 150.00, 'sqft', 2.00, 1000.00, 1.00, 148470.00),
(12, 26, 104, 'Door handle', 2.00, 'pieces', 0.00, 1000.00, 0.00, 2000.00),
(13, 27, 63, 'roller door', 99.00, 'sqft', 2.00, 1000.00, 3.00, 99930.60),
(14, 28, 63, 'roller door', 130.00, 'sqft', 2.00, 1000.00, 2.00, 129948.00),
(15, 29, 63, 'roller door', 160.00, 'sqft', 3.00, 1000.00, 2.00, 158304.00),
(16, 30, 63, 'roller door', 500.00, 'sqft', 1.00, 1000.00, 1.00, 499950.00),
(17, 31, 63, 'roller door', 60.00, 'sqft', 2.00, 1000.00, 1.00, 59388.00),
(18, 32, 118, 'Coil - blue_color (0.47)', 20.00, 'sqft', 0.00, 100.00, 0.00, 2000.00),
(19, 32, 82, 'Door hooks', 30.00, 'pieces', 0.00, 20.00, 0.00, 600.00),
(20, 33, 63, 'roller door', 20.00, 'sqft', 0.00, 1000.00, 0.00, 20000.00),
(21, 34, 104, 'Door handle', 100.00, 'pieces', 0.00, 100.00, 0.00, 10000.00),
(22, 35, 104, 'Door handle', 10.00, 'pieces', 0.00, 100.00, 0.00, 1000.00),
(23, 35, 118, 'Coil - blue_color (0.47)', 10.00, 'sqft', 0.00, 100.00, 0.00, 1000.00),
(24, 36, 63, 'roller door', 49.00, 'sqft', 0.00, 1000.00, 0.00, 49000.00),
(25, 36, 122, 'Coil - beige (0.47)', 20.00, 'sqft', 0.00, 100.00, 0.00, 2000.00),
(26, 37, 63, 'roller door', 25.00, 'sqft', 0.00, 1000.00, 0.00, 25000.00),
(27, 46, NULL, 'tst', 3.00, 'sqft', 0.00, 100.00, 0.00, 300.00),
(28, 47, 122, 'Coil', 50.00, 'sqft', 0.00, 130.00, 0.00, 6500.00),
(29, 47, NULL, 'test6', 70.00, 'sqft', 0.00, 26.00, 0.00, 1820.00),
(30, 48, 117, 'Coil', 50.00, 'sqft', 0.00, 130.00, 0.00, 6500.00),
(31, 49, 63, 'roller door', 21.00, 'sqft', 1.00, 1000.00, 0.00, 20790.00),
(32, 50, 63, 'roller door', 100.00, 'sqft', 10.00, 1000.00, 0.00, 90000.00),
(33, 51, 63, 'roller door', 30.00, 'sqft', 2.00, 1000.00, 0.00, 29400.00),
(34, 52, 63, 'roller door', 65.00, 'sqft', 2.00, 1000.00, 6.00, 67522.00),
(35, 53, 63, 'roller door', 12.00, 'sqft', 1.00, 1000.00, 0.00, 11880.00),
(36, 54, 63, 'roller door', 15.00, 'sqft', 1.00, 1000.00, 1.00, 14998.50),
(37, 55, 63, 'roller door', 28.00, 'sqft', 1.00, 1000.00, 0.00, 27720.00),
(38, 56, 63, 'roller door', 42.00, 'sqft', 0.00, 1000.00, 0.00, 42000.00),
(39, 57, 63, 'roller door', 14.00, 'sqft', 0.00, 1000.00, 0.00, 14000.00),
(40, 58, 63, 'roller door', 4.00, 'sqft', 0.00, 1000.00, 0.00, 4000.00),
(41, 59, 63, 'roller door', 4.00, 'sqft', 0.00, 1000.00, 0.00, 4000.00),
(42, 60, 63, 'roller door', 10.00, 'sqft', 2.00, 1000.00, 0.00, 9800.00),
(43, 61, 63, 'roller door', 10.00, 'sqft', 0.00, 1000.00, 0.00, 10000.00),
(44, 62, 63, 'roller door', 18.00, 'sqft', 0.00, 1000.00, 0.00, 18000.00),
(45, 63, 63, 'roller door', 18.00, 'sqft', 0.00, 1000.00, 0.00, 18000.00),
(46, 63, 104, 'Door handle', 10.00, 'pieces', 0.00, 100.00, 0.00, 1000.00),
(47, 64, 122, 'Coil', 3.00, 'sqft', 0.00, 109.49, 0.00, 328.47),
(48, 64, NULL, 'tst10', 40.00, 'pieces', 0.00, 100.00, 0.00, 4000.00),
(49, 65, 63, 'roller door', 100.00, 'sqft', 2.00, 1000.00, 0.00, 98000.00),
(50, 66, 122, 'Coil - beige (0.47)', 3.00, 'sqft', 2.00, 109.49, 0.00, 321.90),
(51, 67, 63, 'roller door', 6.00, 'sqft', 0.00, 1000.00, 0.00, 6000.00),
(52, 67, 62, 'මෝටර්', 1.00, 'pieces', 2.00, 100.00, 0.00, 98.00),
(53, 68, 117, 'Coil', 100.00, 'sqft', 0.00, 120.00, 0.00, 12000.00),
(54, 68, NULL, 'test50', 100.00, 'meters', 0.00, 100.00, 0.00, 10000.00),
(55, 69, 63, 'roller door', 16.00, 'sqft', 2.00, 1000.00, 0.00, 15680.00),
(56, 70, 63, 'roller door', 10.00, 'sqft', 2.00, 1000.00, 0.00, 9800.00),
(57, 70, 62, 'මෝටර්', 1.00, 'pieces', 0.00, 100.00, 0.00, 100.00),
(58, 71, 104, 'Door handle', 1.00, 'pieces', 0.00, 100.00, 0.00, 100.00),
(59, 72, 122, 'Coil', 10.00, 'sqft', 0.00, 109.49, 0.00, 1094.90),
(60, 72, NULL, 'test20', 2.00, 'meters', 0.00, 100.00, 0.00, 200.00),
(61, 73, 122, 'Coil - beige (0.47)', 3.00, 'sqft', 1.00, 109.49, 1.00, 328.44),
(62, 74, 63, 'roller door', 28.00, 'sqft', 1.00, 1000.00, 0.00, 27720.00),
(63, 75, 117, 'Coil', 40.00, 'sqft', 0.00, 114.00, 0.00, 4560.00),
(64, 75, NULL, 'test80', 30.00, 'sqft', 0.00, 200.00, 0.00, 6000.00),
(65, 76, 63, 'roller door', 15.00, 'sqft', 1.00, 1000.00, 0.00, 14850.00),
(66, 77, 63, 'roller door', 10.00, 'sqft', 1.00, 1000.00, 0.00, 9900.00),
(67, 78, 63, 'roller door', 18.00, 'sqft', 0.00, 1000.00, 0.00, 18000.00),
(68, 79, 63, 'roller door', 21.00, 'sqft', 0.00, 1000.00, 0.00, 21000.00),
(69, 80, 63, 'roller door', 15.00, 'sqft', 0.00, 1000.00, 0.00, 15000.00),
(70, 81, 100, 'Saraneru', 6.00, 'pieces', 0.00, 100.00, 0.00, 600.00),
(71, 82, 63, 'roller door', 0.42, 'sqft', 0.00, 1000.00, 0.00, 416.67),
(72, 83, 63, 'roller door', 5.56, 'sqft', 0.00, 1000.00, 0.00, 5555.56),
(73, 84, 63, 'roller door', 5.56, 'sqft', 0.00, 1000.00, 0.00, 5555.56),
(74, 85, 63, 'roller door', 5.56, 'sqft', 0.00, 1000.00, 0.00, 5555.56),
(75, 86, 63, 'roller door', 5.56, 'sqft', 0.00, 1000.00, 0.00, 5555.56),
(76, 87, 63, 'roller door', 1.39, 'sqft', 0.00, 1000.00, 0.00, 1388.89),
(77, 88, 63, 'roller door', 0.87, 'sqft', 0.00, 1000.00, 0.00, 868.06),
(78, 89, 63, 'roller door', 3.13, 'sqft', 0.00, 1000.00, 0.00, 3125.00),
(79, 90, 63, 'roller door', 1.39, 'sqft', 0.00, 1000.00, 0.00, 1388.89),
(80, 91, 63, 'roller door', 1.39, 'sqft', 0.00, 1000.00, 0.00, 1388.89),
(81, 92, 63, 'roller door', 2.72, 'sqft', 0.00, 1000.00, 0.00, 2722.22),
(82, 93, 63, 'roller door', 2.72, 'sqft', 0.00, 1000.00, 0.00, 2722.22),
(83, 94, 63, 'roller door', 8.68, 'sqft', 0.00, 1000.00, 0.00, 8680.56),
(84, 95, 63, 'roller door', 17.00, 'sqft', 0.00, 1000.00, 0.00, 17000.00),
(85, 96, 63, 'roller door', 6.72, 'sqft', 0.00, 1000.00, 0.00, 6722.22),
(86, 97, 63, 'roller door', 14.00, 'sqft', 0.00, 1000.00, 0.00, 14000.00),
(87, 98, 63, 'roller door', 25.00, 'sqft', 0.00, 1000.00, 0.00, 25000.00),
(88, 99, 63, 'roller door', 5.56, 'sqft', 0.00, 1000.00, 0.00, 5555.56),
(89, 100, 63, 'roller door', 3.56, 'sqft', 0.00, 1000.00, 0.00, 3555.56),
(90, 101, 63, 'roller door', 34.72, 'sqft', 0.00, 1000.00, 0.00, 34722.22),
(91, 102, 63, 'roller door', 0.35, 'sqft', 0.00, 1000.00, 0.00, 347.22),
(92, 103, 63, 'roller door', 4.17, 'sqft', 0.00, 1000.00, 0.00, 4166.67),
(93, 104, 63, 'roller door', 4.17, 'sqft', 0.00, 1000.00, 0.00, 4166.67),
(94, 104, 122, 'Coil - beige (0.47)', 20.00, 'sqft', 0.00, 109.49, 0.00, 2189.80);

-- --------------------------------------------------------

--
-- Table structure for table `roller_door_measurements`
--

CREATE TABLE `roller_door_measurements` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `outside_width` decimal(10,2) DEFAULT NULL,
  `inside_width` decimal(10,2) DEFAULT NULL,
  `door_width` decimal(10,2) DEFAULT NULL,
  `tower_height` decimal(10,2) DEFAULT NULL,
  `tower_type` enum('small','large') DEFAULT NULL,
  `coil_color` varchar(50) DEFAULT NULL,
  `thickness` decimal(4,2) DEFAULT NULL,
  `covering` enum('full','side') DEFAULT NULL,
  `side_lock` tinyint(1) DEFAULT NULL,
  `motor` enum('R','L','manual') DEFAULT NULL,
  `fixing` enum('inside','outside') DEFAULT NULL,
  `down_lock` tinyint(1) DEFAULT NULL,
  `section1` decimal(10,2) DEFAULT NULL,
  `section2` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roller_door_measurements`
--

INSERT INTO `roller_door_measurements` (`id`, `order_id`, `outside_width`, `inside_width`, `door_width`, `tower_height`, `tower_type`, `coil_color`, `thickness`, `covering`, `side_lock`, `motor`, `fixing`, `down_lock`, `section1`, `section2`) VALUES
(1, 1, 67.00, 56.00, 89.00, 87.00, 'small', 'blue_color', 0.47, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(2, 2, 78.00, 78.00, 78.00, 78.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(12, 12, 7.00, 7.00, 7.00, 7.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(13, 13, 3.00, 3.00, 3.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(14, 14, 4.00, 5.00, 5.00, 8.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(15, 15, 4.00, 5.00, 2.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, NULL, NULL),
(16, 18, 9.00, 9.00, 9.00, 9.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 9.00, 9.00),
(17, 24, 5.00, 5.00, 4.00, 6.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 5.00, 5.00),
(18, 25, 5.00, 5.00, 6.00, 6.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 5.00, 5.00),
(26, 44, 4.00, 4.00, 10.00, 6.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 50.00, 2.00),
(27, 45, 6.00, 6.00, 4.00, 5.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 4.00, 6.00),
(28, 46, 6.00, 7.00, 11.00, 6.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 5.00, 5.00),
(29, 47, 5.00, 7.00, 4.00, 4.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 5.00, 5.00),
(30, 48, 5.00, 3.00, 5.00, 6.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 6.00, 7.00),
(31, 49, 5.00, 6.00, 2.00, 6.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 4.00),
(32, 50, 5.00, 7.00, 7.00, 7.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 1.00, 2.00),
(33, 51, 6.00, 6.00, 7.00, 4.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 3.00, 4.00),
(34, 52, 3.00, 4.00, 5.00, 6.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 1.00, 2.00),
(35, 53, 6.00, 2.00, 4.00, 4.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 4.00, 3.00),
(36, 54, 4.00, 4.00, 4.00, 4.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 3.00),
(37, 55, 6.00, 6.00, 6.00, 6.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 5.00),
(38, 56, 4.00, 3.00, 2.00, 1.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 5.00),
(39, 57, 2.00, 2.00, 2.00, 2.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 1.00, 1.00),
(40, 58, 1.00, 1.00, 2.00, 2.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 1.00, 1.00),
(41, 59, 2.00, 2.00, 2.00, 2.00, 'small', 'coffee_brown', NULL, NULL, NULL, NULL, NULL, NULL, 2.00, 3.00),
(42, 60, 2.00, 2.00, 2.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 2.00),
(43, 61, 3.00, 3.00, 3.00, 3.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 3.00, 3.00),
(44, 62, 3.00, 3.00, 3.00, 3.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 4.00, 2.00),
(45, 63, 3.00, 3.00, 2.00, 4.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 1.00, 2.00),
(46, 64, 3.00, 3.00, 2.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 4.00, 2.00),
(47, 65, 2.00, 2.00, 2.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 3.00, 5.00),
(48, 66, 4.00, 4.00, 4.00, 4.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 3.00, 4.00),
(49, 67, 4.00, 2.00, 3.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 3.00),
(50, 68, 4.00, 3.00, 2.00, 2.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 3.00),
(51, 69, 3.00, 3.00, 3.00, 3.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 1.00, 5.00),
(52, 70, 2.00, 3.00, 3.00, 3.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 5.00),
(53, 71, 4.00, 3.00, 3.00, 3.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 2.00, 3.00),
(54, 72, 3.00, 3.00, 3.00, 10.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 10.00, 10.00),
(55, 73, 20.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 20.00, 20.00),
(56, 74, 20.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 20.00, 20.00),
(57, 75, 20.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 20.00, 20.00),
(58, 76, 14.00, 13.00, 14.00, 14.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 14.00, 14.00),
(59, 77, 13.00, 13.00, 13.00, 13.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 13.00, 13.00),
(60, 79, 22.00, 22.00, 22.00, 22.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 22.00, 22.00),
(61, 80, 16.00, 16.00, 16.00, 15.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 16.00, 16.00),
(62, 81, 20.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 20.00, 20.00),
(63, 82, 20.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 20.00, 20.00),
(64, 83, 16.00, 16.00, 16.00, 16.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 16.00, 16.00),
(65, 84, 50.00, 50.00, 50.00, 50.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 50.00, 50.00),
(66, 85, 5.00, 5.00, 5.00, 5.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 5.00, 5.00),
(67, 86, 30.00, 40.00, 10.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 10.00, 10.00),
(68, 87, 30.00, 20.00, 20.00, 20.00, 'small', 'coffee_brown', 0.60, 'full', 1, 'R', 'inside', 1, 10.00, 10.00);

-- --------------------------------------------------------

--
-- Table structure for table `supplier_quotations`
--

CREATE TABLE `supplier_quotations` (
  `id` int(11) NOT NULL,
  `quotation_id` int(11) DEFAULT NULL,
  `supplier_name` varchar(255) DEFAULT NULL,
  `supplier_contact` varchar(100) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_quotations`
--

INSERT INTO `supplier_quotations` (`id`, `quotation_id`, `supplier_name`, `supplier_contact`, `total_amount`, `created_by`, `created_at`) VALUES
(2, 40, 'sameera', '1234567', 0.00, 3, '2025-04-24 05:12:40'),
(3, 41, 'sameera', '1234567', 0.00, 3, '2025-04-24 05:14:43'),
(4, 43, 'sameera', '1234567', 0.00, 3, '2025-04-24 05:51:53'),
(5, 44, 'sameeraa', '07634532', 0.00, 3, '2025-04-24 06:02:46'),
(6, 45, 'sameeraa', '07634532', 0.00, 3, '2025-04-24 06:08:24'),
(7, 46, 'sameera', '1234567', 300.00, 3, '2025-04-24 06:10:45'),
(8, 47, 'sameera', '1234567', 8320.00, 3, '2025-04-24 06:25:17'),
(9, 48, 'sameera', '1234567', 6500.00, 3, '2025-04-24 06:42:27'),
(10, 64, 'sameera', '1234567', 4328.47, 3, '2025-04-24 20:48:35'),
(11, 68, 'sameera', '1234567', 22000.00, 3, '2025-04-27 17:49:11'),
(12, 72, 'kalhara', '123456789', 1294.90, 3, '2025-04-29 10:00:54'),
(13, 75, 'kalhara', '123456789', 10560.00, 3, '2025-04-30 04:43:24');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','supervisor','office_staff') NOT NULL,
  `name` varchar(100) NOT NULL,
  `contact` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `role`, `name`, `contact`, `created_at`) VALUES
(1, 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', 'System Admin', '0000000000', '2025-04-09 18:52:57'),
(2, 'oshitha', '$2y$10$Fh.fHMizcqJLv7bV6objwOdPdphccy6v/i2l/2KXDTeq.MY/rVmYi', 'supervisor', 'oshitha', '0766961189', '2025-04-09 18:55:23'),
(3, 'sameera', '$2y$10$RmZjJvYajbmN/S.sCZ64L.nmmrZTJWkLYHwfneF5RXHUAtiUYrle2', 'office_staff', 'sameera', '076678567', '2025-04-09 18:57:34');

-- --------------------------------------------------------

--
-- Table structure for table `wicket_door_measurements`
--

CREATE TABLE `wicket_door_measurements` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `point1` decimal(10,2) DEFAULT NULL,
  `point2` decimal(10,2) DEFAULT NULL,
  `point3` decimal(10,2) DEFAULT NULL,
  `point4` decimal(10,2) DEFAULT NULL,
  `point5` decimal(10,2) DEFAULT NULL,
  `thickness` decimal(4,2) DEFAULT NULL,
  `door_opening` enum('inside_left','inside_right','outside_left','outside_right') DEFAULT NULL,
  `handle` tinyint(1) DEFAULT NULL,
  `letter_box` tinyint(1) DEFAULT NULL,
  `coil_color` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wicket_door_measurements`
--

INSERT INTO `wicket_door_measurements` (`id`, `order_id`, `point1`, `point2`, `point3`, `point4`, `point5`, `thickness`, `door_opening`, `handle`, `letter_box`, `coil_color`) VALUES
(1, 1, 1.00, 5.00, 8.00, 9.00, 8.00, NULL, 'inside_right', 1, 1, NULL),
(2, 2, 1.00, 2.00, 3.00, 4.00, 5.00, NULL, 'inside_left', 1, 1, NULL),
(12, 12, 8.00, 8.00, 8.00, 9.00, 8.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(13, 13, 1.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(14, 14, 6.00, 7.00, 4.00, 3.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(15, 15, 1.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(16, 18, 9.00, 9.00, 9.00, 9.00, 9.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(17, 24, 5.00, 5.00, 5.00, 5.00, 5.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(18, 25, 5.00, 5.00, 5.00, 5.00, 5.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(26, 44, 4.00, 4.00, 4.00, 4.00, 4.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(27, 45, 7.00, 7.00, 7.00, 7.00, 7.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(28, 46, 5.00, 6.00, 7.00, 7.00, 7.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(29, 47, 1.00, 5.00, 5.00, 5.00, 5.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(30, 54, 2.00, 3.00, 4.00, 3.00, 3.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(31, 60, 2.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(32, 61, 3.00, 3.00, 3.00, 3.00, 3.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(33, 62, 3.00, 3.00, 3.00, 3.00, 3.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(34, 63, 2.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(35, 64, 1.00, 2.00, 3.00, 4.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(36, 65, 3.00, 3.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(37, 66, 4.00, 4.00, 4.00, 4.00, 4.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(38, 67, 3.00, 3.00, 3.00, 3.00, 3.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(39, 68, 2.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(40, 70, 3.00, 3.00, 3.00, 3.00, 3.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(41, 71, 2.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(42, 72, 7.00, 7.00, 7.00, 7.00, 7.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(43, 73, 20.00, 20.00, 20.00, 20.00, 20.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(44, 74, 20.00, 20.00, 20.00, 20.00, 20.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(45, 75, 20.00, 20.00, 20.00, 20.00, 20.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(46, 76, 14.00, 14.00, 15.00, 15.00, 15.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(47, 80, 12.00, 2.00, 2.00, 2.00, 2.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(48, 81, 1.00, 1.00, 1.00, 1.00, 1.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(49, 82, 20.00, 20.00, 20.00, 20.00, 20.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(50, 83, 16.00, 16.00, 16.00, 16.00, 16.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(51, 84, 50.00, 50.00, 50.00, 50.00, 50.00, 0.60, 'inside_left', 1, 1, 'coffee_brown'),
(52, 85, 5.00, 5.00, 5.00, 5.00, 5.00, 0.60, 'inside_left', 1, 1, 'coffee_brown');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `materials`
--
ALTER TABLE `materials`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prepared_by` (`prepared_by`),
  ADD KEY `checked_by` (`checked_by`),
  ADD KEY `approved_by` (`approved_by`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `admin_approved_by` (`admin_approved_by`);

--
-- Indexes for table `order_materials`
--
ALTER TABLE `order_materials`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `quotations`
--
ALTER TABLE `quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `material_id` (`material_id`);

--
-- Indexes for table `roller_door_measurements`
--
ALTER TABLE `roller_door_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `quotation_id` (`quotation_id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `wicket_door_measurements`
--
ALTER TABLE `wicket_door_measurements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `materials`
--
ALTER TABLE `materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=133;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=88;

--
-- AUTO_INCREMENT for table `order_materials`
--
ALTER TABLE `order_materials`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;

--
-- AUTO_INCREMENT for table `quotations`
--
ALTER TABLE `quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT for table `quotation_items`
--
ALTER TABLE `quotation_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=95;

--
-- AUTO_INCREMENT for table `roller_door_measurements`
--
ALTER TABLE `roller_door_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=69;

--
-- AUTO_INCREMENT for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `wicket_door_measurements`
--
ALTER TABLE `wicket_door_measurements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `contacts`
--
ALTER TABLE `contacts`
  ADD CONSTRAINT `contacts_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`prepared_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_2` FOREIGN KEY (`checked_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_3` FOREIGN KEY (`approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_4` FOREIGN KEY (`admin_approved_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `orders_ibfk_5` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `orders_ibfk_6` FOREIGN KEY (`admin_approved_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_materials`
--
ALTER TABLE `order_materials`
  ADD CONSTRAINT `order_materials_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_materials_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `quotations`
--
ALTER TABLE `quotations`
  ADD CONSTRAINT `quotations_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `quotations_ibfk_2` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `quotation_items`
--
ALTER TABLE `quotation_items`
  ADD CONSTRAINT `quotation_items_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `quotation_items_ibfk_2` FOREIGN KEY (`material_id`) REFERENCES `materials` (`id`);

--
-- Constraints for table `roller_door_measurements`
--
ALTER TABLE `roller_door_measurements`
  ADD CONSTRAINT `roller_door_measurements_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);

--
-- Constraints for table `supplier_quotations`
--
ALTER TABLE `supplier_quotations`
  ADD CONSTRAINT `supplier_quotations_ibfk_1` FOREIGN KEY (`quotation_id`) REFERENCES `quotations` (`id`),
  ADD CONSTRAINT `supplier_quotations_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `wicket_door_measurements`
--
ALTER TABLE `wicket_door_measurements`
  ADD CONSTRAINT `wicket_door_measurements_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
