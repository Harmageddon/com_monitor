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
	/**
	 * Upload files and attach them to an issue or a comment.
	 *
	 * @param   array   $files  Array containing the uploaded files.
	 * @param   int     $id     ID of the issue/comment where to attach the files.
	 * @param   string  $type   One of 'issue' or 'comment', indicating if the files should be attached to an issue or a comment.
	 *
	 * @return  boolean   True on success, false otherwise.
	 */
	public function upload($files, $id, $type)
	{
		if ($type !== 'issue' && $type !== 'comment')
		{
			return false;
		}

		if (!is_array($files))
		{
			return false;
		}

		jimport('joomla.filesystem.file');

		foreach ($files as $file)
		{
			$rand = MonitorHelper::genRandHash();

			$pathParts = array(
				JPATH_ROOT,
				'media',
				'com_monitor',
				$type,
				$id,
				$rand . '-' . $file[0]['name'],
			);
			$path = JPath::clean(implode(DIRECTORY_SEPARATOR, $pathParts));

			$values = array(
				$type . '_id' => $id,
				'path' => $path,
			);

			if (!JFile::upload($file[0]['tmp_name'], $path))
			{
				// TODO: Error handling
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
	 * Deletes entities from the database.
	 *
	 * @param   int[]  $ids  IDs of the entities to be deleted.
	 *
	 * @return  mixed  A database cursor resource on success, boolean false on failure.
	 */
	public function delete($ids)
	{
		// TODO: Implement delete() method.
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
}
