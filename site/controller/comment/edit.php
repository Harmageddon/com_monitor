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
 * Edit or create a comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentEdit extends JControllerBase
{
	/**
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Execute the controller, load a form for the given comment.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$model = new MonitorModelComment;
		$id = $this->input->getInt('id');

		// ACL: Check if user can create
		if (!$model->canEdit(JFactory::getUser(), $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		if ($id != 0)
		{
			$model->setCommentId($id);
		}
		else
		{
			$issue_id = $this->input->getInt('issue_id');

			if ($issue_id == 0)
			{
				$this->app->enqueueMessage(JText::_('COM_MONITOR_ERROR_INVALID_ID'), 'error');

				return false;
			}

			$model->setIssueId($issue_id);
		}

		$model->loadForm();
		$view = new MonitorViewCommentHtml($model);
		$view->setLayout('edit');
		echo $view->render();

		return true;
	}
}
