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
 * Save an issue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerIssueSave extends JControllerBase
{
	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   12.1
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		$app = JFactory::getApplication();

		$id = $this->input->getInt('id');
		$model = new MonitorModelIssue($app);
		$user = JFactory::getUser();

		if (!$model->canEdit($user, $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$issue_id = $model->save($this->input);

		if ($issue_id === false)
		{
			$url = 'index.php?option=com_monitor&task=issue.edit';

			if ($id)
			{
				$url .= '&id=' . $id;
			}

			$app->redirect(JRoute::_($url, false));

			return false;
		}

		$app->enqueueMessage(\JText::_('COM_MONITOR_ISSUE_SAVED'));

		// Send notification mails for new issues.
		if (!$id)
		{
			$project_id = $this->input->get('project_id');
			$modelSubscription = new MonitorModelSubscription;
			$modelSubscription->notifyProject($project_id, $user, $issue_id);
		}

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=issues', false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=issue&id=' . $issue_id, false));
		}

		return true;
	}
}
