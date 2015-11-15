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
<?php if ($this->params->get('project_show_logo', 1) && $this->item->logo) : ?>
	<div class="monitor-project-logo">
	<?php if ($this->params->get('project_link_logo', 1) && $this->item->url) : ?>
		<a
			href="<?php echo $this->item->url; ?>"
			title="<?php echo JText::sprintf('COM_MONITOR_VISIT_WEBSITE', $this->item->name); ?>"
			>
	<?php endif; ?>

	<img class="monitor-project-logo"
		src="<?php echo $this->item->logo; ?>"
		alt="<?php echo $this->item->logo_alt; ?>"
		/>

	<?php if ($this->params->get('project_link_logo', 1) && $this->item->url) : ?>
		</a>
	<?php endif; ?>

	</div>
<?php endif; ?>

<?php if ($this->params->get('project_show_url', 1) && $this->item->logo) : ?>
	<div class="monitor-project-website">
		<a
			href="<?php echo $this->item->url; ?>"
			title="<?php echo JText::sprintf('COM_MONITOR_VISIT_WEBSITE', $this->item->name); ?>"
			>
			<?php echo JText::_('COM_MONITOR_WEBSITE'); ?>
		</a>

	</div>
<?php endif; ?>

<div class="monitor-project-description">
	<?php echo $this->item->description; ?>
</div>

<?php if (!empty($this->buttons)): ?>
	<div class="btn-group">
		<?php foreach ($this->buttons as $button): ?>
			<a class="btn"
					href="<?php echo JRoute::_($button['url']); ?>"
					title="<?php echo JText::_($button['title']); ?>"
			>
				<span class="<?php echo $button['icon']; ?>"></span>
				<?php echo JText::_($button['text']); ?>
			</a>
		<?php endforeach; ?>
	</div>
<?php endif; ?>
