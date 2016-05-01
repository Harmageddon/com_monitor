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
class MonitorRouterTest extends TestCase
{
	/**
	 * MonitorRouterTest constructor.
	 *
	 * @param   string  $name      Name of the test.
	 * @param   array   $data      Data
	 * @param   string  $dataName  Data name
	 */
	public function __construct($name = null, array $data = array(), $dataName = '')
	{
		JLoader::register('MonitorRouter', JPATH_ROOT . '/components/com_monitor/router.php');
		JLoader::registerPrefix('MonitorTest', JPATH_ROOT . '/components/com_monitor/tests/unit', false, true);

		class_exists('MonitorTestMockMenu');
		class_exists('MonitorTestMockModelProject');
		class_exists('MonitorTestMockModelIssue');

		parent::__construct($name, $data, $dataName);
	}

	/**
	 * Test of the build function.
	 *
	 * @param   array   $query       A sample query to test with.
	 * @param   string  $expected    The expected built URL.
	 * @param   array   $exceptions  Array of exceptional menu items, where the expected value should be different.
	 *
	 * @dataProvider buildProvider
	 * @return null
	 */
	public function testBuild($query, $expected, $exceptions)
	{
		$modelProject = MonitorTestMockModelProject::create($this);
		$modelIssue = MonitorTestMockModelIssue::create($this);

		MonitorTestMockMenu::createMenuSampleData();

		for ($i = 0; $i < MonitorTestMockMenu::getItemCount(); $i++)
		{
			MonitorTestMockMenu::setActiveIndex($i);
			$menu = MonitorTestMockMenu::create($this);

			$router = new MonitorRouter($this->getMockCmsApp(), $menu, $modelProject, $modelIssue);

			$description = "Active Item: ($i) " . $menu->getActive()->link . "\n"
				. "Query: " . http_build_query($query);

			$queryCopy = $query;
			$url = implode('/', $router->build($queryCopy));

			// TODO: Test query rest

			if (isset($exceptions[$i]))
			{
				$this->assertEquals($exceptions[$i], $url, $description);
			}
			else
			{
				$this->assertEquals($expected, $url, $description);
			}
		}
	}

	/**
	 * Test of the build function.
	 *
	 * @param   array   $queryExpected  The expected parsed query..
	 * @param   string  $url            URL to be parsed.
	 * @param   array   $exceptions     Array of exceptional menu items, where the URL is different.
	 *
	 * @dataProvider buildProvider
	 * @return null
	 */
	public function testParse($queryExpected, $url, $exceptions)
	{
		$modelProject = MonitorTestMockModelProject::create($this);
		$modelIssue = MonitorTestMockModelIssue::create($this);

		MonitorTestMockMenu::createMenuSampleData();

		for ($i = 0; $i < MonitorTestMockMenu::getItemCount(); $i++)
		{
			MonitorTestMockMenu::setActiveIndex($i);
			$menu = MonitorTestMockMenu::create($this);

			$router = new MonitorRouter($this->getMockCmsApp(), $menu, $modelProject, $modelIssue);

			$description = "Active Item: ($i) " . $menu->getActive()->link . "\n";

			// TODO: Test query rest

			if (isset($exceptions[$i]))
			{
				$segments = explode('/', $exceptions[$i]);
				$description .= "URL: " . $exceptions[$i];
			}
			else
			{
				$segments = explode('/', $url);
				$description .= "URL: " . $url;
			}

			$query = $router->parse($segments);
			MonitorRouter::convertTaskToView($query);
			MonitorRouter::convertTaskToView($queryExpected);

			$this->assertEquals($queryExpected, $query, $description);
		}
	}

	/**
	 * Provides sample data for the router to test the build and parse functions.
	 *
	 * @return   array  Sample menu queries.
	 */
	public function buildProvider()
	{
		return array(
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'view'   => 'projects',
				),
				'expected'   => 'projects',
				'exceptions' => array(
					0 => '',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'view'   => 'project',
					'id'     => '1',
				),
				'expected'   => 'test-project',
				'exceptions' => array(
					1 => '',
				),
			),
			array(
				'query'      => array(
					'option'     => 'com_monitor',
					'view'       => 'issues',
					'project_id' => '1',
				),
				'expected'   => 'test-project/issues',
				'exceptions' => array(
					1 => 'issues',
					2 => '',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'view'   => 'issue',
					'id'     => '1',
				),
				'expected'   => 'test-project/1',
				'exceptions' => array(
					3 => '',
					1 => '1',
					2 => '1',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'task'   => 'issue.edit',
					'id'     => '1',
				),
				'expected'   => 'test-project/1/edit',
				'exceptions' => array(
					3 => 'edit',
					4 => '',
					1 => '1/edit',
					2 => '1/edit',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'view'   => 'issue',
					'id'     => '2',
				),
				'expected'   => 'test-project/2',
				'exceptions' => array(
					5 => '',
					1 => '2',
					2 => '2',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'task'   => 'issue.edit',
					'id'     => '2',
				),
				'expected'   => 'test-project/2/edit',
				'exceptions' => array(
					5 => 'edit',
					6 => '',
					1 => '2/edit',
					2 => '2/edit',
				),
			),
			array(
				'query'      => array(
					'option'     => 'com_monitor',
					'task'       => 'issue.edit',
					'project_id' => '1',
				),
				'expected'   => 'test-project/new',
				'exceptions' => array(
					1 => 'new',
					2 => 'new',
					7 => '',
				),
			),
			array(
				'query'      => array(
					'option' => 'com_monitor',
					'task'   => 'comment.edit',
					'id'     => '1',
				),
				'expected'   => 'comment/edit/1',
				'exceptions' => array(
					8 => 'edit/1',
					9 => '',
				),
			),
			array(
				'query'      => array(
					'option'   => 'com_monitor',
					'task'     => 'comment.edit',
					'issue_id' => '1',
				),
				'expected'   => 'comment/new/1',
				'exceptions' => array(
					8 => '',
					9 => 'new/1',
				),
			),
			array(
				'query'      => array(
					'option'   => 'com_monitor',
					'view'     => 'comments',
				),
				'expected'   => 'comments',
				'exceptions' => array(
					10 => '',
				),
			),
		);
	}
}
