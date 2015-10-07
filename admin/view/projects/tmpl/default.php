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
<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=projects'); ?>" method="post" name="adminForm" id="adminForm">
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
	echo JLayoutHelper::render('joomla.searchtools.default',
		array(
			'view' => $this,
			'options' => array(
				'filterButton' => false,
			),
		)
	);
	?>
	<?php if (empty($this->items)) : ?>
		<div class="alert alert-no-items">
			<?php echo JText::_('COM_MONITOR_PROJECTS_NONE'); ?>
		</div>
	<?php else : ?>

		<table class="table table-striped" id="articleList">
			<thead>
			<tr>
				<th width="1%" class="center hidden-phone">
					<?php echo JHtml::_('grid.checkall'); ?>
				</th>
				<th>
					<?php echo JHtml::_('searchtools.sort', 'JGLOBAL_TITLE', 'name', $this->listDir, $this->listOrder); ?>
				</th>
				<th width="1%" class="center">
					<?php echo JHtml::_('searchtools.sort', 'JGRID_HEADING_ID', 'id', $this->listDir, $this->listOrder); ?>
				</th>
			</tr>
			</thead>
			<tbody>
			<?php foreach ($this->items as $i => $item) :
				$canEdit = $user->authorise('core.edit', 'com_monitor.project.' . $item->id);
				?>
				<tr>
					<td class="center hidden-phone">
						<?php echo JHtml::_('grid.id', $i, $item->id); ?>
					</td>
					<td>
						<?php if ($canEdit) : ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=project.edit&id=' . $item->id); ?>"
								title="<?php echo JText::_('JACTION_EDIT'); ?>">
								<?php echo $this->escape($item->name); ?>
							</a>
						<?php else : ?>
							<span>
								<?php echo $this->escape($item->name); ?>
							</span>
						<?php endif; ?>
					</td>
					<td class="center">
						<?php echo $item->id; ?>
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
