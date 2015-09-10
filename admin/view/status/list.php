<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');

/**
 * View to display a list of status entries.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewStatusList extends MonitorViewList
{
	/**
	 * @var string Name of the view.
	 */
	protected $name = 'status';

	/**
	 * @var string Suffix to append to the title for plural form.
	 */
	protected $pluralSuffix = '';

	/**
	 * @var string Icon to be displayed for backend views.
	 */
	protected $icon = 'folder';

	/**
	 * @var MonitorModelStatus
	 */
	protected $model;

	/**
	 * @var bool Indicates if the current user is allowed to edit status entries.
	 */
	protected $canEditStatus = false;

	/**
	 * @var bool Indicates if the current user is allowed to edit projects.
	 */
	protected $canEditProjects = false;

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
		$status = $this->model->getAllStatus();

		$this->items = $status;

		$user = JFactory::getUser();
		$this->canEditProjects = $user->authorise('project.edit', 'com_monitor');
		$this->canEditStatus = $user->authorise('status.edit', 'com_monitor');

		$this->setLayout('default');

		$this->addToolbar();

		return parent::render();
	}

	/**
	 * Sets up the toolbar for backend editing.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();

		parent::addToolbar();

		if ($user->authorise('status.edit', 'com_monitor'))
		{
			JToolbarHelper::makeDefault('status.default');
			JToolbarHelper::custom('status.open', 'eye-open', '', 'COM_MONITOR_STATUS_TASK_OPEN');
			JToolbarHelper::custom('status.close', 'eye-close', '', 'COM_MONITOR_STATUS_TASK_CLOSE');
		}
	}
}
