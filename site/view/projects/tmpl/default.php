<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */
defined('_JEXEC') or die;
?>

<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h2>
		<?php echo JText::_('COM_MONITOR_PROJECTS'); ?>
	</h2>
<?php endif; ?>

<table class="table table-striped">

	<thead>
	<tr>
		<th><?php echo JText::_('COM_MONITOR_PROJECT'); ?></th>
	</tr>
	</thead>
	<tbody>
		<?php foreach ($this->items as $item) : ?>
		<tr>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=project&id=' . $item->id); ?>">
					<?php echo $item->name; ?>
				</a>
			</td>
			<td>
				<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=issues&project_id=' . $item->id); ?>">
					<?php echo JText::_('COM_MONITOR_GO_TO_ISSUES'); ?>
				</a>
			</td>
			<td>
				<a class="btn" href="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit&project_id=' . $item->id); ?>">
					<span class="icon-new"></span>
					<?php echo JText::_('COM_MONITOR_CREATE_ISSUE'); ?>
				</a>
			</td>
		</tr>
		<?php endforeach; ?>
	</tbody>
</table>

<?php
echo $this->pagination->getListFooter();
