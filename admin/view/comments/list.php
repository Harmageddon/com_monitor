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
 * View to display a list of comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewCommentsList extends MonitorViewList
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'comment';

	/**
	 * @var string Icon to be displayed for backend views.
	 */
	protected $icon = 'comments-2';

	/**
	 * @var bool Indicates if the current user is allowed to edit all issues.
	 */
	protected $canEditIssues = false;

	/**
	 * @var bool Indicates if the current user is allowed to edit own issues.
	 */
	protected $canEditOwnIssues = false;

	/**
	 * @var bool Indicates if the current user is allowed to delete issues.
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
	 * @var bool Indicates if the current user is allowed to edit projects.
	 */
	protected $canEditProjects = false;

	/**
	 * @var MonitorModelComment
	 */
	protected $model;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function render()
	{
		$this->prefix = 'comment';

		$comments = $this->model->getComments();

		$this->items = $comments;

		$user = JFactory::getUser();
		$this->canDeleteComments = $user->authorise('comment.delete', 'com_monitor');
		$this->canEditComments = $user->authorise('comment.edit', 'com_monitor');
		$this->canEditOwnComments = $user->authorise('comment.edit.own', 'com_monitor');
		$this->canEditIssues = $user->authorise('issue.edit', 'com_monitor');
		$this->canEditOwnIssues = $user->authorise('issue.edit.own', 'com_monitor');
		$this->canEditProjects = $user->authorise('project.edit', 'com_monitor');

		$this->setLayout('default');

		$this->addToolbar();

		return parent::render();
	}
}
