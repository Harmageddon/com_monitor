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
 * Cancel editing or creating action of a comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentCancel extends JControllerBase
{
	/**
	 * Execute the controller, redirect to comments view.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$app = JFactory::getApplication();

		if ($app->isAdmin())
		{
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=comments', false));
		}
		else
		{
			$issue_id = $this->input->getInt('issue_id');
			$app->redirect(JRoute::_('index.php?option=com_monitor&view=issue&id=' . $issue_id, false));
		}
	}
}
