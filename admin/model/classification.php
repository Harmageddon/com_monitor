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
 * Model for retrieving data about issue classifications and their associated comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelClassification extends MonitorModelAbstract
{
	/**
	 * @var int ID of the project (needed for
	 *          loading of classifications from a specified project).
	 */
	private $projectId;

	/**
	 * @var int ID of the classification.
	 */
	private $classificationId;

	/**
	 * Retrieves an classification from the database.
	 *
	 * @return stdClass Object containing all relevant information about the classification, if it exists;
	 *                  null if no classification with the given ID was found.
	 */
	public function getClassification()
	{
		$query = $this->buildQuery();
		$query->where('c.id = ' . (int) $this->classificationId);

		$this->db->setQuery($query);

		$classification = $this->db->loadObject();

		if ($classification)
		{
			$this->projectId = $classification->project_id;
		}

		return $classification;
	}

	/**
	 * Retrieves a set of classifications from the database.
	 *
	 * @return array All classifications, if $projectId is not set or 0.
	 *               All classifications belonging to the project specified in $projectId.
	 */
	public function getClassifications()
	{
		$query = $this->buildQuery();

		// Filter by project
		if ($this->filters !== null && isset($this->filters['project_id']) && (int) $this->filters['project_id'] !== 0)
		{
			$query->where('c.project_id = ' . (int) $this->filters['project_id']);
		}

		// Filter by title / text
		if ($this->filters !== null && !empty($this->filters['search']))
		{
			$query->where('c.title LIKE "%' . $this->filters['search'] . '%"');
		}

		$this->countItems($query);

		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Prepares the part of the select query that is common to all retrieval operations.
	 *
	 * @return JDatabaseQuery Common part of the query.
	 */
	private function buildQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select('c.id, c.title, c.project_id, c.access, a.title AS viewlevel, p.name AS project_name')
			->from('#__monitor_issue_classifications AS c')
			->leftJoin('#__monitor_projects AS p ON p.id = c.project_id')
			->leftJoin('#__viewlevels AS a ON a.id = c.access');

		return $query;
	}

	/**
	 * Saves a classification entity.
	 *
	 * @param   JInput  $input  Holds the data to be saved.
	 *
	 * @return  int   ID of the inserted / saved object.
	 *
	 * @throws Exception
	 */
	public function save($input)
	{
		$query = $this->db->getQuery(true);
		$user = JFactory::getUser();

		$values = array (
			"title" => $input->getString('title'),
			"access"  => $input->getString('access'),
			"project_id" => $input->getInt('project_id'),
		);

		$values = $this->validate($values);

		if (!$values)
		{
			return false;
		}

		$id = $input->getInt('id');

		if ($id != 0)
		{
			$query->update('#__monitor_issue_classifications')
				->where('id = ' . $id);
		}
		else
		{
			$query->insert('#__monitor_issue_classifications');
		}

		$query->set(MonitorHelper::sqlValues($values, $query));

		$this->db->setQuery($query);

		$this->db->execute();

		if ($id != 0)
		{
			return $id;
		}

		return $this->db->insertid();
	}

	/**
	 * Prepares and binds the form.
	 *
	 * @return void
	 */
	public function loadForm()
	{
		JForm::addFormPath(__DIR__ . '/forms');
		JForm::addFieldPath(__DIR__ . '/fields');
		$this->form = JForm::getInstance('com_monitor.classification', 'classification');

		if ($data = $this->app->getUserState($this->form->getName() . '.data'))
		{
			$this->form->bind($data);
		}
		elseif ($this->classificationId)
		{
			$this->form->bind($this->getClassification());
		}
		else
		{
			$data = array('project_id' => $this->app->input->getInt('project_id'));
			$this->form->bind($data);
		}
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
	 * Sets the classification ID.
	 *
	 * @param   int  $id  Classification ID.
	 *
	 * @return void
	 */
	public function setClassificationId($id)
	{
		$this->classificationId = $id;
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

		$query->delete('#__monitor_issue_classifications')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		$this->db->execute();
	}
}
