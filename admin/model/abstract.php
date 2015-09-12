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
 * Abstract model class that provides the base functionality common to all models.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
abstract class MonitorModelAbstract extends JModelDatabase
{
	/**
	 * Pagination object for this model.
	 *
	 * @var JPagination
	 */
	private $pagination;

	/**
	 * Form object.
	 *
	 * @var JForm
	 */
	protected $form;

	/**
	 * Active filters.
	 *
	 * @var array
	 */
	protected $filters;

	/**
	 * Active list options.
	 *
	 * @var array
	 */
	protected $list;

	/**
	 * MonitorModelAbstract constructor.
	 */
	public function __construct()
	{
		parent::__construct();

		$app = JFactory::getApplication();

		// Receive & set filters
		if ($this->filters = $app->getUserStateFromRequest('filter', 'filter', array(), 'array'))
		{
			foreach ($this->filters as $filter => $value)
			{
				$this->getState()->set('filter.' . $filter, $value);
			}
		}

		// Receive & set list options
		if ($this->list = $app->getUserStateFromRequest('list', 'list', array(), 'array'))
		{
			if ($this->list['fullordering'])
			{
				$fullOrdering = explode(' ', $this->list['fullordering']);
				$this->list['ordering'] = $fullOrdering[0];
				$this->list['direction'] = $fullOrdering[1];
			}

			foreach ($this->list as $key => $value)
			{
				$this->getState()->set('list.' . $key, $value);
			}
		}
	}

	/**
	 * Counts the number of items resulting from a given database query.
	 *
	 * @param   JDatabaseQuery  $query  Query whose results should be counted.
	 *
	 * @throws Exception
	 * @return void
	 */
	public function countItems($query)
	{
		$app = JFactory::getApplication();

		$cloned = clone $query;
		$cloned->clear('select')->select('COUNT(*)');
		$this->db->setQuery($cloned);

		$count = $this->db->loadResult();

		$offset = $app->input->getInt('limitstart', $this->getState()->get('list.offset', 0));
		$this->getState()->set('list.offset', $offset);

		$defaultLimit = $app->get('list_limit');
		$limit = $this->getState()->get('list.limit', $defaultLimit);

		$this->pagination = new JPagination($count, $offset, $limit);

		if ($query instanceof JDatabaseQueryLimitable)
		{
			$query->setLimit($limit, $offset);
		}
	}

	/**
	 * Prepares and binds the filter form.
	 *
	 * @return JForm filter form.
	 */
	public function getFilterForm()
	{
		if (preg_match("/MonitorModel([a-zA-Z]*)/", get_called_class(), $matches) === 1)
		{
			$name = $matches[1];

			JForm::addFormPath(__DIR__ . '/forms');
			JForm::addFieldPath(__DIR__ . '/fields');
			$filterForm = JForm::getInstance('com_monitor.filter.' . $name, 'filter_' . $name);
			$filterForm->bind(
				array(
					"filter" => $this->filters,
					"list" => $this->list
				)
			);

			return $filterForm;
		}

		return null;
	}

	/**
	 * Prepares and binds the form.
	 *
	 * @return void
	 */
	abstract public function loadForm();

	/**
	 * Deletes entities from the database.
	 *
	 * @param   int[]  $ids  IDs of the entities to be deleted.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	abstract public function delete($ids);

	/**
	 * Method to get the pagination object.
	 *
	 * @return JPagination
	 */
	public function getPagination()
	{
		return $this->pagination;
	}

	/**
	 * Getter for the form.
	 *
	 * @return JForm
	 */
	public function getForm()
	{
		return $this->form;
	}
}
