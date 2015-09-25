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

		$id = $this->input->getInt('id');
		$model = new MonitorModelComment($app);

		if (!$model->canEdit(JFactory::getUser(), $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model->save($this->input);
		$app->enqueueMessage(JText::_('COM_MONITOR_COMMENT_SAVED'));

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=comments', false));
		}
		else
		{
			$issue_id = $this->input->getInt('issue_id');
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=issue&id=' . $issue_id, false));
		}

		return true;
	}
}
