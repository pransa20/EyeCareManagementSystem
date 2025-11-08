-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jun 08, 2025 at 05:29 AM
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
-- Database: `eye_care_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `access_level` enum('super','normal') DEFAULT 'normal',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
(14, 119, 123, '2025-04-26', '11:00:00', 'pending', 'eye', '2025-04-21 05:22:59'),
(15, NULL, 104, '2025-06-10', '15:00:00', 'pending', '', '2025-06-07 14:05:31'),
(16, NULL, 104, '2025-06-10', '10:00:00', 'pending', '', '2025-06-07 14:15:41'),
(17, NULL, 101, '2025-06-17', '10:00:00', 'pending', '', '2025-06-07 14:36:00');

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
-- Table structure for table `customers`
--

CREATE TABLE `customers` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `shipping_address` text DEFAULT NULL,
  `billing_address` text DEFAULT NULL,
  `preferred_payment_method` varchar(50) DEFAULT NULL,
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
(106, 123, 'Pediatric Opthalmology', NULL, '2025-04-21 00:11:44', '2025-06-07 14:34:02', NULL),
(107, 127, 'Binocular Vision and Vision Therapy Specialist', NULL, '2025-06-07 16:10:00', '2025-06-07 16:10:00', NULL);

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
-- Table structure for table `doctor_schedules`
--

CREATE TABLE `doctor_schedules` (
  `id` int(11) NOT NULL,
  `doctor_id` int(11) NOT NULL,
  `day_of_week` tinyint(4) NOT NULL COMMENT '0=Sunday, 1=Monday, ..., 6=Saturday',
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `is_available` tinyint(1) DEFAULT 1
) ;

--
-- Dumping data for table `doctor_schedules`
--

INSERT INTO `doctor_schedules` (`id`, `doctor_id`, `day_of_week`, `start_time`, `end_time`, `break_start`, `break_end`, `is_available`) VALUES
(1, 102, 1, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(2, 102, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(3, 102, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(4, 102, 4, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(5, 102, 5, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(6, 104, 1, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(7, 104, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(8, 104, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(9, 104, 4, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(10, 104, 5, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(11, 101, 0, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(12, 101, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(13, 101, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(14, 101, 4, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(15, 101, 5, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(16, 105, 1, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(17, 105, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(18, 105, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(19, 105, 4, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(20, 105, 5, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(21, 106, 1, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(22, 106, 2, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(23, 106, 3, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(24, 106, 4, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1),
(25, 106, 5, '09:00:00', '17:00:00', '13:00:00', '14:00:00', 1);

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
(3, 5, 101, '2025-04-21', 'fsgd', 'xc', 'df', 'xncm', '2025-04-20 23:40:33', '2025-04-20 23:40:33');

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
-- Table structure for table `patients`
--

CREATE TABLE `patients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medical_history` text DEFAULT NULL,
  `insurance_info` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
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
(43, 'Test', 'test', 400.00, 40, '', 'frames', '2025-04-20 23:42:44', NULL),
(46, 'Black and Gold Eyeglasses', 'A hybrid design combining black upper rims and gold lower rims, giving a chic and professional look.\r\nShape: Slightly cat-eye or rounded-square', 999.00, 15, 'uploads/products/684467a2ee9be.jpg', NULL, '2025-06-07 16:24:02', 1);

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

--
-- Dumping data for table `shopping_cart`
--

INSERT INTO `shopping_cart` (`id`, `user_id`, `product_id`, `quantity`, `created_at`, `updated_at`) VALUES
(1, 127, 46, 1, '2025-06-07 17:14:56', '2025-06-07 17:14:56');

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
(1, 'admin@admin.com', '$2y$10$vYbljeSrHFxm5.iy6P40YuTUbmQrXKx/OUEeo4OVGpOm7FDPC/LVS', 'admin', 'Admin', '', '2025-03-26 16:45:13', 1, NULL, 'active'),
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
(127, 'shriya@hotmail.com', '$2y$10$oTMNPK4w1DpViXHWxOsMROJMkJcpPY7qTXpqhL8CGHn4ydFqsbEeO', 'doctor', 'Dr. Shriya Karmacharya', '9850358945', '2025-06-07 16:10:00', 0, NULL, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `website_content`
--

CREATE TABLE `website_content` (
  `id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `content` text DEFAULT NULL,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `customers`
--
ALTER TABLE `customers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
-- Indexes for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `doctor_id` (`doctor_id`);

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
-- Indexes for table `patients`
--
ALTER TABLE `patients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

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
  ADD KEY `idx_section_name` (`section_name`);

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
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `customers`
--
ALTER TABLE `customers`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT for table `doctor_departments`
--
ALTER TABLE `doctor_departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medical_records`
--
ALTER TABLE `medical_records`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT for table `patients`
--
ALTER TABLE `patients`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=48;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=128;

--
-- AUTO_INCREMENT for table `website_content`
--
ALTER TABLE `website_content`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `admins`
--
ALTER TABLE `admins`
  ADD CONSTRAINT `admins_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `customers`
--
ALTER TABLE `customers`
  ADD CONSTRAINT `customers_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
-- Constraints for table `doctor_schedules`
--
ALTER TABLE `doctor_schedules`
  ADD CONSTRAINT `doctor_schedules_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctors` (`id`);

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
-- Constraints for table `patients`
--
ALTER TABLE `patients`
  ADD CONSTRAINT `patients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

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
