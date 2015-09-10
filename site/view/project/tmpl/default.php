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
	<?php echo $this->item->name; ?>
</h2>
<?php endif; ?>

<div class="monitor-project-description">
	<?php echo $this->item->description; ?>
</div>
<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=issues&project_id=' . $this->item->id); ?>">
	<span class="icon-chevron-right"></span>
	<?php echo JText::_('COM_MONITOR_GO_TO_ISSUES'); ?>
</a>
<a class="btn" href="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit&project_id=' . $this->item->id); ?>">
	<span class="icon-new"></span>
	<?php echo JText::_('COM_MONITOR_CREATE_ISSUE'); ?>
</a>
