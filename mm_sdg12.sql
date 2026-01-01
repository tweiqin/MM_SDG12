-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 18, 2025 at 11:44 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.1.25

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mm_sdg12`
--

-- --------------------------------------------------------

--
-- Table structure for table `messages`
--

CREATE TABLE `messages` (
  `message_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `sender_id` int(11) UNSIGNED NOT NULL,
  `receiver_id` int(11) UNSIGNED NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `messages`
--

INSERT INTO `messages` (`message_id`, `product_id`, `sender_id`, `receiver_id`, `message`, `created_at`) VALUES
(5, 4, 5, 2, 'hi seller, how to collect?', '2025-10-29 07:00:43'),
(6, 4, 2, 5, 'Just come over our store & pick-up ^^', '2025-10-29 07:01:33'),
(7, 7, 5, 6, 'Hihi', '2025-10-30 09:27:19'),
(8, 7, 6, 5, 'henlo', '2025-10-30 09:29:14'),
(9, 15, 7, 2, 'hello, can give discount?', '2025-11-03 16:19:53'),
(10, 15, 2, 7, 'hi, this is the discounted price ya', '2025-11-03 16:20:28'),
(11, 22, 5, 8, 'hello', '2025-11-21 16:11:56'),
(12, 22, 5, 8, 'hihi', '2025-11-25 08:16:10'),
(13, 22, 8, 5, 'hey', '2025-11-25 08:18:11'),
(14, 22, 5, 8, 'hahaha', '2025-11-25 08:19:13'),
(15, 22, 8, 5, 'hehehehe', '2025-11-25 08:22:10'),
(16, 31, 7, 17, 'hi there', '2025-12-04 06:49:58'),
(17, 32, 7, 18, 'hi sir', '2025-12-13 19:02:49');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int(11) UNSIGNED NOT NULL,
  `buyer_id` int(11) UNSIGNED NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `order_status` enum('received','preparing','ready for pick-up','collected') NOT NULL DEFAULT 'received',
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `buyer_id`, `total_price`, `order_status`, `full_name`, `email`, `phone`, `payment_method`, `created_at`) VALUES
(3, 5, 16.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-29 07:51:37'),
(4, 5, 10.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-29 14:04:15'),
(6, 5, 10.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-30 07:56:25'),
(7, 7, 5.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-10-30 09:36:44'),
(8, 7, 12.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-10-30 09:42:06'),
(24, 5, 30.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-31 05:08:51'),
(30, 5, 20.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-31 05:31:16'),
(31, 5, 16.00, 'preparing', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-31 09:10:49'),
(32, 7, 6.00, 'preparing', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-10-31 09:25:31'),
(33, 7, 22.00, 'received', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-10-31 09:32:48'),
(34, 5, 18.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-31 09:50:10'),
(35, 7, 16.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-10-31 09:53:52'),
(36, 5, 14.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-10-31 16:58:34'),
(37, 7, 18.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-11-03 16:32:48'),
(38, 7, 6.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-11-05 15:41:54'),
(39, 5, 20.00, 'received', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-08 15:33:50'),
(40, 5, 10.00, 'preparing', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-08 15:34:34'),
(41, 7, 4.00, 'collected', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-11-12 18:15:05'),
(42, 7, 30.00, 'ready for pick-up', 'ERIC', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-11-15 19:41:10'),
(43, 5, 14.00, 'collected', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-18 04:58:49'),
(44, 5, 5.00, 'received', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-19 14:35:01'),
(45, 5, 20.00, 'collected', 'weixuan', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-21 16:13:01'),
(46, 5, 30.00, 'preparing', 'TAY WEI XUAN', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-11-25 08:25:45'),
(47, 9, 20.00, 'collected', 'Fish', 'fish@outoo.com', '016-2481528', 'cash_on_delivery', '2025-11-26 15:28:06'),
(48, 7, 12.00, 'collected', 'Cheong', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-12-04 06:50:25'),
(49, 5, 16.00, 'collected', 'weixuan', 'xuantay98@gmail.com', '010-7600532', 'cash_on_delivery', '2025-12-13 16:08:06'),
(50, 7, 20.00, 'collected', 'Sheng', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-12-13 19:00:30'),
(51, 7, 10.00, 'received', 'Cheong Hao Sheng', 'ericcheong19@gmail.com', '012-6378720', 'cash_on_delivery', '2025-12-15 12:31:34'),
(52, 9, 10.00, 'received', 'Fish', 'fish@outoo.com', '6016-4453154', 'cash_on_delivery', '2025-12-15 14:30:23'),
(53, 5, 10.00, 'ready for pick-up', 'Wei Xuan', 'xuantay98@gmail.com', '010-7600532', 'credit-card', '2025-12-18 09:59:11'),
(55, 5, 13.99, 'received', 'Wei Xuan', 'xuantay98@gmail.com', '010-7600532', 'fpx_online_banking', '2025-12-18 10:12:29'),
(56, 5, 12.00, 'received', 'Wei Xuan', 'xuantay98@gmail.com', '010-7600532', 'grab_pay', '2025-12-18 10:13:06'),
(57, 5, 8.00, 'received', 'Xuan', 'xuantay98@gmail.com', '010-7600532', 'credit-card', '2025-12-18 10:14:09');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `item_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `quantity` int(11) UNSIGNED NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`item_id`, `order_id`, `product_id`, `quantity`, `price`) VALUES
(3, 3, 7, 2, 8.00),
(4, 4, 10, 1, 4.00),
(5, 4, 8, 1, 6.00),
(8, 6, 10, 1, 4.00),
(9, 6, 8, 1, 6.00),
(10, 7, 12, 1, 5.00),
(11, 8, 8, 2, 6.00),
(27, 24, 13, 3, 10.00),
(33, 30, 13, 2, 10.00),
(34, 31, 13, 1, 10.00),
(35, 31, 8, 1, 6.00),
(36, 32, 14, 1, 6.00),
(37, 33, 14, 1, 6.00),
(38, 33, 7, 2, 8.00),
(39, 34, 12, 2, 5.00),
(40, 34, 7, 1, 8.00),
(41, 35, 13, 1, 10.00),
(42, 35, 8, 1, 6.00),
(43, 36, 13, 1, 10.00),
(44, 36, 10, 1, 4.00),
(45, 37, 14, 3, 6.00),
(46, 38, 14, 1, 6.00),
(47, 39, 13, 2, 10.00),
(48, 40, 13, 1, 10.00),
(49, 41, 10, 1, 4.00),
(50, 42, 13, 3, 10.00),
(51, 43, 8, 1, 6.00),
(52, 43, 19, 1, 8.00),
(53, 44, 12, 1, 5.00),
(54, 45, 22, 2, 10.00),
(55, 46, 22, 3, 10.00),
(56, 47, 30, 1, 20.00),
(57, 48, 31, 1, 12.00),
(58, 49, 15, 2, 8.00),
(59, 50, 32, 1, 20.00),
(60, 51, 23, 1, 10.00),
(61, 52, 13, 1, 10.00),
(62, 53, 13, 1, 10.00),
(64, 55, 24, 1, 13.99),
(65, 56, 8, 2, 6.00),
(66, 57, 15, 1, 8.00);

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `product_id` int(11) UNSIGNED NOT NULL,
  `seller_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `original_price` decimal(10,2) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `image` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `product_status` enum('Available','Unavailable') NOT NULL DEFAULT 'Available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`product_id`, `seller_id`, `name`, `original_price`, `price`, `category`, `description`, `image`, `quantity`, `created_at`, `product_status`) VALUES
(4, 2, 'Nasi', 8.00, 6.00, 'Hotels', 'fluffyyy', 'apple pie 2.jpg', 3, '2025-10-28 06:54:35', 'Unavailable'),
(7, 6, 'Chicken Box', 9.00, 8.00, 'Hotels', 'Operation hours: 10am - 10pm\r\nPick up time: 8pm', 'chicken.jpg', 10, '2025-10-28 14:56:11', 'Available'),
(8, 2, 'Fish Mystery Box', 15.00, 6.00, 'Hotels', 'Operation hours: 9am - 3pm\r\nPick up time: 12pm', 'fish.jpg', 2, '2025-10-28 14:59:43', 'Available'),
(10, 2, 'Vege Petite', 8.00, 4.00, 'Hotels', 'Operation Hours: 6am - 10am\r\nPick-up Time: 9am', 'petite-vege.jpg', 5, '2025-10-29 06:57:20', 'Available'),
(12, 6, 'Pastry Mystery Box', 12.00, 5.00, 'Hotels', 'Breads and pastries for sale', 'pastry.jpg', 8, '2025-10-30 09:34:11', 'Available'),
(13, 2, 'Hotel Mystery Ticket', 25.00, 10.00, 'Hotels', 'Just head over the hotel', 'buffet.jpg', 5, '2025-10-30 13:40:30', 'Available'),
(14, 6, 'Beef Mystery Box', 8.00, 6.00, 'Hotels', 'Operation Hours: 12pm - 3pm\r\nPick-up time: 2pm', 'beef.jpg', 5, '2025-10-31 04:48:29', 'Available'),
(15, 2, 'Seafood Mystery Box', 6.00, 8.00, 'Resaturants & Cafes', 'Operation Hours: 24/7', 'seafood.jpg', 2, '2025-10-31 09:16:15', 'Available'),
(19, 2, 'Siew Yok', 12.00, 8.00, 'Hotels', 'Pick Up Time: 2pm', 'pork.jpg', 3, '2025-11-15 04:21:03', 'Available'),
(20, 2, 'Vegetarian Friendly', 8.00, 3.00, 'Resaturants & Cafes', 'Ops Hours: 11am - 9pm\r\nPick-up Time: 5pm - 6pm', 'vege.jpg', 2, '2025-11-15 04:24:18', 'Available'),
(22, 8, 'Lamb Box', 15.00, 10.00, 'Resaturants & Cafes', 'Ops Hours: 12:00- 22:00\r\nPick-up Time: 20:00 - 21:00\r\n', 'lamb.jpg', 3, '2025-11-19 16:15:13', 'Available'),
(23, 2, 'Seafood - Petite', 15.99, 10.00, 'Resaturants & Cafes', 'Pick-up Time: 17:00 - 18:00\r\nHalal\r\n', 'spaghetti.jpg', 0, '2025-11-25 14:05:56', 'Unavailable'),
(24, 8, 'Sushi Box', 20.99, 13.99, 'Resaturants & Cafes', 'Ops Hours: 11:00- 21:00\r\njpg for illustration purpose', 'sushi.jpg', 8, '2025-11-25 14:13:15', 'Available'),
(27, 8, 'Sushi Petite Plate', 12.90, 7.99, 'restaurants & cafes', 'Pick-up Time: 20:00 - 21:00\r\nSmaller portion\r\n', 'petite-sushi.jpg', 4, '2025-11-25 15:24:54', 'Available'),
(28, 10, 'Vegetarian Friendly', 23.80, 13.90, 'Hotels', 'Ops Hours: 11:00- 21:00\r\n', 'nasi.jpg', 10, '2025-11-26 07:57:27', 'Available'),
(30, 15, 'Tau Sar Piah', 28.88, 20.00, 'Bakeries', 'Pick-up Time: Anytime', 'sushi.jpg', 2, '2025-11-26 15:08:20', 'Unavailable'),
(31, 17, 'Nigiri Sushi Box', 18.80, 12.00, 'Resaturants & Cafes', 'Ops Hours: 11:30 - 21:30\r\n', 'nigiri.jpg', 3, '2025-12-04 06:34:59', 'Available'),
(32, 18, 'Breakfast Buffet', 45.00, 20.00, 'Hotels', 'Dine-in: 10:30 - 11:30am\r\n', 'ticket.jpg', 8, '2025-12-13 18:05:09', 'Available'),
(33, 15, 'Traditional Pastry Set', 28.80, 18.80, 'Hotels', 'Pick-up: Anytime\r\n', 'tausar.jpg', 5, '2025-12-18 09:04:12', 'Available'),
(34, 19, 'Mystery Box', 15.00, 9.90, 'restaurants & cafes', 'Pick-up Time: 21:00 - 21:30\r\n', 'takoviral.jpg', 6, '2025-12-18 10:37:28', 'Available');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `review_id` int(11) UNSIGNED NOT NULL,
  `order_id` int(11) UNSIGNED NOT NULL,
  `product_id` int(11) UNSIGNED NOT NULL,
  `user_id` int(11) UNSIGNED NOT NULL,
  `rating` tinyint(1) UNSIGNED NOT NULL,
  `review_text` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`review_id`, `order_id`, `product_id`, `user_id`, `rating`, `review_text`, `created_at`) VALUES
(2, 3, 7, 5, 5, 'Nice, recommended', '2025-10-30 09:08:33'),
(3, 4, 10, 5, 4, 'Good', '2025-10-30 09:15:19'),
(4, 4, 8, 5, 3, 'Good', '2025-10-30 09:15:19'),
(5, 6, 10, 5, 2, 'hahaha', '2025-10-30 09:24:36'),
(6, 6, 8, 5, 1, 'xixixi', '2025-10-30 09:24:36'),
(7, 7, 12, 7, 5, 'Wow, amazing!', '2025-10-30 09:38:03'),
(8, 8, 8, 7, 4, 'Yeay! Recommended', '2025-10-30 09:51:07'),
(9, 24, 13, 5, 5, 'Nice, satisfied', '2025-10-31 05:10:45'),
(10, 34, 12, 5, 1, 'hope to improve', '2025-10-31 09:51:10'),
(11, 34, 7, 5, 1, 'Wish to give less', '2025-10-31 09:51:10'),
(12, 35, 13, 7, 5, 'Like it', '2025-10-31 09:54:52'),
(13, 35, 8, 7, 4, 'Suits me', '2025-10-31 09:54:52'),
(14, 36, 13, 5, 5, 'Like it', '2025-10-31 16:59:55'),
(15, 36, 10, 5, 5, 'Suits vegetarian', '2025-10-31 16:59:55'),
(16, 37, 14, 7, 5, 'I like beef so much', '2025-11-03 16:34:35'),
(17, 38, 14, 7, 4, 'Beef still tender, worth it!', '2025-11-05 15:44:10'),
(18, 41, 10, 7, 3, 'Omg I love vege', '2025-11-12 18:16:44'),
(19, 45, 22, 5, 4, 'I am lovin it', '2025-11-21 16:16:35'),
(20, 47, 30, 9, 5, 'Like it!', '2025-11-26 15:29:46'),
(21, 49, 15, 5, 4, 'Nice!', '2025-12-13 16:09:18'),
(22, 50, 32, 7, 5, 'Very worth', '2025-12-13 19:02:13');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) UNSIGNED NOT NULL,
  `name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) NOT NULL,
  `address` text NOT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','seller','buyer') NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `name`, `email`, `phone`, `address`, `latitude`, `longitude`, `password`, `role`, `logo`, `is_active`, `created_at`) VALUES
(1, 'Admin', 'admin@mm.com', '000-000-0000', 'MM Address', NULL, NULL, '$2a$04$eWYu3HhllUPA5vdHzlwbtumDutSZ1LtOVLlo5grxOaLvyXs3tvVdm', 'admin', NULL, 1, '2025-10-25 18:17:34'),
(2, 'CC by MEL', 'ccbymel@gmail.com', '+6010-2868617', 'Residensi Park, 2-13, Persiaran Jalil Utama, 57000 Bukit Jalil, KL.', 3.05335700, 101.67222100, '$2y$10$4n6o076SfnHe4I62v9JXhOzy2oUx5OAW0rTOjADx.XsJYgdeh87Ca', 'seller', 'ccbymel.jpg', 1, '2025-10-25 18:38:17'),
(5, 'Wei Xuan', 'xuantay98@gmail.com', '016-7688532', '381, Jalan Indah, Taman Indah, 84000 Muar, Johor.', NULL, NULL, '$2y$10$bsuqE/Wf2iT3QRtb8haxi.OaQC3oyMlfwi/I.pdUsyx0.RIiShv.S', 'buyer', NULL, 1, '2025-10-27 09:19:15'),
(6, 'Long Chen Toast House', 'longchen96@gmail.com', '+012-7386920', 'Jalan 34/154, Bandar Damai Perdana, 56000 KL.', 3.05772000, 101.74695500, '$2y$10$k6bHGLDYeIKAUteRb5MAl.DqhvMcnLtQdjHqM4lGLINJ9HbSYeKn2', 'seller', 'longchen.jpg', 1, '2025-10-28 07:00:20'),
(7, 'Cheong Hao Sheng', 'ericcheong19@gmail.com', '010-6338320', '13, Jalan BK8/A, 47100 Puchong', NULL, NULL, '$2y$10$WYxRe4OyTUreNR2cKIEdROvMOkwz6UtZDd6tEw0FUkflfWNrIKzJe', 'buyer', NULL, 1, '2025-10-30 09:35:52'),
(8, 'Sakura Sushi', 'sakura@gmail.com', '016-6333131', 'Jalan BK 5/3, Bandar Kinrara, 47180 Subang Jaya City Council, Selangor, Malaysia', 3.04500200, 101.64391000, '$2y$10$PgAPqTjyKPPcDXtfoVhhU.S801P/gwRB/aANZPGFMUtU516nJ8ctW', 'seller', 'sakuras.jpg', 1, '2025-11-19 16:07:45'),
(9, 'Fish', 'fish@outoo.com', '6016-4453154', '17, Jalan Desa 13/4, Taman Desa, 84000 Muar, Johor', NULL, NULL, '$2y$10$TbCXLkAUt21m81SyPuNvZuw9VGWkPufcD4D6aDxVm0RqwLJk9AzN.', 'buyer', NULL, 1, '2025-11-26 07:45:42'),
(10, 'Arin Shop', 'arin@gmail.com', '6011-28809573', 'Jalan TPK 2/8, Taman Perindustrian Kinrara, 47170 Selangor.', 3.04749400, 101.63739600, '$2y$10$d/HWo9ELYM0dWxU6DEkUpOKgi.ystI4tiV0UGTDGiU.wC3sskCxkS', 'seller', 'arin.jpg', 1, '2025-11-26 07:56:41'),
(15, 'Kouping', 'kouping@gmail.com', '+60107869878', 'Zone J, Jalan Radin Bagus 6, Sri Petaling, 57000 KL.', 3.07031800, 101.69310800, '$2y$10$KdEt.QUA.TSga39QOlxhbeCUaJI.msskoINuORi3BYZ7aJ/45w2NO', 'seller', 'huat.jpg', 1, '2025-11-26 15:06:51'),
(16, 'Mee', 'mee@gmail.com', '+60134679021', '6543ertryt', NULL, NULL, '$2y$10$0rMQatdjVE/zg.ENjCYzBujsndDu6Lor/h7uWVp/r/XoIgu/MQXJG', 'buyer', NULL, 1, '2025-11-26 15:51:27'),
(17, 'Shin Zushi', 'shin@gmail.com', '+6014-9509946', 'Jalan Jalil Jaya 7, Bukit Jalil, 57000 Kuala Lumpur, Malaysia.', 3.06012600, 101.67294200, '$2y$10$9YXW8KdwEpXOSRkD7Xn2z.W14w7l9dbClzruij24i012hkp.Xsxbu', 'seller', 'shin.jpg', 1, '2025-12-04 06:24:07'),
(18, 'Hyatt Place KL', 'hyatt@gmail.com', '+60163738958', 'M-1, Pusat Perdagangan Bandar, Persiaran Jalil 1, Bukit Jalil, 57000 KL.', 3.05377900, 101.67090800, '$2y$10$qk2NxV2ilZoV7YNfcFjL9.oYxrN/8lf5twiVTBjn9A6XS68Py9/JC', 'seller', 'hyatt.jpg', 1, '2025-12-13 17:52:26'),
(19, 'Takoyaki Kafe', 'tako@gmail.com', '+601155083930', '15, Jln PUJ 3/9, Tmn Puncak Jalil, Bandar Putra Permai, 43300 Seri Kembangan, Selangor', 3.01400600, 101.67633800, '$2y$10$ahzgL3eQSKXNn4f88MorKO.vP4BEXUc8oCBoXwO1Zoy6bAhTkBXVG', 'seller', 'tako.jpg', 1, '2025-12-18 10:32:11');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `messages`
--
ALTER TABLE `messages`
  ADD PRIMARY KEY (`message_id`),
  ADD KEY `sender_id` (`sender_id`),
  ADD KEY `receiver_id` (`receiver_id`),
  ADD KEY `messages_ibfk_1` (`product_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `buyer_id` (`buyer_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`item_id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_items_ibfk_2` (`product_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`product_id`),
  ADD KEY `seller_id` (`seller_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`review_id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `reviews_ibfk_1` (`product_id`),
  ADD KEY `fk_review_order` (`order_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `messages`
--
ALTER TABLE `messages`
  MODIFY `message_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=58;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `item_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `product_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `review_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `messages`
--
ALTER TABLE `messages`
  ADD CONSTRAINT `messages_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`),
  ADD CONSTRAINT `messages_ibfk_2` FOREIGN KEY (`sender_id`) REFERENCES `users` (`user_id`),
  ADD CONSTRAINT `messages_ibfk_3` FOREIGN KEY (`receiver_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`buyer_id`) REFERENCES `users` (`user_id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`);

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`seller_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `fk_review_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
