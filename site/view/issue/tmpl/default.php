<?php
/**
 * @package     Monitor
 * @subpackage  admin
 *
 * @copyright   Copyright (C) 2015 Constantin Romankiewicz.
 * @license     Apache License 2.0; see LICENSE
 */

$date_format = $this->params->get('issue_date_format', JText::_('DATE_FORMAT_LC2'));
$urlCommentSave = JRoute::_('index.php?option=com_monitor');
$urlCommentEdit = JRoute::_('index.php?option=com_monitor&task=comment.edit&issue_id=' . $this->item->id);
?>

<?php if ($this->params->get('show_page_heading', 1)) : ?>
<h2><?php echo $this->item->title; ?></h2>
<?php endif; ?>

<?php echo $this->item->event->afterDisplayTitle; ?>

<?php if ($this->canEditIssue): ?>
<a class="btn" href="<?php echo JRoute::_('index.php?option=com_monitor&task=issue.edit&id=' . $this->item->id); ?>">
	<span class="icon-pencil-2"></span>
	<?php echo JText::_('JGLOBAL_EDIT'); ?>
</a>
<?php endif; ?>

<?php // TODO: Split in blocks when new MVC is implemented. ?>
<div class="row-fluid issue" itemscope itemtype="http://schema.org/Question">
	<?php echo $this->item->event->beforeDisplayContent; ?>
	<div class="issue-description span9" itemprop="text">
		<?php echo $this->item->text; ?>
	</div>
	<div class="issue-details span3">
		<dl class="dl-horizontal">
			<?php if ($this->params->get('issue_show_status', 1)): ?>
				<dt><?php echo JText::_('COM_MONITOR_STATUS'); ?>:</dt>
				<dd>
					<?php
						$statusHelp = ($this->params->get('issue_show_status_help', 1) && isset($this->item->status_help))
							? 'data-content="' . $this->item->status_help . '" data-original-title="' . $this->item->status . '"'
							: '';
						?>
					<span class="<?php echo $this->item->status_style; ?> hasPopover"
						<?php echo $statusHelp; ?>>
						<?php echo $this->item->status; ?>
					</span>
				</dd>
			<?php endif; ?>

			<?php if ($this->params->get('issue_show_classification', 1)): ?>
				<dt><?php echo JText::_('COM_MONITOR_CLASSIFICATION'); ?>:</dt>
				<dd>
					<?php echo $this->item->classification_title; ?>
				</dd>
			<?php endif; ?>

			<?php if ($this->params->get('issue_show_author', 1)) : ?>
				<dt><?php echo JText::_('COM_MONITOR_CREATED_BY'); ?>:</dt>
				<dd>
					<?php
					// Profile avatar (using the CMAvatar plugin).
					if ($this->params->get('issue_show_avatar', 1) && $this->avatars !== null && isset($this->avatars[$this->item->author_id]))
					{
						$url   = $this->avatars[$this->item->author_id];
						$attributes = array('title' => JText::sprintf('COM_MONITOR_VIEW_PROFILE', $this->item->author_name));
						$image = JHtml::_('image', $url, $this->item->author_name, $attributes);

						echo $image;
					}

					if (!empty($this->item->contact_id) && $this->params->get('link_author', 1) == true)
					{
						$contact_link = JRoute::_('index.php?option=com_contact&view=contact&id=' . (int) $this->item->contact_id);
						echo JHtml::_('link', $contact_link, $this->item->author_name, array('itemprop' => 'author'));
					}
					else
					{
						echo $this->item->author_name;
					}
					?>
				</dd>
			<?php endif; ?>

			<?php if ($this->params->get('issue_show_date_created', 1)): ?>
				<dt><?php echo JText::_('COM_MONITOR_CREATE_DATE'); ?>:</dt>
				<dd itemprop="dateCreated">
					<?php echo JHtml::_('date', $this->item->created, $date_format); ?>
				</dd>
			<?php endif; ?>

			<?php if ($this->params->get('issue_show_project', 1)): ?>
				<dt><?php echo JText::_('COM_MONITOR_PROJECT_NAME'); ?>:</dt>
				<dd>
					<a href="<?php echo JRoute::_('index.php?option=com_monitor&view=project&id=' . $this->item->project_id); ?>">
						<?php echo $this->item->project_name; ?>
					</a>
				</dd>
			<?php endif; ?>

			<?php if ($this->params->get('issue_show_version', 1)): ?>
				<dt><?php echo JText::_('COM_MONITOR_VERSION'); ?>:</dt>
				<dd>
					<?php echo $this->item->version; ?>
				</dd>
			<?php endif; ?>
		</dl>
	</div>
</div>
<div class="comments">
	<?php if ($this->comments) : ?>
		<?php
		$oldStatus = $this->defaultStatus;

		foreach ($this->comments as $i => $comment) :
			$class = ($i % 2 == 0) ? 'even' : 'odd';
			$canEdit = $this->canEditComments || ($this->canEditOwnComments && $comment->author_id == JFactory::getUser()->id);
			?>
			<div class="comment row-fluid row-<?php echo $class; ?>"
				id="comment-<?php echo $comment->id; ?>"
				 itemscope itemtype="http://schema.org/Comment"
				data-issue="<?php echo $this->item->id; ?>"
				data-comment="<?php echo $comment->id; ?>"
				data-author="<?php echo $comment->author_name; ?>">
				<div class="comment-details span3">
					<?php if ($this->params->get('comment_show_author', 1)) : ?>
						<div class="comment-author">
							<?php
							// Profile avatar (using the CMAvatar plugin).
							if ($this->params->get('comment_show_avatar', 1) && $this->avatars !== null && isset($this->avatars[$comment->author_id]))
							{
								$url = $this->avatars[$comment->author_id];
								$attributes = array('title' => JText::sprintf('COM_MONITOR_VIEW_PROFILE', $comment->author_name));
								$image = JHtml::_('image', $url, $comment->author_name, $attributes);

								echo $image;
							}

							if (!empty($comment->contact_id) && $this->params->get('link_author', 1))
							{
								$contact_link = JRoute::_('index.php?option=com_contact&view=contact&id=' . (int) $comment->contact_id);
								echo JHtml::_('link', $contact_link, $comment->author_name, array('itemprop' => 'author'));
							}
							else
							{
								echo $comment->author_name;
							}
							?>
						</div>
					<?php endif; ?>
					<?php if ($this->params->get('comment_show_date_created', 1)): ?>
						<div class="comment-date" itemprop="dateCreated">
							<?php echo JHtml::_('date', $comment->created, $date_format); ?>
						</div>
					<?php endif; ?>

				</div>
				<div class="comment-content span9">
					<?php if ($canEdit || $this->canDeleteComments) : ?>
					<div class="comment-icons pull-right">
						<?php if ($canEdit): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=comment.edit&id=' . $comment->id); ?>"
							   title="<?php echo JText::_('COM_MONITOR_EDIT_COMMENT'); ?>">
								<span class="icon-pencil"></span>
							</a>
						<?php endif; ?>
						<?php if ($this->canDeleteComments): ?>
							<a href="<?php echo JRoute::_('index.php?option=com_monitor&task=comment.delete&id=' . $comment->id . '&issue_id=' . $this->item->id); ?>"
							   title="<?php echo JText::_('COM_MONITOR_DELETE_COMMENT'); ?>">
								<span class="icon-trash"></span>
							</a>
						<?php endif; ?>
					</div>
					<?php endif; ?>
					<?php if ($comment->status_id && $this->params->get('comment_show_status', 1)):
						$newStatus = $this->status[$comment->status_id];
						$oldHelp = ($this->params->get('comment_show_status_help', 1) && isset($oldStatus->helptext))
							? ' data-content="' . $oldStatus->helptext . '" data-original-title="' . $oldStatus->name . '"'
							: '';
						$newHelp = ($this->params->get('comment_show_status_help', 1) && isset($newStatus->helptext))
							? ' data-content="' . $newStatus->helptext . '" data-original-title="' . $newStatus->name . '"'
							: '';
						?>
						<div class="comment-status">
						<span class="<?php echo $oldStatus->style; ?> hasPopover"
							<?php echo $oldHelp; ?>
							>
							<?php echo $oldStatus->name; ?>
						</span>
						<span class="icon-arrow-right-4"></span>
						<span class="<?php echo $newStatus->style; ?> hasPopover"
							<?php echo $newHelp; ?>
							>
							<?php echo $newStatus->name; ?>
						</span>
						</div>
						<?php
						$oldStatus = $newStatus;
					endif; ?>
					<div class="comment-text" itemprop="text">
						<?php echo $comment->text; ?>
					</div>
				</div>
			</div>
		<?php endforeach; ?>

		<?php echo $this->pagination->getListFooter(); ?>
	<?php else: ?>
		<p class="muted"><?php echo JText::_('COM_MONITOR_NO_COMMENTS'); ?></p>
	<?php endif; ?>

	<?php if ($this->canEditOwnComments): ?>
		<h3>
			<?php echo JText::_('COM_MONITOR_CREATE_COMMENT'); ?>
		</h3>
		<div class="comment-form-inline" id="comment-form">
			<form method="post" action="<?php echo $urlCommentEdit; ?>">
				<input type="hidden" name="issue_id" value="<?php echo $this->item->id; ?>" />
				<textarea
					placeholder="<?php echo JText::_('COM_MONITOR_COMMENT_TEXT'); ?>"
					name="text" id="text" class="required" required="" aria-required="true"></textarea>
				<div class="btn-toolbar">
					<button type="submit" name="task" value="comment.save" class="btn btn-primary"
						formaction="<?php echo $urlCommentSave; ?>">
						<?php echo JText::_('COM_MONITOR_CREATE_COMMENT_SEND'); ?>
					</button>
					<button type="submit" name="task" value="comment.edit" class="btn">
						<?php echo JText::_('COM_MONITOR_CREATE_COMMENT_EXTENDED'); ?>
					</button>
				</div>
			</form>
		</div>
	<?php endif; ?>
</div>

<?php echo $this->item->event->afterDisplayContent; ?>

<script>
	jQuery(document).ready(function() {
		jQuery('.hasPopoverClick').popover({
			"html"     : true,
			"placement": "top",
			"trigger"  : "click focus",
			"container": "body"
		});
	});
</script>
