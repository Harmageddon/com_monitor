CREATE TABLE IF NOT EXISTS `#__monitor_attachments`(
	`id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Attachment ID, primary key',
	`issue_id` int(10) unsigned NOT NULL COMMENT 'ID of the issue where the attachment belongs.',
	`comment_id` int(10) unsigned COMMENT 'ID of the comment where the attachment belongs.',
	`path` varchar(400) NOT NULL COMMENT 'File path for the attachment. Relative to the base folder of the component in /media.',
	PRIMARY KEY (`id`),
	FOREIGN KEY (`issue_id`) REFERENCES `#__monitor_issues`(`id`) ON DELETE CASCADE,
	FOREIGN KEY (`comment_id`) REFERENCES `#__monitor_comments`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
