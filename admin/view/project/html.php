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
 * View to edit a project.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewProjectHtml extends MonitorViewEdit
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'project';

	/**
	 * @var MonitorModelProject
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
		$this->item = $this->model->getProject();

		if ($this->getLayout() == null)
		{
			$this->setLayout('default');
		}

		$this->form = $this->model->getForm();

		$this->addToolbar();

		return parent::render();
	}
}
