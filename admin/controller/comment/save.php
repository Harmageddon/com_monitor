<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * Save a comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentSave extends JControllerBase
{
	/**
	 * Execute the controller, save the values from the form and redirect to comments view.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$app = JFactory::getApplication();

		$issue_id = $this->input->getInt('issue_id');
		$id = $this->input->getInt('id');
		$model = new MonitorModelComment($app);
		$user = JFactory::getUser();

		if (!$model->canEdit($user, $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$result = $model->save($this->input);

		if ($result === false)
		{
			$url = 'index.php?option=com_monitor&task=comment.edit';

			if ($id)
			{
				$url .= '&id=' . $id;
			}
			elseif ($issue_id)
			{
				$url .= '&issue_id=' . $issue_id;
			}

			$app->redirect(JRoute::_($url, false));

			return false;
		}

		$app->enqueueMessage(JText::_('COM_MONITOR_COMMENT_SAVED'));

		$commentLink = JRoute::_('index.php?option=com_monitor&view=issue&id=' . $issue_id . '#comment-' . $result, false);

		if (!$id)
		{
			// Send notification mails for new comments.
			$modelSubscription = new MonitorModelSubscription;
			$modelSubscription->notifyIssue($issue_id, $user, $commentLink);

			// Mark issue as unread.
			$modelNotification = new MonitorModelNotifications;
			$modelNotification->markUnread($issue_id, $result);
		}

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=comments', false));
		}
		else
		{
			$app->redirect($commentLink);
		}

		return true;
	}
}
