-- Clean SQL Dump for Bowsers Assignment
-- Version: 1.0
-- Generated on: May 7, 2025

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- --------------------------------------------------------
-- Table structure for `users`
-- --------------------------------------------------------

CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `sessionKey` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `verified` tinyint(1) NOT NULL DEFAULT 0,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `userType` enum('public','admin','maintainer','dispatcher','driver') NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `area_reports`
-- --------------------------------------------------------

CREATE TABLE `area_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report` text NOT NULL,
  `postcode` text NOT NULL,
  `reportType` text NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `area_reports_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `assigned_area_reports`
-- --------------------------------------------------------

CREATE TABLE `assigned_area_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report` text NOT NULL,
  `postcode` text NOT NULL,
  `reportType` text NOT NULL,
  `userId` int(11) NOT NULL,
  `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  CONSTRAINT `assigned_area_reports_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `bowsers`
-- --------------------------------------------------------

CREATE TABLE `bowsers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
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
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `status_maintenance` ENUM(
    'On Depot',
    'Dispatched',
    'In Transit',
    'Maintenance Requested',
    'Under Maintenance',
    'Ready',
    'Out of Service',
    'Dispatch Requested'
  ) DEFAULT 'On Depot',
  PRIMARY KEY (`id`),
  KEY `ownerId` (`ownerId`),
  CONSTRAINT `bowsers_ibfk_1` FOREIGN KEY (`ownerId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `bowser_reports`
-- --------------------------------------------------------

CREATE TABLE `bowser_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `bowserId` int(11) NOT NULL,
  `report` text NOT NULL,
  `typeOfReport` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `userId` (`userId`),
  KEY `bowserId` (`bowserId`),
  CONSTRAINT `bowser_reports_ibfk_1` FOREIGN KEY (`userId`) REFERENCES `users` (`id`),
  CONSTRAINT `bowser_reports_ibfk_2` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `maintain_bowser`
-- --------------------------------------------------------

CREATE TABLE `maintain_bowser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bowserId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `descriptionOfWork` text NOT NULL,
  `maintenanceType` text NOT NULL,
  `dateOfMaintenance` text NOT NULL,
  PRIMARY KEY (`id`),
  KEY `bowserId` (`bowserId`),
  KEY `userId` (`userId`),
  CONSTRAINT `maintain_bowser_ibfk_1` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`),
  CONSTRAINT `maintain_bowser_ibfk_2` FOREIGN KEY (`userId`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `uploads`
-- --------------------------------------------------------

CREATE TABLE `uploads` (
  `fileName` text NOT NULL,
  `bowserId` int(11) NOT NULL,
  KEY `bowserId` (`bowserId`),
  CONSTRAINT `uploads_ibfk_1` FOREIGN KEY (`bowserId`) REFERENCES `bowsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `verification_codes`
-- --------------------------------------------------------

CREATE TABLE `verification_codes` (
  `userId` text NOT NULL,
  `code` text NOT NULL,
  `expires` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------
-- Table structure for `drivers_tasks`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `drivers_tasks`;

CREATE TABLE `drivers_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `area_report_id` int(11) NOT NULL, -- Now references `assigned_area_reports`
  `bowser_id` int(11) NOT NULL,
  `status` ENUM('Driving', 'On Depot') DEFAULT 'On Depot',
  `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`),
  FOREIGN KEY (`area_report_id`) REFERENCES `assigned_area_reports` (`id`), -- Updated foreign key
  FOREIGN KEY (`bowser_id`) REFERENCES `bowsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

COMMIT;