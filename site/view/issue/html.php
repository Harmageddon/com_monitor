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
 * View to display information about an issue along with its comments or to edit an issue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewIssueHtml extends MonitorViewAbstract
{
	/**
	 * @var mixed List of comments for the issue.
	 */
	protected $comments;

	/**
	 * @var mixed Current status of the issue.
	 */
	protected $status;

	/**
	 * @var mixed Default status value.
	 */
	protected $defaultStatus;

	/**
	 * @var JForm Form object for editing.
	 */
	protected $form;

	/**
	 * @var mixed Object containing data about the item to be edited or displayed.
	 */
	protected $item;

	/**
	 * @var bool Indicates if the current user is allowed to edit this issue.
	 */
	protected $canEditIssue = false;

	/**
	 * @var bool Indicates if the current user is allowed to delete comments.
	 */
	protected $canDeleteComments = false;

	/**
	 * @var bool Indicates if the current user is allowed to edit all comments.
	 */
	protected $canEditComments = false;

	/**
	 * @var bool Indicates if the current user is allowed to edit own comments.
	 */
	protected $canEditOwnComments = false;

	/**
	 * @var MonitorModelIssue
	 */
	protected $model;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  RuntimeException
	 */
	public function render()
	{
		$this->item = $this->model->getIssue();

		$user = JFactory::getUser();

		if ($this->item)
		{
			if (!in_array($this->item->access, $user->getAuthorisedViewLevels()))
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
			}

			$this->canEditIssue = $this->model->canEdit($user, $this->item->id);

			if ($this->layout === 'edit')
			{
				$this->defaultTitle = JText::sprintf('COM_MONITOR_EDIT_ISSUE', $this->item->title);
			}
			else
			{
				$this->defaultTitle = $this->escape($this->item->title);
			}
		}
		else
		{
			$this->canEditIssue = $this->model->canEdit($user);
			$this->defaultTitle = JText::_('COM_MONITOR_CREATE_ISSUE');
		}

		$this->canDeleteComments = $user->authorise('comment.delete', 'com_monitor');
		$this->canEditComments = $user->authorise('comment.edit', 'com_monitor');
		$this->canEditOwnComments = $user->authorise('comment.edit.own', 'com_monitor');

		$this->comments = $this->model->getComments();
		$this->status = $this->model->getStatus();

		$this->defaultStatus = $this->model->getDefaultStatus();

		// Process the content plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');
		$dispatcher->trigger('onContentPrepare', array ('com_monitor.issue', &$this->item, &$this->params, 0));

		$this->item->event = new stdClass;
		$results = $dispatcher->trigger('onContentAfterTitle', array('com_monitor.issue', &$this->item, &$this->params, 0));
		$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentBeforeDisplay', array('com_monitor.issue', &$this->item, &$this->params, 0));
		$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

		$results = $dispatcher->trigger('onContentAfterDisplay', array('com_monitor.issue', &$this->item, &$this->params, 0));
		$this->item->event->afterDisplayContent = trim(implode("\n", $results));

		foreach ($this->comments as $comment)
		{
			$dispatcher->trigger('onContentPrepare', array ('com_monitor.comment', &$comment, &$this->params, 0));
		}

		return parent::render();
	}

	/**
	 * Loads the form.
	 *
	 * @return void
	 */
	public function loadForm()
	{
		$this->form = $this->model->getForm();
	}
}
