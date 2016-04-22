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
 * Displays comments, optionally filtered, e.g. by author.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentsDisplay extends JControllerBase
{
	/**
	 * Execute the controller, instantiate model and view and display the issue.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$model = new MonitorModelComment;

		$view = new MonitorViewCommentsList($model);

		echo $view->render();
	}
}
