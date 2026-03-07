-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 11:53 PM
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
-- Database: `aircon`
--

-- --------------------------------------------------------

--
-- Table structure for table `aircon_units`
--

CREATE TABLE `aircon_units` (
  `id` int(11) NOT NULL,
  `unit_name` varchar(255) NOT NULL,
  `installation_date` date NOT NULL,
  `location` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `completed_tasks`
--

CREATE TABLE `completed_tasks` (
  `id` int(30) NOT NULL,
  `service_type` text NOT NULL,
  `start_date` datetime NOT NULL,
  `service_type_2` text NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `customer_name` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `customer_cp` int(50) NOT NULL,
  `staff_name` text NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `client_name` text NOT NULL,
  `status` text NOT NULL,
  `date_created` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `delete_list`
--

CREATE TABLE `delete_list` (
  `id` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `po` varchar(50) NOT NULL,
  `indoor` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `indoor_serial` varchar(255) NOT NULL,
  `outdoor` varchar(255) NOT NULL,
  `outdoor_serial` varchar(255) NOT NULL,
  `brand` text NOT NULL,
  `type` text NOT NULL,
  `series` varchar(50) NOT NULL,
  `hp` varchar(30) NOT NULL,
  `quantity` text NOT NULL,
  `price` int(255) NOT NULL DEFAULT 0,
  `unit` text NOT NULL,
  `total` int(255) NOT NULL,
  `remarks` text DEFAULT NULL,
  `from_order` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=PO ,2 = BO',
  `form_id` int(30) NOT NULL,
  `supplier_id` int(30) NOT NULL,
  `supplier` varchar(255) NOT NULL,
  `po_number` varchar(255) NOT NULL,
  `discount_perc` float NOT NULL DEFAULT 0,
  `discount` float NOT NULL DEFAULT 0,
  `tax_perc` float NOT NULL DEFAULT 0,
  `tax` float NOT NULL DEFAULT 0,
  `stock_ids` text DEFAULT NULL,
  `delivery_date` date NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `total_items` int(11) NOT NULL DEFAULT 0,
  `status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Pending, 1=Received',
  `sub_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `receiving_price` decimal(10,2) DEFAULT NULL,
  `invoice` varchar(55) NOT NULL,
  `dr` varchar(55) NOT NULL,
  `invoice_number` varchar(50) NOT NULL,
  `transaction_number` varchar(50) DEFAULT NULL,
  `client_name` text NOT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `client` text DEFAULT NULL,
  `row_perc` decimal(50,0) DEFAULT 0,
  `sales_code` varchar(50) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `sale_date` date NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `discount-amount` int(50) NOT NULL,
  ` overall_discount_perc` int(50) NOT NULL,
  `discount_per_item` decimal(10,2) DEFAULT 0.00,
  `per_item_discount_input` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) DEFAULT 0.00,
  `record_type` varchar(20) DEFAULT 'purchase_order'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `item_list`
--

CREATE TABLE `item_list` (
  `id` int(30) NOT NULL,
  `brand` text NOT NULL,
  `type` text NOT NULL,
  `description` text NOT NULL,
  `hp` varchar(30) NOT NULL,
  `indoor_outdoor` varchar(50) DEFAULT NULL,
  `price` float NOT NULL DEFAULT 0,
  `payment_terms` varchar(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `item_list`
--

INSERT INTO `item_list` (`id`, `brand`, `type`, `description`, `hp`, `indoor_outdoor`, `price`, `payment_terms`, `date_created`, `date_updated`) VALUES
(26, 'AUX', 'Floor', 'AUX Floor 1.5 HP', '1.5 HP', NULL, 15000, '', '2025-08-29 09:31:29', '2025-08-29 09:31:29'),
(27, 'TCL', 'Ceiling', 'TCL Ceiling 2 HP', '2 HP', NULL, 35000, '', '2025-08-29 09:32:53', '2025-08-29 09:32:53'),
(28, 'MATRIX', 'Floor', 'MATRIX Floor 2 HP', '2 HP', NULL, 15400, '', '2025-08-29 10:21:47', '2025-08-29 10:21:47'),
(29, 'TCL', 'Split', 'TCL Split 2 HP', '2 HP', NULL, 12500, '', '2025-08-29 10:22:13', '2025-08-29 10:22:13'),
(30, 'TCL', 'Floor', 'TCL Floor 2 HP', '2 HP', NULL, 165000, '', '2025-08-29 10:30:40', '2025-08-29 10:30:40'),
(31, 'MIDEA', 'Window', 'MIDEA Window 2.5 HP', '2.5 HP', NULL, 13200, '', '2025-08-29 10:35:38', '2025-08-29 10:35:38'),
(32, 'AUX', 'Ceiling', 'AUX Ceiling 1.5 HP', '1.5 HP', NULL, 12500, '', '2025-08-29 10:59:59', '2025-08-29 10:59:59'),
(33, 'MATRIX', 'Window', 'MATRIX Window 2.5 HP', '2.5 HP', NULL, 16500, '', '2025-08-29 11:00:18', '2025-08-29 11:00:18'),
(34, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 2.5 HP', '2.5 HP', NULL, 140000, '', '2025-08-29 11:05:45', '2025-08-29 11:05:45'),
(35, 'MATRIX', 'Split', 'MATRIX Split 1.5 HP', '1.5 HP', NULL, 15000, '', '2025-08-29 11:15:18', '2025-08-29 11:15:18'),
(36, 'GREE', 'Floor', 'GREE Floor 2.5 HP', '2.5 HP', NULL, 16500, '', '2025-08-29 11:38:45', '2025-08-29 11:38:45'),
(37, 'AUX', 'Ceiling', 'AUX Ceiling 2 HP', '2 HP', NULL, 18000, '', '2025-08-29 11:40:13', '2025-08-29 11:40:13'),
(38, 'LG', 'Ceiling', 'LG Ceiling 2.5 HP', '2.5 HP', NULL, 15600, '', '2025-08-29 11:49:26', '2025-08-29 11:49:26'),
(39, 'MATRIX', 'Floor', 'MATRIX Floor 2.5 HP', '2.5 HP', NULL, 13600, '', '2025-08-29 13:06:33', '2025-08-29 13:06:33'),
(40, 'DAIKIN', 'Floor', 'DAIKIN Floor 2.5 HP', '2.5 HP', NULL, 16500, '', '2025-08-29 13:43:12', '2025-08-29 13:43:12'),
(41, 'GREE', 'Cassette', 'GREE Cassette 2 HP', '2 HP', NULL, 135600, '', '2025-08-29 13:50:09', '2025-08-29 13:50:09'),
(42, 'DAIKIN', 'Window', 'DAIKIN Window 2 HP', '2 HP', NULL, 15000, '', '2025-08-30 13:41:28', '2025-08-30 13:41:28'),
(43, 'MIDEA', 'Ceiling', 'MIDEA Ceiling 2.5 HP', '2.5 HP', NULL, 16500, '', '2025-09-08 10:56:12', '2025-09-08 10:56:12'),
(44, 'MIDEA', 'Floor', 'MIDEA Floor 2 HP', '2 HP', NULL, 18000, '', '2025-09-08 16:41:36', '2025-09-08 16:41:36'),
(45, 'LG', 'Ceiling', 'LG Ceiling 1.5 HP', '1.5 HP', NULL, 18500, '', '2025-09-11 10:24:06', '2025-09-11 10:24:06'),
(46, 'GREE', 'Window', 'GREE Window 2 HP', '2 HP', NULL, 18000, '', '2025-09-16 10:04:21', '2025-09-16 10:04:21'),
(47, 'MATRIX', 'Ceiling', 'MATRIX Ceiling 2 HP', '2 HP', NULL, 15000, '', '2025-09-17 15:34:09', '2025-09-17 15:34:09'),
(48, 'AUX', 'Floor', 'AUX Floor 2 HP', '2 HP', NULL, 16000, '', '2025-09-18 15:10:43', '2025-09-18 15:10:43'),
(49, 'AUX', 'Cassette', 'AUX Cassette 2 HP', '2 HP', NULL, 15000, '', '2025-09-20 09:44:23', '2025-09-20 09:44:23'),
(50, 'AUX', 'Cassette', 'AUX Cassette SETS', 'SETS', NULL, 15000, '', '2025-09-20 13:17:17', '2025-09-20 13:17:17'),
(51, 'DAIKIN', 'Cassette', 'DAIKIN Cassette SETS', 'SETS', NULL, 15000, '', '2025-09-20 13:20:04', '2025-09-20 13:20:04'),
(52, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling SETS', 'SETS', NULL, 18000, '', '2025-09-20 15:07:34', '2025-09-20 15:07:34'),
(53, 'AUX', 'Ceiling', 'AUX Ceiling SETS', 'SETS', NULL, 18000, '', '2025-09-20 15:10:01', '2025-09-20 15:10:01'),
(54, 'AUX', 'Floor', 'AUX Floor SETS', 'SETS', NULL, 19000, '', '2025-09-20 15:17:12', '2025-09-20 15:17:12'),
(55, 'DAIKIN', 'Floor', 'DAIKIN Floor SETS', 'SETS', NULL, 158000, '', '2025-09-20 15:19:54', '2025-09-20 15:19:54'),
(56, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 1', '1', NULL, 18000, '', '2025-09-20 16:07:37', '2025-09-20 16:07:37'),
(57, 'DAIKIN', 'Floor', 'DAIKIN Floor 1.5', '1.5', NULL, 18000, '', '2025-09-20 16:13:51', '2025-09-20 16:13:51'),
(58, 'GREE', 'Ceiling', 'GREE Ceiling 1', '1', NULL, 15000, '', '2025-09-20 16:16:49', '2025-09-20 16:16:49'),
(59, 'AUX', 'Ceiling', 'AUX Ceiling 1', '1', NULL, 18500, '', '2025-09-20 16:18:14', '2025-09-20 16:18:14'),
(60, 'AUX', 'Ceiling', 'AUX Ceiling 1.5', '1.5', NULL, 18900, '', '2025-09-20 16:22:37', '2025-09-20 16:22:37'),
(61, 'DAIKIN', 'Floor', 'DAIKIN Floor 1', '1', NULL, 18900, '', '2025-09-20 16:24:02', '2025-09-20 16:24:02'),
(62, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 1.5', '1.5', NULL, 19700, '', '2025-09-22 08:03:44', '2025-09-22 08:03:44'),
(63, 'GREE', 'Split', 'GREE Split 1.5', '1.5', NULL, 21000, '', '2025-09-22 08:26:24', '2025-09-22 08:26:24'),
(64, 'LG', 'Ceiling', 'LG Ceiling 1.5', '1.5', NULL, 18700, '', '2025-09-22 08:27:19', '2025-09-22 08:27:19'),
(65, 'GREE', 'Split', 'GREE Split 1', '1', NULL, 18700, '', '2025-09-22 08:34:01', '2025-09-22 08:34:01'),
(66, 'GREE', 'Window', 'GREE Window 2', '2', NULL, 17000, '', '2025-09-22 08:45:18', '2025-09-22 08:45:18'),
(67, 'MATRIX', 'Floor', 'MATRIX Floor 3', '3', NULL, 19000, '', '2025-09-22 08:45:46', '2025-09-22 08:45:46'),
(68, 'DAIKIN', 'Floor', 'DAIKIN Floor 3', '3', NULL, 1230200, '', '2025-09-22 08:46:03', '2025-09-22 08:46:03'),
(69, 'LG', 'Split', 'LG Split 2', '2', NULL, 19700, '', '2025-09-22 08:49:50', '2025-09-22 08:49:50'),
(70, 'GREE', 'Ceiling', 'GREE Ceiling 2', '2', NULL, 89900, '', '2025-09-22 08:50:10', '2025-09-22 08:50:10'),
(71, 'DAIKIN', 'Split', 'DAIKIN Split 2', '2', NULL, 174000, '', '2025-09-22 08:54:08', '2025-09-22 08:54:08'),
(72, 'MATRIX', 'Floor', 'MATRIX Floor 2', '2', NULL, 14500, '', '2025-09-22 08:54:31', '2025-09-22 08:54:31'),
(73, 'MATRIX', 'Window', 'MATRIX Window 2', '2', NULL, 19000, '', '2025-09-22 08:56:53', '2025-09-22 08:56:53'),
(74, 'MATRIX', 'Split', 'MATRIX Split 2', '2', NULL, 19800, '', '2025-09-22 08:57:38', '2025-09-22 08:57:38'),
(75, 'AUX', 'Split', 'AUX Split 2', '2', NULL, 34000, '', '2025-09-22 09:05:55', '2025-09-22 09:05:55'),
(76, 'LG', 'Window', 'LG Window 2.5', '2.5', NULL, 25000, '', '2025-09-22 09:06:18', '2025-09-22 09:06:18'),
(77, 'LG', 'Floor', 'LG Floor 2', '2', NULL, 1234440, '', '2025-09-22 09:10:20', '2025-09-22 09:10:20'),
(78, 'MIDEA', 'Ceiling', 'MIDEA Ceiling 1', '1', NULL, 198700, '', '2025-09-22 09:10:43', '2025-09-22 09:10:43'),
(79, 'DAIKIN', 'Floor', 'DAIKIN Floor 2', '2', NULL, 89000, '', '2025-09-22 09:20:14', '2025-09-22 09:20:14'),
(80, 'TCL', 'Split', 'TCL Split 1', '1', NULL, 123000, '', '2025-09-22 09:20:34', '2025-09-22 09:20:34'),
(81, 'MIDEA', 'Window', 'MIDEA Window 2', '2', NULL, 145666, '', '2025-09-22 09:20:52', '2025-09-22 09:20:52'),
(82, 'TCL', 'Floor', 'TCL Floor 2', '2', NULL, 1235560, '', '2025-09-22 09:27:09', '2025-09-22 09:27:09'),
(83, 'LG', 'Split', 'LG Split 2.5', '2.5', NULL, 130000, '', '2025-09-22 09:27:37', '2025-09-22 09:27:37'),
(84, 'TCL', 'Ceiling', 'TCL Ceiling 2.5', '2.5', NULL, 189777, '', '2025-09-22 09:27:59', '2025-09-22 09:27:59'),
(85, 'LG', 'Floor', 'LG Floor 1.5', '1.5', NULL, 189000, '', '2025-09-22 09:34:18', '2025-09-22 09:34:18'),
(86, 'AUX', 'Cassette', 'AUX Cassette 2', '2', NULL, 18000, '', '2025-09-22 09:39:54', '2025-09-22 09:39:54'),
(87, 'GREE', 'Split', 'GREE Split 2', '2', NULL, 14800, '', '2025-09-22 09:41:08', '2025-09-22 09:41:08'),
(88, 'GREE', 'Floor', 'GREE Floor 2.5', '2.5', NULL, 20000, '', '2025-10-03 16:16:52', '2025-10-03 16:16:52'),
(89, 'LG', 'Split', 'LG Split 1', '1', NULL, 25000, '', '2025-10-03 16:17:40', '2025-10-03 16:17:40'),
(90, 'MATRIX', 'Window', 'MATRIX Window 3', '3', NULL, 26000, '', '2025-10-03 16:18:42', '2025-10-03 16:18:42'),
(91, 'AUX', 'Ceiling', 'AUX Ceiling 3.5', '3.5', NULL, 29000, '', '2025-10-03 16:19:34', '2025-10-03 16:19:34'),
(92, 'AUX', 'Cassette', 'AUX Cassette 2.5', '2.5', NULL, 18900, '', '2025-10-04 08:37:39', '2025-10-04 08:37:39'),
(93, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 3', '3', NULL, 19000, '', '2025-10-04 08:38:14', '2025-10-04 08:38:14'),
(94, 'GREE', 'Floor', 'GREE Floor 3.5', '3.5', NULL, 17600, '', '2025-10-04 08:38:41', '2025-10-04 08:38:41'),
(95, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 3.5', '3.5', NULL, 15000, '', '2025-10-04 08:39:39', '2025-10-04 08:39:39'),
(96, 'LG', 'Floor', 'LG Floor 3.5', '3.5', NULL, 21500, '', '2025-10-04 08:40:15', '2025-10-04 08:40:15'),
(97, 'MATRIX', 'Split', 'MATRIX Split 4', '4', NULL, 18500, '', '2025-10-04 08:40:48', '2025-10-04 08:40:48'),
(98, 'MATRIX', 'Window', 'MATRIX Window 5', '5', NULL, 24000, '', '2025-10-04 08:41:23', '2025-10-04 08:41:23'),
(99, 'GREE', 'Floor', 'GREE Floor 23000', '23000', NULL, 25600, '', '2025-10-04 10:40:40', '2025-10-04 10:40:40'),
(100, 'GREE', 'Ceiling', 'GREE Ceiling 4', '4', NULL, 26500, '', '2025-10-04 10:41:46', '2025-10-04 10:41:46'),
(101, 'GREE', 'Floor', 'GREE Floor 5', '5', NULL, 198700, '', '2025-10-04 10:42:34', '2025-10-04 10:42:34'),
(102, 'MATRIX', 'Split', 'MATRIX Split 3.5', '3.5', NULL, 26000, '', '2025-10-04 10:43:39', '2025-10-04 10:43:39'),
(103, 'LG', 'Split', 'LG Split 6', '6', NULL, 26000, '', '2025-10-04 10:44:16', '2025-10-04 10:44:16'),
(104, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 2.5', '2.5', NULL, 19800, '', '2025-10-04 10:53:04', '2025-10-04 10:53:04'),
(105, 'GREE', 'Floor', 'GREE Floor 1.5', '1.5', NULL, 28000, '', '2025-10-04 10:53:39', '2025-10-04 10:53:39'),
(106, 'MATRIX', 'Split', 'MATRIX Split 5', '5', NULL, 16900, '', '2025-10-04 10:55:00', '2025-10-04 10:55:00'),
(107, 'MIDEA', 'Split', 'MIDEA Split 8', '8', NULL, 254700, '', '2025-10-04 10:55:35', '2025-10-04 10:55:35'),
(108, 'TCL', 'Window', 'TCL Window 4.5', '4.5', NULL, 18700, '', '2025-10-04 10:56:08', '2025-10-04 10:56:08'),
(109, 'TCL', 'Split', 'TCL Split 5', '5', NULL, 165700, '', '2025-10-04 10:56:55', '2025-10-04 10:56:55'),
(110, 'AUX', 'Floor', 'AUX Floor 3.5', '3.5', NULL, 196800, '', '2025-10-04 10:57:29', '2025-10-04 10:57:29'),
(111, 'GREE', 'Split', 'GREE Split 2.5', '2.5', NULL, 168000, '', '2025-10-04 11:43:23', '2025-10-04 11:43:23'),
(112, 'LG', 'Split', 'LG Split 3', '3', NULL, 1360000, '', '2025-10-04 11:43:50', '2025-10-04 11:43:50'),
(113, 'DAIKIN', 'Ceiling', 'DAIKIN Ceiling 2', '2', NULL, 18500, '', '2025-10-04 12:46:47', '2025-10-04 12:46:47'),
(114, 'LG', 'Floor', 'LG Floor 3', '3', NULL, 19700, '', '2025-10-04 12:47:41', '2025-10-04 12:47:41'),
(115, 'LG', 'Cassette', 'LG Cassette 3.5', '3.5', NULL, 19877, '', '2025-10-04 12:48:20', '2025-10-04 12:48:20'),
(116, 'LG', 'Ceiling', 'LG Ceiling 5', '5', NULL, 198000, '', '2025-10-04 12:48:51', '2025-10-04 12:48:51'),
(117, 'MATRIX', 'Split', 'MATRIX Split 3', '3', NULL, 16800, '', '2025-10-04 12:49:19', '2025-10-04 12:49:19'),
(118, 'DAIKIN', 'Cassette', 'DAIKIN Cassette 2', '2', NULL, 780000, '', '2025-10-04 13:10:52', '2025-10-04 13:10:52'),
(119, 'GREE', 'Floor', 'GREE Floor 3', '3', NULL, 20500, '', '2025-10-04 14:39:07', '2025-10-04 14:39:07'),
(120, 'AUX', 'Cassette', 'AUX Cassette 1.5', '1.5', NULL, 15000, '', '2025-10-04 15:27:44', '2025-10-04 15:27:44'),
(121, 'AUX', 'Split', 'AUX Split 4', '4', NULL, 19500, '', '2025-10-04 15:30:56', '2025-10-04 15:30:56'),
(122, 'AUX', 'Ceiling', 'AUX Ceiling 5', '5', NULL, 20500, '', '2025-10-04 15:31:35', '2025-10-04 15:31:35'),
(123, 'AUX', 'Ceiling', 'AUX Ceiling 2', '2', NULL, 16800, '', '2025-10-04 15:35:21', '2025-10-04 15:35:21'),
(124, 'AUX', 'Floor', 'AUX Floor 0.8', '0.8', NULL, 16000, '', '2025-10-04 15:36:38', '2025-10-04 15:36:38'),
(125, 'AUX', 'Split', 'AUX Split 5', '5', NULL, 198000, '', '2025-10-04 15:37:14', '2025-10-04 15:37:14'),
(126, 'AUX', 'Floor', 'AUX Floor 2.5', '2.5', NULL, 26000, '', '2025-10-06 09:19:09', '2025-10-06 09:19:09'),
(127, 'LG', 'Split', 'LG Split 5', '5', NULL, 28000, '', '2025-10-06 09:20:46', '2025-10-06 09:20:46'),
(128, 'AUX', 'Ceiling', 'AUX Ceiling 2.5', '2.5', NULL, 19800, '', '2025-10-06 10:13:56', '2025-10-06 10:13:56'),
(129, 'DAIKIN', 'Floor', 'DAIKIN Floor 5', '5', NULL, 26000, '', '2025-10-06 10:16:32', '2025-10-06 10:16:32'),
(130, 'DAIKIN', 'Split', 'DAIKIN Split 6', '6', NULL, 18900, '', '2025-10-06 10:18:33', '2025-10-06 10:18:33'),
(131, 'DAIKIN', 'Window', 'DAIKIN Window 7', '7', NULL, 25000, '', '2025-10-06 10:18:53', '2025-10-06 10:18:53'),
(132, 'AUX', 'Split', 'AUX Split 1.5', '1.5', NULL, 26000, '', '2025-10-06 10:20:49', '2025-10-06 10:20:49'),
(133, 'DAIKIN', 'Cassette', 'DAIKIN Cassette 5', '5', NULL, 12500, '', '2025-10-06 14:26:46', '2025-10-06 14:26:46'),
(134, 'AUX', 'Cassette', 'AUX Cassette 1', '1', NULL, 158000, '', '2025-10-06 15:27:26', '2025-10-06 15:27:26'),
(135, 'AUX', 'Floor', 'AUX Floor 2', '2', NULL, 7899, '', '2025-10-06 15:28:09', '2025-10-06 15:28:09'),
(136, 'GREE', 'Ceiling', 'GREE Ceiling 3', '3', NULL, 290000, '', '2025-10-08 15:29:30', '2025-10-08 15:29:30'),
(137, 'GREE', 'Floor', 'GREE Floor 2', '2', NULL, 28000, '', '2025-10-08 17:17:11', '2025-10-08 17:17:11'),
(138, 'AUX', 'Ceiling', 'AUX Ceiling 3', '3', NULL, 30000, '', '2025-10-13 09:42:17', '2025-10-13 09:42:17'),
(139, 'AUX', 'Floor', 'AUX Floor 1.5', '1.5', NULL, 29000, '', '2025-10-13 09:43:24', '2025-10-13 09:43:24'),
(140, 'AUX', 'Cassette', 'AUX Cassette 3', '3', NULL, 20500, '', '2025-10-13 11:40:11', '2025-10-13 11:40:11'),
(141, 'AUX', 'Floor', 'AUX Floor 3', '3', NULL, 19000, '', '2025-10-13 13:30:13', '2025-10-13 13:30:13'),
(142, 'AUX', 'Split', 'AUX Split 1', '1', NULL, 125600, '', '2025-10-13 13:44:51', '2025-10-13 13:44:51'),
(143, 'AUX', 'Split', 'AUX Split 3', '3', NULL, 189000, '', '2025-10-13 13:45:26', '2025-10-13 13:45:26'),
(144, 'LG', 'Split', 'LG Split 1.5 HP', '1.5 HP', NULL, 198000, '', '2025-10-13 13:45:41', '2025-10-13 13:45:41'),
(145, 'AUX', 'Floor', 'AUX Floor 1', '1', NULL, 14000, '', '2025-10-13 14:53:40', '2025-10-13 14:53:40'),
(146, 'LG', 'Floor', 'LG Floor 4', '4', NULL, 180000, '', '2025-10-13 14:53:59', '2025-10-13 14:53:59'),
(147, 'AUX', 'Ceiling', 'AUX Ceiling 4', '4', NULL, 18700, '', '2025-10-13 15:56:07', '2025-10-13 15:56:07'),
(148, 'GREE', 'Ceiling', 'GREE Ceiling rwerew', 'rwerew', NULL, 40000, '', '2025-10-20 09:06:54', '2025-10-20 09:06:54'),
(149, 'DAIKIN', 'Split', 'DAIKIN Split 3', '3', NULL, 20000, '', '2025-10-21 15:36:52', '2025-10-21 15:36:52'),
(150, 'DAIKIN', 'Window', 'DAIKIN Window 1', '1', NULL, 40000, '', '2025-10-22 08:01:14', '2025-10-22 08:01:14'),
(151, 'GREE', 'Split', 'GREE Split 3', '3', NULL, 20000, '', '2025-10-22 10:50:54', '2025-10-22 10:50:54'),
(152, 'DAIKIN', 'Window', 'DAIKIN Window 3', '3', NULL, 20000, '', '2025-10-22 10:51:13', '2025-10-22 10:51:13'),
(153, 'DAIKIN', 'Split', 'DAIKIN Split 1', '1', NULL, 40000, '', '2025-10-22 16:45:41', '2025-10-22 16:45:41'),
(154, 'DAIKIN', 'Cassette', 'DAIKIN Cassette 1', '1', NULL, 40000, '', '2025-10-23 08:21:22', '2025-10-23 08:21:22'),
(155, 'LG', 'Window', 'LG Window 1', '1', NULL, 40000, '', '2025-10-23 08:23:05', '2025-10-23 08:23:05'),
(156, 'LG', 'Window', 'LG Window 3', '3', NULL, 40000, '', '2025-10-23 13:27:48', '2025-10-23 13:27:48'),
(157, 'LG', 'Window', 'LG Window 2', '2', NULL, 40000, '', '2025-10-23 14:34:23', '2025-10-23 14:34:23'),
(158, 'GREE', 'Floor', 'GREE Floor 1', '1', NULL, 40000, '', '2025-10-23 15:42:38', '2025-10-23 15:42:38'),
(159, 'GREE', 'Cassette', 'GREE Cassette 2', '2', NULL, 40000, '', '2025-10-23 15:42:52', '2025-10-23 15:42:52'),
(160, 'DAIKIN', 'Window', 'DAIKIN Window 2', '2', NULL, 40000, '', '2025-10-24 10:39:43', '2025-10-24 10:39:43'),
(161, 'GREE', 'Window', 'GREE Window 1', '1', NULL, 40000, '', '2025-10-28 11:00:26', '2025-10-28 11:00:26'),
(162, 'DAIKIN', 'Split', 'DAIKIN Split 0.8', '0.8', NULL, 32500, '', '2025-11-07 16:33:53', '2025-11-07 16:33:53'),
(163, 'DAIKIN', 'Split', 'DAIKIN Split 1.0', '1.0', NULL, 36700, '', '2025-11-11 11:59:01', '2025-11-11 11:59:01'),
(164, 'N/A', 'N/A', 'N/A N/A N/A', 'N/A', NULL, 0, '', '2025-11-11 13:44:28', '2025-11-11 13:44:28'),
(165, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 0.8', '0.8', NULL, 32500, '', '2025-11-11 14:11:53', '2025-11-11 14:11:53'),
(166, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted N/A', 'N/A', NULL, 32500, '', '2025-11-11 14:15:39', '2025-11-11 14:15:39'),
(167, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 1.0', '1.0', NULL, 36700, '', '2025-11-11 14:20:42', '2025-11-11 14:20:42'),
(168, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 1.5', '1.5', NULL, 41200, '', '2025-11-11 14:44:53', '2025-11-11 14:44:53'),
(169, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 2.0', '2.0', NULL, 53800, '', '2025-11-11 15:06:34', '2025-11-11 15:06:34'),
(170, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 2.5', '2.5', NULL, 62300, '', '2025-11-11 15:26:00', '2025-11-11 15:26:00'),
(171, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 3.0', '3.0', NULL, 125800, '', '2025-11-11 15:34:50', '2025-11-11 15:34:50'),
(172, 'DAIKIN', 'Wall Mounted', 'DAIKIN Wall Mounted 4.0', '4.0', NULL, 174200, '', '2025-11-11 15:45:59', '2025-11-11 15:45:59'),
(173, 'DAIKIN', 'Floor Mounted', 'DAIKIN Floor Mounted 4.0', '4.0', NULL, 147200, '', '2025-11-11 15:48:28', '2025-11-11 15:48:28'),
(174, 'GREE', 'Wall Mounted', 'GREE Wall Mounted 1.0', '1.0', NULL, 32999, '', '2025-11-12 09:39:19', '2025-11-12 09:39:19'),
(175, 'GREE', 'Wall Mounted', 'GREE Wall Mounted 1.5', '1.5', NULL, 36999, '', '2025-11-12 09:41:36', '2025-11-12 09:41:36'),
(176, 'GREE', 'Wall Mounted', 'GREE Wall Mounted 2.0', '2.0', NULL, 45999, '', '2025-11-12 09:50:21', '2025-11-12 09:50:21'),
(177, 'AUX', 'Wall Mounted', 'AUX Wall Mounted 1.0', '1.0', NULL, 31399, '', '2025-11-12 13:02:52', '2025-11-12 13:02:52'),
(178, 'AUX', 'Wall Mounted', 'AUX Wall Mounted 1.5', '1.5', NULL, 35899, '', '2025-11-12 13:04:00', '2025-11-12 13:04:00'),
(179, 'AUX', 'Wall Mounted', 'AUX Wall Mounted 2.0', '2.0', NULL, 45899, '', '2025-11-12 13:07:53', '2025-11-12 13:07:53'),
(180, 'AUX', 'Wall Mounted', 'AUX Wall Mounted 2.5', '2.5', NULL, 55899, '', '2025-11-12 13:09:46', '2025-11-12 13:09:46'),
(181, 'AUFIT', 'Wall Mounted', 'AUFIT Wall Mounted 1.0', '1.0', NULL, 29699, '', '2025-11-12 13:23:47', '2025-11-12 13:23:47'),
(182, 'HK', 'Window', 'HK Window 1.0', '1.0', NULL, 26499, '', '2025-11-13 09:04:21', '2025-11-13 09:04:21'),
(183, 'HK', 'Window', 'HK Window 1.5', '1.5', NULL, 32999, '', '2025-11-13 09:07:22', '2025-11-13 09:07:22'),
(184, 'N/A', 'Window', 'N/A Window 1.5', '1.5', NULL, 32999, '', '2025-11-13 09:11:22', '2025-11-13 09:11:22'),
(185, 'HK', 'Window', 'HK Window 2.0', '2.0', NULL, 42699, '', '2025-11-13 16:24:39', '2025-11-13 16:24:39'),
(186, 'HK', 'Window', 'HK Window 2.5', '2.5', NULL, 47699, '', '2025-11-13 16:26:41', '2025-11-13 16:26:41'),
(187, 'N/A', 'Window', 'N/A Window N/A', 'N/A', NULL, 32000, '', '2025-11-13 16:29:15', '2025-11-13 16:29:15'),
(188, 'MATRIX', 'Window', 'MATRIX Window 1.0', '1.0', NULL, 30200, '', '2025-11-13 17:07:43', '2025-11-13 17:07:43'),
(189, 'MATRIX', 'Wall Mounted', 'MATRIX Wall Mounted 1.5', '1.5', NULL, 29000, '', '2025-11-14 08:37:33', '2025-11-14 08:37:33'),
(190, 'MATRIX', 'Wall Mounted', 'MATRIX Wall Mounted 2.5', '2.5', NULL, 49800, '', '2025-11-14 08:38:31', '2025-11-14 08:38:31'),
(191, 'MATRIX', 'Window', 'MATRIX Window 1.5', '1.5', NULL, 22200, '', '2025-11-14 08:55:25', '2025-11-14 08:55:25'),
(192, 'MATRIX', 'N/A', 'MATRIX N/A 1.5', '1.5', NULL, 22200, '', '2025-11-14 08:56:34', '2025-11-14 08:56:34'),
(193, 'AUFIT', 'Cassette', 'AUFIT Cassette 5', '5', NULL, 13500, '', '2025-11-17 14:17:40', '2025-11-17 14:17:40'),
(194, 'AUFIT', 'Ceiling', 'AUFIT Ceiling 5', '5', NULL, 18000, '', '2025-11-17 16:46:25', '2025-11-17 16:46:25'),
(195, 'DAIKIN', 'Floor', 'DAIKIN Floor N/A', 'N/A', NULL, 187000, '', '2025-11-17 16:53:25', '2025-11-17 16:53:25'),
(196, 'AUX', 'N/A', 'AUX N/A N/A', 'N/A', NULL, 0, '', '2025-11-18 12:02:31', '2025-11-18 12:02:31'),
(197, 'LG', 'N/A', 'LG N/A 3', '3', NULL, 20000, '', '2025-11-18 14:43:13', '2025-11-18 14:43:13'),
(198, 'LG', 'N/A', 'LG N/A 2', '2', NULL, 15000, '', '2025-11-19 13:26:35', '2025-11-19 13:26:35'),
(199, 'LG', 'N/A', 'LG N/A N/A', 'N/A', NULL, 0, '', '2025-11-19 14:07:19', '2025-11-19 14:07:19'),
(200, 'AUFIT', 'N/A', 'AUFIT N/A 2', '2', NULL, 20000, '', '2025-11-20 09:32:40', '2025-11-20 09:32:40'),
(201, 'AUFIT', 'N/A', 'AUFIT N/A N/A', 'N/A', NULL, 0, '', '2025-11-20 10:01:10', '2025-11-20 10:01:10'),
(202, 'AUX', 'N/A', 'AUX N/A 2', '2', NULL, 1000, '', '2025-11-20 10:22:07', '2025-11-20 10:22:07'),
(203, 'MATRIX', 'N/A', 'MATRIX N/A N/A', 'N/A', NULL, 25000, '', '2025-11-20 10:36:38', '2025-11-20 10:36:38'),
(204, 'MIDEA', 'N/A', 'MIDEA N/A 2', '2', NULL, 170000, '', '2025-11-20 10:38:36', '2025-11-20 10:38:36'),
(205, 'MATRIX', 'N/A', 'MATRIX N/A 2', '2', NULL, 180000, '', '2025-11-20 10:43:18', '2025-11-20 10:43:18'),
(206, 'DAIKIN', 'N/A', 'DAIKIN N/A N/A', 'N/A', NULL, 178000, '', '2025-11-20 10:47:43', '2025-11-20 10:47:43'),
(207, 'TCL', 'N/A', 'TCL N/A 2', '2', NULL, 15000, '', '2025-11-20 14:10:17', '2025-11-20 14:10:17'),
(208, 'LG', 'N/A', 'LG N/A 1.5', '1.5', NULL, 10000, '', '2025-11-21 09:09:04', '2025-11-21 09:09:04'),
(209, 'HK', 'N/A', 'HK N/A N/A', 'N/A', NULL, 0, '', '2025-11-21 10:11:24', '2025-11-21 10:11:24'),
(210, 'MATRIX', 'WINDOW', 'MATRIX WINDOW 2.0', '2.0', NULL, 30100, '', '2025-11-24 12:57:47', '2025-11-24 12:57:47'),
(211, 'MATRIX', 'WINDOW', 'MATRIX WINDOW 2.5', '2.5', NULL, 35700, '', '2025-11-24 12:59:26', '2025-11-24 12:59:26');

-- --------------------------------------------------------

--
-- Table structure for table `po_items`
--

CREATE TABLE `po_items` (
  `id` int(30) NOT NULL,
  `po_id` int(30) NOT NULL,
  `serial_no` varchar(50) NOT NULL DEFAULT '',
  `indoor_outdoor` text NOT NULL DEFAULT '',
  `item_id` int(30) NOT NULL,
  `hp` varchar(30) NOT NULL,
  `quantity` int(30) NOT NULL,
  `payment_terms` varchar(50) NOT NULL,
  `price` float NOT NULL DEFAULT 0,
  `unit` varchar(50) NOT NULL,
  `total` float NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_items`
--

CREATE TABLE `purchase_order_items` (
  `id` int(11) NOT NULL,
  `po_id` int(11) NOT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `total` decimal(10,2) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `indoor_outdoor` varchar(255) DEFAULT NULL,
  `unit` varchar(255) DEFAULT NULL,
  `type` varchar(255) DEFAULT NULL,
  `brand` varchar(255) DEFAULT NULL,
  `hp` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `purchase_order_list`
--

CREATE TABLE `purchase_order_list` (
  `id` int(30) NOT NULL,
  `po_id` int(30) NOT NULL,
  `po_code` int(30) NOT NULL,
  `delivery_date` date NOT NULL,
  `po` varchar(50) NOT NULL,
  `dr` varchar(50) NOT NULL,
  `invoice` varchar(50) NOT NULL,
  `indoor` varchar(255) DEFAULT NULL,
  `indoor_serial` varchar(50) NOT NULL,
  `outdoor` varchar(255) DEFAULT NULL,
  `outdoor_serial` varchar(50) NOT NULL,
  `supplier` text NOT NULL,
  `brand` text NOT NULL,
  `type` text NOT NULL,
  `hp` varchar(30) NOT NULL,
  `series` varchar(255) DEFAULT NULL,
  `unit` varchar(100) DEFAULT NULL,
  `price` float NOT NULL DEFAULT 0,
  `quantity` int(30) NOT NULL,
  `sub_total` float NOT NULL DEFAULT 0,
  `total_amount` float NOT NULL DEFAULT 0,
  `total` decimal(10,2) NOT NULL,
  `remarks` text NOT NULL,
  `discount_perc` float NOT NULL DEFAULT 0,
  `discount` float NOT NULL DEFAULT 0,
  `tax_perc` float NOT NULL DEFAULT 0,
  `tax` float NOT NULL DEFAULT 0,
  `status` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `supplier_id` int(30) NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `payment_status` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=Unpaid, 1=Paid',
  `description` text DEFAULT NULL,
  `supplier_name` text NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `aux` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `purchase_order_list`
--

INSERT INTO `purchase_order_list` (`id`, `po_id`, `po_code`, `delivery_date`, `po`, `dr`, `invoice`, `indoor`, `indoor_serial`, `outdoor`, `outdoor_serial`, `supplier`, `brand`, `type`, `hp`, `series`, `unit`, `price`, `quantity`, `sub_total`, `total_amount`, `total`, `remarks`, `discount_perc`, `discount`, `tax_perc`, `tax`, `status`, `date_created`, `supplier_id`, `date_updated`, `payment_status`, `description`, `supplier_name`, `deleted`, `aux`) VALUES
(713, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ20BVA', 'K006422G', 'RKQ20BVA', 'K007268F', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '0.8', 'D-SMART SERIES', 'SETS', 32500, 1, 382400, 0, 32500.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:38:38', 0, NULL, '', 0, NULL),
(714, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ20BVA', 'K006340F', 'RKQ20BVA', 'K007328C', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '0.8', 'D-SMART SERIES', 'SETS', 32500, 1, 382400, 0, 32500.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:38:28', 0, NULL, '', 0, NULL),
(715, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTNE20AXVL9', 'K003292P', 'RNE20AGXVL9', 'K003292T', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '0.8', 'COOLING KING', 'SETS', 23800, 1, 382400, 0, 23800.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-11 16:20:12', 0, NULL, '', 0, NULL),
(716, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K037138', 'RKQ25BVA', 'K027992T', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:38:16', 0, NULL, '', 0, NULL),
(717, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'TFKQ25BVA', 'K036994', 'RKQ25BVA', 'K030314B', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:38:05', 0, NULL, '', 0, NULL),
(718, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K037016', 'RKQ25BVA', 'K023728M', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:35:09', 0, NULL, '', 0, NULL),
(719, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K037167', 'RKQ25BVA', 'K030471F', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:34:59', 0, NULL, '', 0, NULL),
(720, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K027983', 'RKQ25BVA', 'K0303129', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:34:38', 0, NULL, '', 0, NULL),
(721, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K037004', 'RKQ25BVA', 'K0285168M', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:34:27', 0, NULL, '', 0, NULL),
(722, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K037147', 'RKQ25BVA', 'K0305019', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:34:19', 0, NULL, '', 0, NULL),
(723, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K03491K', 'RKQ25BVA', 'K0374690', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 382400, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:35:58', 5, '2025-11-13 14:33:52', 0, NULL, '', 0, NULL),
(724, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K032168K', 'RKQ25BVA', 'K036992T', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 197000, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:48:57', 5, '2025-11-13 14:33:37', 0, NULL, '', 0, NULL),
(725, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ25BVA', 'K035508L', 'RKQ25BVA', 'K036995W', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.0', 'D-SMART SERIES', 'SETS', 36700, 1, 197000, 0, 36700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:48:57', 5, '2025-11-13 14:33:27', 0, NULL, '', 0, NULL),
(726, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K0414119', 'RKQ35BVA', 'K043564K', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 197000, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:48:57', 5, '2025-11-24 10:11:43', 0, NULL, '', 0, NULL),
(727, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K041674K', 'RKQ35BVA', 'K043598R', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 197000, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 14:48:57', 5, '2025-11-13 14:32:43', 0, NULL, '', 0, NULL),
(729, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K041294', 'RKQ35BVA', 'K043584M', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:28:13', 0, NULL, '', 0, NULL),
(730, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K040422', 'RKQ35BVA', 'K041128E', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:28:05', 0, NULL, '', 0, NULL),
(731, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K042070', 'RKQ35BVA', 'K041129F', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:27:54', 0, NULL, '', 0, NULL),
(732, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K040419G', 'RKQ35BVA', 'K041146E', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:27:38', 0, NULL, '', 0, NULL),
(733, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K040569M', 'RKQ35BVA', 'K041347H', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:27:31', 0, NULL, '', 0, NULL),
(734, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K038471L', 'RKQ35BVA', 'K041282F', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:27:21', 0, NULL, '', 0, NULL),
(735, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ35BVA', 'K042087J', 'RKQ35BVA', 'K041137E', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'D-SMART SERIES', 'SETS', 41200, 1, 329600, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:02:14', 5, '2025-11-13 14:27:12', 0, NULL, '', 0, NULL),
(741, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKQ60CVAF', 'K002602M', 'RKQ60CVA', 'K00529Q', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '2.5', 'D-SMART SERIES', 'SETS', 62300, 1, 590200, 0, 62300.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 15:37:17', 5, '2025-12-16 14:51:00', 0, NULL, '', 0, NULL),
(746, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FTKZ71WVM', 'E0011505', 'RKZ71WVM', 'E0011325', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '3.0', 'D-SMART KING SERIES (INVERTER)', 'SETS', 125800, 1, 125800, 0, 125800.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 16:15:33', 5, '2025-11-14 11:57:02', 0, NULL, '', 0, NULL),
(747, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FVFC100BXVA', 'K000711', 'RZFC100BXVM', 'K006707', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Floor Mounted', '4.0', 'INVERTER', 'SETS', 147200, 1, 294400, 0, 147200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 16:18:36', 5, '2025-11-11 16:18:36', 0, NULL, '', 0, NULL),
(748, 0, 0, '2025-11-12', 'N/A', 'N/A', 'N/A', 'FVFC100BXVA', 'K000713', 'RZFC100BXVM', 'K006708', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Floor Mounted', '4.0', 'INVERTER', 'SETS', 147200, 1, 294400, 0, 147200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-11 16:18:36', 5, '2025-11-11 16:18:36', 0, NULL, '', 0, NULL),
(749, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW09-6DR-I', '1971', 'GW09-6DR-O', '2469', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '1.0', 'N/A', 'SETS', 32999, 1, 231994, 0, 32999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-12 13:35:49', 0, NULL, '', 0, NULL),
(750, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW09-6DR-I', '1978', 'GW09-6DR-O', '3194', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '1.0', 'N/A', 'SETS', 32999, 1, 231994, 0, 32999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-12 14:43:18', 0, NULL, '', 0, NULL),
(751, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW12-6DR-I', '3309', 'GW12-6DR-O', '3182', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '1.5', 'N/A', 'SETS', 36999, 1, 231994, 0, 36999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-12 14:43:11', 0, NULL, '', 0, NULL),
(752, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW12-6DR-I', '3278', 'GW12-6DR-O', '3143', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '1.5', 'N/A', 'SETS', 36999, 1, 231994, 0, 36999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-12 14:43:02', 0, NULL, '', 0, NULL),
(753, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW18-6DR-I', '4232', 'GW18-6DR-O', '2849', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '2.0', 'N/A', 'SETS', 45999, 1, 231994, 0, 45999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-14 11:56:37', 0, NULL, '', 0, NULL),
(754, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'GW18-6DR-I', '3193', 'GW18-6DR-O', '2877', 'GREAT BUYS HOME DEPOT INC.', 'GREE', 'Wall Mounted', '2.0', 'N/A', 'SETS', 45999, 1, 231994, 0, 45999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 09:52:01', 6, '2025-11-14 11:56:24', 0, NULL, '', 0, NULL),
(755, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW09A2/QCDI', '01511', 'AS09A2/QDDI', '00108', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '1.0', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 31399, 1, 342692, 0, 31399.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 13:36:34', 0, NULL, '', 0, NULL),
(756, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW12A2/QCDI', '00977', 'AS12A2/QDDI', '00412', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '1.5', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 35899, 1, 342692, 0, 35899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 14:42:26', 0, NULL, '', 0, NULL),
(757, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW12A2/QCDI', '00971', 'AS12A2/QDDI', '00470', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '1.5', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 35899, 1, 342692, 0, 35899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 14:42:19', 0, NULL, '', 0, NULL),
(758, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW12A2/QCDI', '01184', 'AS12A2/QDDI', '00361', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '1.5', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 35899, 1, 342692, 0, 35899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-14 11:21:03', 0, NULL, '', 0, NULL),
(759, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW18A2/QCDI', '0390', 'AS18A2/QDDI', '00163', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '2.0', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 45899, 1, 342692, 0, 45899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 13:45:44', 0, NULL, '', 0, NULL),
(760, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW18A2/QCDI', '00688', 'AS18A2/QDDI', '00406', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '2.0', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 45899, 1, 342692, 0, 45899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 14:42:04', 0, NULL, '', 0, NULL),
(761, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW24A2/QCDI', '00250', 'AS24A2/QDDI', '00554', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '2.5', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 55899, 1, 342692, 0, 55899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 14:41:32', 0, NULL, '', 0, NULL),
(762, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW24A2/QCDI', '00043', 'AS24A2/QDDI', '00175', 'FIRST STAR INC.', 'AUX', 'Wall Mounted', '2.5', 'AUX PRIMA SERIES- FULL DC INVERTER', 'SETS', 55899, 1, 342692, 0, 55899.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:18:28', 1, '2025-11-12 14:41:25', 0, NULL, '', 0, NULL),
(763, 0, 0, '2025-11-13', 'N/A', 'N/A', 'N/A', 'ASW09A2/QDDI', 'N00153', 'AS09A2/QDDI', '0089', 'FIRST STAR INC.', 'AUFIT', 'Wall Mounted', '1.0', 'AUFIT PRIMA SERIES-FULL DC INVERTER', 'SETS', 29699, 1, 29699, 0, 29699.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-12 13:24:14', 1, '2025-11-12 14:41:16', 0, NULL, '', 0, NULL),
(764, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-09WR-INV/F9MD', '150080', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '1.0', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 26499, 1, 26499, 0, 26499.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 09:12:21', 1, '2025-11-13 16:19:27', 0, NULL, '', 0, NULL),
(765, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-09WR-INV/F9MD', '150111', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '1.0', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 26499, 1, 225594, 0, 26499.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:30:55', 0, NULL, '', 0, NULL),
(766, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-12WR-INV/F9MD', '150037', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '1.5', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 32999, 1, 225594, 0, 32999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:30:43', 0, NULL, '', 0, NULL),
(767, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-12WR-INV/F9MD', '150048', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '1.5', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 32999, 1, 225594, 0, 32999.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:30:33', 0, NULL, '', 0, NULL),
(768, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-18WR-INV/F9MD', '150019', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '2.0', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 42699, 1, 225594, 0, 42699.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:30:22', 0, NULL, '', 0, NULL),
(769, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-18WR-INV/F9MD', '150035', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '2.0', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 42699, 1, 225594, 0, 42699.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:30:14', 0, NULL, '', 0, NULL),
(770, 0, 0, '2025-09-11', '2025-06', '0054061', '53496', 'HK-24WR-INV/F9MD', '150004', 'N/A', 'N/A', 'GREAT BUYS HOME DEPOT INC.', 'HK', 'Window', '2.5', 'HK - F9 SERIES-FULL DC INVERTER', 'SETS', 47699, 1, 225594, 0, 47699.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-13 16:27:32', 1, '2025-11-13 16:29:52', 0, NULL, '', 0, NULL),
(772, 0, 0, '2025-11-15', 'N/A', 'N/A', 'N/A', 'MX-CS35L2A', '170830223', 'MX-CS35L2A', '160810102', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'Wall Mounted', '1.5', 'MIDEA SERIES', 'SETS', 29000, 1, 78800, 0, 29000.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-14 08:41:25', 8, '2025-11-14 08:41:25', 0, NULL, '', 0, NULL),
(773, 0, 0, '2025-11-15', 'N/A', 'N/A', 'N/A', 'MX-CS70L2A', '120077', 'MX-CS70L2A', '170147', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'Wall Mounted', '2.5', 'MIDEA SERIES', 'SETS', 49800, 1, 78800, 0, 49800.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-14 08:41:25', 8, '2025-11-14 08:41:25', 0, NULL, '', 0, NULL),
(778, 0, 0, '2025-11-18', 'N/A', 'N/A', 'N/A', 'ftkq35bva', 'k040681h', 'rkq35bva', 'k041780i', 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', 'DAIKIN', 'Wall Mounted', '1.5', 'd-smart series', 'SETS', 41200, 1, 41200, 0, 41200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-18 11:21:34', 5, '2025-11-18 11:21:34', 0, NULL, '', 0, NULL),
(823, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC1100', '2U0631', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '1.0', 'INVERTER', 'SETS', 17400, 1, 210800, 0, 17400.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(824, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC1100', '2U0667', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '1.0', 'INVERTER', 'SETS', 17400, 1, 210800, 0, 17400.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(825, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC35A', '2U0465', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '1.5', 'INVERTER', 'SETS', 22200, 1, 210800, 0, 22200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(826, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC35A', '2U0226', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '1.5', 'INVERTER', 'SETS', 22200, 1, 210800, 0, 22200.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(827, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC50', '7000361', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '2.0', 'N/A', 'SETS', 30100, 1, 210800, 0, 30100.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(828, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC50', 'N/A', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '2.0', 'N/A', 'SETS', 30100, 1, 210800, 0, 30100.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(829, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC25', '1800012', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '2.5', 'N/A', 'SETS', 35700, 1, 210800, 0, 35700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL),
(830, 0, 0, '2025-09-11', '2025-16', '09418', 'N/A', 'MX-KC25', '18000157', 'N/A', 'N/A', 'MEGAJOY POWER SOLUTION INC.', 'MATRIX', 'WINDOW', '2.5', 'N/A', 'SETS', 35700, 1, 210800, 0, 35700.00, 'N/A', 0, 0, 0, 0, 'pending', '2025-11-24 13:11:25', 8, '2025-11-24 13:11:25', 0, NULL, '', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `brand` varchar(100) NOT NULL,
  `hp` varchar(50) DEFAULT '',
  `type` varchar(50) DEFAULT '',
  `price` decimal(10,2) NOT NULL,
  `unit` varchar(20) DEFAULT '',
  `series` varchar(50) DEFAULT '',
  `indoor` varchar(50) DEFAULT '',
  `indoor_serial` varchar(50) DEFAULT '',
  `outdoor` varchar(50) DEFAULT '',
  `outdoor_serial` varchar(50) DEFAULT '',
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sales_list`
--

CREATE TABLE `sales_list` (
  `id` int(30) NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `invoice_number` varchar(50) NOT NULL,
  `transaction_number` varchar(50) DEFAULT NULL,
  `client_name` text NOT NULL,
  `indoor` varchar(255) NOT NULL,
  `indoor_serial` varchar(255) NOT NULL,
  `outdoor` varchar(255) NOT NULL,
  `outdoor_serial` varchar(255) NOT NULL,
  `brand` text DEFAULT NULL,
  `series` varchar(255) NOT NULL,
  `unit` text NOT NULL,
  `type` varchar(50) DEFAULT NULL,
  `hp` varchar(50) DEFAULT NULL,
  `quantity` int(255) NOT NULL,
  `price` float NOT NULL DEFAULT 0,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `remarks` text DEFAULT NULL,
  `client` text DEFAULT NULL,
  `supplier_id` int(30) NOT NULL,
  `discount_perc` float NOT NULL DEFAULT 0,
  `tax_perc` float NOT NULL DEFAULT 0,
  `row_perc` decimal(50,0) DEFAULT 0,
  `sales_code` varchar(50) NOT NULL,
  `grand_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` text DEFAULT NULL,
  `discount` float NOT NULL DEFAULT 0,
  `tax` float NOT NULL DEFAULT 0,
  `stock_ids` text NOT NULL,
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `sale_date` date NOT NULL,
  `deleted` tinyint(1) NOT NULL DEFAULT 0,
  `discount-amount` int(50) NOT NULL,
  ` overall_discount_perc` int(50) NOT NULL,
  `discount_per_item` decimal(10,2) DEFAULT 0.00,
  `per_item_discount_input` decimal(10,2) DEFAULT 0.00,
  `sub_total` decimal(10,2) DEFAULT 0.00,
  `amount` decimal(10,2) DEFAULT 0.00,
  `sale_status` varchar(20) DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales_list`
--

INSERT INTO `sales_list` (`id`, `date_created`, `invoice_number`, `transaction_number`, `client_name`, `indoor`, `indoor_serial`, `outdoor`, `outdoor_serial`, `brand`, `series`, `unit`, `type`, `hp`, `quantity`, `price`, `total_amount`, `remarks`, `client`, `supplier_id`, `discount_perc`, `tax_perc`, `row_perc`, `sales_code`, `grand_total`, `status`, `discount`, `tax`, `stock_ids`, `date_updated`, `sale_date`, `deleted`, `discount-amount`, ` overall_discount_perc`, `discount_per_item`, `per_item_discount_input`, `sub_total`, `amount`, `sale_status`) VALUES
(496, '2025-11-24 10:13:06', 'SI-00001', '0000001', 'Mayor Mike (Office)', 'FTKQ35BVA', 'K0414029', 'RKQ35BVA', 'K043591K', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '1.5', 1, 41200, 41200.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-11-24 10:31:58', '2025-11-25', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(497, '2025-12-05 11:04:51', 'SI-00002', '0000002', 'MAYOR MIKE MALALUAN', 'FTKQ60CVAF', 'K002628Q', 'RKQ60CVA', 'K000480M', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.5', 1, 62300, 62300.00, 'INSTALL: 12/06/2025', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-05 11:04:51', '2025-12-05', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(498, '2025-12-12 09:59:44', 'SI-00003', '0000003', 'BUILDER\'S OFFICE', 'FTKQ50DVA', 'K008869X', 'RKF50BVA', 'K008395I', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.0', 1, 53800, 53800.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-12 16:05:32', '2025-12-12', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(499, '2025-12-12 09:59:44', 'SI-00003', '0000003', 'BUILDER\'S OFFICE', 'FTKQ50BVA', 'K008867', 'RKF50BVA', 'K008357F', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.0', 1, 53800, 53800.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-12 16:05:45', '2025-12-12', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(500, '2025-12-12 09:59:44', 'SI-00003', '0000003', 'BUILDER\'S OFFICE', 'FTKQ60CVAF', 'K002642M', 'RKQ60CVA', 'K000401F', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.5', 1, 62300, 62300.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-12 16:05:52', '2025-12-12', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(501, '2025-12-16 14:51:56', 'SI-00004', '0000004', 'MAYOR ROLDAN', 'FTKQ50BVA', 'K008865T', 'RKF50BVA', 'K008396H', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.0', 1, 53800, 53800.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-16 14:51:56', '2025-12-16', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(502, '2025-12-16 14:51:56', 'SI-00004', '0000004', 'MAYOR ROLDAN', 'FTKQ60CVAF', 'K002630J', 'RKQ60CVA', 'K00400', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.5', 1, 62300, 62300.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-16 14:51:56', '2025-12-16', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending'),
(503, '2025-12-16 14:52:32', 'SI-00005', '0000005', 'KGS', 'FTKF50DVA', 'K008448Q', 'RKF50DVA', 'K0089721', 'DAIKIN', 'D-SMART SERIES', 'SETS', 'Wall Mounted', '2.0', 1, 53800, 53800.00, '', NULL, 0, 0, 0, 0, '', 0.00, NULL, 0, 0, '', '2025-12-16 14:52:32', '2025-12-16', 0, 0, 0, 0.00, 0.00, 0.00, 0.00, 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `sale_items`
--

CREATE TABLE `sale_items` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) NOT NULL,
  `stock_id` int(11) NOT NULL,
  `serial_no` varchar(255) DEFAULT NULL,
  `hp` varchar(50) DEFAULT NULL,
  `type` varchar(100) DEFAULT NULL,
  `indoor_outdoor` varchar(20) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT 0.00,
  `discount_perc` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sale_items`
--

INSERT INTO `sale_items` (`id`, `sale_id`, `stock_id`, `serial_no`, `hp`, `type`, `indoor_outdoor`, `total_amount`, `discount_perc`) VALUES
(26, 124, 0, NULL, '5', 'Split', NULL, 0.00, 0.00),
(27, 124, 0, NULL, '3.5', 'Floor', NULL, 0.00, 0.00),
(28, 127, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(29, 127, 0, NULL, '3.5', 'Floor', NULL, 0.00, 0.00),
(30, 128, 0, NULL, '4.5', 'Window', NULL, 0.00, 0.00),
(31, 128, 0, NULL, '8', 'Split', NULL, 0.00, 0.00),
(32, 129, 0, NULL, '5', 'Split', NULL, 0.00, 0.00),
(33, 129, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(34, 132, 0, NULL, '2.5', 'Ceiling', NULL, 0.00, 0.00),
(35, 132, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(36, 133, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(37, 133, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(38, 136, 0, NULL, '2.5', 'Split', NULL, 0.00, 0.00),
(39, 136, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(40, 139, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(41, 139, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(42, 142, 0, NULL, '5', 'Ceiling', NULL, 0.00, 0.00),
(43, 142, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(44, 145, 0, NULL, '3.5', 'Cassette', NULL, 0.00, 0.00),
(45, 145, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(46, 145, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(47, 149, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(48, 149, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(49, 152, 0, NULL, '2.5', 'Ceiling', NULL, 0.00, 0.00),
(50, 152, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(51, 155, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(52, 155, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(53, 158, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(54, 160, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(55, 162, 0, NULL, '1.5', 'Ceiling', NULL, 0.00, 0.00),
(56, 162, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(57, 165, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(58, 165, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(59, 168, 0, NULL, '3.5', 'Floor', NULL, 0.00, 0.00),
(60, 168, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(61, 171, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(62, 171, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(63, 174, 0, NULL, '3.5', 'Ceiling', NULL, 0.00, 0.00),
(64, 174, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(65, 177, 0, NULL, '1.5', 'Ceiling', NULL, 0.00, 0.00),
(66, 177, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(67, 180, 0, NULL, '5', 'Ceiling', NULL, 0.00, 0.00),
(68, 180, 0, NULL, '4', 'Split', NULL, 0.00, 0.00),
(69, 180, 0, NULL, '3.5', 'Floor', NULL, 0.00, 0.00),
(70, 180, 0, NULL, '2.5', 'Floor', NULL, 0.00, 0.00),
(71, 180, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(72, 180, 0, NULL, '1.5', 'Cassette', NULL, 0.00, 0.00),
(73, 187, 0, NULL, '5', 'Split', NULL, 0.00, 0.00),
(74, 187, 0, NULL, '0.8', 'Floor', NULL, 0.00, 0.00),
(75, 190, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(76, 192, 0, NULL, '1.5 HP', 'Cassette', NULL, 0.00, 0.00),
(77, 194, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(78, 196, 0, NULL, '1.5', 'Cassette', NULL, 0.00, 0.00),
(79, 198, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(80, 200, 0, NULL, '2.5', 'Floor', NULL, 0.00, 0.00),
(81, 202, 0, NULL, '5', 'Split', NULL, 0.00, 0.00),
(82, 204, 0, NULL, '1.5', 'Split', NULL, 0.00, 0.00),
(83, 206, 0, NULL, '7', 'Window', NULL, 0.00, 0.00),
(84, 208, 0, NULL, '5', 'Floor', NULL, 0.00, 0.00),
(85, 210, 0, NULL, '5', 'Floor', NULL, 0.00, 0.00),
(86, 212, 0, NULL, '5', 'Cassette', NULL, 0.00, 0.00),
(87, 214, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(88, 216, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(89, 218, 0, NULL, '1.5 HP', 'Cassette', NULL, 0.00, 0.00),
(90, 220, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(91, 222, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(92, 224, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(93, 226, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(94, 228, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(95, 230, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(96, 232, 0, NULL, '3', 'Cassette', NULL, 0.00, 0.00),
(97, 234, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(98, 236, 0, NULL, '1.5', 'Cassette', NULL, 0.00, 0.00),
(99, 238, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(100, 240, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(101, 242, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(102, 244, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(103, 247, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(104, 248, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(105, 249, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(106, 250, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(107, 251, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(108, 252, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(109, 253, 0, NULL, '1.5 HP', 'Split', NULL, 0.00, 0.00),
(110, 254, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(111, 255, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(112, 256, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(113, 257, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(114, 258, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(115, 259, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(116, 260, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(117, 261, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(118, 262, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(119, 263, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(120, 264, 0, NULL, '3', 'Cassette', NULL, 0.00, 0.00),
(121, 265, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(122, 266, 0, NULL, '4', 'Floor', NULL, 0.00, 0.00),
(123, 267, 0, NULL, '1', 'Floor', NULL, 0.00, 0.00),
(124, 268, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(125, 269, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(126, 270, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(127, 271, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(128, 272, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(129, 273, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(130, 274, 0, NULL, '4', 'Ceiling', NULL, 0.00, 0.00),
(131, 275, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(132, 276, 0, NULL, '1.5', 'Split', NULL, 0.00, 0.00),
(133, 277, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(134, 278, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(135, 279, 0, NULL, '3', 'Cassette', NULL, 0.00, 0.00),
(136, 280, 0, NULL, '1.5', 'Cassette', NULL, 0.00, 0.00),
(137, 281, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(138, 282, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(139, 283, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(140, 284, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(141, 285, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(142, 286, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(143, 287, 0, NULL, '1.5', 'Split', NULL, 0.00, 0.00),
(144, 288, 0, NULL, '1', 'Floor', NULL, 0.00, 0.00),
(145, 289, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(146, 290, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(147, 291, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(148, 292, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(149, 293, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(150, 294, 0, NULL, '3', 'Cassette', NULL, 0.00, 0.00),
(151, 295, 0, NULL, '3', 'Cassette', NULL, 0.00, 0.00),
(152, 296, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(153, 297, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(154, 298, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(155, 299, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(156, 300, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(157, 301, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(158, 302, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(159, 303, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(160, 304, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(161, 305, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(162, 306, 0, NULL, 'rwerew', 'Ceiling', NULL, 0.00, 0.00),
(163, 307, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(164, 308, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(165, 309, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(166, 310, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(167, 311, 0, NULL, '1.5', 'Floor', NULL, 0.00, 0.00),
(168, 312, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(169, 313, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(170, 314, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(171, 315, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(172, 316, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(173, 317, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(174, 318, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(175, 319, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(176, 320, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(177, 321, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(178, 322, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(179, 323, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(180, 324, 0, NULL, '1', 'Window', NULL, 0.00, 0.00),
(181, 325, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(182, 326, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(183, 327, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(184, 328, 0, NULL, '3', 'Window', NULL, 0.00, 0.00),
(185, 329, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(186, 330, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(187, 331, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(188, 332, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(189, 333, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(190, 334, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(191, 335, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(192, 336, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(193, 337, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(194, 338, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(195, 339, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(196, 340, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(197, 341, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(198, 342, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(199, 343, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(200, 344, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(201, 345, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(202, 346, 0, NULL, '1', 'Window', NULL, 0.00, 0.00),
(203, 347, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(204, 348, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(205, 349, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(206, 350, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(207, 351, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(208, 352, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(209, 353, 0, NULL, '2', 'Ceiling', NULL, 0.00, 1.00),
(210, 354, 0, NULL, '2', 'Floor', NULL, 0.00, 1.00),
(211, 355, 0, NULL, '1', 'Ceiling', NULL, 0.00, 1.00),
(212, 356, 0, NULL, '1', 'Cassette', NULL, 0.00, 1.00),
(213, 357, 0, NULL, '1', 'Cassette', NULL, 0.00, 1.00),
(214, 358, 0, NULL, '2', 'Ceiling', NULL, 0.00, 1.00),
(215, 359, 0, NULL, '3', 'Window', NULL, 0.00, 1.00),
(216, 360, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(217, 361, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(218, 362, 0, NULL, '2', 'Window', NULL, 0.00, 0.00),
(219, 363, 0, NULL, '1', 'Split', NULL, 0.00, 1.00),
(220, 364, 0, NULL, '3', 'Split', NULL, 0.00, 1.00),
(221, 365, 0, NULL, '3', 'Floor', NULL, 0.00, 1.00),
(222, 366, 0, NULL, '1', 'Cassette', NULL, 0.00, 1.00),
(223, 367, 0, NULL, '1', 'Ceiling', NULL, 0.00, 1.00),
(224, 368, 0, NULL, '2', 'Ceiling', NULL, 0.00, 1.00),
(225, 369, 0, NULL, '1', 'Floor', NULL, 0.00, 1.00),
(226, 370, 0, NULL, '1', 'Ceiling', NULL, 0.00, 1.00),
(227, 371, 0, NULL, '1', 'Cassette', NULL, 0.00, 1.00),
(228, 372, 0, NULL, '1', 'Cassette', NULL, 0.00, 1.00),
(229, 373, 0, NULL, '2', 'Ceiling', NULL, 0.00, 1.00),
(230, 374, 0, NULL, '3', 'Window', NULL, 0.00, 1.00),
(231, 375, 0, NULL, '2', 'Split', NULL, 0.00, 1.00),
(232, 376, 0, NULL, '1', 'Split', NULL, 0.00, 1.00),
(233, 377, 0, NULL, '2', 'Window', NULL, 0.00, 0.00),
(234, 378, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(235, 379, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(236, 380, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(237, 381, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(238, 382, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(239, 383, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(240, 384, 0, NULL, '2', 'Window', NULL, 0.00, 0.00),
(241, 385, 0, NULL, '2', 'Window', NULL, 0.00, 0.00),
(242, 386, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(243, 387, 0, NULL, '3', 'Split', NULL, 0.00, 0.00),
(244, 388, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(245, 389, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(246, 390, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(247, 391, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(248, 392, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(249, 393, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(250, 394, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(251, 395, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(252, 396, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(253, 397, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(254, 398, 0, NULL, '1', 'Floor', NULL, 0.00, 0.00),
(255, 399, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(256, 400, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(257, 401, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(258, 402, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(259, 403, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(260, 404, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(261, 405, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(262, 406, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(263, 407, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(264, 408, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(265, 409, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(266, 410, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(267, 411, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(268, 412, 0, NULL, '2', 'Window', NULL, 0.00, 0.00),
(269, 413, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(270, 414, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(271, 415, 0, NULL, '1', 'Split', NULL, 0.00, 0.00),
(272, 416, 0, NULL, '1', 'Floor', NULL, 0.00, 0.00),
(273, 417, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(274, 418, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(275, 419, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(276, 420, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(277, 421, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(278, 422, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(279, 423, 0, NULL, '1', 'Window', NULL, 0.00, 0.00),
(280, 424, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(281, 425, 0, NULL, '1', 'Ceiling', NULL, 0.00, 0.00),
(282, 426, 0, NULL, '2', 'Split', NULL, 0.00, 0.00),
(283, 427, 0, NULL, '3', 'Ceiling', NULL, 0.00, 0.00),
(284, 428, 0, NULL, '2', 'Floor', NULL, 0.00, 0.00),
(285, 429, 0, NULL, '1', 'Floor', NULL, 0.00, 0.00),
(286, 430, 0, NULL, '3', 'Floor', NULL, 0.00, 0.00),
(287, 431, 0, NULL, '2', 'Ceiling', NULL, 0.00, 0.00),
(288, 432, 0, NULL, '2', 'Cassette', NULL, 0.00, 0.00),
(289, 433, 0, NULL, '1', 'Cassette', NULL, 0.00, 0.00),
(290, 434, 0, NULL, '0.8', 'Split', NULL, 0.00, 0.00),
(291, 435, 0, NULL, '5', 'Cassette', NULL, 0.00, 0.00),
(292, 436, 0, NULL, 'N/A', 'Floor', NULL, 0.00, 0.00),
(293, 437, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(294, 438, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(295, 439, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(296, 440, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(297, 441, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(298, 442, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(299, 443, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(300, 444, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(301, 445, 0, NULL, '3', 'N/A', NULL, 0.00, 0.00),
(302, 446, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(303, 447, 0, NULL, '3', 'N/A', NULL, 0.00, 0.00),
(304, 448, 0, NULL, '2', 'N/A', NULL, 0.00, 0.00),
(305, 449, 0, NULL, '2', 'N/A', NULL, 0.00, 0.00),
(306, 450, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(307, 451, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(308, 452, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(309, 453, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(310, 454, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(311, 455, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(312, 456, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(313, 457, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(314, 459, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(315, 460, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(316, 461, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(317, 462, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(318, 463, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(319, 464, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(320, 465, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(321, 466, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(322, 467, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(323, 468, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(324, 469, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(325, 470, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(326, 471, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(327, 472, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(328, 473, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(329, 474, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(330, 475, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(331, 476, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(332, 477, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(333, 478, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(334, 479, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(335, 480, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(336, 481, 0, NULL, '1.5', 'N/A', NULL, 0.00, 0.00),
(337, 482, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(338, 483, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(339, 484, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(340, 485, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(341, 486, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(342, 487, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(343, 488, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(344, 489, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(345, 490, 0, NULL, '3', 'N/A', NULL, 0.00, 0.00),
(346, 491, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(347, 492, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(348, 493, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(349, 494, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(350, 495, 0, NULL, 'N/A', 'N/A', NULL, 0.00, 0.00),
(351, 496, 0, NULL, '1.5', 'Wall Mounted', NULL, 0.00, 0.00),
(352, 497, 0, NULL, '2.5', 'Wall Mounted', NULL, 0.00, 0.00),
(353, 498, 0, NULL, '2.0', 'Wall Mounted', NULL, 0.00, 0.00),
(354, 499, 0, NULL, '2.0', 'Wall Mounted', NULL, 0.00, 0.00),
(355, 500, 0, NULL, '2.5', 'Wall Mounted', NULL, 0.00, 0.00),
(356, 501, 0, NULL, '2.0', 'Wall Mounted', NULL, 0.00, 0.00),
(357, 502, 0, NULL, '2.5', 'Wall Mounted', NULL, 0.00, 0.00),
(358, 503, 0, NULL, '2.0', 'Wall Mounted', NULL, 0.00, 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `schedule_list`
--

CREATE TABLE `schedule_list` (
  `id` int(30) NOT NULL,
  `service_type` text NOT NULL,
  `start_date` datetime NOT NULL,
  `service_type_2` text NOT NULL,
  `end_date` datetime DEFAULT NULL,
  `customer_name` text NOT NULL,
  `address` varchar(255) NOT NULL,
  `customer_cp` varchar(255) DEFAULT NULL,
  `staff_name` text NOT NULL,
  `remarks` varchar(255) NOT NULL,
  `title` text NOT NULL,
  `description` text NOT NULL,
  `client_name` text NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_list`
--

INSERT INTO `schedule_list` (`id`, `service_type`, `start_date`, `service_type_2`, `end_date`, `customer_name`, `address`, `customer_cp`, `staff_name`, `remarks`, `title`, `description`, `client_name`, `status`) VALUES
(93, 'INSTALLING', '2025-07-17 00:00:00', 'CLEANING', '2026-01-17 00:00:00', 'Jese Asilo (SJ Mansion Hotel)', 'N/A', 'N/A', 'VLADIMER', '', '', '', '', '1'),
(94, 'INSTALLING', '2025-08-28 00:00:00', 'CLEANING', '2026-02-28 00:00:00', 'ASTRA', 'CAINTA RIZAL', '0951-843-8028', 'MANILA INSTALLER', '', '', '', '', '1'),
(95, 'INSTALLING', '2025-10-14 00:00:00', 'CLEANING', '2026-04-14 00:00:00', 'DOC BIRUNG', 'N/A', '0917-709-1279', 'VLADIMER', '', '', '', '', '1'),
(96, 'INSTALLING', '2025-11-11 00:00:00', 'CLEANING', '2026-05-11 00:00:00', 'MERCURY DRUG VICTORIA', 'VICTORIA ORIENTAL MINDORO', '0917-700-6153', 'VLADIMER', '', '', '', '', '1'),
(97, 'INSTALLING', '2024-01-03 00:00:00', 'CLEANING', '2026-01-03 00:00:00', 'ENGINEER RENAN', 'POLA, ORIENTAL MINDORO', '0928-287-7802', 'MARVIN GACUL', '', '', '', '', '1'),
(98, 'INSTALLING', '2025-07-20 00:00:00', 'CLEANING', '2026-12-20 00:00:00', 'MERCURY DRUG NEO CALAPAN', 'CALAPAN', '09772346451', 'MARVIN GACUL', '', '', '', '', '1');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_settings`
--

CREATE TABLE `schedule_settings` (
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL,
  `date_create` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedule_settings`
--

INSERT INTO `schedule_settings` (`meta_field`, `meta_value`, `date_create`) VALUES
('day_schedule', 'Monday,Tuesday,Wednesday,Thursday,Friday', '2021-09-02 19:55:37'),
('morning_schedule', '08:00,11:00', '2021-09-02 19:55:37'),
('afternoon_schedule', '13:00,16:00', '2021-09-02 19:55:37');

-- --------------------------------------------------------

--
-- Table structure for table `stock_list`
--

CREATE TABLE `stock_list` (
  `id` int(30) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `item_id` int(30) NOT NULL,
  `serial_no` varchar(50) NOT NULL,
  `quantity` int(30) NOT NULL,
  `unit` varchar(250) DEFAULT NULL,
  `price` float NOT NULL DEFAULT 0,
  `total` float NOT NULL DEFAULT 0,
  `type` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=IN , 2=OUT',
  `brand` varchar(100) DEFAULT NULL,
  `indoor_outdoor` varchar(50) DEFAULT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `supplier_list`
--

CREATE TABLE `supplier_list` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `address` text NOT NULL,
  `contact_person` varchar(255) DEFAULT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `status` tinyint(1) NOT NULL DEFAULT 1,
  `date_created` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `cperson` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `supplier_list`
--

INSERT INTO `supplier_list` (`id`, `name`, `address`, `contact_person`, `contact`, `status`, `date_created`, `date_updated`, `cperson`) VALUES
(1, 'GREAT BUYS HOME DEPOT INC.', 'Mandaluyong City\r\n', 'Supplier Staff 101', '09123456789', 1, '2021-11-02 00:00:00', '2025-11-14 11:50:25', 'Art Salas'),
(4, 'AUX', 'Mansalay', NULL, '09988888', 1, '2025-10-08 14:55:08', '2025-10-08 14:55:08', 'Toto '),
(5, 'DAIKIN AIRCONDITIONING PHILIPPINES INC.', '', NULL, '', 1, '2025-11-07 16:29:53', '2025-11-07 16:29:53', ''),
(6, 'FIRST STAR INC.', '', NULL, '', 1, '2025-11-11 16:58:38', '2025-11-11 16:58:38', ''),
(8, 'MEGAJOY POWER SOLUTION INC.', '', NULL, '', 1, '2025-11-13 16:54:00', '2025-11-13 16:54:00', '');

-- --------------------------------------------------------

--
-- Table structure for table `system_info`
--

CREATE TABLE `system_info` (
  `id` int(30) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_info`
--

INSERT INTO `system_info` (`id`, `meta_field`, `meta_value`) VALUES
(1, 'name', ''),
(6, 'short_name', '2HHI AIRCON'),
(11, 'logo', 'uploads/logo.png'),
(13, 'user_avatar', 'uploads/ad.png'),
(14, 'cover', 'uploads/bgcovernew.png'),
(15, 'content', 'Array');

-- --------------------------------------------------------

--
-- Table structure for table `transaction_sequence`
--

CREATE TABLE `transaction_sequence` (
  `id` int(11) NOT NULL,
  `next_transaction_number` int(11) NOT NULL DEFAULT 1,
  `next_value` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transaction_sequence`
--

INSERT INTO `transaction_sequence` (`id`, `next_transaction_number`, `next_value`) VALUES
(1, 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(50) NOT NULL,
  `firstname` varchar(250) NOT NULL,
  `middlename` text DEFAULT NULL,
  `lastname` varchar(250) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `avatar` text DEFAULT NULL,
  `last_login` datetime DEFAULT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 0,
  `date_added` datetime NOT NULL DEFAULT current_timestamp(),
  `date_updated` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `middlename`, `lastname`, `username`, `password`, `avatar`, `last_login`, `type`, `date_added`, `date_updated`) VALUES
(1, 'MAIN', NULL, '', 'main', '3705a4e1b7fc2beaa2450839cec59f79', 'uploads/user.png?v=1635556826', NULL, 1, '2021-11-02 00:00:00', '2021-10-30 00:00:00'),
(2, 'STAFF', NULL, '', 'staff', '1253208465b1efa876f982d8a9e73eef', 'uploads/user.png?v=1635920488', NULL, 2, '2021-11-02 00:00:00', '2021-11-03 00:00:00'),
(3, 'Jasmine', NULL, '', 'jas_21', '54f3f00632d0ed720008d828c13e03a2', 'uploads/user.png?v=1635920566', NULL, 1, '2021-11-02 00:00:00', '2025-11-07 11:14:13');

-- --------------------------------------------------------

--
-- Table structure for table `user_meta`
--

CREATE TABLE `user_meta` (
  `user_id` int(30) NOT NULL,
  `meta_field` text NOT NULL,
  `meta_value` text NOT NULL,
  `date_created` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aircon_units`
--
ALTER TABLE `aircon_units`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `delete_list`
--
ALTER TABLE `delete_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `item_list`
--
ALTER TABLE `item_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `po_items`
--
ALTER TABLE `po_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `po_id` (`po_id`);

--
-- Indexes for table `purchase_order_list`
--
ALTER TABLE `purchase_order_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `supplier_id` (`supplier_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sales_list`
--
ALTER TABLE `sales_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `sale_items`
--
ALTER TABLE `sale_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `stock_id` (`stock_id`);

--
-- Indexes for table `schedule_list`
--
ALTER TABLE `schedule_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_list`
--
ALTER TABLE `stock_list`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`),
  ADD KEY `sale_id` (`sale_id`);

--
-- Indexes for table `supplier_list`
--
ALTER TABLE `supplier_list`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `system_info`
--
ALTER TABLE `system_info`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `transaction_sequence`
--
ALTER TABLE `transaction_sequence`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `user_meta`
--
ALTER TABLE `user_meta`
  ADD KEY `user_id` (`user_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aircon_units`
--
ALTER TABLE `aircon_units`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `completed_tasks`
--
ALTER TABLE `completed_tasks`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `delete_list`
--
ALTER TABLE `delete_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=642;

--
-- AUTO_INCREMENT for table `item_list`
--
ALTER TABLE `item_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `po_items`
--
ALTER TABLE `po_items`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `purchase_order_list`
--
ALTER TABLE `purchase_order_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=831;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sales_list`
--
ALTER TABLE `sales_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=504;

--
-- AUTO_INCREMENT for table `sale_items`
--
ALTER TABLE `sale_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=359;

--
-- AUTO_INCREMENT for table `schedule_list`
--
ALTER TABLE `schedule_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=99;

--
-- AUTO_INCREMENT for table `stock_list`
--
ALTER TABLE `stock_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `supplier_list`
--
ALTER TABLE `supplier_list`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `system_info`
--
ALTER TABLE `system_info`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `transaction_sequence`
--
ALTER TABLE `transaction_sequence`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `po_items`
--
ALTER TABLE `po_items`
  ADD CONSTRAINT `po_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_order_list` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `po_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `item_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_items`
--
ALTER TABLE `purchase_order_items`
  ADD CONSTRAINT `purchase_order_items_ibfk_1` FOREIGN KEY (`po_id`) REFERENCES `purchase_order_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `purchase_order_list`
--
ALTER TABLE `purchase_order_list`
  ADD CONSTRAINT `purchase_order_list_ibfk_1` FOREIGN KEY (`supplier_id`) REFERENCES `supplier_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `stock_list`
--
ALTER TABLE `stock_list`
  ADD CONSTRAINT `stock_list_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `item_list` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_meta`
--
ALTER TABLE `user_meta`
  ADD CONSTRAINT `user_meta_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
