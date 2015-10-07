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
 * Edit or create a status.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerStatusEdit extends JControllerBase
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

		$model = new MonitorModelStatus;
		$cid = $this->input->get('cid', array(), 'array');
		$id = $cid ? $cid[0] : $this->input->getInt('id');
		$model->setStatusId($id);
		$model->loadForm();
		$view = new MonitorViewStatusHtml($model);
		$view->setLayout('edit');
		echo $view->render();

		return true;
	}
}
