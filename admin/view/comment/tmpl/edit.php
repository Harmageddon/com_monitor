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

$app   = JFactory::getApplication();
$input = $app->input;
$id    = ($this->item) ? '&id=' . (int) $this->item->id : '';

$prefix = '../media/com_monitor/';
?>

<form action="<?php echo JRoute::_('index.php?option=com_monitor&task=comment.edit' . $id); ?>" method="post"
	name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">

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

		<div class="control-group">
			<div class="control-label">
				<?php echo $this->form->getLabel('file[]'); ?>
			</div>
			<div class="controls">
				<?php echo $this->form->getInput('file[]'); ?>
			</div>
		</div>

	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="return" value="<?php echo $input->getCmd('return'); ?>" />
</form>

<?php if (!empty($this->attachments)) : ?>
	<table class="table table-striped table-hover">
		<caption>
			<?php echo JText::_('COM_MONITOR_ATTACHMENTS'); ?>
		</caption>
		<tbody>
		<?php
		foreach ($this->attachments as $attachment) :
			$url = 'index.php?option=com_monitor&task=attachment.delete&id=' . $attachment['id']
				. '&return=' . base64_encode(JUri::getInstance()->toString());
			?>
			<tr>
				<td>
					<a href="<?php echo JUri::getInstance($prefix . $attachment['path'])->toString(); ?>"
						title="<?php echo JText::_('COM_MONITOR_ATTACHMENT_VIEW'); ?>"
						target="_blank">
						<?php echo $attachment['name']; ?>
					</a>
				</td>
				<td>
					<form action="<?php echo JRoute::_($url); ?>" method="post"
						name="adminForm" id="adminForm" class="form-validate" enctype="multipart/form-data">
						<button class="btn btn-danger" type="submit">
							<span class="icon-remove"></span>
							<?php echo JText::_('COM_MONITOR_ATTACHMENT_DELETE'); ?>
						</button>
					</form>
				</td>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

