<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die;

$user = JFactory::getUser();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
JHtml::_('formbehavior.chosen', 'select');
?>
<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=comments'); ?>" method="post" name="adminForm" id="adminForm">
	<?php if (!empty($this->sidebar)) : ?>
	<div id="j-sidebar-container" class="span2">
		<?php echo $this->sidebar; ?>
	</div>
	<div id="j-main-container" class="span10">
	<?php else : ?>
	<div id="j-main-container">
	<?php endif; ?>
	<?php
	// Search tools bar
	echo JLayoutHelper::render('joomla.searchtools.default', array('view' => $this));
	?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
		</div>
	<?php else : ?>

		<table class="table table-striped" id="articleList">
			<thead>
			<tr>
				<th width="1%" class="center hidden-phone">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'c.id', $this->listDir, $this->listOrder); ?>
				</th>
				<th class="hidden-phone">
					<?php echo JText::_('COM_MONITOR_PROJECT_NAME'); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_MONITOR_ISSUE_TITLE', 'i.title', $this->listDir, $this->listOrder); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_MONITOR_COMMENT_AUTHOR', 'u.name', $this->listDir, $this->listOrder); ?>
				</th>
				<th class="hidden-phone">
					<?php echo JText::_('COM_MONITOR_COMMENT_TEXT'); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'JDATE', 'c.created', $this->listDir, $this->listOrder); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_COMMENT_STATUS'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$user = JFactory::getUser();
				$canEditComment = $this->canEditComments || ($this->canEditOwnComments && $item->author_id == $user->id);

				?>
				<tr>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($canEditComment) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=comment.edit&id=' . $item->id); ?>"
								title="<?php echo JText::_('COM_MONITOR_EDIT_COMMENT'); ?>">
								<?php echo $this->escape($item->id); ?></a>
						<?php else : ?>
							<span><?php echo $this->escape($item->id); ?></span>
						<?php endif; ?></td>
					<td class="hidden-phone">
						<?php if ($this->canEditProjects) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=project.edit&id=' . $item->project_id); ?>"
								title="<?php echo JText::_('COM_MONITOR_EDIT_PROJECT'); ?>">
								<?php echo $this->escape($item->project_name); ?></a>
						<?php else : ?>
							<span><?php echo $this->escape($item->project_name); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<?php if ($this->canEditIssues) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit&id=' . $item->issue_id); ?>"
								title="<?php echo JText::_('COM_MONITOR_EDIT_ISSUE'); ?>">
								<?php echo $this->escape($item->issue_title); ?></a>
						<?php else : ?>
							<span><?php echo $this->escape($item->issue_title); ?></span>
						<?php endif; ?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->author_id); ?>"
							title="<?php echo JText::_('COM_MONITOR_EDIT_USER'); ?>">
							<?php echo $this->escape($item->author_name); ?></a>
					</td>
					<td class="hidden-phone">
						<?php echo $this->escape(MonitorHelper::cutStr($item->text, 50)); ?>
					</td>
					<td>
						<?php echo $this->escape($item->created); ?>
					</td>
					<td>
						<?php
						if ($item->status_id)
						{
							echo $this->escape($item->status_name);
						}
						else
						{
							echo "<em>" . JText::_('COM_MONITOR_STATUS_NO_CHANGE') . "</em>";
						}
						?>
					</td>
				</tr>
			<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
	<?php echo $this->pagination->getListFooter(); ?>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>

