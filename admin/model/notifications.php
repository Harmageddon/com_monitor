<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * This model keeps track on unread issues for every user.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelNotifications extends JModelDatabase
{
	/**
	 * MonitorModelNotifications constructor.
	 *
	 * @param   JApplicationCms  $application  The Application object to use in this model.
	 *
	 * @throws Exception
	 */
	public function __construct($application = null)
	{
		parent::__construct();

		if ($application)
		{
			$this->app = $application;
		}
		else
		{
			$this->app = JFactory::getApplication();
		}
	}

	/**
	 * Marks an issue as unread.
	 * If the "unread" mark already exists for a user, only the timestamp will be updated.
	 *
	 * @param   int  $issueId    ID of the issue to mark.
	 * @param   int  $commentId  ID of a new comment. If omitted, the whole issue is marked as unread.
	 * @param   int  $userId     ID of the user. If omitted, the issue is marked as unread for all active users.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function markUnread($issueId, $commentId = null, $userId = 0)
	{
		if (!$issueId)
		{
			return false;
		}

		if (!$userId)
		{
			return $this->markUnreadAllUsers($issueId, $commentId);
		}

		return $this->markUnreadSingleUser($issueId, $userId, $commentId);
	}

	/**
	 * Marks an issue as unread for all users.
	 *
	 * @param   int  $issueId    ID of the issue to mark.
	 * @param   int  $commentId  ID of the new comment.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	private function markUnreadAllUsers($issueId, $commentId = null)
	{
		$commentId = $commentId ? (int) $commentId : 'NULL';

		// Get the params
		// TODO: may be removed when new MVC is implemented completely
		if ($this->app instanceof JApplicationSite)
		{
			$params = $this->app->getParams();
			$active = $this->app->getMenu()->getActive();

			if ($active)
			{
				$params->merge($active->params);
			}
		}
		else
		{
			$params = JComponentHelper::getParams('com_monitor');
		}

		$query = $this->db->getQuery(true);

		$queryString = 'INSERT INTO `#__monitor_unread_issues`(issue_id, comment_id, timestamp, user_id) '
				. 'SELECT ' . (int) $issueId . ', ' . $commentId . ', NOW(), id AS user_id '
				. 'FROM `#__users` WHERE DATEDIFF(NOW(), lastvisitDate) < ' . (int) $params->get('inactivity_period_mark', 50)
				. ' ON DUPLICATE KEY UPDATE `timestamp` = NOW();';

		$query->setQuery($queryString);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Marks an issue as unread for a single user.
	 *
	 * @param   int  $issueId    ID of the issue to mark.
	 * @param   int  $userId     ID of the user for whom to set the mark.
	 * @param   int  $commentId  ID of the new comment.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	private function markUnreadSingleUser($issueId, $userId, $commentId = null)
	{
		$values = array(
				'issueId'   => (int) $issueId,
				'userId'    => (int) $userId,
				'commentId' => $commentId ? (int) $commentId : 'NULL',
		);

		$query       = $this->db->getQuery(true);
		$queryString = 'INSERT INTO `#__monitor_unread_issues` '
				. 'SET ' . MonitorHelper::sqlValues($values, $query)
				. ' ON DUPLICATE KEY UPDATE `timestamp` = NOW()';

		$query->setQuery($queryString);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Marks an issue as read for a single user.
	 * This removes the according database entry from #__monitor_unread_issues.
	 *
	 * @param   int  $issueId  ID of the issue to mark as read.
	 * @param   int  $userId   ID of the reading user.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function markRead($issueId, $userId)
	{
		$query = $this->db->getQuery(true);
		$query->delete('#__monitor_unread_issues')
			->where('issue_id = ' . (int) $issueId)
			->where('user_id = ' . (int) $userId);

		return $this->db->setQuery($query)->execute();
	}
}
