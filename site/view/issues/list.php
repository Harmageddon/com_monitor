<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */
defined('_JEXEC') or die;

/**
 * View to display a list of issues.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewIssuesList extends MonitorViewAbstract
{
	/**
	 * @var MonitorModelIssue
	 */
	protected $model;

	/**
	 * @var MonitorModelSubscription
	 */
	protected $modelSubscription;

	/**
	 * @var MonitorModelNotifications
	 */
	protected $modelNotifications;

	/**
	 * @var   mixed  Items to be displayed.
	 */
	protected $items;

	/**
	 * @var JForm The filter form.
	 */
	public $filterForm;

	/**
	 * Contains buttons to be rendered in the view.
	 *
	 * @var array
	 */
	protected $buttons = array();

	/**
	 * Method to render the view.
	 *
	 * @return  string  The rendered view.
	 *
	 * @since   12.1
	 * @throws  RuntimeException
	 */
	public function render()
	{
		$this->items = $this->model->getIssues();

		$this->filterForm = $this->model->getFilterForm();

		$this->defaultTitle = JText::_('COM_MONITOR_ISSUES');

		$this->setLayout('default');

		$projectId = $this->model->getProjectId();

		if ($projectId !== null)
		{
			$this->buttons['new-issue'] = array(
				'url'   => 'index.php?option=com_monitor&task=issue.edit&project_id=' . $projectId,
				'text'  => 'COM_MONITOR_CREATE_ISSUE',
				'title' => 'COM_MONITOR_CREATE_ISSUE',
				'icon'  => 'icon-new',
			);

			$user = JFactory::getUser();

			if (!$user->guest)
			{
				$this->modelSubscription = new MonitorModelSubscription;
				$this->modelNotifications  = new MonitorModelNotifications;

				if ($this->params->get('enable_notifications', 1))
				{
					$subscribed = $this->modelSubscription->isSubscriberProject($projectId, $user->id);
					$task       = $subscribed ? 'unsubscribe' : 'subscribe';

					$this->buttons['subscribe'] = array(
						'url'   => 'index.php?option=com_monitor&task=project.' . $task . '&id=' . $projectId .
							'&return=' . base64_encode(JUri::getInstance()->toString()),
						'text'  => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_PROJECT' : 'COM_MONITOR_SUBSCRIBE_PROJECT',
						'title' => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_PROJECT_DESC' : 'COM_MONITOR_SUBSCRIBE_PROJECT_DESC',
						'icon'  => $subscribed ? 'icon-star' : 'icon-star-empty',
					);
				}

				$this->buttons['all-read'] = array(
					'url'   => 'index.php?option=com_monitor&task=project.read&id=' . $projectId,
					'text'  => 'COM_MONITOR_PROJECT_MARK_ALL_READ',
					'title' => 'COM_MONITOR_PROJECT_MARK_ALL_READ_DESC',
					'icon'  => 'icon-eye',
				);
			}
		}

		return parent::render();
	}

	/**
	 * Renders a filter field for the list display.
	 *
	 * @param   string  $fieldName  Name of the filter field.
	 *
	 * @return   string  HTML output.
	 */
	public function renderFilterField($fieldName)
	{
		$filters = $this->filterForm->getGroup('filter');

		$class = ($filters[$fieldName]->value == '') ? '' : ' filter-selected';

		return '<div class="controls span4' . $class . '">'
			. $filters[$fieldName]->label
			. $filters[$fieldName]->input
			. '</div>';
	}
}
