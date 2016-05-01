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
			if ($this->filters = $this->app->getUserStateFromRequest('filter.' . $this->prefix, 'filter', array(), 'array'))
			{
				foreach ($this->filters as $filter => $value)
				{
					$this->getState()->set('filter.' . $this->prefix . '.' . $filter, $value);
				}
			}

			// Receive & set list options
			if ($this->list = $this->app->getUserStateFromRequest('list.' . $this->prefix, 'list', array(), 'array'))
			{
				if (isset($this->list['fullordering']) && $this->list['fullordering'])
				{
					$fullOrdering            = explode(' ', $this->list['fullordering']);
					$this->list['ordering']  = $fullOrdering[0];
					$this->list['direction'] = $fullOrdering[1];
				}

				foreach ($this->list as $key => $value)
				{
					$this->getState()->set('list.' . $this->prefix . '.' . $key, $value);
				}
			}

			if (!isset($this->list['limit'])
				&& ($limit = $this->app->getUserStateFromRequest('list.' . $this->prefix . '.limit', 'limit', null)) !== null)
			{
				$this->list['limit'] = $limit;
				$this->getState()->set('list.' . $this->prefix . '.limit', $limit);
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

		$offset = (isset($this->list['offset'])) ? $this->list['offset'] : $this->getState()->get('list.' . $this->prefix . '.offset', 0);
		$offset = $this->app->input->getInt('limitstart', $offset);
		$this->getState()->set('list.' . $this->prefix . 'offset', $offset);

		$defaultLimit = $this->app->get('list_limit');
		$limit = (isset($this->list['limit'])) ? $this->list['limit'] : $this->getState()->get('list.' . $this->prefix . '.limit', $defaultLimit);
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
	 * Returns all used filters whose values are not empty.
	 *
	 * @return  array  Active filters and their values.
	 */
	public function getActiveFilters()
	{
		return array_filter(
			$this->filters,
			function ($v)
			{
				return !empty($v);
			}
		);
	}

	/**
	 * Validates data from a form.
	 *
	 * @param   array  $data  The data to validate.
	 * @param   JForm  $form  The form to use for validation.
	 *
	 * @return  mixed|bool  Array of filtered data if valid, false otherwise.
	 *
	 * @see ConfigModelForm::validate
	 */
	public function validate($data, $form = null)
	{
		if (!$form)
		{
			if (!$this->form)
			{
				$this->loadForm();
			}

			$form = $this->form;
		}

		// Filter and validate the form data.
		$data   = $form->filter($data);

		$return = $form->validate($data);

		// Store data for eventual redirects.
		$this->app->setUserState($form->getName() . '.data', $data);

		// Check for an error.
		if ($return instanceof Exception)
		{
			$this->app->enqueueMessage($return->getMessage(), 'error');

			return false;
		}

		// Check the validation results.
		if ($return === false)
		{
			// Get the validation messages from the form.
			foreach ($form->getErrors() as $message)
			{
				$this->app->enqueueMessage($message->getMessage(), 'error');
			}

			return false;
		}

		$this->app->setUserState($form->getName() . '.data', null);

		return $data;
	}

	/**
	 * Validates uploaded files from a form.
	 *
	 * @param   array                    $files             Array containing the files to validate.
	 * @param   array                    $data              Other data from the form.
	 * @param   MonitorModelAttachments  $modelAttachments  Model to use.
	 *
	 * @return  array|null  A filtered array if all files are valid, null if not.
	 */
	public function validateFiles($files, $data, $modelAttachments = null)
	{
		if (!$modelAttachments)
		{
			$modelAttachments = new MonitorModelAttachments($this->app);
		}

		foreach ($files as $i => $file)
		{
			if ($file[0]['error'] === UPLOAD_ERR_NO_FILE)
			{
				unset($files[$i]);
			}
		}

		if (!$modelAttachments->canUpload($files))
		{
			// Store data for redirects.
			if (!$this->form)
			{
				$this->loadForm();
			}

			$this->app->setUserState($this->form->getName() . '.data', $data);

			return null;
		}

		return $files;
	}

	/**
	 * Get the configuration for the component and (if given) the active menu item.
	 *
	 * TODO: may be removed when new MVC is implemented completely
	 *
	 * @return  \Joomla\Registry\Registry  Object containing the parameters.
	 */
	public function getParams()
	{
		if ($this->app instanceof JApplicationSite)
		{
			$params = $this->app->getParams();
			$active = $this->app->getMenu()->getActive();

			if ($active)
			{
				$params->merge($active->params);
			}
		}
		else
		{
			$params = JComponentHelper::getParams('com_monitor');
		}

		return $params;
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
