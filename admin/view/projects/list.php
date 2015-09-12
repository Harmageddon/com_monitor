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
 * View to display a list of projects.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewProjectsList extends MonitorViewList
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'project';

	/**
	 * @var string Icon to be displayed for backend views.
	 */
	protected $icon = 'folder';

	/**
	 * @var MonitorModelProject
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
		$projects = $this->model->getProjects();

		$this->items = $projects;
		$this->setLayout('default');

		$this->addToolbar();

		return parent::render();
	}
}
