<?php
/**
 * Entry point for all backend controllers.
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

// Application
$app = JFactory::getApplication();
$view = $app->input->get('view');

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
	$action = "Display";
}
else
{
	$object = "Projects";
	$action = "Display";
}

if ($action == "SetDefault")
{
	$action = "Default";
}

if ($action == "SetUnDefault")
{
	$action = "UnDefault";
}

$controllerClass = "MonitorController" . $object . $action;

$controller = new $controllerClass;

$controller->execute();
