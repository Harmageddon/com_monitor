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
?>

<?php if ($this->params->get('show_page_heading', 1)) : ?>
	<h2><?php echo JText::_('COM_MONITOR_ISSUES'); ?></h2>
<?php endif; ?>

<form action="<?php echo JRoute::_('index.php?option=com_monitor&view=issues' . $projectId); ?>" method="post" id="adminForm"
	class="search-form form-inline">
	<?php
	// Search tools bar
	if ($this->params->get('list_show_filters', 1)):
		$filters = $this->filterForm->getGroup('filter');
		$i       = 0;
		?>
		<div class="row-fluid">
		<?php
		if ($this->params->get('list_show_filter_search', 1)) :
			$i += 8;
			?>
			<div class="controls span8">
				<label for="filter_search" class="element-invisible">
					<?php echo JText::_('JSEARCH_FILTER'); ?>
				</label>

				<div class="btn-wrapper input-append">
					<?php echo $filters['filter_search']->input; ?>
					<button type="submit" class="btn hasTooltip" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_SUBMIT'); ?>">
						<i class="icon-search"></i>
					</button>
				</div>
			</div>
		<?php endif; ?>

		<?php
		if ($this->params->get('list_show_filter_status', 1))
		{
			$i += 4;
			echo $this->renderFilterField('filter_issue_status');
		}

		if ($i >= 12) :
			$i = 0;
			?>
			</div>
			<div class="row-fluid">
		<?php endif;

		if ($this->params->get('list_show_filter_classification', 1))
		{
			$i += 4;
			echo $this->renderFilterField('filter_classification');
		}

		if ($i >= 12) :
			$i = 0;
			?>
			</div>
			<div class="row-fluid">
		<?php endif;

		if ($this->params->get('list_show_filter_project', 1))
		{
			$i += 4;
			echo $this->renderFilterField('filter_project_id');
		}

		if ($i >= 12) :
			$i = 0;
			?>
			</div>
			<div class="row-fluid">
		<?php endif;

		if ($this->params->get('list_show_filter_author', 1))
		{
			$i += 4;
			echo $this->renderFilterField('filter_author');
		}

		if ($i >= 12) :
			$i = 0;
			?>
			</div>
			<div class="row-fluid">
		<?php endif; ?>

		<div class="span-<?php echo 12 - $i; ?>">
			<button type="button" class="btn hasTooltip js-stools-btn-clear" title="<?php echo JHtml::tooltipText('JSEARCH_FILTER_CLEAR'); ?>"
				onclick="clearForm(this.form)">
				<?php echo JText::_('JSEARCH_FILTER_CLEAR'); ?>
			</button>
		</div>
		</div>
	<?php endif; ?>
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

			<?php if ($this->params->get('list_show_status', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_STATUS'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_author', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_CREATED_BY'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_date_created', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_CREATE_DATE'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_date_modified', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_LAST_MODIFIED'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_project', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_PROJECT_NAME'); ?></th>
			<?php endif; ?>

			<?php if ($this->params->get('list_show_version', 1)): ?>
				<th><?php echo JText::_('COM_MONITOR_VERSION'); ?></th>
			<?php endif; ?>
		</tr>
		</thead>

		<tbody>

		<?php foreach ($this->items as $item) : ?>
			<tr>
				<td>
					<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=issue&id=' . $item->id); ?>">
						<?php echo $item->title; ?>
					</a>
				</td>
				<?php if ($this->params->get('list_show_status', 1)) : ?>
					<td>
						<?php
						$statusHelp = ($this->params->get('list_show_status_help', 1) && isset($item->status_help))
							? 'data-content="' . $item->status_help . '" data-original-title="' . $item->status . '"'
							: '';
						?>
						<span class="<?php echo $item->status_style; ?> hasPopover"
							<?php echo $statusHelp; ?>>
							<?php echo $item->status; ?>
						</span>
					</td>
				<?php endif; ?>

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

				<?php if ($this->params->get('list_show_date_modified', 1)) : ?>
					<td>
						<?php
						$modified = empty($item->modified) ? $item->created : $item->modified;
						echo JHtml::_('date', $modified, $date_format);
						?>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_project', 1)): ?>
					<td>
						<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=project&id=' . $item->project_id); ?>">
							<?php echo $item->project_name; ?>
						</a>
					</td>
				<?php endif; ?>

				<?php if ($this->params->get('list_show_version', 1)): ?>
					<td>
						<?php echo $item->version; ?>
					</td>
				<?php endif; ?>
			</tr>
		<?php endforeach; ?>
		</tbody>
	</table>
<?php endif; ?>

<?php
echo $this->pagination->getListFooter();
?>

<script>
	function clearForm(el) {
		if (jQuery) {
			jQuery(el).find('select[name^="filter"], input[name^="filter"]').each(function () {
				jQuery(this).val('');
			});
			jQuery(el).submit();
		}
		else {
			console.error("jQuery is disabled.");
		}
	}
	jQuery(document).ready(function () {
		jQuery('.hasPopoverClick').popover({
			"html"     : true,
			"placement": "top",
			"trigger"  : "click focus",
			"container": "body"
		});
	});
</script>
