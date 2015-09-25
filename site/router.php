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
	const COMPONENT = 'com_monitor';

	/**
	 * @var JApplicationCms
	 */
	protected $app;

	/**
	 * @var JMenu
	 */
	protected $menu;

	/**
	 * @var MonitorModelProject
	 */
	private $modelProject;

	/**
	 * @var MonitorModelIssue
	 */
	private $modelIssue;

	/**
	 * MonitorRouter constructor.
	 *
	 * @param   JApplicationCms     $app          Application object that the router should use
	 * @param   JMenu               $menu         Menu object that the router should use
	 * @param   MonitorModelProject $modelProject Project model to use in the router.
	 * @param   MonitorModelIssue   $modelIssue   Issue model to use in the router.
	 *
	 * @throws Exception
	 */
	public function __construct($app = null, $menu = null, $modelProject = null, $modelIssue = null)
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

		if ($modelProject)
		{
			$this->modelProject = $modelProject;
		}
		else
		{
			$this->modelProject = new MonitorModelProject($app, false);
		}

		if ($modelIssue)
		{
			$this->modelIssue = $modelIssue;
		}
		else
		{
			$this->modelIssue = new MonitorModelIssue($app, false);
		}
	}

	/**
	 * Prepare-method for URLs
	 * This method is meant to validate and complete the URL parameters.
	 * For example it can add the Itemid or set a language parameter.
	 * This method is executed on each URL, regardless of SEF mode switched
	 * on or not.
	 *
	 * @param   array $query An associative array of URL arguments
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
	 * @param   array &$query An array of URL arguments
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

		// TODO: What about the option?

		// Convert task to view/layout format.
		self::convertTaskToView($query);

		if (!isset($query['view']))
		{
			return array();
		}

		// Get the active menu item
		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		$menuQuery = $menuItem->query;
		self::convertTaskToView($menuQuery);

		switch ($query['view'])
		{
			case 'projects':
				$url = $this->buildProjects($query, $menuQuery);
				break;
			case 'comment':
				$url = $this->buildComment($query, $menuQuery);
				break;
			case 'issues':
			case 'issue':
				$url = $this->buildIssue($query, $menuQuery);
				break;
			default:
				$url = $this->buildProject($query, $menuQuery);
		}

		ksort($url);

		return $url;
	}

	/**
	 * Builds an URL for the "projects" view.
	 *
	 * @param   array &$query    An array of URL arguments.
	 * @param   array $menuQuery The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildProjects(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : '';

		// If the menu item already points to "projects", return an empty subsequent URL.
		if ($menuView !== 'projects')
		{
			$url[0] = 'projects';
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Builds an URL for the "comment" view.
	 *
	 * @param   array &$query    An array of URL arguments.
	 * @param   array $menuQuery The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildComment(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		$sameView    = $menuView === $query['view'];
		$sameComment = $sameView && isset($menuQuery['id']) && isset($query['id']) && $menuQuery['id'] === $query['id'];
		$sameIssue   = $sameView && isset($menuQuery['issue_id']) && isset($query['issue_id']) && $menuQuery['issue_id'] === $query['issue_id'];

		if (!$sameView)
		{
			$url[0] = 'comment';
		}

		if (isset($query['id']))
		{
			if (!$sameComment)
			{
				$url[1] = 'edit';
				$url[2] = $query['id'];
			}

			unset($query['id']);
		}
		elseif (isset($query['issue_id']))
		{
			if (!$sameIssue)
			{
				$url[1] = 'new';
				$url[2] = $query['issue_id'];
			}

			unset($query['issue_id']);
		}

		if (isset($query['layout']))
		{
			unset($query['layout']);
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Builds an URL for the "issue" and "issues" views.
	 *
	 * @param   array &$query    An array of URL arguments.
	 * @param   array $menuQuery The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildIssue(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		if ($query['view'] === 'issues')
		{
			$this->modelProject->setProjectId($query['project_id']);
			unset($query['project_id']);

			// Does the menu item point to the "issues" view for the same project?
			$menuViewSameProjectIssues = $menuView === 'issues' && $this->modelProject->getProjectId() === (int) $menuQuery['project_id'];

			if (!($menuViewSameProjectIssues))
			{
				$url[1] = 'issues';
			}
		}
		else
		{
			$hasId             = isset($query['id']);
			$menuViewSameIssue = $hasId && isset($menuQuery['id']) && $menuView === 'issue' && $query['id'] === $menuQuery['id'];
			$editing           = isset($query['layout']) && $query['layout'] == 'edit';
			$menuEditing       = isset($menuQuery['layout']) && $menuQuery['layout'] === 'edit';

			if ($hasId)
			{
				if (!$menuViewSameIssue || ($menuEditing && !$editing))
				{
					$this->modelIssue->setIssueId($query['id']);
					$issue = $this->modelIssue->getIssue();

					if ($issue)
					{
						$this->modelProject->setProjectId($issue->project_id);

						$url[1] = $query['id'];
					}
				}

				unset($query['id']);
			}

			if ($editing)
			{
				if (!isset($url[1]) && !$hasId && isset($query['project_id']))
				{
					$menuEditingSameProject = isset($menuQuery['project_id']) && $menuQuery['project_id'] == $query['project_id'];

					$this->modelProject->setProjectId($query['project_id']);
					unset($query['project_id']);

					if (!($menuEditing && $menuEditingSameProject))
					{
						$url[1] = 'new';
					}
				}
				else
				{
					if (!$menuViewSameIssue || !$menuEditing)
					{
						$url[2] = 'edit';
					}
				}

				unset($query['layout']);
			}
		}

		// Does the menu item point to the "project" view for the same project?
		$menuViewSameProject       = $menuView === 'project' && $this->modelProject->getProjectId() === (int) $menuQuery['id'];
		$menuViewSameProjectIssues = $menuView === 'issues' && $this->modelProject->getProjectId() === (int) $menuQuery['project_id'];
		$menuViewSameIssue         = isset($menuViewSameIssue) && $menuViewSameIssue
			&& isset($menuEditing) && isset($editing) && (!$menuEditing || $editing);
		$menuViewSameIssueEditing  = isset($menuViewSameIssue) && $menuViewSameIssue
			&& isset($menuEditing) && isset($editing) && $menuEditing && $editing;
		$menuEditingSameProject    = isset($menuEditingSameProject) && $menuEditingSameProject;

		if (!($menuViewSameProjectIssues || $menuViewSameProject || $menuViewSameIssue || $menuViewSameIssueEditing || $menuEditingSameProject))
		{
			$project = $this->modelProject->getProject();

			if ($project)
			{
				$url[0] = $project->alias;
			}
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Builds an URL for the "project" view.
	 *
	 * @param   array &$query    An array of URL arguments.
	 * @param   array $menuQuery The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildProject(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		$this->modelProject->setProjectId($query['id']);

		$project = $this->modelProject->getProject();

		if ($project)
		{
			if (!($menuView === 'project' && $query['id'] === $menuQuery['id']))
			{
				$url[0] = $project->alias;
			}

			unset($query['id']);
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Converts a "task" URL parameter to the view/layout format.
	 *
	 * @param   array &$query The query to edit.
	 *
	 * @return null
	 */
	public static function convertTaskToView(&$query)
	{
		if (isset($query['task']))
		{
			$parts           = explode('.', $query['task']);
			$query['view']   = $parts[0];
			$query['layout'] = $parts[1];
			unset($query['task']);
		}
	}

	/**
	 * Parse method for URLs
	 * This method is meant to transform the human readable URL back into
	 * query parameters. It is only executed when SEF mode is switched on.
	 *
	 * @param   array &$segments The segments of the URL to parse.
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

		$query = array(
			'option' => 'com_monitor',
		);

		if (empty($query['Itemid']))
		{
			$menuItem = $this->menu->getActive();
		}
		else
		{
			$menuItem = $this->menu->getItem($query['Itemid']);
		}

		if (empty(array_filter($segments)))
		{
			return $menuItem->query;
		}

		$menuQuery = ($menuItem->query['option'] === self::COMPONENT) ? $menuItem->query : null;
		self::convertTaskToView($menuQuery);

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		if ($segments[0] == 'projects')
		{
			$query['view'] = 'projects';
		}
		elseif ($segments[0] == 'issues' && ($menuView == 'project' && isset($menuQuery['id'])))
		{
			$query['view']       = 'issues';
			$query['project_id'] = $menuQuery['id'];
		}
		elseif ($segments[0] == 'comment')
		{
			$query['view']   = 'comment';
			$query['layout'] = 'edit';

			if (isset($segments[1]) && $segments[1] == 'new' && is_numeric($segments[2]))
			{
				$query['issue_id'] = $segments[2];
			}
			elseif (is_numeric($segments[2]))
			{
				$query['id'] = $segments[2];
			}
		}
		// {issue}
		elseif (is_numeric($segments[0]))
		{
			$query['view'] = 'issue';
			$query['id']   = $segments[0];

			if (isset($segments[1]) && $segments[1] === 'edit')
			{
				$query['layout'] = 'edit';
			}
		}
		// /edit
		elseif ($segments[0] === 'edit' || $segments[0] === 'new')
		{
			$query['layout'] = 'edit';

			if ($menuView === 'comment')
			{
				$query['view'] = 'comment';

				if (is_numeric($segments[1]))
				{
					if ($segments[0] === 'new')
					{
						$query['issue_id'] = $segments[1];
					}
					else
					{
						$query['id'] = $segments[1];
					}
				}
			}
			else
			{
				$query['view'] = 'issue';

				if ($segments[0] === 'new')
				{
					if (isset($menuQuery['id']))
					{
						$query['project_id'] = $menuQuery['id'];
					}
					elseif (isset($menuQuery['project_id']))
					{
						$query['project_id'] = $menuQuery['project_id'];
					}
				}
				else
				{
					if (isset($menuQuery['id']))
					{
						$query['id'] = $menuQuery['id'];
					}
				}
			}
		}
		else
		{
			// {project}
			if (!isset($segments[1]))
			{
				$id = $this->modelProject->resolveAlias($segments[0]);

				$query['view'] = 'project';
				$query['id']   = $id;
			}
			else
			{
				// {project}/issues
				if ($segments[1] === 'issues')
				{
					$id = $this->modelProject->resolveAlias($segments[0]);

					$query['view']       = 'issues';
					$query['project_id'] = $id;
				}
				else
				{
					$query['view'] = 'issue';

					// {project}/new
					if ($segments[1] === 'new')
					{
						$id = $this->modelProject->resolveAlias($segments[0]);

						$query['layout']     = 'edit';
						$query['project_id'] = $id;
					}
					// {project}/{issue}
					elseif (is_numeric($segments[1]))
					{
						$query['id'] = $segments[1];

						// {project}/{issue}/edit
						if (isset($segments[2]) && $segments[2] == 'edit')
						{
							$query['layout'] = 'edit';
						}
					}
				}
			}
		}

		return $query;
	}
}
