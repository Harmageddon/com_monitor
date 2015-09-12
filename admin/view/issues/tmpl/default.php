<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

defined('_JEXEC') or die('Restricted access');
$user = JFactory::getUser();
JHtml::_('bootstrap.tooltip');
JHtml::_('behavior.multiselect');
?>
<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=issues'); ?>" method="post" name="adminForm" id="adminForm">

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
				<th width="1%" class="hidden-phone">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'i.title', $this->listDir, $this->listOrder); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_MONITOR_ISSUE_AUTHOR', 'u.name', $this->listDir, $this->listOrder); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'COM_MONITOR_PROJECT_NAME', 'p.name', $this->listDir, $this->listOrder); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_ISSUE_STATUS'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_CLASSIFICATION'); ?>
				</th>
				<th width="1%">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'i.id', $this->listDir, $this->listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canEdit = $user->authorise('issue.edit', 'com_monitor');

				?>
				<tr>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit&id=' . $item->id); ?>"
								title="<?php echo JText::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->title); ?></a>
						<?php else : ?>
								<?php echo $this->escape($item->title); ?>
						<?php endif; ?>
					</td>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_users&task=user.edit&id=' . (int) $item->author_id); ?>"
							title="<?php echo JText::_('COM_MONITOR_EDIT_USER'); ?>">
							<?php echo $this->escape($item->author_name); ?></a>
					</td>
					<td class="center">
						<?php echo $this->escape($item->project_name); ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->status); ?>
					</td>
					<td class="center">
						<?php echo $this->escape($item->classification_title); ?>
					</td>
					<td><?php echo $item->id; ?></td>
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

