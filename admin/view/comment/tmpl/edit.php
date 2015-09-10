<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die;

$app   = JFactory::getApplication();
$input = $app->input;
$id    = ($this->item) ? '&id=' . (int) $this->item->id : '';

?>

<form action="<?php echo JRoute::_('index.php?option=com_monitor&task=comment.edit' . $id); ?>" method="post"
	name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal">
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('issue_id'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('issue_id'); ?>
			</div>
		</div>

		<?php if (JFactory::getUser()->authorise('comment.edit.status', 'com_monitor')) : ?>
		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('issue_status'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('issue_status'); ?>
			</div>
		</div>
		<?php endif; ?>
		<?php echo $this->form->getLabel('text'); ?>
		<?php echo $this->form->getInput('text'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
</form>
