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
 * Deletes a given comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentDelete extends JControllerBase
{
	/**
	 * Execute the controller, delete the record and redirect to comments view.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		if (!JFactory::getUser()->authorise('comment.delete', 'com_monitor'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$app = JFactory::getApplication();
		$model = new MonitorModelComment($app);
		$ids = $app->input->get('cid', array(), 'array');

		if (count($ids) <= 0)
		{
			$ids = $app->input->get('id', array(), 'array');
		}

		if (count($ids) <= 0)
		{
			$app->enqueueMessage(JText::_('JLIB_HTML_PLEASE_MAKE_A_SELECTION_FROM_THE_LIST'), 'error');
		}
		else
		{
			$model->delete($ids);
			$app->enqueueMessage(\JText::_('COM_MONITOR_COMMENT_DELETED'));
		}

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=comments', false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=issues', false));
		}

		return true;
	}
}
