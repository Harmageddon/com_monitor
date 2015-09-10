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
 * Abstract class providing functionality common to all list views.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
abstract class MonitorViewList extends MonitorViewAbstract
{
	/**
	 * @var mixed Set of items to be displayed.
	 */
	protected $items;

	/**
	 * @var string Suffix to append to the title for plural form.
	 */
	protected $pluralSuffix = 's';

	/**
	 * @var JForm The filter form.
	 */
	public $filterForm;

	/**
	 * Sets up the toolbar for backend editing.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		$user = JFactory::getUser();

		JToolbarHelper::title(JText::_('COM_MONITOR_' . strtoupper($this->name . $this->pluralSuffix)), $this->icon);

		if ($user->authorise($this->name . '.create', 'com_monitor'))
		{
			JToolbarHelper::addNew($this->name . '.edit');
		}

		if ($user->authorise($this->name . '.edit', 'com_monitor'))
		{
			JToolbarHelper::editList($this->name . '.edit');
		}

		if ($user->authorise($this->name . '.delete', 'com_monitor'))
		{
			JToolbarHelper::deleteList('', $this->name . '.delete');
		}

		if ($user->authorise('core.admin', 'com_monitor'))
		{
			JToolBarHelper::preferences('com_monitor');
		}
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
		if ($this->model)
		{
			$this->filterForm = $this->model->getFilterForm();
		}

		return parent::render();
	}
}
