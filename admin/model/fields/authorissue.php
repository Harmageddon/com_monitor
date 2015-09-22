<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die;

JFormHelper::loadFieldClass('list');

/**
 * Custom form field that displays a select list of authors who have written at least one issue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class JFormFieldAuthorIssue extends JFormFieldList
{
	protected $type = 'AuthorIssue';
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getOptions()
	{
		$options = array();

		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select('u.id, u.name')
			->from('#__users AS u')
			->innerJoin('#__monitor_issues AS i ON i.author_id = u.id')
			->group('u.id')
			->order('u.name');

		// Setup the query
		$db->setQuery($query);

		$users = $db->loadObjectList();

		foreach ($users as $user)
		{
			$options[] = JHtml::_('select.option', $user->id, $user->name);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
