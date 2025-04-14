-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 14, 2025 at 11:47 PM
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
-- Database: `bowsers_assignment`
--

-- --------------------------------------------------------

--
-- Table structure for table `active_bowser`
--

CREATE TABLE `active_bowser` (
  `id` int(11) NOT NULL,
  `bowserId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `dispatchDate` text NOT NULL,
  `dispatchType` text NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `area_reports`
--

CREATE TABLE `area_reports` (
  `id` int(11) NOT NULL,
  `report` text NOT NULL,
  `postcode` text NOT NULL,
  `reportType` text NOT NULL,
  `userId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `area_reports`
--

INSERT INTO `area_reports` (`id`, `report`, `postcode`, `reportType`, `userId`) VALUES
(1, '123', 'GL51 8HP', 'Urgent', 4),
(2, '&#39;&#39;&#39;&#39;&#39;&#34;&#34;&#34;&#34;', 'Test', 'Urgent', 4),
(3, '&#39;&#39;&#39;&#39;&#39;&#34;&#34;&#34;&#34;', '&#39;[][&#34;1@?>', 'Urgent', 4),
(4, '12121', 'GL51 8HP', 'Urgent', 4),
(5, '', '', 'Urgent', 4),
(6, 'dddd', 'ox14 3yd', 'Urgent', 4),
(7, 'dddd', 'OX14 3YD', 'Urgent', 4);

-- --------------------------------------------------------

--
-- Table structure for table `bowsers`
--

CREATE TABLE `bowsers` (
  `id` int(11) NOT NULL,
  `ownerId` int(11) NOT NULL,
  `name` text NOT NULL,
  `manufacturer_details` text NOT NULL,
  `model` text NOT NULL,
  `serial_number` text NOT NULL,
  `specific_notes` text NOT NULL,
  `capacity_litres` text NOT NULL,
  `length_mm` text NOT NULL,
  `width_mm` text NOT NULL,
  `height_mm` text NOT NULL,
  `weight_empty_kg` text NOT NULL,
  `weight_full_kg` text NOT NULL,
  `supplier_company` text NOT NULL,
  `date_received` text NOT NULL,
  `date_returned` text NOT NULL,
  `eastings` int(11) NOT NULL DEFAULT 0,
  `northings` int(11) NOT NULL DEFAULT 0,
  `longitude` text NOT NULL,
  `latitude` text NOT NULL,
  `postcode` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bowsers`
--

INSERT INTO `bowsers` (`id`, `ownerId`, `name`, `manufacturer_details`, `model`, `serial_number`, `specific_notes`, `capacity_litres`, `length_mm`, `width_mm`, `height_mm`, `weight_empty_kg`, `weight_full_kg`, `supplier_company`, `date_received`, `date_returned`, `eastings`, `northings`, `longitude`, `latitude`, `postcode`, `active`) VALUES
(1, 4, 'ddd', 'ddd', 'ddd', 'ddd', 'ddd', 'dddd', 'dd', 'd', 'd', 'd', 'd', 'd', '2025-05-09', '2025-05-02', 451354, 198137, '-1.258656', '51.679658', 'OX14 3YD', 1),
(2, 4, 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', 'd', '2025-04-25', '2025-04-01', 451354, 198137, '-1.258656', '51.679658', 'OX14 3YD', 1);

-- --------------------------------------------------------

--
-- Table structure for table `bowser_reports`
--

CREATE TABLE `bowser_reports` (
  `id` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `bowserId` int(11) NOT NULL,
  `report` text NOT NULL,
  `typeOfReport` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `maintain_bowser`
--

CREATE TABLE `maintain_bowser` (
  `id` int(11) NOT NULL,
  `bowserId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `descriptionOfWork` text NOT NULL,
  `maintenanceType` text NOT NULL,
  `dateOfMaintenance` text NOT NULL,
  `status` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `uploads`
--

CREATE TABLE `uploads` (
  `fileName` text NOT NULL,
  `bowserId` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `sessionKey` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `userType` enum('public','admin','maintainer','dispatcher','driver') NOT NULL DEFAULT 'public'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `sessionKey`, `active`, `verified`, `admin`, `userType`) VALUES
(1, '123', 'ed8779a2222dc578f2cffbf308411b41381a94ef25801f9dfbe04746ea0944cd', '123', 'SPKFWLVnqHSbFZ_Zmw_WgrtJV', 1, 0, 0, 'public'),
(2, 'NewUser122', 'ed8779a2222dc578f2cffbf308411b41381a94ef25801f9dfbe04746ea0944cd', '&#039@&quot]@&lt&gt*&amp^%$.', 'MOFzpfNqf_HDRFiHpIYTSrMTW', 1, 0, 0, 'public'),
(3, 'NewUser124', 'ed8779a2222dc578f2cffbf308411b41381a94ef25801f9dfbe04746ea0944cd', 'test@test.com', 'YkZnWWOlwHpjkWzHjzjexjfqi', 1, 0, 0, 'public'),
(4, 'CoD', 'ed8779a2222dc578f2cffbf308411b41381a94ef25801f9dfbe04746ea0944cd', 'oftwx312@gmail.com', 'pVldUgyMgWbeuqyYVafABz**y', 1, 1, 0, 'public'),
(5, 'CodeTests', 'b16723164bc89d5b8e389db92db7d1c5222d9411e4b0371a52d17a4a656fe23f', 'swifttest@yopmail.com', 'UymuNJmpDUYuhHBYULfgVRRje', 1, 1, 0, 'public');

-- --------------------------------------------------------

--
-- Table structure for table `verification_codes`
--

CREATE TABLE `verification_codes` (
  `userId` text NOT NULL,
  `code` text NOT NULL,
  `expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `verification_codes`
--

INSERT INTO `verification_codes` (`userId`, `code`, `expires`) VALUES
('', 'test', 1744662046),
('', '523453', 1744662230),
('4', '640101', 1744662629),
('4', '695167', 1744662953),
('4', '555094', 1744663149),
('4', '399579', 1744663303),
('4', '312158', 1744663432),
('4', '251722', 1744663690),
('4', '495829', 1744664138),
('4', '767164', 1744664278),
('5', '415661', 1744664383),
('0', '571037', 1744665277),
('4', '819434', 1744665400),
('4', '892807', 1744665771),
('0', '351958', 1744665863),
('4', '873780', 1744666180),
('0', '153416', 1744666206),
('4', '462826', 1744666325),
('0', '557818', 1744666337),
('4', '499185', 1744666447),
('0', '917561', 1744666477),
('0', '899102', 1744666612),
('0', '907894', 1744666758),
('4', '978874', 1744666785),
('0', '323817', 1744666884),
('4', '524464', 1744666914),
('4', '102813', 1744667134),
('0', '675156', 1744667171),
('4', '334322', 1744667263),
('0', '222319', 1744667291);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `active_bowser`
--
ALTER TABLE `active_bowser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bowserId` (`bowserId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `area_reports`
--
ALTER TABLE `area_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `bowsers`
--
ALTER TABLE `bowsers`
  ADD PRIMARY KEY (`id`),
  ADD KEY `ownerId` (`ownerId`);

--
-- Indexes for table `bowser_reports`
--
ALTER TABLE `bowser_reports`
  ADD PRIMARY KEY (`id`),
  ADD KEY `userId` (`userId`),
  ADD KEY `bowserId` (`bowserId`);

--
-- Indexes for table `maintain_bowser`
--
ALTER TABLE `maintain_bowser`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bowserId` (`bowserId`),
  ADD KEY `userId` (`userId`);

--
-- Indexes for table `uploads`
--
ALTER TABLE `uploads`
  ADD KEY `bowserId` (`bowserId`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `active_bowser`
--
ALTER TABLE `active_bowser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `area_reports`
--
ALTER TABLE `area_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `bowsers`
--
ALTER TABLE `bowsers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `bowser_reports`
--
ALTER TABLE `bowser_reports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `maintain_bowser`
--
ALTER TABLE `maintain_bowser`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `active_bowser`
--
ALTER TABLE `active_bowser`
  ADD CONSTRAINT `active_bowser_ibfk_1` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`),
  ADD CONSTRAINT `active_bowser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `area_reports`
--
ALTER TABLE `area_reports`
  ADD CONSTRAINT `area_reports_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `bowsers`
--
ALTER TABLE `bowsers`
  ADD CONSTRAINT `bowsers_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`id`);

--
-- Constraints for table `bowser_reports`
--
ALTER TABLE `bowser_reports`
  ADD CONSTRAINT `bowser_reports_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bowser_reports_ibfk_2` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`);

--
-- Constraints for table `maintain_bowser`
--
ALTER TABLE `maintain_bowser`
  ADD CONSTRAINT `maintain_bowser_ibfk_1` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`),
  ADD CONSTRAINT `maintain_bowser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`);

--
-- Constraints for table `uploads`
--
ALTER TABLE `uploads`
  ADD CONSTRAINT `uploads_ibfk_1` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
