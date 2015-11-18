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
