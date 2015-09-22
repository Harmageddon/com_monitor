<?php
/**
 * @package     Monitor
 * @subpackage  site
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Component router to handle SEF URLs.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorRouter implements JComponentRouterInterface
{
	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JMenu
	 */
	protected $menu;

	/**
	 * MonitorRouter constructor.
	 *
	 * @param   JApplicationCms  $app   Application object that the router should use
	 * @param   JMenu            $menu  Menu object that the router should use
	 */
	public function __construct($app = null, $menu = null)
	{
		JLoader::register('MonitorModelAbstract', JPATH_ROOT . '/administrator/components/com_monitor/model/abstract.php');
		JLoader::register('MonitorModelProject', JPATH_ROOT . '/administrator/components/com_monitor/model/project.php');
		JLoader::register('MonitorModelIssue', JPATH_ROOT . '/administrator/components/com_monitor/model/issue.php');

		if ($app)
		{
			$this->app = $app;
		}
		else
		{
			$this->app = JFactory::getApplication();
		}

		if ($menu)
		{
			$this->menu = $menu;
		}
		else
		{
			$this->menu = $this->app->getMenu();
		}
	}

	/**
	 * Prepare-method for URLs
	 * This method is meant to validate and complete the URL parameters.
	 * For example it can add the Itemid or set a language parameter.
	 * This method is executed on each URL, regardless of SEF mode switched
	 * on or not.
	 *
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function preprocess($query)
	{
		// TODO

		return $query;
	}

	/**
	 * Build method for URLs
	 * This method is meant to transform the query parameters into a more human
	 * readable form. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$query  An array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function build(&$query)
	{
		/*
		 * Available urls:
		 *
		 * projects
		 * {project}
		 * {project}/issues
		 * {project}/{issue}
		 * {project}/{issue}/edit
		 * comment/edit/{comment}
		*/

		$url = array();

		// Convert task to view/layout format.
		if (isset($query['task']))
		{
			$parts           = explode('.', $query['task']);
			$query['view']   = $parts[0];
			$query['layout'] = $parts[1];
			unset($query['task']);
		}

		if (!isset($query['view']))
		{
			return $url;
		}

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$menuView = (isset($menuItem->query['view'])) ? $menuItem->query['view'] : null;

		if ($query['view'] === 'projects')
		{
			if ($menuView !== 'projects')
			{
				$url[0] = 'projects';
			}

			unset($query['view']);
		}
		elseif ($query['view'] === 'comment')
		{
			if ($menuView !== 'comment')
			{
				$url[0] = 'comment';
			}

			if (isset($query['id']))
			{
				$url[1] = 'edit';
				$url[2] = $query['id'];

				unset($query['id']);
			}
			elseif (isset($query['issue_id']))
			{
				$url[1] = 'new';
				$url[2] = $query['issue_id'];

				unset($query['issue_id']);
			}

			if (isset($query['layout']))
			{
				unset($query['layout']);
			}

			unset($query['view']);
		}
		elseif ($query['view'] === 'issues' || $query['view'] === 'issue')
		{
			$modelProject = new MonitorModelProject(false);

			if ($query['view'] === 'issues')
			{
				$modelProject->setProjectId($query['project_id']);
				unset($query['project_id']);

				if (!($menuView === 'issues' && $modelProject->getProjectId() === $menuItem->query['project_id']))
				{
					$url[1] = 'issues';
				}
			}
			else
			{
				$hasId = isset($query['id']);
				$menuViewSameIssue = $hasId && isset($menuItem->query['id']) && $menuView === 'issue' && $query['id'] === $menuItem->query['id'];
				$editing = isset($query['layout']) && $query['layout'] == 'edit';
				$menuEditing = isset($menuItem->query['layout']) && $menuItem->query['layout'] == 'edit';

				if ($hasId)
				{
					if (!$menuViewSameIssue || $menuEditing)
					{
						$modelIssue = new MonitorModelIssue(false);
						$modelIssue->setIssueId($query['id']);
						$issue = $modelIssue->getIssue();

						if ($issue)
						{
							$modelProject->setProjectId($issue->project_id);

							$url[1] = $query['id'];
						}
					}

					unset($query['id']);
				}

				if (isset($query['layout']) && $query['layout'] === 'edit')
				{
					if (!isset($url[1]) && !isset($query['id']) && isset($query['project_id']))
					{
						$modelProject->setProjectId($query['project_id']);
						unset($query['project_id']);

						$url[1] = 'new';
					}
					else
					{
						$url[2] = 'edit';
					}

					unset($query['layout']);
				}
			}

			if (!(($menuView === 'issues' && $modelProject->getProjectId() === $menuItem->query['project_id'])
				|| ($menuView === 'project' && $modelProject->getProjectId() === $menuItem->query['id'])))
			{
				$project = $modelProject->getProject();

				if ($project)
				{
					$url[0] = $project->alias;
				}
			}

			unset($query['view']);
		}
		// {project}
		else
		{
			$modelProject = new MonitorModelProject(false);
			$modelProject->setProjectId($query['id']);

			$project = $modelProject->getProject();

			if ($project)
			{
				$url[0] = $project->alias;
				unset($query['id']);
			}

			unset($query['view']);
		}

		ksort($url);

		return $url;
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array  &$segments  The segments of the URL to parse.
	 *
	 * @return  array  The URL attributes to be used by the application.
	 *
	 * @since   3.3
	 */
	public function parse(&$segments)
	{
		/*
		 * Available urls:
		 *
		 * projects
		 * {project}
		 * {project}/issues
		 * {project}/{issue}
		 * {project}/{issue}/edit
		 * comment/edit/{comment}
		*/

		$query = array();

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$menuView = (isset($menuItem->query['view'])) ? $menuItem->query['view'] : null;

		if ($segments[0] == 'projects')
		{
			$query['view'] = 'projects';
		}
		elseif ($segments[0] == 'comment')
		{
			$query['view']   = 'comment';
			$query['layout'] = 'edit';

			if (isset($segment[1]) && $segment[1] == 'new' && isset($segment[2]))
			{
				$query['issue_id'] = (int) $segment[2];
			}
			elseif (isset($segment[2]))
			{
				$query['id'] = (int) $segment[2];
			}
		}
		else
		{
			// {project}
			if (!isset($segments[1]))
			{
				$model = new MonitorModelProject(false);
				$id    = $model->resolveAlias($segments[0]);

				$query['view'] = 'project';
				$query['id']   = $id;
			}
			else
			{
				// {project}/issues
				if ($segments[1] == 'issues')
				{
					$model = new MonitorModelProject(false);
					$id    = $model->resolveAlias($segments[0]);

					$query['view'] = 'issues';
					$query['id']   = $id;
				}
				else
				{
					$query['view'] = 'issue';
					$query['id']   = $segments[1];

					// {project}/{issue}/edit
					if (isset($segments[2]) && $segments[2] == 'edit')
					{
						$query['layout'] = 'edit';
					}
				}
			}
		}

		return $query;
	}
}
