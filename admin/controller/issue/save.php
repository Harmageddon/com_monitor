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
		$id = $this->input->getInt('id');
		$model = new MonitorModelIssue;

		if (!$model->canEdit(JFactory::getUser(), $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$app = JFactory::getApplication();
		$issue_id = $model->save($this->input);
		$app->enqueueMessage(\JText::_('COM_MONITOR_ISSUE_SAVED'));

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
