CREATE TABLE IF NOT EXISTS `#__monitor_projects`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Project ID, primary key',
	`name` varchar(255) NOT NULL COMMENT 'Name of the project',
	`alias` varchar(255) NOT NULL COMMENT 'Unique alias',
	`description` mediumtext NOT NULL,
	`url` varchar(255) NOT NULL COMMENT 'Link to the project homepage',
	`logo` varchar(255) NOT NULL COMMENT 'Path to the logo image.',
	`logo_alt` varchar(255) NOT NULL COMMENT 'Alternative text for the logo.',
	`issue_template` varchar(255) NOT NULL COMMENT 'Default text for issues of this project.',
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
	`project_id` int(10) unsigned NOT NULL COMMENT 'Project ID',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`project_id`) REFERENCES `#__monitor_projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_issue_classifications`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID, primary key',
	`project_id` int(10) unsigned NOT NULL COMMENT 'Project ID',
	`title` varchar(255) NOT NULL COMMENT 'Title of the type',
	`access` int(10) unsigned NOT NULL DEFAULT 1 COMMENT 'Access level needed to view issues of this type.',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`project_id`) REFERENCES `#__monitor_projects`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_issues`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Issue ID, primary key',
	`project_id` int(10) unsigned COMMENT 'Project ID',
	`title` varchar(255) NOT NULL COMMENT 'Issue title',
	`text` mediumtext NOT NULL COMMENT 'Issue description',
	`version` varchar(10) COMMENT 'Version number',
	`author_id` int(11),
	`created` datetime NOT NULL,
	`status` int(10) unsigned DEFAULT NULL COMMENT 'Issue status',
	`classification` int(10) unsigned COMMENT 'Issue classification',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`project_id`) REFERENCES `#__monitor_projects`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`status`) REFERENCES `#__monitor_status`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`classification`) REFERENCES `#__monitor_issue_classifications`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`author_id`) REFERENCES `#__users`(`id`) ON DELETE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_comments`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Comment ID, primary key',
	`issue_id` int(10) unsigned NOT NULL COMMENT 'Issue ID',
	`author_id` int(11),
	`text` mediumtext NOT NULL COMMENT 'Comment content',
	`created` datetime NOT NULL,
	`status` int(10) unsigned DEFAULT NULL COMMENT 'Issue status set by this comment',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`issue_id`) REFERENCES `#__monitor_issues`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`status`) REFERENCES `#__monitor_status`(`id`) ON DELETE SET NULL,
	FOREIGN KEY (`author_id`) REFERENCES `#__users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_subscriptions_issues`(
	`item_id` int(10) unsigned NOT NULL COMMENT 'ID of the issue subscribed to.',
	`user_id` int(11) COMMENT 'ID of the subscribing user.',
	FOREIGN KEY (`item_id`) REFERENCES `#__monitor_issues`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_subscriptions_projects`(
	`item_id` int(10) unsigned NOT NULL COMMENT 'ID of the project subscribed to.',
	`user_id` int(11) COMMENT 'ID of the subscribing user.',
	FOREIGN KEY (`item_id`) REFERENCES `#__monitor_projects`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_unread_issues`(
	`issue_id` int(10) unsigned NOT NULL COMMENT 'ID of the unread issue.',
	`user_id` int(11) NOT NULL COMMENT 'ID of the user.',
	`comment_id` int(10) unsigned COMMENT 'ID of the first(!) unread comment.',
	`timestamp` datetime NOT NULL COMMENT 'Timestamp of the last update of this row.',
	PRIMARY KEY (`issue_id`, `user_id`),
	FOREIGN KEY (`issue_id`) REFERENCES `#__monitor_issues`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`user_id`) REFERENCES `#__users`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`comment_id`) REFERENCES `#__monitor_comments`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `#__monitor_attachments`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attachment ID, primary key',
	`issue_id` int(10) unsigned NOT NULL COMMENT 'ID of the issue where the attachment belongs.',
	`comment_id` int(10) unsigned COMMENT 'ID of the comment where the attachment belongs.',
	`name` varchar(255) NOT NULL COMMENT 'File name.',
	`path` varchar(400) NOT NULL COMMENT 'File path for the attachment. Relative to the base folder of the component in /media.',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`issue_id`) REFERENCES `#__monitor_issues`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`comment_id`) REFERENCES `#__monitor_comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
