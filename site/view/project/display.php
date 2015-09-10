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
 * View to display a single project.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewProjectDisplay extends MonitorViewAbstract
{
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
		$project = $this->model->getProject();

		$this->item = $project;
		$this->setLayout('default');

		if ($this->item)
		{
			$this->defaultTitle = $this->escape($this->item->name);
		}

		return parent::render();
	}
}
