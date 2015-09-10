CREATE TABLE IF NOT EXISTS `#__monitor_projects`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Project ID, primary key',
	`name` varchar(255) NOT NULL COMMENT 'Name of the project',
	`description` mediumtext NOT NULL,
	`url` varchar(255) NOT NULL COMMENT 'Link to the project homepage',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_status`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Status ID, primary key',
	`ordering` int(10) unsigned NOT NULL COMMENT 'Ordering key',
	`name` varchar(255) NOT NULL COMMENT 'Status name',
	`helptext` mediumtext NOT NULL COMMENT 'Help text',
	`open` boolean NOT NULL DEFAULT true COMMENT 'Tickets having this status are open(1) or closed(0).',
	`is_default` boolean NOT NULL DEFAULT false COMMENT 'Default status?',
	`style` varchar(255) NOT NULL COMMENT 'Optional CSS class',
	`project_id` int(10) NOT NULL COMMENT 'Project ID',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_issues`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Issue ID, primary key',
	`project_id` int(10) unsigned NOT NULL COMMENT 'Project ID',
	`title` varchar(255) NOT NULL COMMENT 'Issue title',
	`text` mediumtext NOT NULL COMMENT 'Issue description',
	`version` varchar(10) COMMENT 'Version number',
	`author_id` int(10) unsigned NOT NULL,
	`created` datetime NOT NULL,
	`status` int(10) unsigned NOT NULL COMMENT 'Issue status',
	`classification` int(10) unsigned NOT NULL COMMENT 'Issue classification',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_issue_classifications`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID, primary key',
	`project_id` int(10) unsigned NOT NULL COMMENT 'Project ID',
	`title` varchar(255) NOT NULL COMMENT 'Title of the type',
	`access` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Access level needed to view issues of this type.',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_comments`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Comment ID, primary key',
	`issue_id` int(10) unsigned NOT NULL COMMENT 'Issue ID',
	`author_id` int(10) unsigned NOT NULL,
	`text` mediumtext NOT NULL COMMENT 'Comment content',
	`created` datetime NOT NULL,
	`status` int(10) unsigned NOT NULL COMMENT 'Issue status set by this comment',
	PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
