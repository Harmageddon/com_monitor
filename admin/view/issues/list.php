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
class MonitorViewIssuesList extends MonitorViewList
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'issue';

	/**
	 * @var string Icon to be displayed for backend views.
	 */
	protected $icon = 'file-2';

	/**
	 * @var MonitorModelIssue
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
		$issues = $this->model->getIssues();

		$this->items = array_filter(
			$issues,
			function ($issue)
			{
				$user = JFactory::getUser();

				return in_array($issue->access, $user->getAuthorisedViewLevels());
			}
		);

		$this->setLayout('default');

		$this->addToolbar();

		return parent::render();
	}
}
