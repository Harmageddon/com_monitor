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
	 * @var array Contains all avatars of the authors of the issue and comments.
	 */
	protected $avatars = null;

	/**
	 * @var MonitorModelIssue
	 */
	protected $model;

	/**
	 * @var MonitorModelComment
	 */
	protected $modelComment;

	/**
	 * @var MonitorModelSubscription
	 */
	protected $modelSubscription;

	/**
	 * Contains the buttons for the button toolbar on top of the issue.
	 *
	 * @var array
	 */
	protected $buttons = array();

	/**
	 * Contains all attachments for this issue.
	 *
	 * @var array
	 */
	protected $attachments;

	/**
	 * Constructor for all views.
	 *
	 * @param   MonitorModelIssue         $modelIssue         Model providing information on issues.
	 * @param   SplPriorityQueue          $paths              The paths queue.
	 * @param   MonitorModelComment       $modelComment       Model providing information on comments.
	 * @param   MonitorModelSubscription  $modelSubscription  Model providing information aboutsubscriptions.
	 *
	 * @throws Exception
	 */
	public function __construct(MonitorModelIssue $modelIssue, SplPriorityQueue $paths = null, MonitorModelComment $modelComment = null,
		MonitorModelSubscription $modelSubscription = null)
	{
		$this->modelComment      = $modelComment;
		$this->modelSubscription = $modelSubscription;

		parent::__construct($modelIssue, $paths);
	}

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
		$app = JFactory::getApplication();

		if ($this->item)
		{
			if (!in_array($this->item->access, $user->getAuthorisedViewLevels()))
			{
				if ($user->guest && $this->params->get('redirect_login', 1))
				{
					$app->enqueueMessage(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'error');
					$app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()), '403'));
				}
				else
				{
					throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
				}
			}

			$this->canEditIssue = $this->model->canEdit($user, $this->item->id);

			// Title
			if ($this->layout === 'edit')
			{
				$this->defaultTitle = JText::sprintf('COM_MONITOR_EDIT_ISSUE', $this->item->title);
			}
			else
			{
				$this->defaultTitle = $this->escape($this->item->title);
			}

			// Avatar
			if (JPluginHelper::isEnabled('user', 'cmavatar'))
			{
				// Include the CMAvatar plugin.
				require_once JPATH_PLUGINS . '/user/cmavatar/helper.php';

				$this->avatars = array();
				$this->avatars[$this->item->author_id] = PlgUserCMAvatarHelper::getAvatar($this->item->author_id);
			}

			// Buttons
			if ($this->getLayout() === 'default')
			{
				if ($this->canEditIssue)
				{
					$this->buttons['edit'] = array(
							'url'   => 'index.php?option=com_monitor&task=issue.edit&id=' . $this->item->id,
							'text'  => 'JGLOBAL_EDIT',
							'title' => 'JGLOBAL_EDIT',
							'icon'  => 'icon-pencil-2',
					);
				}

				if ($this->params->get('enable_notifications', 1) && !$user->guest)
				{
					$subscribed = $this->modelSubscription->isSubscriberIssue($this->item->id, $user->id);
					$task       = $subscribed ? 'unsubscribe' : 'subscribe';

					$this->buttons['subscribe'] = array(
							'url'   => 'index.php?option=com_monitor&task=issue.' . $task . '&id=' . $this->item->id,
							'text'  => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_ISSUE' : 'COM_MONITOR_SUBSCRIBE_ISSUE',
							'title' => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_ISSUE_DESC' : 'COM_MONITOR_SUBSCRIBE_ISSUE_DESC',
							'icon'  => $subscribed ? 'icon-star' : 'icon-star-empty',
					);
				}
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

		// Comments
		if ($this->modelComment)
		{
			$this->comments = $this->modelComment->getIssueComments($this->item->id);

			foreach ($this->comments as $comment)
			{
				// Avatars
				if ($this->avatars !== null)
				{
					if (!isset($this->avatars[$comment->author_id]))
					{
						$this->avatars[$comment->author_id] = PlgUserCMAvatarHelper::getAvatar($comment->author_id);
					}
				}

				// Attachments
				$comment->attachments = $this->modelComment->getCommentAttachments($comment->id);
			}

			// Pagination
			$this->pagination = $this->modelComment->getPagination();

			// Ordering
			$this->listOrder = $this->escape($this->modelComment->getState()->get('list.ordering'));
			$this->listDir   = $this->escape($this->modelComment->getState()->get('list.direction'));
		}

		$this->status = $this->model->getStatus();

		$this->defaultStatus = $this->model->getDefaultStatus();

		// Attachments
		$this->attachments = $this->model->getAttachments();

		// Process the content plugins.
		$dispatcher	= JEventDispatcher::getInstance();
		JPluginHelper::importPlugin('content');

		if ($this->item && $this->getLayout() === 'default')
		{
			$dispatcher->trigger('onContentPrepare', array('com_monitor.issue', &$this->item, &$this->params, 0));

			$this->item->event = new stdClass;
			$results = $dispatcher->trigger('onContentAfterTitle', array('com_monitor.issue', &$this->item, &$this->params, 0));
			$this->item->event->afterDisplayTitle = trim(implode("\n", $results));

			$results  = $dispatcher->trigger('onContentBeforeDisplay', array('com_monitor.issue', &$this->item, &$this->params, 0));
			$this->item->event->beforeDisplayContent = trim(implode("\n", $results));

			$results = $dispatcher->trigger('onContentAfterDisplay', array('com_monitor.issue', &$this->item, &$this->params, 0));
			$this->item->event->afterDisplayContent = trim(implode("\n", $results));

			if ($this->comments)
			{
				foreach ($this->comments as $comment)
				{
					$dispatcher->trigger('onContentPrepare', array('com_monitor.comment', &$comment, &$this->params, 0));
				}
			}
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
