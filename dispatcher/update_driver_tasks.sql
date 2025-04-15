CREATE TABLE IF NOT EXISTS `drivers_tasks` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `driver_id` int(11) NOT NULL,
  `area_report_id` int(11) NOT NULL,
  `bowser_id` int(11) NOT NULL,
  `status` ENUM('Driving', 'On Depot') DEFAULT 'On Depot',
  `assigned_date` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  FOREIGN KEY (`driver_id`) REFERENCES `users` (`id`),
  FOREIGN KEY (`area_report_id`) REFERENCES `area_reports` (`id`),
  FOREIGN KEY (`bowser_id`) REFERENCES `bowsers` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Add status_maintenance column to bowsers table (using SHOW COLUMNS to check first)
SET @dbname = DATABASE();
SET @tablename = "bowsers";
SET @columnname = "status_maintenance";
SET @preparedStatement = (SELECT IF(
  (
    SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS
    WHERE 
      TABLE_SCHEMA = @dbname
      AND TABLE_NAME = @tablename
      AND COLUMN_NAME = @columnname
  ) > 0,
  "SELECT 'Column already exists'",
  "ALTER TABLE bowsers ADD COLUMN status_maintenance ENUM('Dispatched', 'On Depot', 'Maintenance Requested', 'Driving') DEFAULT 'On Depot'"
));
PREPARE alterIfNotExists FROM @preparedStatement;
EXECUTE alterIfNotExists;
DEALLOCATE PREPARE alterIfNotExists;

-- Update existing bowsers to have default status
UPDATE `bowsers` SET `status_maintenance` = 'On Depot' WHERE `status_maintenance` IS NULL;