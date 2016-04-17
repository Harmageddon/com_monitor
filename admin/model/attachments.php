<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Model for managing attachments for issues and comments.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorModelAttachments extends MonitorModelAbstract
{
	protected $pathPrefix;

	/**
	 * MonitorModelAttachments constructor.
	 *
	 * @param   JApplicationCms  $application  The Application object to use in this model.
	 * @param   boolean          $loadFilters  If set to true, filters and list options will be loaded from the page request.
	 * @param   string           $pathPrefix   Base path for file storage for this component.
	 *                                         Defaults to /media/com_monitor.
	 */
	public function __construct($application = null, $loadFilters = true,
		$pathPrefix = '/media/com_monitor/')
	{
		$this->pathPrefix = JPATH_ROOT . $pathPrefix;

		parent::__construct();
	}

	/**
	 * Upload files and attach them to an issue or a comment.
	 *
	 * @param   array  $files      Array containing the uploaded files.
	 * @param   int    $issueId    ID of the issue/comment where to attach the files.
	 * @param   int    $commentId  One of 'issue' or 'comment', indicating if the files should be attached to an issue or a comment.
	 *
	 * @return  boolean   True on success, false otherwise.
	 */
	public function upload($files, $issueId, $commentId = null)
	{
		if (!$issueId || !is_array($files))
		{
			return false;
		}

		jimport('joomla.filesystem.file');

		if ($commentId)
		{
			$type = 'comment';
			$id = $commentId;
		}
		else
		{
			$type = 'issue';
			$id = $issueId;
		}

		foreach ($files as $file)
		{
			$rand = MonitorHelper::genRandHash();

			$pathParts = array(
				$type,
				$id,
				$rand . '-' . $file[0]['name'],
			);
			$path = JPath::clean(implode(DIRECTORY_SEPARATOR, $pathParts));

			$values = array(
				'issue_id' => $issueId,
				'comment_id' => $commentId,
				'path' => $path,
				'name' => $file[0]['name'],
			);

			if (!JFile::upload($file[0]['tmp_name'], $this->pathPrefix . $path))
			{
				JFactory::getApplication()->enqueueMessage(JText::sprintf('COM_MONITOR_ATTACHMENT_UPLOAD_FAILED', $file[0]['name']));

				return false;
			}

			$query = $this->db->getQuery(true);
			$query->insert('#__monitor_attachments')
				->set(MonitorHelper::sqlValues($values, $query));

			$this->db->setQuery($query);

			if ($this->db->execute() === false)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * Checks if the uploaded files are valid.
	 *
	 * @param   array  $files  Array containing the uploaded files.
	 *
	 * @return  bool  True if all files are valid, false if not.
	 */
	public function canUpload($files)
	{
		foreach ($files as $file)
		{
			$helper = new JHelperMedia;

			if (!$helper->canUpload($file[0], 'com_monitor'))
			{
				return false;
			}
		}

		return true;
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
		if (!is_array($ids))
		{
			return false;
		}

		jimport('joomla.filesystem.file');
		$app = JFactory::getApplication();

		$conditions = array();

		foreach ($ids as $id)
		{
			$conditions[] = 'id = ' . (int) $id;
		}

		// Step 1: Get file paths to delete
		$query = $this->db->getQuery(true);

		$query->select('id, path')
			->from('#__monitor_attachments')
			->where($conditions, 'OR');

		$this->db->setQuery($query)->execute();
		$result = $this->db->loadAssocList();

		// Step 2: Delete files
		foreach ($result as $row)
		{
			if (!JFile::delete($this->pathPrefix . $row['path']))
			{
				// Don't delete database entries for files that couldn't get removed.
				$conditions = array_filter(
					$conditions,
					function ($a) use ($row)
					{
						return $a != $row['id'];
					}
				);

				$app->enqueueMessage(JText::sprintf('COM_MONITOR_ATTACHMENT_DELETION_FAILED', $this->pathPrefix . $row['path']));
			}
		}

		// Step 3: Remove entries from database
		$query = $this->db->getQuery(true);

		$query->delete('#__monitor_attachments')
			->where($conditions, 'OR');

		$this->db->setQuery($query);

		return $this->db->execute();
	}

	/**
	 * Unused.
	 *
	 * @return null
	 */
	public function loadForm()
	{
		return null;
	}

	/**
	 * Get attachments of a single issue, specified by the issue's ID.
	 *
	 * @param   int  $id  ID of the issue.
	 *
	 * @return  mixed  Null on failure, an array indexed by attachment IDs on success.
	 */
	public function attachmentsIssue($id)
	{
		return $this->getAttachments($id, 'issue');
	}

	/**
	 * Get attachments of a single comment, specified by the comment's ID.
	 *
	 * @param   int  $id  ID of the comment.
	 *
	 * @return  mixed  Null on failure, an array indexed by attachment IDs on success.
	 */
	public function attachmentsComment($id)
	{
		return $this->getAttachments($id, 'comment');
	}

	/**
	 * Get attachments of all comments that belong to a single issue, specified by the issue's ID.
	 *
	 * @param   int  $issueId  ID of the issue.
	 *
	 * @return  mixed  Null on failure, an array indexed by attachment IDs on success.
	 */
	public function attachmentsComments($issueId)
	{
		return $this->getAttachments($issueId, 'comments');
	}

	/**
	 * Retrieves attachments for a given issue or comment.
	 *
	 * @param   int     $id    ID of the issue or comment.
	 * @param   string  $type  'issue': Get attachments of a single issue, specified by the issue's ID.
	 *                         'comment': Get attachments of a single comment, specified by the comment's ID.
	 *                         'comments': Get attachments of all comments that belong to a single issue, specified by the issue's ID.
	 *
	 * @return  mixed  Null on failure, an array indexed by attachment IDs on success.
	 */
	private function getAttachments($id, $type)
	{
		if ($type !== 'issue' && $type !== 'comment' && $type !== 'comments')
		{
			return null;
		}

		$query = $this->db->getQuery(true);
		$query->select('id, name, path')
			->from('#__monitor_attachments');

		switch ($type)
		{
			case 'issue':
				$query->where('issue_id = ' . (int) $id)
					->where('comment_id IS NULL');
				break;
			case 'comment':
				$query->where('comment_id = ' . (int) $id);
				break;
			case 'comments':
				$query->where('issue_id = ' . (int) $id)
					->where('comment_id IS NOT NULL');
				break;
		}

		$this->db->setQuery($query)->execute();

		return $this->db->loadAssocList('id');
	}
}
