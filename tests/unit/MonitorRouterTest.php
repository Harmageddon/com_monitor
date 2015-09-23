<?php
/**
 * @package     Monitor
 * @subpackage  tests
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Unit test for the router.
 *
 * @author  Constantin Romankiewicz  <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorRouterTest extends TestCaseDatabase
{
	/**
	 * @var array Sample menu queries to test the build and parse functions.
	 */
	private $samples = array(
		array(
			'query'    => '',
			'expected' => null,
		),
		array(
			'query' => array(
				'view' => 'projects',
			),
			'expected' => 'projects',
			'exceptions' => array(
				0 => '',
			),
		),
		array(
			'query' => array(
				'view' => 'project',
				'id'   => '1',
			),
			'expected' => 'test-project',
			'exceptions' => array(
				1 => '',
			),
		),
		array(
			'query' => array(
				'view'       => 'issues',
				'project_id' => '1',
			),
			'expected' => 'test-project/issues',
			'exceptions' => array(
				1 => 'issues',
				2 => '',
			),
		),
		array(
			'query' => array(
				'view' => 'issue',
				'id'   => '1',
			),
			'expected' => 'test-project/1',
			'exceptions' => array(
				3 => '',
				1 => '1',
				2 => '1',
			),
		),
		array(
			'query' => array(
				'task' => 'issue.edit',
				'id'   => '1',
			),
			'expected' => 'test-project/1/edit',
			'exceptions' => array(
				3 => 'edit',
				4 => '',
				1 => '1/edit',
				2 => '1/edit',
			),
		),
		array(
			'query' => array(
				'view' => 'issue',
				'id'   => '2',
			),
			'expected' => 'test-project/2',
			'exceptions' => array(
				5 => '',
				1 => '2',
				2 => '2',
			),
		),
		array(
			'query' => array(
				'task' => 'issue.edit',
				'id'   => '2',
			),
			'expected' => 'test-project/2/edit',
			'exceptions' => array(
				5 => 'edit',
				6 => '',
				1 => '2/edit',
				2 => '2/edit',
			),
		),
		array(
			'query' => array(
				'task'       => 'issue.edit',
				'project_id' => '1',
			),
			'expected' => 'test-project/new',
			'exceptions' => array(
				1 => 'new',
				7 => '',
			),
		),
		array(
			'query' => array(
				'task' => 'comment.edit',
				'id'   => '1',
			),
			'expected' => 'projects',
			'exceptions' => array(),
		),
		array(
			'query' => array(
				'task'     => 'comment.new',
				'issue_id' => '1',
			),
			'expected' => 'projects',
			'exceptions' => array(),
		),
	);

	/**
	 * Test of the build function.
	 *
	 * @return null
	 */
	public function testBuild()
	{
		JLoader::register('MonitorRouter', JPATH_ROOT . '/components/com_monitor/router.php');
		JLoader::registerPrefix('MonitorTest', JPATH_ROOT . '/components/com_monitor/tests/unit', false, true);

		class_exists('MonitorTestMockMenu');

		$menu = MonitorTestMockMenu::create($this);

		$router = new MonitorRouter($this->getMockApplication(), $menu);

		for ($i = 0; $i < MonitorTestMockMenu::getItemCount(); $i++)
		{
			MonitorTestMockMenu::setActiveIndex($i);

			foreach ($this->samples as $sample)
			{
				$url = implode('/', $router->build($sample['query']));

				$description = "Active Item: " . $menu->getActive()->link . "\n";

				if (isset($sample['exceptions'][$i]))
				{
					$this->assertEquals($sample['exceptions'][$i], $url, $description);
				}
				else
				{
					$this->assertEquals($sample['expected'], $url, $description);
				}
			}
		}
	}

	/**
	 * Gets the data set to be loaded into the database during setup
	 *
	 * @return  PHPUnit_Extensions_Database_DataSet_XmlDataSet
	 */
	protected function getDataSet()
	{
		return $this->createXMLDataSet(JPATH_ROOT . '/components/com_monitor/tests/unit/stubs/database/database.xml');
	}

	/**
	 * Returns the database operation executed in test setup.
	 *
	 * @return  PHPUnit_Extensions_Database_Operation_IDatabaseOperation
	 *
	 * @since   12.1
	 */
	protected function getSetUpOperation()
	{
		return PHPUnit_Extensions_Database_Operation_Factory::NONE();
	}

	/**
	 * This method is called before the first test of this test class is run.
	 *
	 * @return  void
	 *
	 * @see TestCaseDatabase::setUpBeforeClass
	 */
	public static function setUpBeforeClass()
	{
		parent::setUpBeforeClass();

		self::$driver->setQuery(file_get_contents(JPATH_SITE . '/components/com_monitor/tests/unit/stubs/database/tables.sql'));
		self::$driver->execute();
	}
}
