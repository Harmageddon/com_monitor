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
 * Controller to delete a given attachment from a comment or an issue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerAttachmentDelete extends JControllerBase
{
	/**
	 * @var    JApplicationCms
	 */
	protected $app;

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
		if (!JFactory::getUser()->authorise('attachment.delete', 'com_monitor')) // TODO
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
		}

		$app = JFactory::getApplication();
		$model = new MonitorModelAttachments($app);
		$id = $app->input->getInt('id');

		if (!$id)
		{
			throw new Exception(JText::_('JERROR_NO_ITEMS_SELECTED'), 404);
		}

		$model->delete(array($id));

		$app->enqueueMessage(JText::_('COM_MONITOR_ATTACHMENT_DELETED'));

		$return = base64_decode($this->app->input->get('return', '', 'BASE64'));

		if (!JUri::isInternal($return))
		{
			$return = 'index.php?option=com_monitor&view=projects';
		}

		$this->app->redirect(JRoute::_($return, false));

		return true;
	}
}
