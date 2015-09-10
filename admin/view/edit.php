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
 * Abstract class providing functionality common to all editing views.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
abstract class MonitorViewEdit extends MonitorViewAbstract
{
	protected $icon = 'pencil';

	/**
	 * @var mixed Object containing data about the item to be edited.
	 */
	protected $item;

	/**
	 * @var JForm needed for the edit form.
	 */
	protected $form;

	/**
	 * Sets up the toolbar for backend editing.
	 *
	 * @return void
	 */
	protected function addToolbar()
	{
		JToolbarHelper::title(JText::_('COM_MONITOR_' . strtoupper($this->name)), $this->icon);

		JToolbarHelper::apply($this->name . '.save');
		JToolbarHelper::cancel($this->name . '.cancel');
	}
}
