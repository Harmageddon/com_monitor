<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Helper class that contains some useful methods.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorHelper
{
	/**
	 * Converts an array of values to a string that can be used in SQL to set
	 * the according values in a query.
	 *
	 * @param   array           $data  Values that should be processed.
	 * @param   JDatabaseQuery  $q     Optional query object for escaping.
	 *
	 * @return array
	 */
	public static function sqlValues($data, $q = null)
	{
		$output = array();

		foreach ($data as $key => $value)
		{
			if ($value !== null)
			{
				if ($q)
				{
					$value = $q->escape($value);
				}

				$output[] = $key . ' = "' . $value . '"';
			}
		}

		return $output;
	}

	/**
	 * Cuts a string at the given position.
	 *
	 * @param   string  $string  String to be processed.
	 * @param   int     $size    Cutting position.
	 *
	 * @return string If the given string was longer than $size, return the cut string, with trailing "...",
	 *                otherwise, the full string is returned.
	 */
	public static function cutStr($string, $size)
	{
		if (strlen($string) > $size)
		{
			return substr($string, 0, $size) . "...";
		}
		else
		{
			return $string;
		}
	}
}
