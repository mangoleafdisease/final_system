-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 16, 2024 at 07:56 PM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pos_inventory`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `admin_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `password`, `admin_name`) VALUES
(1, 'admin', '$2y$10$anDWDJUqdqqVIpxygWsvoOTkfH6xksjwCyp8KEQvwg6dFEn5PQESu', 'Admin1');

-- --------------------------------------------------------

--
-- Table structure for table `cashier`
--

CREATE TABLE `cashier` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(100) NOT NULL,
  `cashier_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `cashier`
--

INSERT INTO `cashier` (`id`, `username`, `password`, `cashier_name`) VALUES
(1, 'cashier', '$2y$10$C0N26SVjaEbH8vGwRzOxgOpRsWijAN9eeW5HecpCc5R6GvS9dJtTS', 'Cashier1');

-- --------------------------------------------------------

--
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(50) NOT NULL,
  `username` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `customer_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customers`
--

INSERT INTO `customers` (`id`, `username`, `password`, `customer_name`) VALUES
(1, 'customer1', '$2y$10$36ei1p5QGEdPx7ipAQpzN.1xAbPMrDzcexVuNeeNumcmfDUtC6aR.', 'Customer1'),
(2, 'cj', '$2y$10$dCiuXe0AvtoOYRCGQJG53./pyS/4aNO.0ZiVdC7bfrHbv.sf8eWNG', 'CJ');

-- --------------------------------------------------------

--
-- Table structure for table `customer_cart`
--

CREATE TABLE `customer_cart` (
  `id` int(11) NOT NULL,
  `cart_id` varchar(255) DEFAULT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `item_id` int(11) DEFAULT NULL,
  `item_name` varchar(255) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `price` decimal(10,2) DEFAULT NULL,
  `added_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_cart`
--

INSERT INTO `customer_cart` (`id`, `cart_id`, `customer_id`, `customer_name`, `item_id`, `item_name`, `size`, `quantity`, `price`, `added_date`) VALUES
(12, '673a43511e6447.04861027', 1, 'Customer1', 24, 'P.E', 'Small', 2, 250.00, '2024-11-18 03:26:09'),
(31, '6748fcc1067549.57304644', 2, 'CJ', 24, 'P.E', 'Small', 1, 250.00, '2024-11-29 07:29:05'),
(32, '6748fcc10787c3.56568934', 2, 'CJ', 23, 'P.E', 'Medium', 1, 250.00, '2024-11-29 07:29:05'),
(33, '6748fcc107eba6.67790019', 2, 'CJ', 22, 'Jogging Pants', 'Medium', 1, 300.00, '2024-11-29 07:29:05'),
(34, '6748fcc108c838.18473559', 2, 'CJ', 21, 'P.E', 'Medium', 1, 250.00, '2024-11-29 07:29:05'),
(37, '67490409f3b509.27385643', 2, 'CJ', 23, 'P.E', 'Medium', 1, 250.00, '2024-11-29 08:00:09'),
(38, '67490409f418c8.63332092', 2, 'CJ', 19, 'Arnis padding', 'Long', 1, 50.00, '2024-11-29 08:00:09'),
(40, '67490420bca990.10568624', 2, 'CJ', 11, 'Glove', 'XL', 1, 50.00, '2024-11-29 08:00:32'),
(41, '67490420bd9f15.69460620', 2, 'CJ', 10, 'Shoes', '42', 1, 500.00, '2024-11-29 08:00:32'),
(42, '67490420be1cc3.47484199', 2, 'CJ', 8, 'Mat', 'Long', 1, 200.00, '2024-11-29 08:00:32'),
(43, '67490475e6e641.61499044', 2, 'CJ', 24, 'P.E', 'Small', 1, 250.00, '2024-11-29 08:01:57'),
(44, '67490475e7b8f0.45261414', 2, 'CJ', 23, 'P.E', 'Medium', 1, 250.00, '2024-11-29 08:01:57'),
(45, '67490475e86f53.65930580', 2, 'CJ', 21, 'P.E', 'Medium', 1, 250.00, '2024-11-29 08:01:57'),
(46, '67490475e91a17.63208512', 2, 'CJ', 20, 'P.E', 'Small', 1, 250.00, '2024-11-29 08:01:57'),
(47, '67490475e9e217.73257849', 2, 'CJ', 19, 'Arnis padding', 'Long', 1, 50.00, '2024-11-29 08:01:57'),
(51, '674925c897a353.89555822', 2, 'CJ', 9, 'Jogging Pants', 'L', 1, 250.00, '2024-11-29 10:24:08'),
(52, '674925c8989218.51479492', 2, 'CJ', 8, 'Mat', 'Long', 1, 200.00, '2024-11-29 10:24:08'),
(53, '674925c8996804.78276840', 2, 'CJ', 10, 'Shoes', '42', 1, 500.00, '2024-11-29 10:24:08'),
(54, '674925c89a4986.18601302', 2, 'CJ', 11, 'Glove', 'XL', 1, 50.00, '2024-11-29 10:24:08'),
(55, '674925c89b3884.82501102', 2, 'CJ', 19, 'Arnis padding', 'Long', 1, 50.00, '2024-11-29 10:24:08'),
(58, '6749469c57efd6.62966742', 2, 'CJ', 9, 'Jogging Pants', 'L', 1, 250.00, '2024-11-29 12:44:12'),
(59, '674948f6e62439.88868937', 2, 'CJ', 11, 'Glove', 'XL', 1, 50.00, '2024-11-29 12:54:14');

-- --------------------------------------------------------

--
-- Table structure for table `customer_orders`
--

CREATE TABLE `customer_orders` (
  `id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('paid','unpaid') DEFAULT 'unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `customer_orders`
--

INSERT INTO `customer_orders` (`id`, `customer_id`, `customer_name`, `total_amount`, `order_date`, `created_at`, `status`) VALUES
(1, 1, 'Customer1', 250.00, '2024-11-17 19:37:03', '2024-11-17 19:43:14', 'paid'),
(2, 1, 'Customer1', 1050.00, '2024-11-17 19:37:20', '2024-11-17 19:43:14', 'paid'),
(3, 2, 'CJ', 500.00, '2024-11-17 20:16:32', '2024-11-17 20:16:32', 'paid'),
(4, 2, 'CJ', 500.00, '2024-11-17 22:04:40', '2024-11-17 22:04:40', 'paid'),
(5, 2, 'CJ', 500.00, '2024-11-17 22:29:55', '2024-11-17 22:29:55', 'paid'),
(6, 2, 'CJ', 1750.00, '2024-11-17 22:41:42', '2024-11-17 22:41:42', 'paid'),
(7, 2, 'CJ', 550.00, '2024-11-17 22:43:33', '2024-11-17 22:43:33', 'unpaid'),
(8, 2, 'CJ', 550.00, '2024-11-17 22:43:42', '2024-11-17 22:43:42', 'paid'),
(9, 2, 'CJ', 1500.00, '2024-11-17 22:43:48', '2024-11-17 22:43:48', 'paid'),
(10, 2, 'CJ', 500.00, '2024-11-28 22:43:15', '2024-11-28 22:43:15', 'paid'),
(11, 2, 'CJ', 350.00, '2024-11-29 02:45:06', '2024-11-29 02:45:06', 'unpaid'),
(12, 2, 'CJ', 250.00, '2024-11-29 02:52:43', '2024-11-29 02:52:43', 'unpaid'),
(13, 2, 'CJ', 250.00, '2024-11-29 02:53:55', '2024-11-29 02:53:55', 'unpaid'),
(14, 2, 'CJ', 500.00, '2024-11-29 03:08:45', '2024-11-29 03:08:45', 'unpaid'),
(15, 2, 'CJ', 200.00, '2024-11-29 04:03:12', '2024-11-29 04:03:12', 'paid');

-- --------------------------------------------------------

--
-- Table structure for table `inventory`
--

CREATE TABLE `inventory` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `barcode` varchar(100) NOT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `item_code` varchar(50) NOT NULL,
  `size` varchar(20) NOT NULL,
  `image` varchar(255) NOT NULL,
  `year_level` varchar(30) NOT NULL,
  `category` varchar(255) NOT NULL,
  `description` varchar(225) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inventory`
--

INSERT INTO `inventory` (`id`, `name`, `barcode`, `quantity`, `price`, `item_code`, `size`, `image`, `year_level`, `category`, `description`) VALUES
(7, 'College', '12345678990', 500, 270.00, '00987654321', 'XS', '../uploads/sus.jpg', 'College', 'PE Upper', 'Physical Education - NORMI Colleges'),
(8, 'Mat', '5252525212', 500, 200.00, '6456765785', 'Long', '../uploads/mattttt.jpg', '', 'Equipment', ''),
(9, 'Jogging Pants', '993532525', 500, 250.00, '85235251234', 'L', '../uploads/jogging pants.jpg', 'Junior High School', 'PE Lower', 'Physical Education - NORMI Colleges'),
(10, 'Shoes', '6653253536', 499, 500.00, '7742445756', '42', '../uploads/school shoes.jpg', '', 'Foot Wear', ''),
(11, 'Glove', '4467642424536', 500, 50.00, '5565635242425', 'XL', '../uploads/Glove.jpg', '', 'Equipment', ''),
(19, 'Arnis padding', '49614521465', 500, 50.00, '54415613165', 'Long', '671fae1fbba7c_arnis.jpg', '', 'Equipment', ''),
(20, 'P.E', '2266465123165', 497, 250.00, '3377512354332', 'Small', '672765cd019d9_sus.jpg', 'College', 'PE Upper', ''),
(21, 'P.E', '0033556654', 214, 250.00, '1122336658', 'Medium', '672773792bff9_sus.jpg', 'College', 'PE Upper', ''),
(22, 'Jogging Pants', '2345678998764354', 505, 300.00, '23467885754653134', 'Medium', '6728dfeea4b76_jogging pants.jpg', 'College', 'PE Lower', ''),
(23, 'P.E', '54643413', 497, 250.00, '34123431', 'Medium', '672e19c33cdf1_sus.jpg', 'Senior High School', 'PE Upper', ''),
(24, 'P.E', '4945165135', 497, 250.00, '3535321323', 'Small', '672e1a22849a4_sus.jpg', 'Junior High School', 'PE Upper', ''),
(25, 'Jogging Pants', '49841123', 500, 250.00, '128532982', 'M', '67601037e0c00_jogging pants.jpg', 'Junior High School', '', '');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) NOT NULL,
  `item_name` varchar(255) NOT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `received_status` enum('received','not_received') DEFAULT 'not_received'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `item_name`, `size`, `quantity`, `price`, `received_status`) VALUES
(1, 1, 'Jogging Pants', 'L', 1, 250.00, 'received'),
(2, 2, 'P.E', 'Medium', 1, 250.00, 'not_received'),
(3, 2, 'Jogging Pants', 'Medium', 1, 300.00, 'not_received'),
(4, 2, 'P.E', 'Medium', 1, 250.00, 'not_received'),
(5, 2, 'P.E', 'Small', 1, 250.00, 'not_received'),
(6, 3, 'Arnis padding', 'Long', 2, 50.00, 'not_received'),
(7, 3, 'Mat', 'Long', 2, 200.00, 'not_received'),
(8, 4, 'P.E', 'Small', 1, 250.00, 'not_received'),
(9, 4, 'P.E', 'Medium', 1, 250.00, 'not_received'),
(10, 5, 'P.E', 'XS', 1, 250.00, 'not_received'),
(11, 5, 'P.E', 'Small', 1, 250.00, 'not_received'),
(12, 6, 'P.E', 'Medium', 3, 250.00, 'not_received'),
(13, 6, 'P.E', 'Medium', 2, 250.00, 'not_received'),
(14, 6, 'P.E', 'Small', 2, 250.00, 'not_received'),
(15, 7, 'Jogging Pants', 'Medium', 1, 300.00, 'not_received'),
(16, 7, 'P.E', 'Medium', 1, 250.00, 'not_received'),
(17, 8, 'Shoes', '42', 1, 500.00, 'not_received'),
(18, 8, 'Glove', 'XL', 1, 50.00, 'not_received'),
(19, 9, 'P.E', 'XS', 3, 250.00, 'received'),
(20, 9, 'Jogging Pants', 'L', 3, 250.00, 'received'),
(21, 10, 'P.E', 'Small', 1, 250.00, 'received'),
(22, 10, 'P.E', 'Medium', 1, 250.00, 'received'),
(23, 11, 'Arnis padding', 'Long', 1, 50.00, 'not_received'),
(24, 11, 'Jogging Pants', 'Medium', 1, 300.00, 'not_received'),
(25, 12, 'Jogging Pants', 'L', 1, 250.00, 'not_received'),
(26, 13, 'P.E', 'Small', 1, 250.00, 'received'),
(27, 14, 'Shoes', '42', 1, 500.00, 'received'),
(28, 15, 'Mat', 'Long', 1, 200.00, 'received');

-- --------------------------------------------------------

--
-- Table structure for table `price_history`
--

CREATE TABLE `price_history` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `old_price` decimal(10,2) NOT NULL,
  `new_price` decimal(10,2) NOT NULL,
  `change_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `changed_by` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `return_item`
--

CREATE TABLE `return_item` (
  `return_id` int(11) NOT NULL,
  `customer_name` varchar(100) DEFAULT NULL,
  `return_date` date NOT NULL,
  `item_name` varchar(100) DEFAULT NULL,
  `item_code` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `return_item`
--

INSERT INTO `return_item` (`return_id`, `customer_name`, `return_date`, `item_name`, `item_code`) VALUES
(1, 'CJ', '2024-11-08', 'P.E', '00987654321'),
(2, 'Chamber', '2024-11-08', 'P.E', '00987654321'),
(6, 'Justine', '2024-11-08', 'P.E', '00987654321'),
(7, 'Bianca', '2024-11-08', 'P.E', '1122336658'),
(8, 'Angkol Bagol', '2024-11-08', 'P.E', '1122336658'),
(9, 'nov', '2024-11-08', 'Jogging Pants', '23467885754653134'),
(10, 'Justine', '2024-11-08', 'P.E', '00987654321'),
(11, 'Siya', '2024-11-08', 'P.E', '1122336658');

-- --------------------------------------------------------

--
-- Table structure for table `sales`
--

CREATE TABLE `sales` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity_sold` int(11) NOT NULL DEFAULT 1,
  `sale_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sales`
--

INSERT INTO `sales` (`id`, `product_id`, `quantity_sold`, `sale_date`) VALUES
(7, 8, 5, '2024-10-27 14:49:11'),
(8, 9, 2, '2024-10-27 14:53:54'),
(9, 7, 10, '2024-10-27 14:56:12'),
(10, 8, 0, '2024-10-27 15:42:12'),
(11, 11, 0, '2024-10-27 15:42:17'),
(12, 11, 5, '2024-10-27 15:53:04'),
(13, 19, 2, '2024-10-28 16:35:48'),
(14, 7, 1, '2024-10-28 16:57:58'),
(15, 8, 1, '2024-10-28 16:58:08'),
(16, 7, 2, '2024-11-03 13:43:07'),
(17, 7, 2, '2024-11-03 13:44:52'),
(18, 7, 3, '2024-11-03 13:47:30'),
(19, 7, 3, '2024-11-03 13:48:08'),
(20, 7, 3, '2024-11-03 13:49:46'),
(21, 7, 4, '2024-11-03 13:54:00'),
(22, 9, 1, '2024-11-04 13:08:44'),
(23, 7, 1, '2024-11-04 13:09:10'),
(24, 7, 1, '2024-11-04 13:16:59'),
(25, 7, 1, '2024-11-04 13:44:41'),
(26, 7, 1, '2024-11-04 13:47:41'),
(27, 21, 1, '2024-11-04 13:49:59'),
(28, 7, 1, '2024-11-04 13:50:26'),
(29, 7, 1, '2024-11-04 13:51:18'),
(30, 7, 1, '2024-11-04 14:50:09'),
(31, 21, 1, '2024-11-04 14:50:33'),
(32, 22, 1, '2024-11-04 14:54:00'),
(33, 7, 1, '2024-11-04 14:58:37'),
(34, 7, 1, '2024-11-04 15:02:06'),
(35, 21, 1, '2024-11-04 15:02:34'),
(36, 7, 1, '2024-11-04 15:05:21'),
(37, 20, 1, '2024-11-04 15:07:09'),
(38, 20, 2, '2024-11-04 15:48:42'),
(39, 20, 1, '2024-11-04 15:52:29'),
(40, 21, 1, '2024-11-04 15:53:02'),
(41, 7, 3, '2024-11-04 15:53:17'),
(42, 21, 1, '2024-11-07 14:37:37'),
(43, 20, 1, '2024-11-07 14:38:45'),
(44, 20, 1, '2024-11-07 14:39:43'),
(45, 7, 2, '2024-11-08 04:24:52');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `purchase_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `customer_name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `size` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `item_id`, `quantity`, `total_price`, `purchase_date`, `customer_name`, `description`, `size`) VALUES
(7, 8, 5, 1000.00, '2024-10-27 14:49:11', 'Nico', 'Practice', NULL),
(8, 9, 2, 500.00, '2024-10-27 14:53:54', 'Bianca', 'wala lang', NULL),
(9, 7, 10, 2500.00, '2024-10-27 14:56:12', 'Siya', 'para sa mga bata', NULL),
(10, 11, 5, 250.00, '2024-10-27 15:53:04', 'Bianca', 'gamiton', NULL),
(11, 19, 2, 100.00, '2024-10-28 16:35:48', 'CJ', 'Practice', NULL),
(12, 7, 1, 250.00, '2024-10-28 16:57:58', 'Skusta', 'wala lang', NULL),
(13, 8, 1, 200.00, '2024-10-28 16:58:08', 'Chamber', 'Practice', NULL),
(14, 7, 2, 500.00, '2024-11-03 13:43:07', 'nov', 'wala lang', NULL),
(15, 7, 2, 500.00, '2024-11-03 13:44:52', 'Novelyn', 'wala lang', NULL),
(16, 7, 3, 750.00, '2024-11-03 13:49:46', 'Justine', 'Practice', 'M'),
(17, 7, 4, 1000.00, '2024-11-03 13:54:00', 'Justine', 'gamiton', 'M'),
(18, 9, 1, 250.00, '2024-11-04 13:08:44', 'nov', 'wala lang', 'L'),
(19, 7, 1, 250.00, '2024-11-04 13:09:10', 'nov', 'wala lang', 'M'),
(20, 7, 1, 250.00, '2024-11-04 13:16:59', 'nov', 'gamiton', 'S'),
(21, 7, 1, 250.00, '2024-11-04 13:44:41', 'nov', 'Project', 'S'),
(22, 7, 1, 250.00, '2024-11-04 13:47:41', 'Bianca', 'wala lang', 'S'),
(23, 21, 1, 250.00, '2024-11-04 13:49:59', 'ako', 'oo', 'M'),
(24, 7, 1, 250.00, '2024-11-04 13:50:26', 'CJ', 'yes', 'S'),
(25, 7, 1, 250.00, '2024-11-04 13:51:18', 'nov', 'pp', 'S'),
(26, 7, 1, 250.00, '2024-11-04 14:50:09', 'Last', 'wala lang', 'S'),
(27, 21, 1, 250.00, '2024-11-04 14:50:33', 'lastt', 'wala lang', 'M'),
(28, 22, 1, 300.00, '2024-11-04 14:54:00', 'oowwss', 'www', 'Medium'),
(29, 7, 1, 250.00, '2024-11-04 14:58:37', 'CJ', 'tryan', 'S'),
(30, 7, 1, 250.00, '2024-11-04 15:02:06', 'CJ', 'tryan', 'S'),
(31, 21, 1, 250.00, '2024-11-04 15:02:34', 'CJ', 'dd', 'M'),
(32, 7, 1, 250.00, '2024-11-04 15:05:21', 'CJ', 'now', 'S'),
(33, 20, 1, 250.00, '2024-11-04 15:07:09', 'CJ', 'ww', 'Small'),
(34, 20, 2, 500.00, '2024-11-04 15:48:42', 'CJ', 'yes finaly', 'Small'),
(35, 20, 1, 250.00, '2024-11-04 15:52:29', 'CJ', 'hayts salamat', 'Small'),
(36, 21, 1, 250.00, '2024-11-04 15:53:02', 'CJ', 'dada', 'Medium'),
(37, 7, 3, 750.00, '2024-11-04 15:53:17', 'CJ', 'adad', 'XS'),
(38, 21, 1, 250.00, '2024-11-07 14:37:37', 'CJ', '', 'Medium'),
(39, 20, 1, 250.00, '2024-11-07 14:38:45', 'CJ', '', 'Small'),
(40, 20, 1, 250.00, '2024-11-07 14:39:43', 'CJ', '', 'Small'),
(41, 7, 2, 500.00, '2024-11-08 04:24:52', 'ako', 'new', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `customer_id` int(255) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `customer_id`, `username`, `password`) VALUES
(1, 123, 'user', '$2y$10$jkf7boU/zPhrzjApRl18qu3UwL5Z.aK5kqaPoOCX2XZxHYY5HfpFi');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `cashier`
--
ALTER TABLE `cashier`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_cart`
--
ALTER TABLE `customer_cart`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `inventory`
--
ALTER TABLE `inventory`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `barcode` (`barcode`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `price_history`
--
ALTER TABLE `price_history`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `return_item`
--
ALTER TABLE `return_item`
  ADD PRIMARY KEY (`return_id`);

--
-- Indexes for table `sales`
--
ALTER TABLE `sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `cashier`
--
ALTER TABLE `cashier`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `customer_cart`
--
ALTER TABLE `customer_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=60;

--
-- AUTO_INCREMENT for table `customer_orders`
--
ALTER TABLE `customer_orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `inventory`
--
ALTER TABLE `inventory`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `price_history`
--
ALTER TABLE `price_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `return_item`
--
ALTER TABLE `return_item`
  MODIFY `return_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `sales`
--
ALTER TABLE `sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `customer_orders`
--
ALTER TABLE `customer_orders`
  ADD CONSTRAINT `customer_orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `customer_orders` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `sales`
--
ALTER TABLE `sales`
  ADD CONSTRAINT `sales_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `transactions`
--
ALTER TABLE `transactions`
  ADD CONSTRAINT `transactions_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `inventory` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
