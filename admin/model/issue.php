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
 * Model for retrieving data about issues and their associated comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelIssue extends MonitorModelAbstract
{
	/**
	 * @var int ID of the project (needed for
	 *          loading of issues from a specified project).
	 */
	private $projectId;

	/**
	 * @var int ID of the issue.
	 */
	private $issueId;

	/**
	 * Associative array mapping issue IDs to their respective project IDs.
	 *
	 * @var array
	 */
	private $projects;

	/**
	 * @var array All valid ordering options.
	 */
	private $orderOptions = array(
		"i.id ASC",
		"i.id DESC",
		"i.title ASC",
		"i.title DESC",
		"i.created ASC",
		"i.created DESC",
		"u.name ASC",
		"u.name DESC",
		"p.name ASC",
		"p.name DESC",
	);

	/**
	 * @var stdClass The default status.
	 */
	private static $defaultStatus = null;

	/**
	 * Retrieves an issue from the database.
	 *
	 * @return stdClass Object containing all relevant information about the issue, if it exists;
	 *                  null if no issue with the given ID was found.
	 */
	public function getIssue()
	{
		$query = $this->buildQuery();
		$query->select('i.text')
			->where('i.id = ' . (int) $this->issueId);

		$this->db->setQuery($query);

		$issue = $this->db->loadObject();

		if ($issue)
		{
			$this->projectId = $issue->project_id;
		}

		return $this->fillStatus($issue);
	}

	/**
	 * Retrieves the project ID for a given issue.
	 *
	 * @param   int  $issueId  ID of the issue.
	 *
	 * @return  int|null  The project ID if the query was successful, null if it failed.
	 */
	public function getIssueProject($issueId)
	{
		if (isset($this->projects[$issueId]))
		{
			return $this->projects[$issueId];
		}

		$query = $this->db->getQuery(true);

		$query->select('project_id')
			->from('#__monitor_issues')
			->where('id = ' . (int) $issueId);

		$this->db->setQuery($query);

		$this->projects[$issueId] = $this->db->loadResult();

		return $this->projects[$issueId];
	}

	/**
	 * Retrieves a set of issues from the database.
	 *
	 * @param   array|null  $filters  Additional filters to use.
	 * @param   array|null  $list     Additional list options to use.
	 *
	 * @return array All issues, if $projectId is not set or 0.
	 *               All issues belonging to the project specified in $projectId.
	 */
	public function getIssues($filters = null, $list = null)
	{
		if ($filters)
		{
			$this->filters = $filters;
		}

		if ($list)
		{
			$this->list = $list;
		}

		$query = $this->buildQuery();

		// Filter by ACL
		$user = JFactory::getUser();
		$query->where('cl.access IN (' . implode(',', $user->getAuthorisedViewLevels()) . ')');

		// Filter by project: Filter from form takes precedence over ID given by constructor.
		if ($this->filters !== null && isset($this->filters['project_id']) && (int) $this->filters['project_id'] !== 0)
		{
			$query->where('i.project_id = ' . (int) $this->filters['project_id']);
		}
		elseif ($this->projectId !== null && (int) $this->projectId !== 0)
		{
			$query->where('i.project_id = ' . (int) $this->projectId);
		}

		// Filter by classification
		if ($this->filters !== null && isset($this->filters['classification']) && (int) $this->filters['classification'] !== 0)
		{
			$query->where('i.classification = ' . (int) $this->filters['classification']);
		}

		// Filter by author
		if ($this->filters !== null && isset($this->filters['author']) && (int) $this->filters['author'] !== 0)
		{
			$query->where('i.author_id = ' . (int) $this->filters['author']);
		}

		// Filter by status
		if ($this->filters !== null && !empty($this->filters['issue_status']))
		{
			$defaultStatus = $this->getDefaultStatus();

			// All open statuses
			if ($this->filters['issue_status'] == "open")
			{
				$cond = 'i.status in (SELECT `id` FROM `#__monitor_status` WHERE `open` = 1)';

				if ($defaultStatus->open == 1)
				{
					$cond .= ' OR i.status = 0';
				}

				$query->where($cond);
			}
			// All closed statuses
			elseif ($this->filters['issue_status'] == "closed")
			{
				$cond = 'i.status in (SELECT `id` FROM `#__monitor_status` WHERE `open` = 0)';

				if ($defaultStatus->open == 0)
				{
					$cond .= ' OR i.status = 0';
				}

				$query->where($cond);
			}
			// One specified status
			else
			{
				$status = (int) $this->filters['issue_status'];

				$cond = 'i.status = ' . $status;

				if ($defaultStatus !== null && $status == $defaultStatus->id)
				{
					$cond .= ' OR i.status = 0';
				}
			}

			$query->where($cond);
		}

		// Get the params
		// TODO: may be removed when new MVC is implemented completely
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

		// Filter by title / text
		if ($this->filters !== null && !empty($this->filters['search']))
		{
			if (!isset($params) || $params->get('list_filter_search_content', 'title') === "title")
			{
				$query->where('(i.title LIKE "%' . $this->filters['search'] . '%")');
			}
			else
			{
				$query->where('(i.title LIKE "%' . $this->filters['search'] . '%" OR i.text LIKE "%' . $this->filters['search'] . '%")');
			}
		}

		$this->countItems($query);

		if ($this->list !== null && isset($this->list['fullordering']) && in_array($this->list['fullordering'], $this->orderOptions))
		{
			$query->order($this->list['fullordering']);
		}

		$this->db->setQuery($query);

		return array_map(array($this, 'fillStatus'), $this->db->loadObjectList());
	}

	/**
	 * Sets the status of an issue to the default value,
	 * given the issue has no status yet and a default value is set.
	 *
	 * @param   stdClass  $issue  Issue object from the database.
	 *
	 * @return  stdClass Issue object with updated status.
	 *
	 * @throws Exception
	 */
	public function fillStatus($issue)
	{
		if ($issue === null)
		{
			return null;
		}

		if ($issue->status_id)
		{
			return $issue;
		}

		$result = $this->getDefaultStatus();

		if ($result)
		{
			$issue->status = $result->name;
			$issue->status_id = $result->id;
			$issue->status_style = $result->style;
		}

		return $issue;
	}

	/**
	 * Loads the default status from the database.
	 *
	 * @return mixed Object for the default status, if set; null, if no default status is set.
	 */
	public static function getDefaultStatus()
	{
		if (self::$defaultStatus )
		{
			return self::$defaultStatus;
		}

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('name, id, style, open')
			->from('#__monitor_status')
			->where('`is_default` = 1');

		$db->setQuery($query);

		self::$defaultStatus = $db->loadObject();

		return self::$defaultStatus;
	}

	/**
	 * Prepares the part of the select query that is common to all retrieval operations.
	 *
	 * @return JDatabaseQuery Common part of the query.
	 */
	private function buildQuery()
	{
		$query = $this->db->getQuery(true);

		$query->select('i.id, i.title, i.project_id, i.version, i.created, i.author_id, i.status, i.classification, p.name AS project_name')
			->select('c.created AS modified, s.name AS status, s.id AS status_id, s.style AS status_style, s.helptext AS status_help')
			->select('u.name AS author_name')
			->from('#__monitor_issues AS i')
			->leftJoin('(SELECT * FROM #__monitor_comments AS c1 INNER JOIN
				(SELECT MAX(id) AS maxid FROM #__monitor_comments GROUP BY issue_id) AS rc ON rc.maxid = c1.id) AS c
				ON c.issue_id = i.id')
			->leftJoin('#__monitor_status AS s ON i.status = s.id')
			->leftJoin('#__monitor_projects AS p ON p.id = i.project_id')
			->leftJoin('#__users AS u ON u.id = i.author_id')
			->select('contact.id AS contact_id')
			->leftJoin('#__contact_details AS contact ON contact.user_id = i.author_id AND contact.published = 1')
			->select('cl.title AS classification_title, cl.access AS access')
			->leftJoin('#__monitor_issue_classifications AS cl ON i.classification = cl.id');

		return $query;
	}

	/**
	 * Saves an issue entity.
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
			"text"  => $input->getString('text'),
			"version" => $input->getString('version'),
			"project_id" => $input->getInt('project_id'),
			"classification" => $input->getInt('classification'),
		);

		$values = $this->validate($values);

		if (!$values)
		{
			return false;
		}

		$id = $input->getInt('id');

		if ($id != 0)
		{
			$query->update('#__monitor_issues')
				->where('id = ' . $id);
		}
		else
		{
			$values["author_id"] = $user->id;
			$query->insert('#__monitor_issues');
		}

		$values["created"] = JDate::getInstance()->toSql();
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
		$this->form = JForm::getInstance('com_monitor.issue', 'issue');

		if ($data = $this->app->getUserState($this->form->getName() . '.data'))
		{
			$this->form->bind($data);
		}
		elseif ($this->issueId)
		{
			$this->form->bind($this->getIssue());
		}
		else
		{
			$data = array('project_id' => $this->app->input->getInt('project_id'));
			$this->form->bind($data);
		}
	}

	/**
	 * Loads a list of all status values for the project specified in $projectId.
	 *
	 * @return mixed Object list of all statuses of the project, if it exists; null otherwise.
	 */
	public function getStatus()
	{
		if (!$this->projectId)
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$query->select('id, name, style, helptext')
			->from('#__monitor_status')
			->where('project_id = ' . $this->projectId);

		$this->db->setQuery($query);

		return $this->db->loadObjectList('id');
	}

	/**
	 * Checks if a user is allowed to edit a certain issue.
	 *
	 * @param   JUser  $user  The user whose permissions should be checked.
	 * @param   int    $id    ID of the relevant issue. If left empty or set to 0,
	 *                        the permission to create a new issue is checked.
	 *
	 * @return bool True, if the user is allowed to edit the issue, false if not.
	 */
	public function canEdit($user, $id = 0)
	{
		$id = (int) $id;

		// If ID is 0, we create a new issue.
		if ($id == 0)
		{
			return $user->authorise('issue.create', 'com_monitor');
		}

		// If user is not allowed to edit...
		if (!$user->authorise('issue.edit', 'com_monitor'))
		{
			if (!$user->authorise('issue.edit.own', 'com_monitor'))
			{
				return false;
			}
			// ...but to edit own issue...
			$infoQuery = $this->db->getQuery(true)
				->select('author_id, classification')
				->from('#__monitor_issues')
				->where('id = ' . $id);

			$this->db->setQuery($infoQuery);
			$this->db->execute();
			$result = $this->db->loadObject();

			// ...check if the issue belongs to the user.
			if ($result->author_id != $user->id)
			{
				return false;
			}
		}

		// Check if the user has access to the issue according to its classification.
		if (!isset($result))
		{
			$infoQuery = $this->db->getQuery(true)
				->select('author_id, classification')
				->from('#__monitor_issues')
				->where('id = ' . $id);

			$this->db->setQuery($infoQuery);
			$this->db->execute();
			$result = $this->db->loadObject();
		}

		if (!in_array($result->classification, $user->getAuthorisedViewLevels()))
		{
			// Users can edit their own classifications, regardless of the classification.
			if ($result->author_id != $user->id)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Sets the issue ID.
	 *
	 * @param   int  $issueId  Issue ID.
	 *
	 * @return void
	 */
	public function setIssueId($issueId)
	{
		$this->issueId = $issueId;
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

		$query->delete('#__monitor_issues')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		$this->db->execute();
	}
}
