-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 21, 2025 at 09:34 PM
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
-- Database: `wifi`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblannouncements`
--

CREATE TABLE `tblannouncements` (
  `id` int(11) NOT NULL,
  `company_name` varchar(255) NOT NULL,
  `announcement_text` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `announcement_image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblannouncements`
--

INSERT INTO `tblannouncements` (`id`, `company_name`, `announcement_text`, `created_at`, `announcement_image`, `is_visible`) VALUES
(2, 'Menagel SJ', 'hi madlang people', '2025-02-20 12:00:41', '1740410911_download (11).jpg', 0),
(3, 'Menagel SJ', 'helloooo!', '2025-02-20 12:01:09', '1740410901_download (12).jpg', 1),
(4, 'Menagel SJ', 'kagutom oy', '2025-02-20 12:08:43', '1740410919_download (10).jpg', 0),
(7, '', 'himlay na', '2025-03-13 10:49:50', '1741862990_g1.jpg', 1);

-- --------------------------------------------------------

--
-- Table structure for table `tblapplication`
--

CREATE TABLE `tblapplication` (
  `id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text NOT NULL,
  `lname` text NOT NULL,
  `address` text NOT NULL,
  `residenttype` enum('Owner','Rental') NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `billing_proof` varchar(255) NOT NULL,
  `valid_id` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email` varchar(255) NOT NULL,
  `promo_id` int(11) NOT NULL,
  `promo_name` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved') NOT NULL DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblapplication`
--

INSERT INTO `tblapplication` (`id`, `fname`, `mname`, `lname`, `address`, `residenttype`, `mobile`, `billing_proof`, `valid_id`, `created_at`, `email`, `promo_id`, `promo_name`, `status`) VALUES
(19, 'Client', 'C', 'Example', 'Kanto', 'Owner', '092352525252', 'billing_proof_1741255882.jpg', 'valid_id_1741255882.jpg', '2025-03-06 10:11:22', 'idolminminnn@gmail.com', 4, 'Unli Plan 2000', 'Pending');

-- --------------------------------------------------------

--
-- Table structure for table `tblapprove`
--

CREATE TABLE `tblapprove` (
  `id` int(11) NOT NULL,
  `application_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `approval_status` enum('Pending','Approved','Rejected') DEFAULT 'Pending',
  `approved_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `client_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblapprove`
--

INSERT INTO `tblapprove` (`id`, `application_id`, `staff_id`, `approval_status`, `approved_at`, `client_id`) VALUES
(4, 19, 3, 'Approved', '2025-03-06 12:51:45', 0);

-- --------------------------------------------------------

--
-- Table structure for table `tblbilling`
--

CREATE TABLE `tblbilling` (
  `billing_id` int(11) NOT NULL,
  `routernumber` varchar(50) NOT NULL,
  `promo_id` int(11) NOT NULL,
  `amount_due` decimal(10,2) NOT NULL,
  `due_date` date NOT NULL,
  `status` enum('Unpaid','Paid') DEFAULT 'Unpaid'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblbilling`
--

INSERT INTO `tblbilling` (`billing_id`, `routernumber`, `promo_id`, `amount_due`, `due_date`, `status`) VALUES
(6, '000', 2, 1000.00, '2025-04-21', 'Paid'),
(7, '000', 2, 1000.00, '2025-05-21', 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `tblclientlist`
--

CREATE TABLE `tblclientlist` (
  `client_id` int(11) NOT NULL,
  `routernumber` varchar(50) DEFAULT NULL,
  `fname` text NOT NULL,
  `mname` text NOT NULL,
  `lname` text NOT NULL,
  `address` text NOT NULL,
  `residenttype` enum('Owner','Rental') NOT NULL,
  `mobile` varchar(20) NOT NULL,
  `promo_name` varchar(255) DEFAULT NULL,
  `status` enum('Installed','Pending') DEFAULT 'Pending',
  `email` varchar(255) NOT NULL,
  `promo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblclientlist`
--

INSERT INTO `tblclientlist` (`client_id`, `routernumber`, `fname`, `mname`, `lname`, `address`, `residenttype`, `mobile`, `promo_name`, `status`, `email`, `promo_id`) VALUES
(1, NULL, 'Example', 'E', 'Ex', 'Cabuyao', 'Owner', '092352525252', '', 'Installed', 'example@gmail.com', 3),
(6, '000', 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', 'Installed', 'pjarmine000@gmail.com', 2),
(7, NULL, 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', '', 'pjarmine000@gmail.com', 2),
(8, NULL, 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', '', 'pjarmine000@gmail.com', 2),
(9, '054252', 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', 'Installed', 'pjarmine000@gmail.com', 2),
(10, NULL, 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', '', 'pjarmine000@gmail.com', 2),
(11, NULL, 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', 'Installed', 'pjarmine000@gmail.com', 2),
(12, NULL, 'Jarmine Nicole', 'Diaz', 'Perez', 'Santa Rosa City', 'Owner', '092352525252', 'Unli Plan 1000', '', 'pjarmine000@gmail.com', 2),
(13, NULL, 'Client', 'C', 'Example', 'Kanto', 'Owner', '092352525252', 'Unli Plan 2000', '', 'idolminminnn@gmail.com', 4);

-- --------------------------------------------------------

--
-- Table structure for table `tblclient_archive`
--

CREATE TABLE `tblclient_archive` (
  `archive_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `fname` text DEFAULT NULL,
  `mname` text DEFAULT NULL,
  `lname` text DEFAULT NULL,
  `routernumber` varchar(50) DEFAULT NULL,
  `address` text NOT NULL,
  `mobile` varchar(5) NOT NULL,
  `email` varchar(100) NOT NULL,
  `promo_id` varchar(20) DEFAULT NULL,
  `contract_date` date NOT NULL,
  `drop_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblclient_archive`
--

INSERT INTO `tblclient_archive` (`archive_id`, `client_id`, `fname`, `mname`, `lname`, `routernumber`, `address`, `mobile`, `email`, `promo_id`, `contract_date`, `drop_date`) VALUES
(1, 7, 'Jarmine Nicole', 'Diaz', 'Perez', NULL, 'Santa Rosa City', '09235', 'pjarmine000@gmail.com', '2', '2025-02-24', '2025-03-19 10:10:30');

-- --------------------------------------------------------

--
-- Table structure for table `tblinstallations`
--

CREATE TABLE `tblinstallations` (
  `installation_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `status` enum('Pending','Installed') DEFAULT 'Pending',
  `install_date` date DEFAULT NULL,
  `client_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblinstallations`
--

INSERT INTO `tblinstallations` (`installation_id`, `client_id`, `status`, `install_date`, `client_name`) VALUES
(1, 9, 'Installed', '2025-02-24', ''),
(2, 7, 'Installed', '2025-02-24', 'Jarmine Nicole Perez'),
(3, 6, 'Installed', '2025-02-24', 'Jarmine Nicole Perez'),
(4, 11, 'Installed', '2025-02-24', 'Jarmine Nicole Perez'),
(5, 1, 'Installed', '2025-02-24', 'Example Ex');

-- --------------------------------------------------------

--
-- Table structure for table `tblnotifications`
--

CREATE TABLE `tblnotifications` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `is_read` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblpayment`
--

CREATE TABLE `tblpayment` (
  `payment_id` int(11) NOT NULL,
  `routernumber` varchar(50) NOT NULL,
  `payment_date` date NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `updated_by_admin` tinyint(1) DEFAULT 0,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_method` varchar(50) NOT NULL DEFAULT 'Unknown',
  `billing_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpayment`
--

INSERT INTO `tblpayment` (`payment_id`, `routernumber`, `payment_date`, `amount`, `updated_by_admin`, `amount_paid`, `payment_method`, `billing_id`) VALUES
(6, '000', '2025-03-21', 0.00, 0, 1000.00, 'Cash', 6);

-- --------------------------------------------------------

--
-- Table structure for table `tblpromo`
--

CREATE TABLE `tblpromo` (
  `promo_id` int(11) NOT NULL,
  `promo_name` varchar(255) NOT NULL,
  `speed` varchar(50) NOT NULL,
  `amount` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpromo`
--

INSERT INTO `tblpromo` (`promo_id`, `promo_name`, `speed`, `amount`) VALUES
(1, 'Unli Plan 800', 'Up to 20 Mbps', 800.00),
(2, 'Unli Plan 1000', 'Up to 40 Mbps', 1000.00),
(3, 'Unli Plan 1500', 'Up to 70 Mbps', 1500.00),
(4, 'Unli Plan 2000', 'Up to 100 Mbps', 2000.00);

-- --------------------------------------------------------

--
-- Table structure for table `tblpromo_subscribers`
--

CREATE TABLE `tblpromo_subscribers` (
  `id` int(11) NOT NULL,
  `promo_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text DEFAULT NULL,
  `lname` text NOT NULL,
  `email` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblpromo_subscribers`
--

INSERT INTO `tblpromo_subscribers` (`id`, `promo_id`, `client_id`, `fname`, `mname`, `lname`, `email`) VALUES
(1, 3, 0, 'Example', 'E', 'Ex', 'example@gmail.com'),
(3, 2, 6, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(4, 2, 7, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(5, 2, 8, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(6, 2, 9, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(7, 2, 10, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(8, 2, 11, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(9, 2, 12, 'Jarmine Nicole', 'Diaz', 'Perez', 'pjarmine000@gmail.com'),
(10, 4, 13, 'Client', 'C', 'Example', 'idolminminnn@gmail.com');

-- --------------------------------------------------------

--
-- Table structure for table `tblstafflist`
--

CREATE TABLE `tblstafflist` (
  `id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text NOT NULL,
  `lname` text NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `specialization` enum('Technician','Billing','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstafflist`
--

INSERT INTO `tblstafflist` (`id`, `fname`, `mname`, `lname`, `mobile`, `address`, `email`, `specialization`) VALUES
(3, 'Leandro', '', 'Bariat', '092352525252', 'Kanto', 'staff@gmail.com', 'Billing'),
(4, 'Ako', 'Ako', 'Ako', '09764753473', 'Labas', 'ako@gmail.com', 'Technician');

-- --------------------------------------------------------

--
-- Table structure for table `tbltickets`
--

CREATE TABLE `tbltickets` (
  `ticket_id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text DEFAULT NULL,
  `lname` text NOT NULL,
  `mobile` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `routernumber` varchar(50) NOT NULL,
  `concern_type` enum('Technical','Billing','Other') NOT NULL,
  `concern` text NOT NULL,
  `status` enum('Pending','Scheduled','Completed') DEFAULT 'Pending',
  `schedule_date` date DEFAULT NULL,
  `schedule_time` time DEFAULT NULL,
  `assigned_staff` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltickets`
--

INSERT INTO `tbltickets` (`ticket_id`, `fname`, `mname`, `lname`, `mobile`, `address`, `routernumber`, `concern_type`, `concern`, `status`, `schedule_date`, `schedule_time`, `assigned_staff`, `created_at`) VALUES
(1, 'Jarmine Nicole', 'Diaz', 'Perez', '092352525252', 'Santa Rosa City', '000', 'Technical', 'sira cableeeee!!!!', '', '2025-03-20', '10:30:00', 'Leandro Bariat', '2025-02-27 09:21:56'),
(6, 'Jarmine Nicole', 'Diaz', 'Perez', '092352525252', 'Santa Rosa City', '000', 'Billing', 'Already paid, but still not updated.', 'Pending', NULL, NULL, NULL, '2025-03-20 13:12:20');

-- --------------------------------------------------------

--
-- Table structure for table `tblusers`
--

CREATE TABLE `tblusers` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `role` enum('Admin','Staff','Client') NOT NULL DEFAULT 'Client',
  `created_at` date NOT NULL DEFAULT current_timestamp(),
  `verify_code` varchar(6) NOT NULL,
  `is_verified` tinyint(1) DEFAULT 0,
  `reset_token` varchar(255) DEFAULT NULL,
  `reset_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblusers`
--

INSERT INTO `tblusers` (`id`, `email`, `password`, `role`, `created_at`, `verify_code`, `is_verified`, `reset_token`, `reset_expires`) VALUES
(1, 'admin@gmail.com', '$2y$10$JVGmW1W90I9zeb8b62i5gOAe47g4j9E7/8DHCyU9LMVV3TvoLZ5U6', 'Admin', '2025-02-14', '', 0, NULL, NULL),
(11, 'pjarmine000@gmail.com', '$2y$10$.qncspY5qxuvq7680RmGl.doR0ZTP9hCBQPE1iy7wnhX7RPXWE1SC', 'Client', '2025-02-20', '671873', 1, NULL, NULL),
(15, 'ash@gmail.com', '$2y$10$qQ/ZTzyPg38jqfv7ysheMeQGo5rtkHWnL8KKQRFvMb.9a0uXl5kvi', 'Staff', '2025-02-27', '', 0, NULL, NULL),
(16, 'staff@gmail.com', '$2y$10$Ic4NHepwn7/bt4FQXZdHnOmJFJUf/uxCFUXp2DSDznNLOxsjr/Z.S', 'Staff', '2025-02-27', '', 0, NULL, NULL),
(17, 'idolminminnn@gmail.com', '$2y$10$OQ0s2HYgWFIgcYz3fHRGceie5xfKqw9MW33R/akXOJFglK7sQk8Xm', 'Client', '2025-03-06', '552410', 1, '242b7c81f40b804399a488bfa839149a28922c23546312f8a7065691150ff5063d7b56c0f613312de3eabe270857bcc74bb7', '2025-03-20 13:41:14'),
(18, 'ako@gmail.com', '$2y$10$am8X3pYAJDtOr991I4RctugIRoKt0CVpE/Hy0d2UG1um6/Q.mOYfK', 'Staff', '2025-03-19', '', 0, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblannouncements`
--
ALTER TABLE `tblannouncements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tblapplication`
--
ALTER TABLE `tblapplication`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_application_promo` (`promo_id`);

--
-- Indexes for table `tblapprove`
--
ALTER TABLE `tblapprove`
  ADD PRIMARY KEY (`id`),
  ADD KEY `application_id` (`application_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `tblbilling`
--
ALTER TABLE `tblbilling`
  ADD PRIMARY KEY (`billing_id`),
  ADD KEY `promo_id` (`promo_id`),
  ADD KEY `routernumber` (`routernumber`);

--
-- Indexes for table `tblclientlist`
--
ALTER TABLE `tblclientlist`
  ADD PRIMARY KEY (`client_id`),
  ADD UNIQUE KEY `routernumber` (`routernumber`),
  ADD KEY `fk_client_promo` (`promo_id`);

--
-- Indexes for table `tblclient_archive`
--
ALTER TABLE `tblclient_archive`
  ADD PRIMARY KEY (`archive_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `tblinstallations`
--
ALTER TABLE `tblinstallations`
  ADD PRIMARY KEY (`installation_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `tblpayment`
--
ALTER TABLE `tblpayment`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `routernumber` (`routernumber`),
  ADD KEY `fk_billing` (`billing_id`);

--
-- Indexes for table `tblpromo`
--
ALTER TABLE `tblpromo`
  ADD PRIMARY KEY (`promo_id`),
  ADD UNIQUE KEY `promo_name` (`promo_name`);

--
-- Indexes for table `tblpromo_subscribers`
--
ALTER TABLE `tblpromo_subscribers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `promo_id` (`promo_id`),
  ADD KEY `client_id` (`client_id`);

--
-- Indexes for table `tblstafflist`
--
ALTER TABLE `tblstafflist`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `tbltickets`
--
ALTER TABLE `tbltickets`
  ADD PRIMARY KEY (`ticket_id`);

--
-- Indexes for table `tblusers`
--
ALTER TABLE `tblusers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`) USING HASH;

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblannouncements`
--
ALTER TABLE `tblannouncements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblapplication`
--
ALTER TABLE `tblapplication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `tblapprove`
--
ALTER TABLE `tblapprove`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblbilling`
--
ALTER TABLE `tblbilling`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `tblclientlist`
--
ALTER TABLE `tblclientlist`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `tblclient_archive`
--
ALTER TABLE `tblclient_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tblinstallations`
--
ALTER TABLE `tblinstallations`
  MODIFY `installation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblpayment`
--
ALTER TABLE `tblpayment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblpromo`
--
ALTER TABLE `tblpromo`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblpromo_subscribers`
--
ALTER TABLE `tblpromo_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblstafflist`
--
ALTER TABLE `tblstafflist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tbltickets`
--
ALTER TABLE `tbltickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `tblapplication`
--
ALTER TABLE `tblapplication`
  ADD CONSTRAINT `fk_application_promo` FOREIGN KEY (`promo_id`) REFERENCES `tblpromo` (`promo_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblapprove`
--
ALTER TABLE `tblapprove`
  ADD CONSTRAINT `tblapprove_ibfk_1` FOREIGN KEY (`application_id`) REFERENCES `tblapplication` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblapprove_ibfk_2` FOREIGN KEY (`staff_id`) REFERENCES `tblstafflist` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblbilling`
--
ALTER TABLE `tblbilling`
  ADD CONSTRAINT `tblbilling_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `tblpromo` (`promo_id`),
  ADD CONSTRAINT `tblbilling_ibfk_2` FOREIGN KEY (`routernumber`) REFERENCES `tblclientlist` (`routernumber`);

--
-- Constraints for table `tblclientlist`
--
ALTER TABLE `tblclientlist`
  ADD CONSTRAINT `fk_client_promo` FOREIGN KEY (`promo_id`) REFERENCES `tblpromo` (`promo_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblclient_archive`
--
ALTER TABLE `tblclient_archive`
  ADD CONSTRAINT `tblclient_archive_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `tblclientlist` (`client_id`);

--
-- Constraints for table `tblinstallations`
--
ALTER TABLE `tblinstallations`
  ADD CONSTRAINT `tblinstallations_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `tblclientlist` (`client_id`) ON DELETE CASCADE;

--
-- Constraints for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  ADD CONSTRAINT `tblnotifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `tblusers` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `tblpayment`
--
ALTER TABLE `tblpayment`
  ADD CONSTRAINT `fk_billing` FOREIGN KEY (`billing_id`) REFERENCES `tblbilling` (`billing_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `tblpayment_ibfk_1` FOREIGN KEY (`routernumber`) REFERENCES `tblclientlist` (`routernumber`);

--
-- Constraints for table `tblpromo_subscribers`
--
ALTER TABLE `tblpromo_subscribers`
  ADD CONSTRAINT `tblpromo_subscribers_ibfk_1` FOREIGN KEY (`promo_id`) REFERENCES `tblpromo` (`promo_id`),
  ADD CONSTRAINT `tblpromo_subscribers_ibfk_2` FOREIGN KEY (`client_id`) REFERENCES `tblclientlist` (`client_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
