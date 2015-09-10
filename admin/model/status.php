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
 * Model for retrieving data about status entries.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelStatus extends MonitorModelAbstract
{
	/**
	 * @var int ID of the status to be loaded.
	 */
	private $statusId;

	/**
	 * Retrieves the status with the ID given in $statusId from the database.
	 *
	 * @return mixed Object describing the status; null, if $statusId is not set or the status doesn't exist.
	 */
	public function getStatus()
	{
		if (!$this->statusId)
		{
			return null;
		}

		$query = $this->buildQuery();
		$query->select('s.style, s.helptext')
			->where("s.id = " . $query->q($this->statusId));

		$this->db->setQuery($query);

		return $this->db->loadObject();
	}

	/**
	 * Loads a list of all status entries from the database.
	 *
	 * @return mixed Array containing objects for all status entries.
	 */
	public function getAllStatus()
	{
		$query = $this->buildQuery();

		$query->order('s.project_id, s.ordering');

		// Filter by project
		if ($this->filters !== null && isset($this->filters['project_id']) && (int) $this->filters['project_id'] !== 0)
		{
			$query->where('s.project_id = ' . (int) $this->filters['project_id']);
		}

		// Filter by title / text
		if ($this->filters !== null && !empty($this->filters['search']))
		{
			$query->where('s.name LIKE "%' . $this->filters['search'] . '%"');
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
		$query->select('s.id, s.ordering, s.name, s.open, s.is_default, s.project_id, p.name AS project_name')
			->from('#__monitor_status AS s')
			->leftJoin('#__monitor_projects AS p ON s.project_id = p.id');

		return $query;
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
		$this->form = JForm::getInstance('com_monitor.status', 'status');

		if ($this->statusId)
		{
			$this->form->bind($this->getStatus());
		}
	}

	/**
	 * Saves a status entity.
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
		$values = array (
			"name" => $input->getString('name'),
			"helptext" => $input->getString('helptext'),
			"open" => $input->getBool('open'),
			"style" => $input->getString('style'),
			"project_id" => $input->getInt('project_id'),
		);
		$id = $input->getInt('id');

		if ($id != 0)
		{
			$query->update('#__monitor_status')
				->where('id = ' . $id);
		}
		else
		{
			$query->insert('#__monitor_status');
			$orderQuery = $this->db->getQuery(true);
			$orderQuery->select('MAX(ordering)')
				->from('#__monitor_status')
				->where('project_id = ' . $values["project_id"]);
			$this->db->setQuery($orderQuery)->execute();

			$values["ordering"] = ((int) $this->db->loadResult()) + 1;
		}

		$query->set(MonitorHelper::sqlValues($values, $query));

		$this->db->setQuery($query);

		return $this->db->execute();
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

		$query->delete('#__monitor_status')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		return $this->db->execute();
	}

	/**
	 * Sets the "open" value of one or several status entries.
	 *
	 * @param   mixed    $ids   Array of IDs or one ID of the changed entities.
	 * @param   boolean  $open  Value for the "open" field.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function open($ids, $open)
	{
		$conditions = array();

		if (is_array($ids))
		{
			foreach ($ids as $id)
			{
				$conditions[] = 'id = ' . (int) $id;
			}
		}
		elseif ((int) $ids != 0)
		{
			$conditions[] = 'id = ' . (int) $ids;
		}
		else
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$query->update('#__monitor_status')
			->set('open = ' . (($open === true) ? '1' : '0'))
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		return $this->db->execute();
	}

	/**
	 * Changes the ordering of a status (together with its successor or predecessor).
	 *
	 * @param   int        $id  ID of the status to change.
	 * @param   bool|true  $up  True, if the status should moved up (smaller ordering index), false if down.
	 *
	 * @return null
	 *
	 * @throws Exception
	 */
	public function order($id, $up = true)
	{
		$this->db->transactionStart();

		try
		{
			$query = $this->db->getQuery(true);
			$query->select('ordering, project_id')
				->from('#__monitor_status')
				->where('id = ' . $id);
			$this->db->setQuery($query)->execute();

			$result = $this->db->loadObject();

			$query = $this->db->getQuery(true);
			$query->update('#__monitor_status');

			if ($up === true)
			{
				$query->set('ordering = ordering + 1')
					->where('ordering = ' . ($result->ordering - 1), 'AND')
					->where('project_id = ' . $result->project_id);
			}
			else
			{
				$query->set('ordering = ordering - 1')
					->where('ordering = ' . ($result->ordering + 1), 'AND')
					->where('project_id = ' . $result->project_id);
			}

			$this->db->setQuery($query)->execute();

			// If nothing is below / above, cancel the transaction.
			if ($this->db->getAffectedRows() === 0)
			{
				$this->db->transactionRollback();

				return null;
			}

			$query = $this->db->getQuery(true);
			$query->update('#__monitor_status')
				->where('id = ' . $id);

			if ($up === true)
			{
				$query->set('ordering = ordering - 1');
			}
			else
			{
				$query->set('ordering = ordering + 1');
			}

			$this->db->setQuery($query)->execute();

			$this->db->transactionCommit();
		}
		catch (\Exception $e)
		{
			// Roll back the transaction on error
			$this->db->transactionRollback();

			throw $e;
		}
	}

	/**
	 * Sets a status to default.
	 *
	 * @param   int  $id  ID of the new default status.
	 *
	 * @return null
	 */
	public function setDefault($id)
	{
		$id = (int) $id;

		if ($id == 0)
		{
			return null;
		}

		$this->db->transactionStart();

		try
		{
			$query = $this->db->getQuery(true);
			$query->select('project_id')
				->from('#__monitor_status')
				->where('id = ' . $id);
			$this->db->setQuery($query)->execute();

			$project = $this->db->loadResult();

			$query = $this->db->getQuery(true);
			$query->update('#__monitor_status')
				->set('is_default = 0')
				->where('project_id = ' . $project);

			$this->db->setQuery($query)->execute();

			$query = $this->db->getQuery(true);
			$query->update('#__monitor_status')
				->set('is_default = 1')
				->where('id = ' . $id);

			$this->db->setQuery($query)->execute();

			$this->db->transactionCommit();
		}
		catch (\Exception $e)
		{
			// Roll back the transaction on error
			$this->db->transactionRollback();

			throw $e;
		}
	}

	/**
	 * Sets the status ID.
	 *
	 * @param   int  $statusId  Status ID.
	 *
	 * @return void
	 */
	public function setStatusId($statusId)
	{
		$this->statusId = $statusId;
	}
}
