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
 * View to edit an issue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewIssueHtml extends MonitorViewEdit
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'issue';

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
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'), 403);
			}

			$this->canEditIssue = $this->model->canEdit($user, $this->item->id);
		}

		if ($this->getLayout() == null)
		{
			$this->setLayout('edit');
		}

		$this->form = $this->model->getForm();

		$this->addToolbar();

		return parent::render();
	}
}
