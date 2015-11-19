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

	/**
	 * Notifies all users who have subscribed to the commented issue.
	 *
	 * @param   int     $id           ID of the commented issue.
	 * @param   JUser   $commenter    The commenting user.
	 * @param   string  $commentLink  Direct link to the newly created comment.
	 *
	 * @return null
	 */
	public function notifyIssue($id, $commenter, $commentLink)
	{
		// Get subscribing users.
		$query = $this->db->getQuery(true);
		$query->select('u.email, u.name, u.username')
				->from('#__monitor_subscriptions_issues AS s')
				->leftJoin('#__users AS u ON s.user_id = u.id')
				->where('s.user_id != ' . $commenter->get('id'))
				->where('s.item_id = ' . (int) $id)
				->where('(SELECT COUNT(user_id) FROM #__monitor_unread_issues AS ui WHERE ui.user_id = s.user_id AND ui.issue_id = ' . (int) $id . ') = 0');
		$users = $this->db->setQuery($query)->loadObjectList();

		// Set values that are common for every notification mail.
		$commenterName = $commenter->get('name', $commenter->get('username'));
		$unsubscribeLink = JRoute::_('index.php?option=com_monitor&task=issue.unsubscribe&id=' . (int) $id, false);
		$baseUrl = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		$modelIssue = new MonitorModelIssue($this->app, false);
		$modelIssue->setIssueId($id);
		$issue = $modelIssue->getIssue();

		foreach ($users as $user)
		{
			$recipientName = empty($user->name) ? $user->username : $user->name;

			$subject = JText::sprintf('COM_MONITOR_MAIL_NOTIFICATION_ISSUE_HEADER', $issue->project_name, $issue->title);
			$message = JText::sprintf('COM_MONITOR_MAIL_NOTIFICATION_ISSUE_TEXT',
					$recipientName, $commenterName, $issue->title, $baseUrl . $commentLink, $baseUrl . $unsubscribeLink
			);
			$mail = JFactory::getMailer();

			$mail->isHtml(false);
			$mail->setSubject($subject);
			$mail->setBody($message);
			$mail->addRecipient($user->email, $recipientName);
			$mail->Send();
		}
	}

	/**
	 * Adds a subscription for a given project and user.
	 * The user will be notified for new issues for the project.
	 *
	 * @param   int  $id    ID of the project.
	 * @param   int  $user  ID of the subscribing user.
	 *
	 * @return   mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function subscribeProject($id, $user)
	{
		$values = array(
				'item_id' => $id,
				'user_id' => $user,
		);
		$query = $this->db->getQuery(true);

		$query->insert('#__monitor_subscriptions_projects')
				->set(MonitorHelper::sqlValues($values, $query));

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Removes a subscription for a given project and user.
	 * The user will receive no further notifications for new issues for the project.
	 *
	 * @param   int  $id    ID of the project.
	 * @param   int  $user  ID of the subscribing user.
	 *
	 * @return   mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function unsubscribeProject($id, $user)
	{
		$query = $this->db->getQuery(true);

		$query->delete('#__monitor_subscriptions_projects')
				->where('item_id = ' . (int) $id)
				->where('user_id = ' . (int) $user);

		return $this->db->setQuery($query)->execute();
	}

	/**
	 * Checks if a user has set a subscription for a given project.
	 *
	 * @param   int  $id    ID of the project.
	 * @param   int  $user  ID of the user.
	 *
	 * @return   bool  True if the user has a subscription for the project.
	 */
	public function isSubscriberProject($id, $user)
	{
		$query = $this->db->getQuery(true);

		$query->select('COUNT(*)')
				->from('#__monitor_subscriptions_projects')
				->where('item_id = ' . (int) $id)
				->where('user_id = ' . (int) $user);

		return ($this->db->setQuery($query)->loadResult() != 0);
	}

	/**
	 * Notifies all users who have subscribed to the project.
	 *
	 * @param   int    $projectId  ID of the project.
	 * @param   JUser  $author     The user who created a new issue.
	 * @param   int    $issueId    Direct link to the newly created issue.
	 *
	 * @return null
	 */
	public function notifyProject($projectId, $author, $issueId)
	{
		// Get subscribing users.
		$query = $this->db->getQuery(true);
		$query->select('u.email, u.name, u.username')
				->from('#__monitor_subscriptions_projects AS s')
				->leftJoin('#__users AS u ON s.user_id = u.id')
				->where('s.item_id = ' . $projectId)
				->where('s.user_id != ' . $author->get('id'));
		$users = $this->db->setQuery($query)->loadObjectList();

		// Set values that are common for every notification mail.
		$authorName = $author->get('name', $author->get('username'));
		$issueLink = JRoute::_('index.php?option=com_monitor&view=issue&id=' . (int) $issueId, false);
		$unsubscribeLink = JRoute::_('index.php?option=com_monitor&task=project.unsubscribe&id=' . (int) $projectId, false);
		$baseUrl = JUri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));

		$modelIssue = new MonitorModelIssue($this->app, false);
		$modelIssue->setIssueId($issueId);
		$issue = $modelIssue->getIssue();

		$modelProject = new MonitorModelProject($this->app, false);
		$modelProject->setProjectId($projectId);
		$project = $modelProject->getProject();

		foreach ($users as $user)
		{
			$recipientName = empty($user->name) ? $user->username : $user->name;

			$subject = JText::sprintf('COM_MONITOR_MAIL_NOTIFICATION_PROJECT_HEADER', $project->name, $issue->title);
			$message = JText::sprintf('COM_MONITOR_MAIL_NOTIFICATION_PROJECT_TEXT',
					$recipientName, $authorName, $issue->title, $project->name, $baseUrl . $issueLink, $baseUrl . $unsubscribeLink
			);
			$mail = JFactory::getMailer();

			$mail->isHtml(false);
			$mail->setSubject($subject);
			$mail->setBody($message);
			$mail->addRecipient($user->email, $recipientName);
			$mail->Send();
		}
	}
}
