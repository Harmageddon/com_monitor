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
	 * Application used in the model.
	 *
	 * @var JApplicationCms
	 */
	protected $app;

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
	 * Prefix for list option keys.
	 *
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * MonitorModelAbstract constructor.
	 *
	 * @param   JApplicationCms  $application  The Application object to use in this model.
	 * @param   boolean          $loadFilters  If set to true, filters and list options will be loaded from the page request.
	 *
	 * @throws Exception
	 */
	public function __construct($application = null, $loadFilters = true)
	{
		parent::__construct();

		if ($application)
		{
			$this->app = $application;
		}
		else
		{
			$this->app = JFactory::getApplication();
		}

		if ($loadFilters)
		{
			// Receive & set filters
			if ($this->filters = $this->app->getUserStateFromRequest('filter', 'filter', array(), 'array'))
			{
				foreach ($this->filters as $filter => $value)
				{
					$this->getState()->set('filter.' . $filter, $value);
				}
			}

			// Receive & set list options
			if ($this->list = $this->app->getUserStateFromRequest('list', 'list', array(), 'array'))
			{
				if (isset($this->list[$this->prefix . 'fullordering']) && $this->list[$this->prefix . 'fullordering'])
				{
					$fullOrdering            = explode(' ', $this->list[$this->prefix . 'fullordering']);
					$this->list[$this->prefix . 'ordering']  = $fullOrdering[0];
					$this->list[$this->prefix . 'direction'] = $fullOrdering[1];
				}

				foreach ($this->list as $key => $value)
				{
					$this->getState()->set('list.' . $this->prefix . $key, $value);
				}
			}

			$key = $this->prefix . 'limit';

			if (!isset($this->list[$key]) && ($limit = $this->app->getUserStateFromRequest($key, $key, null)) !== null)
			{
				$this->list[$key] = $limit;
				$this->getState()->set('list.' . $key, $limit);
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
		$cloned = clone $query;
		$cloned->clear('select')->select('COUNT(*)');
		$this->db->setQuery($cloned);

		$count = $this->db->loadResult();

		$offset = $this->app->input->getInt('limitstart', $this->getState()->get('list.offset', 0));
		$this->getState()->set('list.offset', $offset);

		$defaultLimit = $this->app->get('list_limit');
		$limit = $this->getState()->get('list.limit', $defaultLimit);

		$this->pagination = new JPagination($count, $offset, $limit, '', $this->app);

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
