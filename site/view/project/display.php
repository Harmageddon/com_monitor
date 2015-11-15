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
 * View to display a single project.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorViewProjectDisplay extends MonitorViewAbstract
{
	/**
	 * @var MonitorModelProject
	 */
	protected $model;

	/**
	 * @var mixed Object containing data about the item to be edited or displayed.
	 */
	protected $item;

	/**
	 * @var MonitorModelSubscription
	 */
	protected $modelSubscription;

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
		$project = $this->model->getProject();
		$this->modelSubscription = new MonitorModelSubscription;

		$this->item = $project;
		$this->setLayout('default');

		if ($this->item)
		{
			$this->defaultTitle = $this->escape($this->item->name);

			$this->buttons['issues'] = array(
					'url'   => 'index.php?option=com_monitor&view=issues&project_id=' . $this->item->id,
					'text'  => 'COM_MONITOR_GO_TO_ISSUES',
					'title' => 'COM_MONITOR_GO_TO_ISSUES',
					'icon'  => 'icon-chevron-right',
			);
			$this->buttons['new-issue'] = array(
					'url'   => 'index.php?option=com_monitor&task=issue.edit&project_id=' . $this->item->id,
					'text'  => 'COM_MONITOR_CREATE_ISSUE',
					'title' => 'COM_MONITOR_CREATE_ISSUE',
					'icon'  => 'icon-new',
			);

			$user = JFactory::getUser();

			if ($this->params->get('enable_notifications', 1) && !$user->guest)
			{
				$subscribed = $this->modelSubscription->isSubscriberProject($this->item->id, $user->id);
				$task       = $subscribed ? 'unsubscribe' : 'subscribe';

				$this->buttons['subscribe'] = array(
						'url'   => 'index.php?option=com_monitor&task=project.' . $task . '&id=' . $this->item->id,
						'text'  => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_ISSUE' : 'COM_MONITOR_SUBSCRIBE_ISSUE',
						'title' => $subscribed ? 'COM_MONITOR_UNSUBSCRIBE_ISSUE_DESC' : 'COM_MONITOR_SUBSCRIBE_ISSUE_DESC',
						'icon'  => $subscribed ? 'icon-star' : 'icon-star-empty',
				);
			}
		}

		return parent::render();
	}
}
