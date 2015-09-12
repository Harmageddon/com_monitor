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
 * Custom form field that displays a select list of issues.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class JFormFieldIssue extends JFormFieldList
{
	protected $type = 'issue';
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

		JLoader::register('MonitorModelAbstract', JPATH_ROOT . '/administrator/components/com_monitor/model/abstract.php');
		JLoader::register('MonitorModelIssue', JPATH_ROOT . '/administrator/components/com_monitor/model/issue.php');
		$model = new MonitorModelIssue(false);
		$issues = $model->getIssues();

		foreach ($issues as $issue)
		{
			$options[] = JHtml::_('select.option', $issue->id, $issue->title);
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
