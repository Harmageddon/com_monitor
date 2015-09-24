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
	 * @var array Sample menu queries to test the build and parse functions.
	 */
	private $samples = array(
		array(
			'query'    => array(),
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
			'expected' => 'comment/edit/1',
			'exceptions' => array(),
		),
		array(
			'query' => array(
				'task'     => 'comment.new',
				'issue_id' => '1',
			),
			'expected' => 'comment/new/1',
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

		$modelProject = MonitorTestMockModelProject::create($this);
		$modelIssue = MonitorTestMockModelIssue::create($this);

		MonitorTestMockMenu::createMenuSampleData();

		for ($i = 0; $i < MonitorTestMockMenu::getItemCount(); $i++)
		{
			MonitorTestMockMenu::setActiveIndex($i);
			$menu = MonitorTestMockMenu::create($this);

			$router = new MonitorRouter($this->getMockCmsApp(), $menu, $modelProject, $modelIssue);

			foreach ($this->samples as $sample)
			{
				$description = "Active Item: ($i) " . $menu->getActive()->link . "\n"
					. "Query: " . http_build_query($sample['query']);

				$url = implode('/', $router->build($sample['query']));

				// TODO: Test query rest

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
}
