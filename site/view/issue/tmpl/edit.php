<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die;

JHtml::_('behavior.formvalidator');
JHtml::_('formbehavior.chosen', 'select');

$app   = JFactory::getApplication();
$input = $app->input;
$id    = ($this->item) ? '&id=' . (int) $this->item->id : '';
?>

<div class="monitor issue-edit">

	<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h2>
		<?php
		if ($this->item)
		{
			echo JText::sprintf('COM_MONITOR_EDIT_ISSUE', $this->item->title);
		}
		else
		{
			echo JText::_('COM_MONITOR_CREATE_ISSUE');
		}
		?>
	</h2>
	<?php endif; ?>

	<?php if ($this->params->get('issue_create_show_description', 0) && $this->item === null): ?>
		<div class="well description">
			<?php echo JText::_('COM_MONITOR_ISSUE_CREATE_DESC'); ?>
		</div>
	<?php endif; ?>

	<form action="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit' . $id); ?>" method="post"
		name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
		<div class="form-horizontal">
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('title'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('title'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('project_id'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('project_id'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('classification'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('classification'); ?>
				</div>
			</div>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('version'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('version'); ?>
				</div>
			</div>
			<?php echo $this->form->getLabel('text'); ?>
			<?php echo $this->form->getInput('text'); ?>
			<div class="control-group">
				<div class="control-label">
					<?php echo $this->form->getLabel('file[]'); ?>
				</div>
				<div class="controls">
					<?php echo $this->form->getInput('file[]'); ?>
				</div>
			</div>

		</div>


		<div class="btn-toolbar">
			<div class="btn-group">
				<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('issue.save')">
					<span class="icon-ok"></span><?php echo JText::_('JSAVE') ?>
				</button>
			</div>
			<div class="btn-group">
				<button type="button" class="btn" onclick="Joomla.submitbutton('issue.cancel')">
					<span class="icon-cancel"></span><?php echo JText::_('JCANCEL') ?>
				</button>
			</div>
		</div>

		<input type="hidden" name="task" value="" />
		<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
	</form>

</div>
