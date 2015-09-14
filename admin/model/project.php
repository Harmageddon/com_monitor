<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

use Joomla\Input\Input;

defined('_JEXEC') or die;

/**
 * Model for retrieving data about projects.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelProject extends MonitorModelAbstract
{
	/**
	 * @var int ID of the project to be loaded.
	 */
	private $projectId;

	/**
	 * @var array All valid ordering options.
	 */
	private $orderOptions = array(
		"id ASC",
		"id DESC",
		"name ASC",
		"name DESC",
	);

	/**
	 * Retrieves the project with the ID given in $projectId from the database.
	 *
	 * @return mixed Object describing the project; null, if $projectId is not set or the project doesn't exist.
	 */
	public function getProject()
	{
		if (!$this->projectId)
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$query->select('id, name, alias, description')
			->from('#__monitor_projects')
		->where("id = " . $query->q($this->projectId));

		$this->db->setQuery($query);

		return $this->db->loadObject();
	}

	/**
	 * Loads a list of all projects from the database.
	 *
	 * @return mixed Array containing objects for all projects.
	 */
	public function getProjects()
	{
		$query = $this->db->getQuery(true);
		$query->select('id, name, alias')
			->from('#__monitor_projects');

		$this->countItems($query);

		if ($this->list !== null && isset($this->list['fullordering']) && in_array($this->list['fullordering'], $this->orderOptions))
		{
			$query->order($this->list['fullordering']);
		}

		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Prepares and binds the form.
	 *
	 * @return void
	 */
	public function loadForm()
	{
		JForm::addFormPath(__DIR__ . '/forms');
		$this->form = JForm::getInstance('com_monitor.project', 'project');

		if ($this->projectId)
		{
			$this->form->bind($this->getProject());
		}
	}

	/**
	 * Saves a project entity.
	 *
	 * @param   Input  $input  Holds the data to be saved.
	 *
	 * @return  mixed   A database cursor resource on success, boolean false on failure.
	 *
	 * @throws Exception
	 */
	public function save($input)
	{
		$query = $this->db->getQuery(true);
		$values = array (
			"name" => $input->getString('name'),
			"alias" => $input->getString('alias'),
			"description" => $input->getString('description'),
		);
		$id = $input->getInt('id');

		if ($id != 0)
		{
			$query->update('#__monitor_projects')
				->where('id = ' . $id);
		}
		else
		{
			$query->insert('#__monitor_projects');
		}

		if ($values['alias'] == null)
		{
			if (JFactory::getConfig()->get('unicodeslugs') == 1)
			{
				$values['alias'] = JFilterOutput::stringURLUnicodeSlug($values['name']);
			}
			else
			{
				$values['alias'] = JFilterOutput::stringURLSafe($values['name']);
			}
		}

		$twin = $this->resolveAlias($values['alias']);

		if ($twin && $twin != $id)
		{
			JFactory::getApplication()->enqueueMessage(JText::_('COM_MONITOR_ERROR_DUPLICATE_PROJECT_ALIAS'), 'error');

			return false;
		}

		$query->set(MonitorHelper::sqlValues($values, $query));

		$this->db->setQuery($query);

		return $this->db->execute();
	}

	/**
	 * Checks if a desired alias is already taken.
	 *
	 * @param   String  $alias  The alias to check.
	 *
	 * @return bool
	 */
	public function resolveAlias($alias)
	{
		$query = $this->db->getQuery(true);

		$query->select('id')
			->from('#__monitor_projects')
			->where('alias = "' . $alias . '"');

		$this->db->setQuery($query)->execute();

		return $this->db->loadResult();
	}

	/**
	 * Deletes entities from the database.
	 *
	 * @param   int[]  $ids  IDs of the entities to be deleted.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function delete($ids)
	{
		$conditions = array();

		foreach ($ids as $id)
		{
			$conditions[] = 'id = ' . (int) $id;
		}

		$query = $this->db->getQuery(true);

		$query->delete('#__monitor_projects')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		$this->db->execute();
	}

	/**
	 * Sets the project ID.
	 *
	 * @param   int  $projectId  Project ID.
	 *
	 * @return void
	 */
	public function setProjectId($projectId)
	{
		$this->projectId = $projectId;
	}

	/**
	 * Gets the project ID.
	 *
	 * @return int
	 */
	public function getProjectId()
	{
		return $this->projectId;
	}
}
