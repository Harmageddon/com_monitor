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
 * View to display a list of comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewCommentsList extends MonitorViewAbstract
{
	/**
	 * @var MonitorModelComment
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
		$this->items = $this->model->getComments();

		$this->filterForm = $this->model->getFilterForm();

		$this->defaultTitle = JText::_('COM_MONITOR_COMMENTS');

		$this->setLayout('default');

		return parent::render();
	}

	/**
	 * Renders a filter field for the list display.
	 *
	 * @param   string  $fieldName  Name of the filter field.
	 *
	 * @return   string  HTML output.
	 */
	public function renderFilterField($fieldName)
	{
		$filters = $this->filterForm->getGroup('filter');

		$class = ($filters[$fieldName]->value == '') ? '' : ' filter-selected';

		return '<div class="controls span6' . $class . '">'
			. $filters[$fieldName]->label
			. $filters[$fieldName]->input
			. '</div>';
	}

	/**
	 * Renders a list field (ordering, limit) for the list display.
	 *
	 * @param   string  $fieldName  Name of the list field.
	 *
	 * @return   string  HTML output.
	 */
	public function renderListField($fieldName)
	{
		$list = $this->filterForm->getGroup('list');

		return '<div class="controls span6">'
		. $list['list_' . $fieldName]->label
		. $list['list_' . $fieldName]->input
		. '</div>';
	}
}
