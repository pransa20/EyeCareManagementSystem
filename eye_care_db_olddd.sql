-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 10:12 AM
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
-- Database: `eye_care_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) DEFAULT NULL,
  `doctor_id` int(11) DEFAULT NULL,
  `appointment_date` date NOT NULL,
  `appointment_time` time NOT NULL,
  `status` enum('pending','confirmed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`id`, `patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`) VALUES
(1, 5, NULL, '2025-04-18', '10:00:00', 'pending', 'I want appointment\r\n', '2025-04-17 16:31:40'),
(2, 5, NULL, '2025-04-18', '12:00:00', 'pending', 'fajhfa', '2025-04-17 16:38:18'),
(3, 5, NULL, '2025-04-22', '15:00:00', 'pending', 'I want appointment\r\n', '2025-04-17 16:45:27'),
(4, 5, NULL, '2025-04-30', '16:00:00', 'pending', 'I want appointment\r\n', '2025-04-17 16:45:46'),
(5, NULL, 104, '2025-04-21', '14:00:00', 'pending', 'gd', '2025-04-19 17:01:20'),
(6, NULL, 101, '2025-05-08', '12:00:00', 'pending', 'dergt', '2025-04-19 17:03:29'),
(7, 2, 101, '2025-04-21', '00:00:00', 'pending', 'severe eye problem', '2025-04-20 22:33:55'),
(8, 2, 101, '2025-04-21', '00:00:00', 'confirmed', 'severe eye problem', '2025-04-20 22:33:59'),
(9, 2, 101, '2025-04-21', '00:00:00', 'pending', 'severe eye problem', '2025-04-20 22:34:02'),
(10, 2, 101, '2025-04-21', '00:00:00', 'confirmed', 'severe eye problem', '2025-04-20 22:34:05'),
(11, 5, 104, '2025-04-23', '10:00:00', 'pending', 'adgfg', '2025-04-20 23:03:03'),
(12, 125, 101, '2025-04-24', '12:00:00', 'pending', '', '2025-04-21 01:35:12'),
(13, 119, 123, '2025-04-23', '09:00:00', 'confirmed', 'Eye Nerves', '2025-04-21 02:30:58'),
(14, 119, 123, '2025-04-26', '11:00:00', 'confirmed', 'eye', '2025-04-21 05:22:59'),
(15, 119, 101, '2025-04-25', '10:00:00', 'pending', 'lkjhgbvc', '2025-04-23 07:15:21'),
(16, 119, 102, '2025-04-29', '15:00:00', 'pending', 'hy', '2025-04-23 07:18:16');

-- --------------------------------------------------------

--
-- Table structure for table `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `variation_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `doctors`
--

CREATE TABLE `doctors` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `specialization` varchar(100) NOT NULL,
  `schedule` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `doctors`
--

INSERT INTO `doctors` (`id`, `user_id`, `specialization`, `schedule`, `created_at`, `updated_at`, `description`) VALUES
(101, 101, 'General Ophthalmology', 'Sun-Fri 10am-5pm', '2025-04-19 09:32:25', '2025-04-19 10:11:07', 'General Ophthalmology - Comprehensive eye care including routine exams and treatment of eye diseases.'),
(102, 102, 'Ocular Prosthetics', 'Mon-Thurs 12am-3pm', '2025-04-19 09:32:25', '2025-04-19 10:11:07', 'Ocular Prosthetics - Specializing in the fitting and maintenance of artificial eyes.'),
(104, 104, 'Retina Specialist', 'Mon-Fri 11am-3pm', '2025-04-19 09:32:25', '2025-04-19 10:11:07', 'Retina Specialist - Focused on diagnosing and treating diseases of the retina and vitreous.'),
(105, 105, 'Glaucoma Management', 'Wed-Fri 10am-4pm', '2025-04-19 09:32:25', '2025-04-19 10:11:07', 'Glaucoma Management - Monitoring and treating glaucoma to prevent vision loss.'),
(106, 123, 'Glaucoma Management', NULL, '2025-04-21 00:11:44', '2025-04-21 00:11:44', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `doctor_departments`
--

CREATE TABLE `doctor_departments` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medical_records`
--

CREATE TABLE `medical_records` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `visit_date` date NOT NULL,
  `diagnosis` text NOT NULL,
  `prescription` text DEFAULT NULL,
  `test_results` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `medical_records`
--

INSERT INTO `medical_records` (`id`, `patient_id`, `doctor_id`, `visit_date`, `diagnosis`, `prescription`, `test_results`, `notes`, `created_at`, `updated_at`) VALUES
(3, 5, 101, '2025-04-21', 'fsgd', 'xc', 'df', 'xncm', '2025-04-20 23:40:33', '2025-04-20 23:40:33'),
(4, 119, 123, '2025-04-23', 'Severe dry eye', 'Relub Eye Drops', NULL, 'Visit after 10 days', '2025-04-23 06:43:20', '2025-04-23 06:43:20');

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','paid','shipped','delivered') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `id` int(11) NOT NULL,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `prescriptions`
--

CREATE TABLE `prescriptions` (
  `id` int(11) NOT NULL,
  `patient_id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `appointment_id` int(11) DEFAULT NULL,
  `medication_name` varchar(255) NOT NULL,
  `dosage` varchar(100) NOT NULL,
  `frequency` varchar(100) NOT NULL,
  `duration` varchar(100) NOT NULL,
  `instructions` text DEFAULT NULL,
  `prescribed_date` date NOT NULL,
  `status` enum('active','completed','cancelled') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `stock` int(11) NOT NULL,
  `image_path` varchar(255) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `price`, `stock`, `image_path`, `category`, `created_at`, `category_id`) VALUES
(1, 'Leopard Print Women Eyeglass', 'Leopard Print Prescription Frames for Women', 800.00, 10, 'uploads/products/68034a9335c83.jpeg', 'frames', '2025-04-19 07:02:43', NULL),
(2, 'Black Big Oval Glass', 'Big Oval Prescription Glass For Male', 2000.00, 12, 'uploads/products/68037d179c127.jpeg', 'frames', '2025-04-19 10:38:15', NULL),
(43, 'Test', 'test', 400.00, 40, 'C:\\xampp\\htdocs\\project_eye_care\\uploads\\products680586744a0e3.jpg', 'frames', '2025-04-20 23:42:44', NULL),
(44, 'Test', 'test', 400.00, 40, 'uploads/products/680587472268a.jpg', 'frames', '2025-04-20 23:46:15', NULL),
(45, 'Test', 'test', 400.00, 40, 'uploads/products/6805878454d7c.jpg', 'frames', '2025-04-20 23:47:16', NULL),
(46, 'Trinetra', 'asdasdasdads', 200.00, 3, 'uploads/products/68089a022356b.jpg', 'frames', '2025-04-23 07:42:58', NULL),
(47, 'Trinetra', 'asdasdasdads', 200.00, 3, 'uploads/products/68089af8b166f.jpg', 'frames', '2025-04-23 07:47:04', NULL),
(48, 'Trinetra', 'asdasdasdads', 200.00, 3, 'uploads/products/68089b1d9a0e3.jpg', 'frames', '2025-04-23 07:47:41', NULL),
(49, 'Trinetra', 'asdasdasdads', 200.00, 3, 'uploads/products/68089ede4ea07.jpg', 'frames', '2025-04-23 08:03:42', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `product_categories`
--

CREATE TABLE `product_categories` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `description` text DEFAULT NULL,
  `slug` varchar(50) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_categories`
--

INSERT INTO `product_categories` (`id`, `name`, `description`, `slug`, `is_active`, `created_at`) VALUES
(1, 'Eyeglasses', 'Prescription and fashion eyeglasses', 'eyeglasses', 1, '2025-03-27 04:16:15'),
(2, 'Sunglasses', 'UV protection and fashion sunglasses', 'sunglasses', 1, '2025-03-27 04:16:15'),
(3, 'Contact Lenses', 'Daily, monthly, and colored contact lenses', 'contact-lenses', 1, '2025-03-27 04:16:15'),
(4, 'Accessories', 'Eyewear care and maintenance products', 'accessories', 1, '2025-03-27 04:16:15');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_path`, `is_primary`, `created_at`) VALUES
(1, 43, 'C:xampphtdocsproject_eye_careuploadsproducts/680586744a0e3.jpg\r\n', 0, '0000-00-00 00:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `product_reviews`
--

CREATE TABLE `product_reviews` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `review` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `product_variations`
--

CREATE TABLE `product_variations` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `size` varchar(20) DEFAULT NULL,
  `color` varchar(20) DEFAULT NULL,
  `frame_material` varchar(50) DEFAULT NULL,
  `lens_material` varchar(50) DEFAULT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `price` decimal(10,2) NOT NULL,
  `sku` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shopping_cart`
--

CREATE TABLE `shopping_cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','doctor','patient','customer') NOT NULL,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) DEFAULT 0,
  `verification_code` varchar(6) DEFAULT NULL,
  `status` enum('active','inactive') NOT NULL DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `email`, `password`, `role`, `name`, `phone`, `created_at`, `email_verified`, `verification_code`, `status`) VALUES
(1, 'admin@admin.com', '$2y$10$pkag6JriGq1fIujIi3Vnael/apPU4fzgyVVxqmzN2p.N224rfVDQ6', 'admin', 'Admin', '', '2025-03-26 16:45:13', 1, NULL, 'active'),
(2, 'rapidnepal21@gmail.com', '$2y$10$BtQ2B9UKHzCPvMoel.1u2eJ.YXkZCWWirkQmNm7f06gSD9HFHGAv2', 'patient', 'Pramesh Rajkarnikar', '9841986396', '2025-03-27 12:55:39', 0, '823279', 'inactive'),
(4, 'pransaraj1@gmail.com', '$2y$10$vFq5hsL3kN8kO1zSVmS08eyWIFVzBW81EAXjjd5MOoYRycCzwK7TW', 'patient', 'Pranisha', '9874424656', '2025-04-07 04:09:28', 0, '263071', 'active'),
(5, 'pransaraj@gmail.com', '$2y$10$SIDk8JhqSx3tabh4gJx/1.3at.txJV9Rmi6fo2eyApXswjIak8HUW', 'patient', 'Pranisha', '9840040309', '2025-04-07 04:19:01', 1, '819349', 'active'),
(101, 'pratik.sharma@gmail.com', 'pratiksharma', 'doctor', 'Dr. Pratik Sharma', NULL, '2025-04-19 09:32:01', 0, NULL, 'active'),
(102, 'anjali.thapa@example.com', 'anjalithapa', 'doctor', 'Dr. Anjali Thapa', NULL, '2025-04-19 09:32:01', 1, NULL, 'active'),
(104, 'nisha.joshi@example.com', 'nishajoshi', 'doctor', 'Dr. Nisha Joshi', '9840040309', '2025-04-19 09:32:01', 0, NULL, 'active'),
(105, 'suman.koirala@example.com', 'sumankoirala', 'doctor', 'Dr. Suman Koirala', NULL, '2025-04-19 09:32:01', 0, NULL, 'active'),
(114, 'Pranisha', '$2y$10$FBiRTzzOUt7KtIBQosPrVuINncbhYoD4WK77pM.pLWvs0pROc/TxS', 'customer', 'pranisha', '', '2025-04-20 03:09:33', 0, '617454', 'active'),
(115, 'pransa', '$2y$10$zJHtB.EZlM0uBysiL.RWUeO6eRENMpahkKTXWRWZqayKmLt1tId4i', 'customer', 'pranisha', '', '2025-04-20 03:20:22', 0, '180319', 'active'),
(117, 'pramesh', '$2y$10$gPXr3Wo94S5wV1YY6e2J7u9DqZN6BFG3UjcHXs5rreIOQl3gUD6fW', 'customer', 'pramesh', '', '2025-04-20 03:49:56', 0, '276952', 'active'),
(118, 'itsme', '$2y$10$tdiEXFK3zc3amgjdFPNNT.ID8L/6BepZGcHQayVKwMAKxyHG9r9q.', 'customer', 'pranisha', '', '2025-04-20 04:16:21', 0, '793156', 'active'),
(119, 'pranisharajk@gmail.com', '$2y$10$kZ5kxWXRy39njuQg1kKSH.iU4NqLyrdq02BtotDgk0Xe7ACS9J9vW', 'patient', 'pransa', '9767316878', '2025-04-20 23:50:46', 1, '495597', 'active'),
(121, 'Mandira', '$2y$10$lM78WUP25p/QcPg4A2SlXOPnSBG7p2emb8Ll2yjxsO/WPPsskleIK', 'customer', 'Mandira', '', '2025-04-20 23:59:14', 0, '998804', 'active'),
(122, 'purushottam', '$2y$10$xKR74ukrYHwdlhmMpntpce7cowr/WBev1xhSTZORZ8WqGumgRfgWC', 'customer', 'purushottam', '', '2025-04-21 00:09:53', 0, '697509', 'active'),
(123, 'nehagadal@gmail.com', '$2y$10$vbjdX4QPwV1nTvU79xkc4OMGtpr.wDHDVVe0ZlqSuznbDMGO1/wNK', 'doctor', 'Neha Gadal', '9767316878', '2025-04-21 00:11:44', 0, NULL, 'active'),
(125, 'mandirarajkarnikar@gmail.com', '$2y$10$pNVWnaAxTjVEStz28hjD7uNM9wkS/rqzrrzZ16wHeAsfzGKEGslwW', 'customer', 'Mandira', '', '2025-04-21 00:39:53', 0, '437130', 'active'),
(126, 'console1913@gmail.com', '$2y$10$pRbHqKVU0s/HagCqNmMzVO10TDVN3cLWh7aIchYFSnbTyCGNPWV5e', 'patient', 'Niraj Khadka', '9852364658', '2025-04-21 05:24:48', 1, NULL, 'active'),
(127, 'pratiksharma@gmail.com', '$2y$10$dJOr8bkF63kqgKw9ZrD1fupNk3dqC2zSTEO5LgcgWFLosXjBMNAMO', 'doctor', 'Pratik Sharma', '9840193212', '2025-04-23 06:47:55', 0, '509411', 'active');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `website_content`
--

INSERT INTO `website_content` (`id`, `section_name`, `content`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'Help Box', '', 1, '2025-04-19 12:19:42', '2025-04-19 12:19:47');

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `variation_id` (`variation_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Indexes for table `doctors`
--
ALTER TABLE `doctors`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_id` (`user_id`);

--
-- Indexes for table `doctor_departments`
--
ALTER TABLE `doctor_departments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_doctor_department` (`doctor_id`,`department_id`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `category_id` (`category_id`);

--
-- Indexes for table `product_categories`
--
ALTER TABLE `product_categories`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `slug` (`slug`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `product_id` (`product_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `sku` (`sku`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cart_item` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `website_content`
--
ALTER TABLE `website_content`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `section_name` (`section_name`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_wishlist` (`user_id`,`product_id`),
  ADD KEY `product_id` (`product_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctors`
--
ALTER TABLE `doctors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=107;

--
-- AUTO_INCREMENT for table `doctor_departments`
--
ALTER TABLE `doctor_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `prescriptions`
--
ALTER TABLE `prescriptions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

--
-- AUTO_INCREMENT for table `product_categories`
--
ALTER TABLE `product_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `product_reviews`
--
ALTER TABLE `product_reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `product_variations`
--
ALTER TABLE `product_variations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`variation_id`) REFERENCES `product_variations` (`id`);

--
-- Constraints for table `doctors`
--
ALTER TABLE `doctors`
  ADD CONSTRAINT `doctors_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `doctor_departments`
--
ALTER TABLE `doctor_departments`
  ADD CONSTRAINT `doctor_departments_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `doctor_departments_ibfk_2` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medical_records`
--
ALTER TABLE `medical_records`
  ADD CONSTRAINT `medical_records_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medical_records_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `prescriptions`
--
ALTER TABLE `prescriptions`
  ADD CONSTRAINT `prescriptions_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `prescriptions_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `product_categories` (`id`);

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `product_reviews`
--
ALTER TABLE `product_reviews`
  ADD CONSTRAINT `product_reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`),
  ADD CONSTRAINT `product_reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `product_variations`
--
ALTER TABLE `product_variations`
  ADD CONSTRAINT `product_variations_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `shopping_cart`
--
ALTER TABLE `shopping_cart`
  ADD CONSTRAINT `shopping_cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `shopping_cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
