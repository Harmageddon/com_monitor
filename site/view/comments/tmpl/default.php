<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */
defined('_JEXEC') or die;

$date_format = $this->params->get('list_date_format', JText::_('DATE_FORMAT_LC2'));

$app       = JFactory::getApplication();
$projectId = $app->input->getInt('project_id', 0);

if ($projectId != 0)
{
	$projectId = '&project_id=' . $projectId;
}
else
{
	$projectId = '';
}
?>

<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h2><?php echo JText::_('COM_MONITOR_COMMENTS'); ?></h2>
<?php endif; ?>

<?php if (!empty($this->buttons)): ?>
	<div class="btn-toolbar">
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
	</div>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=comments' . $projectId); ?>" method="post" id="adminForm"
	class="search-form form-inline">
	<?php
	echo $this->renderFilterField('filter_author');
	?>

	<div class="pull-right">
		<?php echo $this->pagination->getLimitBox(); ?>
	</div>
</form>

<?php if (empty($this->items)) : ?>
	<div class="alert alert-no-items">
		<?php echo JText::_('JGLOBAL_NO_MATCHING_RESULTS'); ?>
	</div>
<?php else : ?>
	<table class="table table-striped table-hover">
		<thead>
		<tr>
			<th><?php echo JText::_('COM_MONITOR_ISSUE_TITLE'); ?></th>

			<th><?php echo JText::_('COM_MONITOR_COMMENT_TEXT'); ?></th>

			<?php if ($this->params->get('list_show_author', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_CREATED_BY'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_date_created', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_CREATE_DATE'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_project', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_PROJECT_NAME'); ?></th>
			<?php endif; ?>
		</tr>
		</thead>

		<tbody>

		<?php foreach ($this->items as $item) : ?>
			<tr>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=issue&id=' . $item->issue_id . '#comment-' . $item->id); ?>">
						<?php echo $item->issue_title; ?>
					</a>
				</td>
				<td>
					<?php echo $this->escape(MonitorHelper::cutStr($item->text, 80)); ?>
				</td>

				<?php if ($this->params->get('list_show_author', 1)) : ?>
					<td>
						<?php
						if (!empty($item->contact_id) && $this->params->get('list_link_author', 1) == true) :
							$contact_link = JRoute::_('index.php?option=com_contact&view=contact&id=' . (int) $item->contact_id);
							echo JHtml::_('link', $contact_link, $item->author_name, array('itemprop' => 'url'));
						else :
							echo $item->author_name;
						endif; ?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_date_created', 1)) : ?>
					<td>
						<?php echo JHtml::_('date', $item->created, $date_format); ?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_project', 1)): ?>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=project&id=' . $item->project_id); ?>">
							<?php echo $item->project_name; ?>
						</a>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php
echo $this->pagination->getListFooter();
