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
class MonitorViewCommentHtml extends MonitorViewEdit
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'comment';

	/**
	 * @var MonitorModelComment
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
		$this->item = $this->model->getComment();

		if ($this->getLayout() == null)
		{
			$this->setLayout('edit');
		}

		$this->form = $this->model->getForm();

		// Attachments
		$this->attachments = $this->model->getCommentAttachments();

		$this->addToolbar();

		return parent::render();
	}
}
