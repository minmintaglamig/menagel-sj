-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 23, 2025 at 08:41 PM
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
-- Table structure for table `tbladvertisement`
--

CREATE TABLE `tbladvertisement` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `image` varchar(255) DEFAULT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbladvertisement`
--

INSERT INTO `tbladvertisement` (`id`, `title`, `content`, `image`, `is_visible`, `created_at`) VALUES
(7, 'ADS 1', '!!!!!!!!!', '1745419527_1743861726_ads1.png', 0, '2025-04-23 14:45:27'),
(8, 'ADS 2', 'hays', '1745419571_1743861747_ads2.jpg', 0, '2025-04-23 14:45:59');

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
(3, 'Menagel SJ', 'helloooo!', '2025-02-20 12:01:09', '1743095588__Demure cat with glasses and sharkie _ Everything is on fire_ Sticker for Sale by FigmaCreations.jpg', 1),
(8, '', 'DEFENSE NAAAAA ><', '2025-04-11 02:41:41', '1745420273_1743095519_download.jpg', 1);

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
  `mobile` varchar(255) DEFAULT NULL,
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
(69, 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'NzlPcVFkZ0Ixdko3dktxOGFhbWJWbEgxZXRkbmp4enRtQ1JGV0RIblR2UHBzRnZ5blpncXNuRkRudkdIMkt6dw==', 'Rental', 'UDVGT210eC9sSG9HbjlLYXpyQ0tUZz09', '../uploads/billing_proof/2022-09-07.png', '../uploads/valid_id/2022-09-13 (2).png', '2025-04-20 15:59:06', 'V09UK1dxbFphSUlYelJJSTdpcC9sbU1ReVh1ZVZkVEU1M2JqUXhnNnh4dz0=', 1, 'Unli Plan 800', ''),
(73, 'ZEUrSlk5VmNDL1hyR3IyblZjVTBVUT09', 'T3FMZk8wYksrcXNBMVgwaGQ0WVE1UT09', 'QzEyb1Mxb29FRERLbDg5N2hYWkhkUT09', 'bnRZQUZwSFVXWnBjS1EwZDFzV3FmSmd0Wk1kY2o0dzg0RWlURDBLT2pmMncxZnJSOEFmU1RuRjN3VWNYeVN5Ng==', 'Rental', 'SzBXK2t3SFRuaStqSEpJdG1KMW5Rdz09', '../uploads/billing_proof/images (1).jpg', '../uploads/valid_id/hrochlogo.png', '2025-04-22 04:50:04', 'SDRlQm1CbG5vd0FVYVBhVEdpSlhYOUlaSEZSRi9ERk5YdDEyVi9jUkdWdz0=', 1, 'Unli Plan 800', 'Pending');

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
(20, 69, 16, 'Approved', '2025-04-23 17:06:41', 57);

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
(61, 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 1, 999.99, '2025-05-20', 'Paid'),
(62, 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 1, 999.99, '2025-06-20', 'Unpaid');

-- --------------------------------------------------------

--
-- Table structure for table `tblchats`
--

CREATE TABLE `tblchats` (
  `chat_id` int(11) NOT NULL,
  `ticket_id` int(11) NOT NULL,
  `sender` varchar(50) NOT NULL,
  `message` text NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `mobile` varchar(255) DEFAULT NULL,
  `promo_name` varchar(255) DEFAULT NULL,
  `status` enum('Installed','Pending') DEFAULT 'Pending',
  `email` varchar(255) NOT NULL,
  `promo_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblclientlist`
--

INSERT INTO `tblclientlist` (`client_id`, `routernumber`, `fname`, `mname`, `lname`, `address`, `residenttype`, `mobile`, `promo_name`, `status`, `email`, `promo_id`) VALUES
(57, 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'NzlPcVFkZ0Ixdko3dktxOGFhbWJWbEgxZXRkbmp4enRtQ1JGV0RIblR2UHBzRnZ5blpncXNuRkRudkdIMkt6dw==', 'Rental', 'UDVGT210eC9sSG9HbjlLYXpyQ0tUZz09', 'Unli Plan 800', 'Installed', 'V09UK1dxbFphSUlYelJJSTdpcC9sbU1ReVh1ZVZkVEU1M2JqUXhnNnh4dz0=', 1),
(61, NULL, 'ZEUrSlk5VmNDL1hyR3IyblZjVTBVUT09', 'T3FMZk8wYksrcXNBMVgwaGQ0WVE1UT09', 'QzEyb1Mxb29FRERLbDg5N2hYWkhkUT09', 'bnRZQUZwSFVXWnBjS1EwZDFzV3FmSmd0Wk1kY2o0dzg0RWlURDBLT2pmMncxZnJSOEFmU1RuRjN3VWNYeVN5Ng==', 'Rental', 'SzBXK2t3SFRuaStqSEpJdG1KMW5Rdz09', 'Unli Plan 800', 'Pending', 'SDRlQm1CbG5vd0FVYVBhVEdpSlhYOUlaSEZSRi9ERk5YdDEyVi9jUkdWdz0=', 1);

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
  `mobile` varchar(255) DEFAULT NULL,
  `email` varchar(100) NOT NULL,
  `promo_id` varchar(20) DEFAULT NULL,
  `contract_date` date NOT NULL,
  `drop_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tblinstallations`
--

CREATE TABLE `tblinstallations` (
  `installation_id` int(11) NOT NULL,
  `client_id` int(11) NOT NULL,
  `status` enum('Pending','Installed') DEFAULT 'Pending',
  `install_date` date DEFAULT NULL,
  `client_name` varchar(255) NOT NULL,
  `proof_photo` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblinstallations`
--

INSERT INTO `tblinstallations` (`installation_id`, `client_id`, `status`, `install_date`, `client_name`, `proof_photo`) VALUES
(14, 57, 'Installed', '2025-04-24', 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09 SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09 QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'uploads/installationproof/680921c02455c.png');

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
(10, 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', '2025-04-23', 0.00, NULL, 800.00, 'Cash', 61);

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
(48, 1, 57, 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'V09UK1dxbFphSUlYelJJSTdpcC9sbU1ReVh1ZVZkVEU1M2JqUXhnNnh4dz0='),
(52, 1, 61, 'ZEUrSlk5VmNDL1hyR3IyblZjVTBVUT09', 'T3FMZk8wYksrcXNBMVgwaGQ0WVE1UT09', 'QzEyb1Mxb29FRERLbDg5N2hYWkhkUT09', 'SDRlQm1CbG5vd0FVYVBhVEdpSlhYOUlaSEZSRi9ERk5YdDEyVi9jUkdWdz0=');

-- --------------------------------------------------------

--
-- Table structure for table `tblstafflist`
--

CREATE TABLE `tblstafflist` (
  `id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text NOT NULL,
  `lname` text NOT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `email` varchar(255) NOT NULL,
  `specialization` enum('Technical','Upgrade/Downgrade Internet','Disconnection','Other') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tblstafflist`
--

INSERT INTO `tblstafflist` (`id`, `fname`, `mname`, `lname`, `mobile`, `address`, `email`, `specialization`) VALUES
(16, 'R0s1c2JrUHlQM2lPdHFkenNEb0RRdz09', 'R2g2RUp0bFc0a0Z6cnordlpySVNHZz09', 'eVdiakR0akwwYjFpcUNRQldvaG41UT09', '09452452747', 'ZDJldzNtQWxKa1VJL2FScyt5VFB2QT09', 'eTQ5TG5CTVNGSml2ZXFzVG9RUGF1dz09', 'Technical');

-- --------------------------------------------------------

--
-- Table structure for table `tbltickets`
--

CREATE TABLE `tbltickets` (
  `ticket_id` int(11) NOT NULL,
  `fname` text NOT NULL,
  `mname` text DEFAULT NULL,
  `lname` text NOT NULL,
  `mobile` varchar(255) DEFAULT NULL,
  `address` text NOT NULL,
  `routernumber` varchar(50) NOT NULL,
  `concern_type` enum('Technical','Upgrade/Downgrade Internet','Disconnection','Other') NOT NULL,
  `concern` text NOT NULL,
  `status` enum('Pending','Scheduled','Completed') DEFAULT 'Pending',
  `schedule_date` date DEFAULT NULL,
  `schedule_time` time DEFAULT NULL,
  `assigned_staff` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `image` varchar(255) DEFAULT NULL,
  `proof_photo` varchar(255) DEFAULT NULL,
  `completed_date` date DEFAULT NULL,
  `completed_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbltickets`
--

INSERT INTO `tbltickets` (`ticket_id`, `fname`, `mname`, `lname`, `mobile`, `address`, `routernumber`, `concern_type`, `concern`, `status`, `schedule_date`, `schedule_time`, `assigned_staff`, `created_at`, `image`, `proof_photo`, `completed_date`, `completed_time`) VALUES
(27, 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'UDVGT210eC9sSG9HbjlLYXpyQ0tUZz09', 'NzlPcVFkZ0Ixdko3dktxOGFhbWJWbEgxZXRkbmp4enRtQ1JGV0RIblR2UHBzRnZ5blpncXNuRkRudkdIMkt6dw==', 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 'Disconnection', 'ajZycE0xOGJjV0JqYjM1YWkvSENGZz09', 'Pending', NULL, NULL, NULL, '2025-04-23 02:50:07', '1745424036_1728948370234.jpg', NULL, NULL, NULL),
(28, 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'UDVGT210eC9sSG9HbjlLYXpyQ0tUZz09', 'NzlPcVFkZ0Ixdko3dktxOGFhbWJWbEgxZXRkbmp4enRtQ1JGV0RIblR2UHBzRnZ5blpncXNuRkRudkdIMkt6dw==', 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 'Disconnection', 'Mk9lcGlabDZKTEVYL1RCWG90YnNpV1F0SzNTcVdneEFqTWwvVEZQbk1BYz0=', 'Scheduled', '2025-04-26', '17:00:00', 16, '2025-04-23 16:01:22', '1745424082_Screenshot_2025-04-12-14-37-42-79_40deb401b9ffe8e1df2f1cc5ba480b12.jpg', NULL, NULL, NULL),
(32, 'NllRUW1za3hnMjl2UVgzNjZ5WmxLUT09', 'SGxpU0lMTmVZSGt5M1hMdHAzUVdjQT09', 'QW1RS0J0L0JrUWlYR2dXbkRJY0RQQT09', 'UDVGT210eC9sSG9HbjlLYXpyQ0tUZz09', 'NzlPcVFkZ0Ixdko3dktxOGFhbWJWbEgxZXRkbmp4enRtQ1JGV0RIblR2UHBzRnZ5blpncXNuRkRudkdIMkt6dw==', 'VWJqWG9uYXRHQ1J4Z3RielM5aUxiUT09', 'Technical', 'VFRwazVtOXk3UHFTM2IzVDJ0a015UT09', 'Completed', '2025-04-24', '05:12:00', 16, '2025-04-23 18:11:59', '1745431919_bd5d9407d1cf0d891d3762a0ebe3f8b5-removebg-preview.png', 'uploads/ticketsproof/6809318749fcd.png', '2025-04-23', '20:29:27');

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
(64, 'Vk9RQ000Y21vR3FiM203ME8wcVZPVGt0d3FRUXlGQjcwMEJYUXkraytKZz0=', '$2y$10$9iEH2tsBl0d4m2QdhZZ/j.JDgKWI5KXza9c31.f1tF/NNWv.Sn61K', 'Admin', '2025-04-20', '', 0, NULL, NULL),
(67, 'V09UK1dxbFphSUlYelJJSTdpcC9sbU1ReVh1ZVZkVEU1M2JqUXhnNnh4dz0=', '$2y$10$FK4BtsxH.8mxCmsFR1x8IOknB8wGC7FR7zeGyoZ6OGXnWHS3iH8uy', 'Client', '2025-04-20', '', 1, NULL, NULL),
(68, 'SDRlQm1CbG5vd0FVYVBhVEdpSlhYOUlaSEZSRi9ERk5YdDEyVi9jUkdWdz0=', '$2y$10$PjTIG9.jrw5d2Mx6RDGMHeenhdEWAwf0qfjZOsZlEO8NPWQpTEZd.', 'Client', '2025-04-22', '', 1, NULL, NULL),
(71, 'eTQ5TG5CTVNGSml2ZXFzVG9RUGF1dz09', '$2y$10$TDb4BlMshJbn5q90lIOcF.ANnXEyiA2On03Q/gokIKaIAFei99ste', 'Staff', '2025-04-23', '', 0, NULL, NULL),
(72, 'VkNjSk13bUhlSnBhU3pWNHZocWZRQWFaYVhBY0hXWjRLMWxzUEdCODZTUT0=', '$2y$10$cxhLEs2xm8cvh2QB2BEKaO7eUPMb3wpB8UbPdqL9zU.LeUyc.B8Tm', 'Client', '2025-04-24', '', 1, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tblverifications`
--

CREATE TABLE `tblverifications` (
  `id` int(11) NOT NULL,
  `email` text NOT NULL,
  `password` text NOT NULL,
  `role` varchar(50) NOT NULL,
  `verify_code` varchar(6) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbladvertisement`
--
ALTER TABLE `tbladvertisement`
  ADD PRIMARY KEY (`id`);

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
-- Indexes for table `tblchats`
--
ALTER TABLE `tblchats`
  ADD PRIMARY KEY (`chat_id`),
  ADD KEY `ticket_id` (`ticket_id`);

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
-- Indexes for table `tblverifications`
--
ALTER TABLE `tblverifications`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbladvertisement`
--
ALTER TABLE `tbladvertisement`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tblannouncements`
--
ALTER TABLE `tblannouncements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `tblapplication`
--
ALTER TABLE `tblapplication`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=74;

--
-- AUTO_INCREMENT for table `tblapprove`
--
ALTER TABLE `tblapprove`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `tblbilling`
--
ALTER TABLE `tblbilling`
  MODIFY `billing_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=63;

--
-- AUTO_INCREMENT for table `tblchats`
--
ALTER TABLE `tblchats`
  MODIFY `chat_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblclientlist`
--
ALTER TABLE `tblclientlist`
  MODIFY `client_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT for table `tblclient_archive`
--
ALTER TABLE `tblclient_archive`
  MODIFY `archive_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tblinstallations`
--
ALTER TABLE `tblinstallations`
  MODIFY `installation_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `tblnotifications`
--
ALTER TABLE `tblnotifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tblpayment`
--
ALTER TABLE `tblpayment`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tblpromo`
--
ALTER TABLE `tblpromo`
  MODIFY `promo_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `tblpromo_subscribers`
--
ALTER TABLE `tblpromo_subscribers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `tblstafflist`
--
ALTER TABLE `tblstafflist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tbltickets`
--
ALTER TABLE `tbltickets`
  MODIFY `ticket_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `tblusers`
--
ALTER TABLE `tblusers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;

--
-- AUTO_INCREMENT for table `tblverifications`
--
ALTER TABLE `tblverifications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

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
-- Constraints for table `tblchats`
--
ALTER TABLE `tblchats`
  ADD CONSTRAINT `tblchats_ibfk_1` FOREIGN KEY (`ticket_id`) REFERENCES `tbltickets` (`ticket_id`) ON DELETE CASCADE;

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
