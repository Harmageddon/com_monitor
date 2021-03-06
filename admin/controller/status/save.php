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
 * Save a status.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerStatusSave extends JControllerBase
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
		if (!JFactory::getUser()->authorise('status.edit', 'com_monitor'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$app = JFactory::getApplication();
		$model = new MonitorModelStatus($app);

		if ($model->save($app->input) === false)
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&task=status.edit&id=' . $app->input->getInt('id'), false));
		}
		else
		{
			$app->enqueueMessage(JText::_('COM_MONITOR_STATUS_SAVED'));
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=status', false));
		}

		return true;
	}
}
