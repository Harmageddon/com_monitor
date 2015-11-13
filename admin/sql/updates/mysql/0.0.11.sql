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
