-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 21, 2025 at 08:41 PM
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
-- Database: `fatima_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bedroom`
--

CREATE TABLE `bedroom` (
  `id` int(50) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(50) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bedroom`
--

INSERT INTO `bedroom` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(11, 'uploads/1744624198003-256149209.png', '', 'dasads', 'bedroom', '', 200, 'BED-SA-880', '132132', '', 'in-stock', ''),
(12, 'uploads/1738112974139.png', '', 'dasads', 'bedroom', 'bedroom', 123, 'BED-BE-917', '132132', '', 'in-stock', '2025-04-21'),
(13, 'uploads/1738112974139.png', '', 'dasads', 'bedroom', 'bedroom', 123, 'BED-BE-917', '132132', '', 'in-stock', ''),
(14, 'uploads/product1.png', '', 'dasads', 'bedroom', 'sample', 321, 'BED-SA-283', '132132', '', 'in-stock', ''),
(15, 'uploads/product1.png', '', 'dasads', 'bedroom', 'sample', 321, 'BED-SA-283', '132132', '', 'in-stock', ''),
(16, 'uploads/product1.png', '', 'dasads', 'bedroom', 'sample', 321, 'BED-SA-283', '132132', '', 'in-stock', ''),
(17, 'uploads/product1.png', '', 'dasads', 'bedroom', 'sample', 321, 'BED-SA-283', '132132', '', 'in-stock', ''),
(18, 'uploads/product2.png', '', 'sae', 'bedroom', 'sample', 321, 'BED-SA-230', '12', '', 'low-stock', ''),
(19, 'uploads/product2.png', '', 'sae', 'bedroom', 'sample', 321, 'BED-SA-230', '12', '', 'low-stock', ''),
(20, 'uploads/product2.png', '', 'sae', 'bedroom', 'sample', 321, 'BED-SA-230', '12', '', 'low-stock', ''),
(21, 'uploads/officeroom.jpg', '', 'classic bed frame', 'bedroom', 'bedframe', 100, 'BED-BE-465', '123', '', 'in-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `diningroom`
--

CREATE TABLE `diningroom` (
  `id` int(11) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `diningroom`
--

INSERT INTO `diningroom` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(1, 'uploads/product4.png', '', 'dasads', 'diningroom', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(2, 'uploads/product4.png', '', 'dasads', 'diningroom', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(3, 'uploads/product4.png', '', 'dasads', 'diningroom', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(4, 'uploads/product4.png', '', 'dasads', 'diningroom', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `kitchen`
--

CREATE TABLE `kitchen` (
  `id` int(11) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `kitchen`
--

INSERT INTO `kitchen` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(1, 'uploads/product4.png', '', 'dasads', 'kitchen', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(2, 'uploads/product4.png', '', 'dasads', 'kitchen', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(3, 'uploads/product4.png', '', 'dasads', 'kitchen', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(4, 'uploads/product4.png', '', 'dasads', 'kitchen', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `livingroom`
--

CREATE TABLE `livingroom` (
  `id` int(50) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `livingroom`
--

INSERT INTO `livingroom` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(1, 'uploads/product1.png', '', 'dasads', 'livingroom', 'sample', 321, 'LIV-SA-131', '132132', '', 'in-stock', ''),
(2, 'uploads/product1.png', '', 'dasads', 'livingroom', 'sample', 321, 'LIV-SA-131', '132132', '', 'in-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `office`
--

CREATE TABLE `office` (
  `id` int(50) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `office`
--

INSERT INTO `office` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(1, 'uploads/product1.png', '', 'sample', 'office', 'sample', 321, 'OFF-SA-718', '0', '', 'out-of-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` varchar(50) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `order_date` datetime DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `subtotal` varchar(50) NOT NULL,
  `tax` varchar(50) NOT NULL,
  `shipping_cost` varchar(50) NOT NULL,
  `order_status` enum('Pending','To Ship','Shipped','Out for Delivery','Delivered') DEFAULT 'Pending',
  `total_amount` decimal(10,2) DEFAULT NULL,
  `shipping_id` int(11) DEFAULT NULL,
  `tracking_number` varchar(50) DEFAULT NULL,
  `qr_code_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_date`, `payment_method`, `subtotal`, `tax`, `shipping_cost`, `order_status`, `total_amount`, `shipping_id`, `tracking_number`, `qr_code_path`) VALUES
('ORD-680435b5ca2e5', NULL, '2025-04-20 01:45:57', 'Cash On Delivery', '444', '35.52', '10', 'Delivered', 489.52, NULL, 'TRK-60CD3', 'qrcodes/ORD-680435b5ca2e5.png'),
('ORD-68058284cf3ab', NULL, '2025-04-21 01:25:56', 'Cash On Delivery', '123', '9.84', '10', '', 142.84, NULL, NULL, NULL),
('ORD-680582e1130c7', NULL, '2025-04-21 01:27:29', 'Cash On Delivery', '123', '9.84', '10', '', 142.84, NULL, NULL, NULL),
('ORD-680582f67c135', NULL, '2025-04-21 01:27:50', 'Cash On Delivery', '200', '16', '10', '', 226.00, NULL, NULL, NULL),
('ORD-6805853f50792', NULL, '2025-04-21 01:37:35', 'Cash On Delivery', '123', '9.84', '10', '', 142.84, NULL, NULL, NULL),
('ORD-68058541db575', NULL, '2025-04-21 01:37:37', 'Cash On Delivery', '123', '9.84', '10', '', 142.84, NULL, NULL, NULL),
('ORD-6805867413f7e', NULL, '2025-04-21 01:42:44', 'Cash On Delivery', '123', '9.84', '10', 'Delivered', 142.84, NULL, 'TRK-836E8', 'qrcodes/ORD-6805867413f7e.png'),
('ORD-68058713f0780', NULL, '2025-04-21 01:45:23', 'Cash On Delivery', '123', '9.84', '10', 'Delivered', 142.84, NULL, 'TRK-C926F', 'qrcodes/ORD-68058713f0780.png');

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` varchar(50) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `product_name` varchar(255) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `image` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`id`, `order_id`, `product_id`, `product_name`, `quantity`, `price`, `subtotal`, `image`, `category`) VALUES
(6, 'ORD-680435b5ca2e5', 13, 'dasads', 1, 123.00, 123.00, '', '0'),
(7, 'ORD-680435b5ca2e5', 14, 'dasads', 1, 321.00, 321.00, '', '0'),
(8, 'ORD-68058713f0780', 13, 'dasads', 1, 123.00, 123.00, './admin/uploads/1738112974139.png', 'Uncategorized');

-- --------------------------------------------------------

--
-- Table structure for table `outdoor`
--

CREATE TABLE `outdoor` (
  `id` int(11) NOT NULL,
  `image` varchar(60) DEFAULT NULL,
  `itemID` varchar(60) NOT NULL,
  `name` varchar(50) NOT NULL,
  `category` varchar(60) NOT NULL,
  `subCategory` varchar(50) NOT NULL,
  `price` int(11) NOT NULL,
  `sku` varchar(60) NOT NULL,
  `quantity` varchar(60) NOT NULL,
  `unitPrice` varchar(60) NOT NULL,
  `status` varchar(60) NOT NULL,
  `lastUpdate` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `outdoor`
--

INSERT INTO `outdoor` (`id`, `image`, `itemID`, `name`, `category`, `subCategory`, `price`, `sku`, `quantity`, `unitPrice`, `status`, `lastUpdate`) VALUES
(1, 'uploads/product4.png', '', 'dasads', 'outdoor', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(2, 'uploads/product4.png', '', 'dasads', 'outdoor', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(3, 'uploads/product4.png', '', 'dasads', 'outdoor', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', ''),
(4, 'uploads/product4.png', '', 'dasads', 'outdoor', 'sample', 321, 'DIN-SA-985', '132132', '', 'in-stock', '');

-- --------------------------------------------------------

--
-- Table structure for table `shipping`
--

CREATE TABLE `shipping` (
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(60) NOT NULL,
  `phone` varchar(50) NOT NULL,
  `address` varchar(60) NOT NULL,
  `address2` varchar(60) NOT NULL,
  `city` varchar(60) NOT NULL,
  `state` varchar(50) NOT NULL,
  `zip` varchar(60) NOT NULL,
  `country` varchar(50) NOT NULL,
  `id` int(11) NOT NULL,
  `order_id` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `shipping`
--

INSERT INTO `shipping` (`firstname`, `lastname`, `email`, `phone`, `address`, `address2`, `city`, `state`, `zip`, `country`, `id`, `order_id`) VALUES
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 20, 'ORD-680435b5ca2e5'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 21, 'ORD-68058284cf3ab'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 22, 'ORD-680582e1130c7'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 23, 'ORD-680582f67c135'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 24, 'ORD-6805853f50792'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 25, 'ORD-68058541db575'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 26, 'ORD-6805867413f7e'),
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '09451570794', '625 Tramo Santo Cristo', '', 'PULILAN', 'Bulacan', '3005', 'PHP', 27, 'ORD-68058713f0780');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(60) NOT NULL,
  `email` varchar(60) NOT NULL,
  `password` varchar(50) NOT NULL,
  `id` int(11) NOT NULL,
  `is_verified` varchar(10) NOT NULL,
  `verification_code` varchar(20) NOT NULL,
  `verification_expiry` datetime(6) NOT NULL,
  `created_at` varchar(10) NOT NULL,
  `reset_token` varchar(120) NOT NULL,
  `reset_token_expiry` datetime(6) NOT NULL,
  `isAdmin` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`firstname`, `lastname`, `email`, `password`, `id`, `is_verified`, `verification_code`, `verification_expiry`, `created_at`, `reset_token`, `reset_token_expiry`, `isAdmin`) VALUES
('Ariel', 'Labuson', 'ariellabuson08@gmail.com', '0043112b802dd5bc9a3c1419c10978f0', 22, '1', '', '0000-00-00 00:00:00.000000', '2025-04-20', '', '0000-00-00 00:00:00.000000', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bedroom`
--
ALTER TABLE `bedroom`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `diningroom`
--
ALTER TABLE `diningroom`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `kitchen`
--
ALTER TABLE `kitchen`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `livingroom`
--
ALTER TABLE `livingroom`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `office`
--
ALTER TABLE `office`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`);

--
-- Indexes for table `outdoor`
--
ALTER TABLE `outdoor`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `shipping`
--
ALTER TABLE `shipping`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bedroom`
--
ALTER TABLE `bedroom`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `diningroom`
--
ALTER TABLE `diningroom`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `kitchen`
--
ALTER TABLE `kitchen`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `livingroom`
--
ALTER TABLE `livingroom`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `office`
--
ALTER TABLE `office`
  MODIFY `id` int(50) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `outdoor`
--
ALTER TABLE `outdoor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `shipping`
--
ALTER TABLE `shipping`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
