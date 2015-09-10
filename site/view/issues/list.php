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
 * View to display a list of issues.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewIssuesList extends MonitorViewAbstract
{
	/**
	 * @var MonitorModelIssue
	 */
	protected $model;

	/**
	 * @var   mixed  Items to be displayed.
	 */
	protected $items;

	/**
	 * @var JForm The filter form.
	 */
	public $filterForm;

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
		$issues = $this->model->getIssues();

		$this->items = array_filter(
			$issues,
			function ($issue)
			{
				$user = JFactory::getUser();

				return in_array($issue->access, $user->getAuthorisedViewLevels());
			}
		);

		$this->filterForm = $this->model->getFilterForm();

		$this->defaultTitle = JText::_('COM_MONITOR_ISSUES');

		$this->setLayout('default');

		return parent::render();
	}
}
