-- Data Migration SQL for Enhanced Eye Care Database

-- Disable foreign key checks before truncating tables
SET FOREIGN_KEY_CHECKS = 0;

-- Truncate tables in reverse order of dependencies
TRUNCATE TABLE `prescriptions`;
TRUNCATE TABLE `medical_records`;
TRUNCATE TABLE `appointments`;
TRUNCATE TABLE `doctor_specializations`;
TRUNCATE TABLE `doctors`;
TRUNCATE TABLE `patients`;
TRUNCATE TABLE `customers`;
TRUNCATE TABLE `admins`;
TRUNCATE TABLE `users`;

-- Re-enable foreign key checks
SET FOREIGN_KEY_CHECKS = 1;

-- Users Data
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

-- Insert admin user into admins table
INSERT INTO `admins` (`user_id`, `name`, `email`, `password`, `access_level`, `created_at`) VALUES
(1, 'Admin', 'admin@admin.com', '$2y$10$pkag6JriGq1fIujIi3Vnael/apPU4fzgyVVxqmzN2p.N224rfVDQ6', 'super', '2025-03-26 16:45:13');

-- Insert sample department
INSERT INTO `departments` (`name`, `description`, `created_at`) VALUES
('General Ophthalmology', 'General eye care and treatment', '2025-04-19 09:32:01'),
('Pediatric Ophthalmology', 'Eye care for children', '2025-04-19 09:32:01'),
('Retina Specialist', 'Retinal diseases and surgery', '2025-04-19 09:32:01');

-- Insert doctors with their departments
INSERT INTO `doctors` (`user_id`, `name`, `email`, `password`, `department_id`, `experience_years`, `qualification`, `schedule`, `description`, `created_at`) VALUES
(101, 'Dr. Pratik Sharma', 'pratik.sharma@gmail.com', 'pratiksharma', 1, 5, 'MD Ophthalmology', 'Monday to Friday, 9 AM - 5 PM', 'Experienced in general eye care', '2025-04-19 09:32:01'),
(102, 'Dr. Anjali Thapa', 'anjali.thapa@example.com', 'anjalithapa', 2, 8, 'MD Ophthalmology, Fellowship in Pediatric Ophthalmology', 'Monday to Thursday, 10 AM - 4 PM', 'Specialized in pediatric eye care', '2025-04-19 09:32:01'),
(104, 'Dr. Nisha Joshi', 'nisha.joshi@example.com', 'nishajoshi', 1, 6, 'MD Ophthalmology', 'Tuesday to Saturday, 9 AM - 5 PM', 'General eye care specialist', '2025-04-19 09:32:01'),
(105, 'Dr. Suman Koirala', 'suman.koirala@example.com', 'sumankoirala', 3, 10, 'MD Ophthalmology, Fellowship in Retina', 'Monday to Friday, 9 AM - 3 PM', 'Retina specialist with extensive experience', '2025-04-19 09:32:01'),
(123, 'Neha Gadal', 'nehagadal@gmail.com', '$2y$10$vbjdX4QPwV1nTvU79xkc4OMGtpr.wDHDVVe0ZlqSuznbDMGO1/wNK', 1, 4, 'MD Ophthalmology', 'Monday to Friday, 10 AM - 6 PM', 'General eye care practitioner', '2025-04-21 00:11:44'),
(127, 'Pratik Sharma', 'pratiksharma@gmail.com', '$2y$10$dJOr8bkF63kqgKw9ZrD1fupNk3dqC2zSTEO5LgcgWFLosXjBMNAMO', 2, 7, 'MD Ophthalmology', 'Monday to Saturday, 9 AM - 5 PM', 'Experienced in pediatric eye care', '2025-04-23 06:47:55');

-- Insert doctor specializations
INSERT INTO `doctor_specializations` (`doctor_id`, `specialization`, `created_at`) VALUES
(1, 'Cataract Surgery', '2025-04-19 09:32:01'),
(1, 'Glaucoma Treatment', '2025-04-19 09:32:01'),
(2, 'Pediatric Eye Surgery', '2025-04-19 09:32:01'),
(2, 'Strabismus Treatment', '2025-04-19 09:32:01'),
(3, 'General Eye Care', '2025-04-19 09:32:01'),
(4, 'Retinal Surgery', '2025-04-19 09:32:01'),
(4, 'Diabetic Retinopathy', '2025-04-19 09:32:01');

-- Insert patients
INSERT INTO `patients` (`user_id`, `name`, `email`, `password`, `created_at`) VALUES
(2, 'Pramesh Rajkarnikar', 'rapidnepal21@gmail.com', '$2y$10$BtQ2B9UKHzCPvMoel.1u2eJ.YXkZCWWirkQmNm7f06gSD9HFHGAv2', '2025-03-27 12:55:39'),
(4, 'Pranisha', 'pransaraj1@gmail.com', '$2y$10$vFq5hsL3kN8kO1zSVmS08eyWIFVzBW81EAXjjd5MOoYRycCzwK7TW', '2025-04-07 04:09:28'),
(5, 'Pranisha', 'pransaraj@gmail.com', '$2y$10$SIDk8JhqSx3tabh4gJx/1.3at.txJV9Rmi6fo2eyApXswjIak8HUW', '2025-04-07 04:19:01'),
(119, 'pransa', 'pranisharajk@gmail.com', '$2y$10$kZ5kxWXRy39njuQg1kKSH.iU4NqLyrdq02BtotDgk0Xe7ACS9J9vW', '2025-04-20 23:50:46'),
(126, 'Niraj Khadka', 'console1913@gmail.com', '$2y$10$pRbHqKVU0s/HagCqNmMzVO10TDVN3cLWh7aIchYFSnbTyCGNPWV5e', '2025-04-21 05:24:48');

-- Insert customers
INSERT INTO `customers` (`user_id`, `created_at`) VALUES
(114, '2025-04-20 03:09:33'),
(115, '2025-04-20 03:20:22'),
(117, '2025-04-20 03:49:56'),
(118, '2025-04-20 04:16:21'),
(121, '2025-04-20 23:59:14'),
(122, '2025-04-21 00:09:53'),
(125, '2025-04-21 00:39:53');

-- Insert appointments
INSERT INTO `appointments` (`patient_id`, `doctor_id`, `appointment_date`, `appointment_time`, `status`, `notes`, `created_at`) VALUES
(1, 1, '2025-04-18', '10:00:00', 'pending', 'I want appointment', '2025-04-17 16:31:40'),
(1, 1, '2025-04-18', '12:00:00', 'pending', 'fajhfa', '2025-04-17 16:38:18');