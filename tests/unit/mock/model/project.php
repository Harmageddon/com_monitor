<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Mock up class for MonitorModelProject.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorTestMockModelProject
{
	private static $projectId = 0;

	private static $data = array();
	/**
	 * Creates and instance of the mock object.
	 *
	 * @param   PHPUnit_Framework_TestCase  $test  A test object.
	 *
	 * @return  PHPUnit_Framework_MockObject_MockObject
	 */
	public static function create($test)
	{
		// Collect all the relevant methods in JDatabase.
		$methods = array(
			'getProjectId',
			'setProjectId',
			'getProject',
			'getProjects',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'MonitorModelProject',
			$methods,
			// Constructor arguments.
			array(),
			// Mock class name.
			'',
			// Call original constructor.
			false
		);

		self::createSampleData();

		$setterCallback = function ($id)
		{
			self::$projectId = $id;

			return $id;
		};

		$mockObject->expects($test->any())
			->method('setProjectId')
			->will($test->returnCallback($setterCallback));

		$mockObject->expects($test->any())
			->method('getProjectId')
			->will($test->returnValue(self::$projectId));

		$mockObject->expects($test->any())
			->method('getProject')
			->will($test->returnValue(self::$data[self::$projectId]));

		$mockObject->expects($test->any())
			->method('getProjects')
			->will($test->returnValue(self::$data));

		return $mockObject;
	}

	/**
	 * Creates a set of sample data.
	 *
	 * @return null
	 */
	private static function createSampleData()
	{
		self::createSampleObject(0, "Project", "test-project");
	}

	/**
	 * Creates a new object representing a project.
	 *
	 * @param   int     $id           Project ID.
	 * @param   string  $name         Project name.
	 * @param   string  $alias        Unique alias, for routing.
	 * @param   string  $description  Project description text.
	 * @param   string  $url          URL linking to the project.
	 *
	 * @return null
	 */
	private static function createSampleObject($id, $name, $alias, $description = '', $url = '')
	{
		self::$data[$id] = new stdClass;
		self::$data[$id]->id = $id;
		self::$data[$id]->name = $name;
		self::$data[$id]->alias = $alias;
		self::$data[$id]->description = $description;
		self::$data[$id]->url = $url;
	}
}
