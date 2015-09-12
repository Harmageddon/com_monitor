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
?>
<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=status'); ?>" method="post" name="adminForm" id="adminForm">
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
			<?php echo JText::_('COM_MONITOR_STATUS_NONE'); ?>
		</div>
	<?php else : ?>

		<table class="table table-striped" id="articleList">
			<thead>
			<tr>
				<th width="1%" class="hidden-phone">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th>
					<?php echo JText::_('JGLOBAL_TITLE'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_STATUS_OPEN'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_STATUS_DEFAULT'); ?>
				</th>
				<th>
					<?php echo JText::_('JFIELD_ORDERING_LABEL'); ?>
				</th>
				<th>
					<?php echo JText::_('COM_MONITOR_PROJECT'); ?>
				</th>
				<th width="1%" class="nowrap hidden-phone">
					<?php echo JText::_('JGRID_HEADING_ID'); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				// (task, text, active title, inactive title, tip (boolean), HTML active class, HTML inactive class)
				$states = array(
					0 => array ('open',  '', 'COM_MONITOR_STATUS_CLOSED', 'COM_MONITOR_STATUS_CLOSED_DISALLOW', true, 'eye-close', 'eye-close'),
					1 => array ('close', '', 'COM_MONITOR_STATUS_OPEN',   'COM_MONITOR_STATUS_OPEN_DISALLOW',   true, 'eye-open', 'eye-open'),
				);
				?>
				<tr>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($this->canEditStatus) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=status.edit&id=' . $item->id); ?>"
								title="<?php echo JText::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->name); ?></a>
						<?php else : ?>
								<?php echo $this->escape($item->name); ?>
						<?php endif; ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.state', $states, $item->open, $i, 'status.', $this->canEditStatus); ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.isdefault', $item->is_default, $i, 'status.', ($this->canEditStatus && $item->is_default == 0)); ?>
					</td>
					<td>
						<?php echo JHtml::_('jgrid.orderup', $i, 'orderup', 'status.', 'JLIB_HTML_MOVE_UP', $this->canEditStatus); ?>
						<?php echo JHtml::_('jgrid.orderdown', $i, 'orderdown', 'status.', 'JLIB_HTML_MOVE_DOWN', $this->canEditStatus); ?>
					</td>
					<td>
						<?php if ($this->canEditProjects) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=project.edit&id=' . $item->project_id); ?>"
								title="<?php echo JText::_('COM_MONITOR_EDIT_PROJECT'); ?>">
								<?php echo $this->escape($item->project_name); ?></a>
						<?php else : ?>
							<span><?php echo $this->escape($item->project_name); ?></span>
						<?php endif; ?>
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
