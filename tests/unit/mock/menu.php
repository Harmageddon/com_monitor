<?php
/**
 * @package     Monitor
 * @subpackage  tests
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Custom mock up class for unit tests.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @see     JMenu
 * @see     TestMockMenu
 * @since   1.0
 */
class MonitorTestMockMenu
{
	/**
	 * @var array  Holds the mocked menu items.
	 */
	protected static $data = array();

	/**
	 * @var int  Index of the active menu item.
	 */
	private static $active = 0;

	/**
	 * Creates an instance of the mock JMenu object.
	 *
	 * @param   object  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 *
	 * @since   3.4
	 */
	public static function create(PHPUnit_Framework_TestCase $test)
	{
		$methods = array(
			'getItem',
			'setActive',
			'getActive',
			'getItems',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'JMenu',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		if (count(self::$data) == 0)
		{
			self::createMenuSampleData();
		}

		$mockObject->expects($test->any())
			->method('getItem')
			->will($test->returnValueMap(self::$data));

		$mockObject->expects($test->any())
			->method('getActive')
			->will($test->returnValue(self::$data[self::$active]));

		$mockObject->expects($test->any())
			->method('getItems')
			->will($test->returnValue(self::$data));

		return $mockObject;
	}

	/**
	 * Inserts sample menu items to self::$data.
	 *
	 * @return null
	 */
	public static function createMenuSampleData()
	{
		self::createMenuItem(
			array(
				'id' => 0,
				'title'        => 'Projects',
				'query'        => array('option' => 'com_monitor', 'view' => 'projects'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 1,
				'title'        => 'Project',
				'query'        => array('option' => 'com_monitor', 'view' => 'project', 'id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 2,
				'title'        => 'Issues',
				'query'        => array('option' => 'com_monitor', 'view' => 'issues', 'project_id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 3,
				'title'        => 'Issue',
				'query'        => array('option' => 'com_monitor', 'view' => 'issue', 'id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 4,
				'title'        => 'Edit Issue',
				'query'        => array('option' => 'com_monitor', 'task' => 'issue.edit', 'id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 5,
				'title'        => 'Next Issue',
				'query'        => array('option' => 'com_monitor', 'view' => 'issue', 'id' => '2'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 6,
				'title'        => 'Edit Next Issue',
				'query'        => array('option' => 'com_monitor', 'task' => 'issue.edit', 'id' => '2'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 7,
				'title'        => 'New Issue',
				'query'        => array('option' => 'com_monitor', 'task' => 'issue.edit', 'project_id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 8,
				'title'        => 'New Comment',
				'query'        => array('option' => 'com_monitor', 'task' => 'comment.edit', 'issue_id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 9,
				'title'        => 'Edit Comment',
				'query'        => array('option' => 'com_monitor', 'task' => 'comment.edit', 'id' => '1'),
			)
		);
		self::createMenuItem(
			array(
				'id' => 10,
				'title'        => 'Other Component',
				'query'        => array('option' => 'com_content', 'view' => 'featured'),
			)
		);
	}

	/**
	 * Generates a menu item based on given data.
	 *
	 * @param   array  $data  Data to insert to the menu item.
	 *
	 * @return null
	 */
	private static function createMenuItem($data)
	{
		$count = count(self::$data);

		$defaults = array(
			'id'           => $count,
			'menutype'     => 'testmenu',
			'title'        => '',
			'alias'        => (isset($data['title'])) ? JFilterOutput::stringURLSafe($data['title']) : '',
			'route'        => (isset($data['title'])) ? JFilterOutput::stringURLSafe($data['title']) : '',
			'link'         => JUri::buildQuery($data['query']),
			'type'         => 'component',
			'level'        => '1',
			'language'     => '*',
			'access'       => '1',
			'params'       => '{}',
			'home'         => '0',
			'component_id' => '1000',
			'parent_id'    => '0',
			'component'    => 'com_monitor',
			'tree'         => (isset($data['id'])) ? array($data['id']) : array($count),
			'query'        => array('option' => 'com_monitor')
		);

		$obj = (object) array_merge($defaults, $data);

		self::$data[$obj->id] = $obj;
	}

	/**
	 * Gets the number of items.
	 *
	 * @return int
	 */
	public static function getItemCount()
	{
		return count(self::$data);
	}

	/**
	 * Gets the index of the active menu item.
	 *
	 * @return int
	 */
	public static function getActiveIndex()
	{
		return self::$active;
	}

	/**
	 * Sets the index of the active menu item.
	 *
	 * @param   int  $active  Index of the active menu item.
	 *
	 * @return null
	 */
	public static function setActiveIndex($active)
	{
		self::$active = $active;
	}
}
