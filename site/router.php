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
	 * Lookup array for relevant menu items.
	 *
	 * Schema: $lookup[view][id][layout] => itemId
	 * $lookup[project][1] => 42
	 * $lookup[issue][2][edit] => 100
	 *
	 * @var array
	 */
	private $lookup;

	/**
	 * MonitorRouter constructor.
	 *
	 * @param   JApplicationCms      $app           Application object that the router should use
	 * @param   JMenu                $menu          Menu object that the router should use
	 * @param   MonitorModelProject  $modelProject  Project model to use in the router.
	 * @param   MonitorModelIssue    $modelIssue    Issue model to use in the router.
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
	 * @param   array  $query  An associative array of URL arguments
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 *
	 * @since   3.3
	 */
	public function preprocess($query)
	{
		if (!isset($query['Itemid']))
		{
			$queryEdited = $query;

			if (!isset($queryEdited['layout']))
			{
				$queryEdited['layout'] = 'default';
			}

			self::convertTaskToView($queryEdited);

			$itemId = $this->lookupQuery($queryEdited);

			if ($itemId)
			{
				$query['Itemid'] = $itemId;
			}
		}

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
		 * comments
		 * comments/{user}
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

		if ($menuItem && isset($menuItem->query))
		{
			$menuQuery = $menuItem->query;
			self::convertTaskToView($menuQuery);
		}
		else
		{
			$menuQuery = array();
		}

		switch ($query['view'])
		{
			case 'project':
				$url = $this->buildProject($query, $menuQuery);
				break;
			case 'comment':
				$url = $this->buildComment($query, $menuQuery);
				break;
			case 'comments':
				$url = $this->buildComments($query, $menuQuery);
				break;
			case 'issues':
			case 'issue':
				$url = $this->buildIssue($query, $menuQuery);
				break;
			case 'projects':
			default:
				$url = $this->buildProjects($query, $menuQuery);
		}

		ksort($url);

		return $url;
	}

	/**
	 * Builds an URL for the "projects" view.
	 *
	 * @param   array  &$query     An array of URL arguments.
	 * @param   array  $menuQuery  The query for the active menu item.
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
	 * Builds an URL for the "comments" view.
	 *
	 * @param   array  &$query     An array of URL arguments.
	 * @param   array  $menuQuery  The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildComments(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : '';
		$hasId = isset($query['user_id']);

		// If the menu item points to "comments", leave out the "comments".
		if ($menuView !== 'comments')
		{
			$url[0] = 'comments';
		}
		// If the menu item points to "comments" of a specific user different
		// from the one specified in the query, return the full URL.
		elseif (
			isset($menuQuery['user_id'])
			&& (!$hasId || $query['user_id'] !== $menuQuery['user_id'])
		)
		{
			$url[0] = 'comments';
		}

		if ($hasId)
		{
			if (!(isset($menuQuery['user_id']) && $menuView === 'comments' && $query['user_id'] === $menuQuery['user_id']))
			{
				$url[1] = $query['user_id'];
			}
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Builds an URL for the "comment" view.
	 *
	 * @param   array  &$query     An array of URL arguments.
	 * @param   array  $menuQuery  The query for the active menu item.
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
				if (isset($query['layout']) && $query['layout'] === 'delete')
				{
					$url[1] = 'delete';
					unset($query['layout']);
				}
				else
				{
					$url[1] = 'edit';
				}

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
	 * @param   array  &$query     An array of URL arguments.
	 * @param   array  $menuQuery  The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildIssue(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		$noProject = false;

		if ($query['view'] === 'issues')
		{
			if (isset($query['project_id']))
			{
				$this->modelProject->setProjectId((int) $query['project_id']);
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
				$noProject = true;
				$url[0] = 'issues';
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
					$projectId = $this->modelIssue->getIssueProject($query['id']);

					if ($projectId)
					{
						$this->modelProject->setProjectId($projectId);

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

		if (!($menuViewSameProjectIssues || $menuViewSameProject || $menuViewSameIssue
			|| $menuViewSameIssueEditing || $menuEditingSameProject || $noProject))
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
	 * @param   array  &$query     An array of URL arguments.
	 * @param   array  $menuQuery  The query for the active menu item.
	 *
	 * @return  array  The URL arguments to use to assemble the subsequent URL.
	 */
	private function buildProject(&$query, $menuQuery)
	{
		$url = array();

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		if (isset($query['id']))
		{
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
		}

		unset($query['view']);

		return $url;
	}

	/**
	 * Converts a "task" URL parameter to the view/layout format.
	 *
	 * @param   array  &$query  The query to edit.
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
		 * comments
		 * comments/{user}
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

		$menuQuery = ($menuItem !== null && $menuItem->query['option'] === self::COMPONENT) ? $menuItem->query : null;
		self::convertTaskToView($menuQuery);

		$menuView = (isset($menuQuery['view'])) ? $menuQuery['view'] : null;

		if ($segments[0] == 'projects')
		{
			$query['view'] = 'projects';
		}
		elseif ($segments[0] == 'issues')
		{
			$query['view'] = 'issues';

			if ($menuView == 'project' && isset($menuQuery['id']))
			{
				$query['project_id'] = $menuQuery['id'];
			}
		}
		elseif ($segments[0] == 'comment')
		{
			$query['view']   = 'comment';
			$query['layout'] = ($segments[1] === 'delete') ? 'delete' : 'edit';

			if (isset($segments[1]) && $segments[1] == 'new' && is_numeric($segments[2]))
			{
				$query['issue_id'] = $segments[2];
			}
			elseif (is_numeric($segments[2]))
			{
				$query['id'] = $segments[2];
			}
		}
		elseif ($segments[0] == 'comments')
		{
			$query['view']   = 'comments';

			if (isset($segments[1]) && is_numeric($segments[1]))
			{
				$query['user_id'] = $segments[1];
			}
		}
		// {issue} or {user_id}
		elseif (is_numeric($segments[0]))
		{
			if ($menuView === 'comments')
			{
				$query['view'] = 'comments';
				$query['user_id']   = $segments[0];
			}
			else
			{
				$query['view'] = 'issue';
				$query['id']   = $segments[0];

				if (isset($segments[1]) && $segments[1] === 'edit')
				{
					$query['layout'] = 'edit';
				}
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

	private function lookupQuery($query)
	{
		// Build the lookup array.
		if (!$this->lookup)
		{
			$this->makeLookup();
		}

		// View matches.
		if (isset($query['view']) && isset($this->lookup[$query['view']]))
		{
			// More complex view.
			if (is_array($this->lookup[$query['view']]))
			{
				$key = isset($query['id']) ? $query['id'] : ((isset($query['project_id'])) ? $query['project_id'] : '_');

				// View and ID match.
				if (isset($this->lookup[$query['view']][$key]))
				{
					if (is_array($this->lookup[$query['view']][$key]))
					{
						if (isset($query['layout']))
						{
							// View, ID and layout match.
							if (isset($this->lookup[$query['view']][$key][$query['layout']]))
							{
								return $this->lookup[$query['view']][$key][$query['layout']];
							}
							// View and ID match, different layout (menu: default, link: edit).
							elseif ($query['layout'] === 'default' && isset($this->lookup[$query['view']][$key]['edit']))
							{
								return $this->lookup[$query['view']][$key]['edit'];
							}
							// View and ID match, different layout (menu: default, link: new).
							elseif ($query['layout'] === 'default' && isset($this->lookup[$query['view']][$key]['new']))
							{
								return $this->lookup[$query['view']][$key]['new'];
							}
						}
					}
					else
					{
						return $this->lookup[$query['view']][$key];
					}
				}
			}
			// Simple view without more parameters.
			else
			{
				return $this->lookup[$query['view']];
			}
		}

		// View doesn't match.
		// Menu: Project, URL: Issues for the same project.
		if ($query['view'] === 'issues' && isset($query['project_id']))
		{
			if (isset($this->lookup['project']) && isset($this->lookup['project'][$query['project_id']]))
			{
				return $this->lookup['project'][$query['project_id']];
			}
		}
		elseif ($query['view'] === 'issue')
		{
			if (isset($query['id']))
			{
				$projectId = $this->modelIssue->getIssueProject($query['id']);

				if ($projectId)
				{
					// Found menu item for the same project.
					if (isset($this->lookup['project']) && isset($this->lookup['project'][$projectId]))
					{
						return $this->lookup['project'][$projectId];
					}
					// Found menu item for the same project (issues view).
					elseif (isset($this->lookup['issues']) && isset($this->lookup['issues'][$projectId]))
					{
						return $this->lookup['issues'][$projectId];
					}
				}
			}
		}

		return null;
	}

	/**
	 * Fills the lookup variable.
	 *
	 * @return null
	 */
	private function makeLookup()
	{
		$this->lookup = array();

		$component  = JComponentHelper::getComponent('com_monitor');

		$attributes = array('component_id');
		$values     = array($component->id);

		$items = $this->menu->getItems($attributes, $values);

		foreach ($items as $item)
		{
			if (isset($item->query))
			{
				switch ($item->query['view'])
				{
					case 'projects':
						$this->addLookup((int) $item->id, 'projects');
						break;
					case 'project':
						if (isset($item->query['id']))
						{
							$this->addLookup((int) $item->id, 'project', $item->query['id']);
						}
						break;
					case 'issues':
						$id = (isset($item->query['project_id'])) ? $item->query['project_id'] : '_';
						$this->addLookup((int) $item->id, 'issues', $id);
						break;
					case 'issue':
						$layout = (isset($item->query['layout'])) ? $item->query['layout'] : 'default';

						if (isset($item->query['id']))
						{
							$this->addLookup((int) $item->id, 'issue', $item->query['id'], $layout);
						}
						elseif ($layout === 'edit' && isset($item->query['project_id']))
						{
							$this->addLookup((int) $item->id, 'issue', $item->query['project_id'], 'new');
						}
						break;
					case 'comment':
						if (isset($item->query['id']))
						{
							$this->addLookup((int) $item->id, 'comment', $item->query['id'], 'edit');
						}
						elseif (isset($item->query['issue_id']))
						{
							$this->addLookup((int) $item->id, 'comment', $item->query['id'], 'new');
						}
						break;
				}
			}
		}
	}

	private function addLookup($value, $view, $id = null, $layout = null)
	{
		if (!isset($this->lookup))
		{
			$this->lookup = array();
		}

		if ($id !== null)
		{
			if (!isset($this->lookup[$view]))
			{
				$this->lookup[$view] = array();
			}

			if ($layout !== null)
			{
				if (!isset($this->lookup[$view][$id]))
				{
					$this->lookup[$view][$id] = array();
				}

				$this->lookup[$view][$id][$layout] = $value;
			}
			else
			{
				$this->lookup[$view][$id] = $value;
			}
		}
		else
		{
			if (!isset($this->lookup[$view]))
			{
				$this->lookup[$view] = $value;
			}
		}
	}
}
