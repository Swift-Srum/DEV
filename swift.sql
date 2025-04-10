-- Drop existing tables if needed
DROP TABLE IF EXISTS `active_bowser`;
DROP TABLE IF EXISTS `maintain_bowser`;
DROP TABLE IF EXISTS `uploads`;
DROP TABLE IF EXISTS `bowsers`;
DROP TABLE IF EXISTS `users`;
DROP TABLE IF EXISTS `area_reports`;
DROP TABLE IF EXISTS `bowser_reports`;

-- USERS TABLE
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` text NOT NULL,
  `password` text NOT NULL,
  `email` text NOT NULL,
  `sessionKey` text NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT 1,
  `admin` tinyint(1) NOT NULL DEFAULT 0,
  `userType` enum('public', 'admin', 'maintainer', 'dispatcher', 'driver') NOT NULL DEFAULT 'public',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- BOWSERS TABLE (corrected with name, longitude, latitude, postcode)
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
  PRIMARY KEY (`id`),
  FOREIGN KEY (`ownerId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- UPLOADS TABLE
CREATE TABLE `uploads` (
  `fileName` text NOT NULL,
  `bowserId` int(11) NOT NULL,
  FOREIGN KEY (`bowserId`) REFERENCES `bowsers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- AREA_REPORTS TABLE
CREATE TABLE `area_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `report` text NOT NULL,
  `postcode` text NOT NULL,
  `reportType` text NOT NULL,
  `userId` int(11) NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- BOWSER_REPORTS TABLE
CREATE TABLE `bowser_reports` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `userId` int(11) NOT NULL,
  `bowserId` int(11) NOT NULL,
  `report` text NOT NULL,
  `typeOfReport` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`),
  FOREIGN KEY (`bowserId`) REFERENCES `bowsers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- MAINTAIN_BOWSER TABLE
CREATE TABLE `maintain_bowser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bowserId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `descriptionOfWork` text NOT NULL,
  `maintenanceType` text NOT NULL,
  `dateOfMaintenance` text NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`bowserId`) REFERENCES `bowsers`(`id`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ACTIVE_BOWSER TABLE
CREATE TABLE `active_bowser` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bowserId` int(11) NOT NULL,
  `userId` int(11) NOT NULL,
  `dispatchDate` text NOT NULL,
  `dispatchType` text NOT NULL,
  `status` text NOT NULL,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`bowserId`) REFERENCES `bowsers`(`id`),
  FOREIGN KEY (`userId`) REFERENCES `users`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

