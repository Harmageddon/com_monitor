<?php
/**
 * Entry point for all frontend controllers.
 *
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */


// No direct access
defined('_JEXEC') or die;

// Load classes
JLoader::registerPrefix('Monitor', JPATH_COMPONENT);
JLoader::register('MonitorHelper', JPATH_ROOT . '/administrator/components/com_monitor/helper/helper.php');
JLoader::register('MonitorModelAbstract', JPATH_ROOT . '/administrator/components/com_monitor/model/abstract.php');
JLoader::register('MonitorModelNotifications', JPATH_ROOT . '/administrator/components/com_monitor/model/notifications.php');
JLoader::register('MonitorModelSubscription', JPATH_ROOT . '/administrator/components/com_monitor/model/subscription.php');
JLoader::register('MonitorModelAttachments', JPATH_ROOT . '/administrator/components/com_monitor/model/attachments.php');
JLoader::register('MonitorViewAbstract', JPATH_ROOT . '/administrator/components/com_monitor/view/abstract.php');

$parts = array('project', 'issue', 'comment');

foreach ($parts as $part)
{
	$part_uc = ucfirst($part);
	JLoader::register('MonitorModel' . $part_uc, JPATH_ROOT . '/administrator/components/com_monitor/model/' . $part . '.php');
	JLoader::register('MonitorController' . $part_uc . 'Cancel',
		JPATH_ROOT . '/administrator/components/com_monitor/controller/' . $part . '/cancel.php');
	JLoader::register('MonitorController' . $part_uc . 'Save',
		JPATH_ROOT . '/administrator/components/com_monitor/controller/' . $part . '/save.php');
	JLoader::register('MonitorController' . $part_uc . 'Delete',
		JPATH_ROOT . '/administrator/components/com_monitor/controller/' . $part . '/delete.php');
}

JLoader::register('MonitorControllerAttachmentDelete', JPATH_ROOT . '/administrator/components/com_monitor/controller/attachment/delete.php');

// Application
$app  = JFactory::getApplication();
$view = $app->input->get('view');
$layout = $app->input->get('layout');

$tasks = array();

if ($task = $app->input->get('task'))
{
	// Toolbar expects old style but we are using new style
	// Remove when toolbar can handle either directly
	if (strpos($task, '/') !== false)
	{
		$tasks = explode('/', $task);
	}
	else
	{
		$tasks = explode('.', $task);
	}
}

if (!empty($tasks))
{
	$object = (empty($tasks[0])) ? "Projects" : ucfirst($tasks[0]);
	$action = (empty($tasks[1])) ? "Display" : ucfirst($tasks[1]);
}
elseif (!empty($view))
{
	$object = ucfirst($view);
	$action = (empty($layout)) ? "Display" : ucfirst($layout);
}
else
{
	$object = "Projects";
	$action = "Display";
}

$controllerClass = "MonitorController" . $object . $action;

$controller = new $controllerClass;

$controller->execute();
