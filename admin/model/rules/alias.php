<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Custom validation rule for project aliases.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class JFormRuleAlias extends JFormRule
{
	/**
	 * Forbidden values.
	 *
	 * @var array
	 */
	protected static $forbidden = array(
		'projects',
		'issues',
		'comment',
		'new',
		'edit'
	);

	/**
	 * Method to test the value.
	 *
	 * @param   SimpleXMLElement           $element  The SimpleXMLElement object representing the <field /> tag for the form field object.
	 * @param   mixed                      $value    The form field value to validate.
	 * @param   string                     $group    The field name group control value. This acts as as an array container for the field.
	 *                                               For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                               full field name would end up being "bar[foo]".
	 * @param   \Joomla\Registry\Registry  $input    An optional Registry object with the entire data set to validate against the entire form.
	 * @param   JForm                      $form     The form object for which the field is being tested.
	 *
	 * @return bool True if the value is valid, false otherwise.
	 *
	 * @since   11.1
	 */
	public function test(SimpleXMLElement $element, $value, $group = null, \Joomla\Registry\Registry $input = null, JForm $form = null)
	{
		return !is_numeric($value) && !in_array($value, self::$forbidden);
	}
}
