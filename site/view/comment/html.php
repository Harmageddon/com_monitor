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
 * View to edit a comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewCommentHtml extends MonitorViewAbstract
{
	/**
	 * @var mixed Object containing data about the item to be edited.
	 */
	protected $item;

	/**
	 * @var JForm needed for the edit form.
	 */
	protected $form;

	/**
	 * @var MonitorModelComment
	 */
	protected $model;

	/**
	 * @var int
	 */
	protected $issue_id;

	/**
	 * @var string
	 */
	protected $issue_title;

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  RuntimeException
	 */
	public function render()
	{
		$this->item = $this->model->getComment();

		if ($this->getLayout() == null)
		{
			$this->setLayout('edit');
		}

		if ($this->item)
		{
			$this->model->setIssueId($this->item->issue_id);
		}

		$this->issue_id     = $this->model->getIssueId();
		$this->issue_title  = $this->model->getIssueTitle();
		$this->form         = $this->model->getForm();
		$this->defaultTitle = JText::sprintf('COM_MONITOR_CREATE_COMMENT_TITLE', $this->issue_title);

		return parent::render();
	}
}
