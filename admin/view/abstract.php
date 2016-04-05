<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Abstract view class implementing some base functionality common to all views.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
abstract class MonitorViewAbstract extends JViewHtml
{
	/**
	 * @var Registry object holding the parameters.
	 */
	protected $params;

	/**
	 * @var string Name of the view.
	 */
	protected $name = '';

	/**
	 * @var string Icon to be displayed for backend views.
	 */
	protected $icon = '';

	/**
	 * @var JPagination Pagination object.
	 */
	protected $pagination;

	/**
	 * @var MonitorModelAbstract
	 */
	protected $model;

	/**
	 * @var String Default title, used if there is no matching menu item.
	 */
	protected $defaultTitle = '';

	/**
	 * @var array Custom toolbar buttons.
	 */
	protected $customToolbar = null;

	/**
	 * Constructor for all views.
	 *
	 * @param   MonitorModelAbstract  $model  Model containing the information to be displayed by the view.
	 * @param   SplPriorityQueue      $paths  The paths queue.
	 *
	 * @throws Exception
	 */
	public function __construct(MonitorModelAbstract $model, SplPriorityQueue $paths = null)
	{
		if ($paths === null)
		{
			$paths = new SplPriorityQueue;
		}

		// Get the params
		// TODO: may be removed when new MVC is implemented completely
		$app = JFactory::getApplication();

		if ($app instanceof JApplicationSite)
		{
			$this->params = $app->getParams();
			$active = $app->getMenu()->getActive();

			if ($active)
			{
				$this->params->merge($active->params);
			}
		}
		else
		{
			$this->params = JComponentHelper::getParams('com_monitor');
		}

		$reflector = new ReflectionObject($this);
		$paths->insert(dirname($reflector->getFileName()) . '/tmpl', 1);

		parent::__construct($model, $paths);
	}

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @throws  RuntimeException
	 */
	public function render()
	{
		if ($this->model && !$this->pagination)
		{
			// Pagination
			$this->pagination = $this->model->getPagination();

			// Ordering
			$this->listOrder	= $this->escape($this->model->getState()->get('list.ordering'));
			$this->listDir	= $this->escape($this->model->getState()->get('list.direction'));
		}

		// Page title
		$app = JFactory::getApplication();

		if ($app instanceof JApplicationSite)
		{
			$active = $app->getMenu()->getActive();

			$title = '';

			if ($active)
			{
				$title = $this->params->get('page_title', '');

				$uri = JUri::getInstance();

				// Check if the menu determines this view.
				foreach ($active->query as $key => $value)
				{
					if (substr($uri->getVar($key), 0, 7) != 'filter_' && $uri->getVar($key) !== $value)
					{
						$title = $this->defaultTitle;
					}
				}
			}

			if (!$title)
			{
				$title = $this->defaultTitle;
			}

			if ($title)
			{
				if ($app->get('sitename_pagetitles', 0) == 1)
				{
					$title = JText::sprintf('JPAGETITLE', $app->get('sitename'), $title);
				}
				elseif ($app->get('sitename_pagetitles', 0) == 2)
				{
					$title = JText::sprintf('JPAGETITLE', $title, $app->get('sitename'));
				}

				$app->getDocument()->setTitle($title);
			}
		}

		return parent::render();
	}
}
