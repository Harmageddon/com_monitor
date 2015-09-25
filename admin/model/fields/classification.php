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
 * Custom form field that displays a select list of projects.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class JFormFieldClassification extends JFormFieldList
{
	protected $type = 'project';
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
		JLoader::register('MonitorModelClassification', JPATH_ROOT . '/administrator/components/com_monitor/model/classification.php');
		$model = new MonitorModelClassification(null, false);
		$classifications = $model->getClassifications();

		$user = JFactory::getUser();

		foreach ($classifications as $classification)
		{
			if (in_array($classification->access, $user->getAuthorisedViewLevels()))
			{
				$options[] = JHtml::_('select.option', $classification->id, $classification->title);
			}
		}

		$options = array_merge(parent::getOptions(), $options);

		return $options;
	}
}
