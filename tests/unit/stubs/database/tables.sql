CREATE TABLE IF NOT EXISTS `jos_monitor_projects`(
	`id` int(10) NOT NULL,
	`name` varchar(255) NOT NULL,
	`alias` varchar(255) NOT NULL,
	`description` mediumtext NOT NULL,
	`url` varchar(255) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `jos_monitor_status`(
	`id` int(10) NOT NULL,
	`ordering` int(10) NOT NULL,
	`name` varchar(255) NOT NULL,
	`helptext` mediumtext NOT NULL,
	`open` boolean NOT NULL DEFAULT true,
	`is_default` boolean NOT NULL DEFAULT false,
	`style` varchar(255) NOT NULL,
	`project_id` int(10) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `jos_monitor_issues`(
	`id` int(10) NOT NULL,
	`project_id` int(10) NOT NULL,
	`title` varchar(255) NOT NULL,
	`text` mediumtext NOT NULL,
	`version` varchar(10),
	`author_id` int(10) NOT NULL,
	`created` datetime NOT NULL,
	`status` int(10) NOT NULL,
	`classification` int(10) NOT NULL,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `jos_monitor_issue_classifications`(
	`id` int(10) NOT NULL,
	`project_id` int(10) NOT NULL,
	`title` varchar(255) NOT NULL,
	`access` int(10) NOT NULL DEFAULT 1,
	PRIMARY KEY (`id`)
);

CREATE TABLE IF NOT EXISTS `jos_monitor_comments`(
	`id` int(10) NOT NULL,
	`issue_id` int(10) NOT NULL,
	`author_id` int(10) NOT NULL,
	`text` mediumtext NOT NULL,
	`created` datetime NOT NULL,
	`status` int(10) NOT NULL,
	PRIMARY KEY (`id`)
);
