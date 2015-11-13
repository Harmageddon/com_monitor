<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Model to handle subscriptions.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelSubscription extends JModelDatabase
{
	/**
	 * MonitorModelSubscription constructor.
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
	 * Adds a subscription for a given issue and user.
	 * The user will be notified for new comments on the issue.
	 *
	 * @param   int  $id    ID of the issue.
	 * @param   int  $user  ID of the subscribing user.
	 *
	 * @return   mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function subscribeIssue($id, $user)
	{
		$values = array(
			'item_id' => $id,
			'user_id' => $user,
		);
		$query = $this->db->getQuery(true);

		$query->insert('#__monitor_subscriptions_issues')
			->set(MonitorHelper::sqlValues($values, $query));

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Removes a subscription for a given issue and user.
	 * The user will receive no further notifications for new comments on the issue.
	 *
	 * @param   int  $id    ID of the issue.
	 * @param   int  $user  ID of the subscribing user.
	 *
	 * @return   mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function unsubscribeIssue($id, $user)
	{
		$query = $this->db->getQuery(true);

		$query->delete('#__monitor_subscriptions_issues')
			->where('item_id = ' . (int) $id)
			->where('user_id = ' . (int) $user);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Checks if a user has set a subscription for a given issue.
	 *
	 * @param   int  $id    ID of the issue.
	 * @param   int  $user  ID of the user.
	 *
	 * @return   bool  True if the user has a subscription for the issue.
	 */
	public function isSubscriberIssue($id, $user)
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(*)')
			->from('#__monitor_subscriptions_issues')
			->where('item_id = ' . (int) $id)
			->where('user_id = ' . (int) $user);

		return ($this->db->setQuery($query)->loadResult() != 0);
	}
}
