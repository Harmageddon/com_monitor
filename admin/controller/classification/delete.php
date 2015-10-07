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
 * Controller to delete a given classification.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerClassificationDelete extends JControllerBase
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
		if (!JFactory::getUser()->authorise('classification.delete', 'com_monitor'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$app = JFactory::getApplication();
		$model = new MonitorModelClassification($app);
		$model->delete($app->input->get('cid', array(), 'array'));

		$app->enqueueMessage(JText::_('COM_MONITOR_CLASSIFICATION_DELETED'));
		$app->redirect(JRoute::_('index.php?option=com_monitor&view=classifications', false));

		return true;
	}
}
