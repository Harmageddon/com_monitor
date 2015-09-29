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
 * Displays an issue along with comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerIssueDisplay extends JControllerBase
{
	/**
	 * Execute the controller, instantiate model and view and display the issue.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$model = new MonitorModelIssue;
		$model->setIssueId($this->input->getInt('id'));
		$modelComment = new MonitorModelComment;
		$view = new MonitorViewIssueHtml($model, null, $modelComment);
		echo $view->render();
	}
}
