ALTER TABLE `#__monitor_projects` ADD COLUMN `logo` varchar(255) NOT NULL COMMENT 'Path to the logo image.',
ADD COLUMN `logo_alt` varchar(255) NOT NULL COMMENT 'Alternative text for the logo.';
