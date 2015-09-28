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
 * Edit or create a comment.
 *
 * @author  Constantin Romankiewicz <constantin@zweiiconkram.de>
 * @since   1.0
 */
class MonitorControllerCommentEdit extends JControllerBase
{
	/**
	 * @var    JApplicationCms
	 */
	protected $app;

	/**
	 * Execute the controller, load a form for the given comment.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$model = new MonitorModelComment;
		$id = $this->input->getInt('id');

		// Get the params
		// TODO: may be removed when new MVC is implemented completely
		$app = JFactory::getApplication();

		if ($app instanceof JApplicationSite)
		{
			$params = $app->getParams();
		}

		// ACL: Check if user can create
		$user = JFactory::getUser();

		if (!$model->canEdit($user, $id))
		{
			if ($user->guest && isset($params) && $params->get('redirect_login', 1))
			{
				$this->app->enqueueMessage(JText::_('JGLOBAL_YOU_MUST_LOGIN_FIRST'), 'error');
				$this->app->redirect(JRoute::_('index.php?option=com_users&view=login&return=' . base64_encode(JUri::getInstance()->toString()), '403'));
			}
			else
			{
				throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
			}
		}

		if ($id != 0)
		{
			$model->setCommentId($id);
		}
		else
		{
			$issue_id = $this->input->getInt('issue_id');

			if ($issue_id == 0)
			{
				$this->app->enqueueMessage(JText::_('COM_MONITOR_ERROR_INVALID_ID'), 'error');

				return false;
			}

			$model->setIssueId($issue_id);
		}

		$model->loadForm();
		$view = new MonitorViewCommentHtml($model);
		$view->setLayout('edit');
		echo $view->render();

		return true;
	}
}
