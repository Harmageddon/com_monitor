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
	 * Execute the controller, load a form for the given comment.
	 *
	 * @return  boolean  True if controller finished execution.
	 */
	public function execute()
	{
		$cid = $this->input->get('cid', array(), 'array');
		$id = $cid ? $cid[0] : $this->input->getInt('id');
		$model = new MonitorModelComment;

		if (!$model->canEdit(JFactory::getUser(), $id))
		{
			throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
		}

		$model->setCommentId($id);
		$model->loadForm();
		$view = new MonitorViewCommentHtml($model);
		$view->setLayout('edit');
		echo $view->render();

		return true;
	}
}
