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
 * Model for retrieving data about comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelComment extends MonitorModelAbstract
{
	/**
	 * ID of the relevant issue.
	 *
	 * @var int
	 */
	private $issueId;

	/**
	 * Comment ID.
	 *
	 * @var int
	 */
	private $commentId;

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
		$this->prefix = 'comment_';

		parent::__construct($application, $loadFilters);
	}

	/**
	 * @var array All valid ordering options.
	 */
	private $orderOptions = array(
		"c.id ASC",
		"c.id DESC",
		"i.title ASC",
		"i.title DESC",
		"u.name ASC",
		"u.name DESC",
		"c.created ASC",
		"c.created DESC",
	);

	/**
	 * Retrieves a comment with all relevant information from the database
	 *
	 * @return object|null Returns null, if $commentId is null or the comment doesn't exist.
	 *                     Otherwise, return an object holding the comment information.
	 */
	public function getComment()
	{
		if (!$this->commentId)
		{
			return null;
		}

		$query = $this->buildQuery();
		$query->where("c.id = " . $query->q($this->commentId));

		$query
			->where('c.issue_id = ' . $this->issueId)
			->select('contact.id AS contact_id, contact.image AS contact_image')
			->leftJoin('#__contact_details AS contact ON contact.user_id = c.author_id AND contact.published = 1');

		$this->db->setQuery($query);

		return $this->db->loadObject();
	}

	/**
	 * Retrieves a comment with all relevant information from the database
	 *
	 * @param   int  $issueId  ID of the issue the comments belong to.
	 *
	 * @return object|null Returns null, if $issueId is null or the issue doesn't exist.
	 *                     Otherwise, return an array of data items on success, false on failure.
	 */
	public function getIssueComments($issueId)
	{
		if ((int) $issueId === 0)
		{
			return null;
		}

		$query = $this->buildQuery();

		$query
			->where('c.issue_id = ' . (int) $issueId)
			->select('contact.id AS contact_id, contact.image AS contact_image')
			->leftJoin('#__contact_details AS contact ON contact.user_id = c.author_id AND contact.published = 1');

		$this->countItems($query);

		$this->db->setQuery($query);

		return $this->db->loadObjectList();
	}

	/**
	 * Retrieves all comments from the database.
	 *
	 * @return mixed An array of data items on success, false on failure.
	 */
	public function getComments()
	{
		$query = $this->buildQuery();
		$query->select('s.name AS status_name')
			->select('i.title AS issue_title, p.name AS project_name, p.id AS project_id')
			->leftJoin('#__monitor_status AS s ON c.status = s.id')
			->leftJoin('#__monitor_issues AS i ON c.issue_id = i.id')
			->leftJoin('#__monitor_projects AS p ON i.project_id = p.id');

		$app = JFactory::getApplication();

		// Filters (admin only)
		if ($app->isAdmin() && $this->filters !== null)
		{
			// Filter by author
			if (isset($this->filters['author']) && (int) $this->filters['author'] !== 0)
			{
				$query->where('c.author_id = ' . (int) $this->filters['author']);
			}

			// Filter by status
			if (isset($this->filters['status']))
			{
				if ($this->filters['status'] === "no-change")
				{
					$query->where('c.status = 0');
				}
				elseif ((int) $this->filters['status'] !== 0)
				{
					$query->where('c.status = ' . (int) $this->filters['status']);
				}
			}

			// Filter by issue
			if (isset($this->filters['issue_id']) && (int) $this->filters['issue_id'] !== 0)
			{
				$query->where('c.issue_id = ' . (int) $this->filters['issue_id']);
			}

			// Filter by text
			if (isset($this->filters['search']))
			{
				$query->where('c.text LIKE "%' . $this->filters['search'] . '%"');
			}
		}

		$this->countItems($query);

		// Ordering
		if ($app->isAdmin() && $this->list !== null && isset($this->list['fullordering']) && in_array($this->list['fullordering'], $this->orderOptions))
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
		JForm::addFieldPath(__DIR__ . '/fields');

		$this->form = JForm::getInstance('com_monitor.comment', 'comment');


		if ($data = $this->app->getUserState($this->form->getName() . '.data'))
		{
			$this->form->bind($data);
		}
		elseif ($this->commentId)
		{
			$this->form->bind($this->getComment());
		}
	}

	/**
	 * Checks if a user is allowed to edit a certain comment.
	 *
	 * @param   JUser  $user  The user whose permissions should be checked.
	 * @param   int    $id    ID of the relevant comment.
	 *
	 * @return bool True, if the user is allowed to edit the given comment, false otherwise.
	 */
	public function canEdit($user, $id)
	{
		// If ID is 0, we create a new comment.
		if ($id == 0)
		{
			return $user->authorise('comment.create', 'com_monitor');
		}
		else
		{
			// If user is not allowed to edit...
			if (!$user->authorise('comment.edit', 'com_monitor'))
			{
				if (!$user->authorise('comment.edit.own', 'com_monitor'))
				{
					return false;
				}
				// ...but to edit own comments...
				$authorQuery = $this->db->getQuery(true)
					->select('author_id')
					->from('#__monitor_comments')
					->where('id = ' . $id);

				$this->db->setQuery($authorQuery);
				$this->db->execute();

				// ...check if the comment belongs to the user.
				if ($this->db->loadResult() !== $user->id)
				{
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Saves a comment entity.
	 *
	 * @param   JInput  $input  Holds the data to be saved.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 *
	 * @throws Exception
	 */
	public function save($input)
	{
		$user = JFactory::getUser();
		$values = array (
			"issue_id" => $input->getInt('issue_id'),
			"text" => $input->getString('text'),
			"created" => $input->getString('created'),
		);

		$values = $this->validate($values);

		if (!$values)
		{
			return false;
		}

		if ($values["issue_id"] == 0)
		{
			throw new Exception(JText::_('JGLOBAL_VALIDATION_FORM_FAILED'));
		}

		if ($user->authorise('comment.edit.status', 'com_monitor'))
		{
			$values["status"] = $input->getInt('issue_status');

			if ($values["status"] != 0)
			{
				$query = $this->db->getQuery(true);
				$query->update('#__monitor_issues')
					->where('id = ' . $values["issue_id"])
					->set('status = ' . $values["status"]);
				$this->db->setQuery($query);
				$this->db->execute();
			}
		}

		$query = $this->db->getQuery(true);

		$id = $input->getInt('id');

		if (!$this->canEdit($user, $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		if ($id != 0)
		{
			$query->update('#__monitor_comments')
				->where('id = ' . $id);
		}
		else
		{
			$values["author_id"] = $user->id;
			$query->insert('#__monitor_comments');
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

		$query->delete('#__monitor_comments')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		return $this->db->execute();
	}

	/**
	 * Loads the title of the issue associated with the comment.
	 *
	 * @return mixed Title of the issue, null if it doesn't exist.
	 */
	public function getIssueTitle()
	{
		$id = (int) $this->issueId;

		if ($id == 0)
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$query->select('title')
			->from('#__monitor_issues')
			->where('id = ' . $id);
		$this->db->setQuery($query);

		$this->db->execute();

		return $this->db->loadResult();
	}

	/**
	 * Sets the comment ID.
	 *
	 * @param   int  $commentId  Comment ID
	 *
	 * @return void
	 */
	public function setCommentId($commentId)
	{
		$this->commentId = $commentId;
	}

	/**
	 * Gets the issue ID.
	 *
	 * @return int
	 */
	public function getIssueId()
	{
		return $this->issueId;
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
	 * Prepares the part of the select query that is common to all retrieval operations.
	 *
	 * @return JDatabaseQuery Common part of the query.
	 */
	private function buildQuery()
	{
		$query = $this->db->getQuery(true);
		$query->select('c.id, c.issue_id, c.author_id, c.text, c.created, u.name AS author_name, username')
			->select('c.status AS status_id')
			->leftJoin('#__monitor_status AS s ON c.status = s.id')
			->from('#__monitor_comments AS c')
			->leftJoin('#__users AS u ON u.id = c.author_id');

		return $query;
	}
}
