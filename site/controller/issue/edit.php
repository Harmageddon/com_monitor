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
 * Controller to edit an issue in front end.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerIssueEdit extends JControllerBase
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
		$model = new MonitorModelIssue;
		$id = $this->input->getInt('id');

		if (!$model->canEdit(JFactory::getUser(), $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model->setIssueId($id);
		$model->loadForm();
		$view = new MonitorViewIssueHtml($model);
		$view->setLayout('edit');
		$view->loadForm();
		echo $view->render();

		return true;
	}
}
