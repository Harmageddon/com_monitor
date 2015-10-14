<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('formbehavior.chosen', 'select');

/**
 * @var   JForm $form
 */

$app   = JFactory::getApplication();
$input = $app->input;
$id    = ($this->item) ? '&id=' . (int) $this->item->id : '';
?>

<form action="<?php echo JRoute::_('index.php?option=com_monitor&task=project.edit' . $id); ?>" method="post"
	name="adminForm" id="adminForm" class="form-validate">

	<ul class="nav nav-tabs">
		<li class="active"><a href="#project" data-toggle="tab"><?php echo JText::_('COM_MONITOR_PROJECT'); ?></a></li>
		<li><a href="#issues" data-toggle="tab"><?php echo JText::_('COM_MONITOR_ISSUES'); ?></a></li>
	</ul>

	<div class="tab-content">
		<div id="project" class="tab-pane active form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('name'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('name'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('alias'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('alias'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('url'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('url'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('logo'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('logo'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('logo_alt'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('logo_alt'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('description'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('description'); ?>
				</div>
			</div>

			<input type="hidden" name="task" value="" />
			<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
		</div>

		<div id="issues" class="tab-pane form-horizontal">
			<?php echo $this->form->renderFieldset('issues'); ?>
		</div>
	</div>
</form>
