<?php
/**
 * @package     Monitor
 * @subpackage  site
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

/**
 * Controller to unsubscribe from a project.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerProjectUnsubscribe extends JControllerBase
{
	/**
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Execute the controller.
	 *
	 * @return  boolean  True if controller finished execution, false if the controller did not
	 *                   finish execution. A controller might return false if some precondition for
	 *                   the controller to run has not been satisfied.
	 *
	 * @since   12.1
	 * @throws  LogicException
	 * @throws  RuntimeException
	 */
	public function execute()
	{
		$id = $this->input->getInt('id');
		$user = JFactory::getUser();

		if ($user->guest)
		{
			$this->app->enqueueMessage(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'error');
		}
		else
		{
			$model = new MonitorModelSubscription;

			if ($model->isSubscriberProject($id, $user->id))
			{
				$model->unsubscribeProject($id, $user->id);
				$this->app->enqueueMessage(JText::_('COM_MONITOR_SUBSCRIPTION_PROJECT_UNSUBSCRIBED'), 'message');
			}
		}

		$return = base64_decode($this->app->input->get('return', '', 'BASE64'));

		if (!JUri::isInternal($return))
		{
			$return = 'index.php?option=com_monitor&view=project&id=' . $id;
		}

		$this->app->redirect(JRoute::_($return, false));
	}
}
