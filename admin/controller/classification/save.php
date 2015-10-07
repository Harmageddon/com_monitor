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
 * Save a classification.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerClassificationSave extends JControllerBase
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
		$model = new MonitorModelClassification($app);

		if (!JFactory::getUser()->authorise('classification.edit', 'com_monitor'))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$classification_id = $model->save($this->input);

		if ($classification_id === false)
		{
			$url = 'index.php?option=com_monitor&task=classification.edit';

			if ($id)
			{
				$url .= '&id=' . $id;
			}

			$app->redirect(JRoute::_($url, false));

			return false;
		}

		$app->enqueueMessage(\JText::_('COM_MONITOR_CLASSIFICATION_SAVED'));

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=classifications', false));
		}
		else
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=classification&id=' . $classification_id, false));
		}

		return true;
	}
}
