<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Mock up class for MonitorModelIssue.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorTestMockModelIssue
{
	private static $issueId = 0;

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
			'getIssueId',
			'setIssueId',
			'getIssue',
			'getIssues',
		);

		// Create the mock.
		$mockObject = $test->getMock(
			'MonitorModelIssue',
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
			self::$issueId = $id;
		};

		$mockObject->expects($test->any())
			->method('setIssueId')
			->will($test->returnCallback($setterCallback));

		$mockObject->expects($test->any())
			->method('getIssueId')
			->will($test->returnValue(self::$issueId));

		$mockObject->expects($test->any())
			->method('getIssue')
			->will($test->returnValue(self::$data[self::$issueId]));

		$mockObject->expects($test->any())
			->method('getIssues')
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
		self::createSampleObject(0, "Issue 0", 0);
		self::createSampleObject(1, "Issue 1", 0);
	}

	/**
	 * Creates a new object representing an issue.
	 *
	 * @param   int     $id              ID of the project.
	 * @param   string  $title           Title of the issue.
	 * @param   int     $projectId       ID of the project.
	 * @param   string  $text            Issue text.
	 * @param   string  $version         Version number for the issue.
	 * @param   int     $authorId        ID of the issue author.
	 * @param   null    $created         Date of creation.
	 * @param   int     $status          ID of the current status of the issue.
	 * @param   int     $classification  ID of the classification of the issue.
	 *
	 * @return null
	 */
	private static function createSampleObject($id, $title, $projectId, $text = '', $version = '', $authorId = 0,
		$created = null, $status = 0, $classification = 0)
	{
		self::$data[$id] = new stdClass;
		self::$data[$id]->id = $id;
		self::$data[$id]->title = $title;
		self::$data[$id]->project_id = $projectId;
		self::$data[$id]->text = $text;
		self::$data[$id]->version = $version;
		self::$data[$id]->author_id = $authorId;
		self::$data[$id]->created = $created;
		self::$data[$id]->status = $status;
		self::$data[$id]->classification = $classification;
	}
}
