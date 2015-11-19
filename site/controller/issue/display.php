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
		$issueId = $this->input->getInt('id');
		$user    = JFactory::getUser();

		$model = new MonitorModelIssue;
		$model->setIssueId($issueId);
		$modelComment       = new MonitorModelComment;
		$modelSubscriptions = new MonitorModelSubscription;

		$view = new MonitorViewIssueHtml($model, null, $modelComment, $modelSubscriptions);
		echo $view->render();

		if (!$user->guest)
		{
			$modelNotifications = new MonitorModelNotifications;
			$modelNotifications->markRead($issueId, $user->id);
		}
	}
}
