<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access
defined('_HZEXEC_') or die();

$ids = array();
foreach ($this->rows as $row)
{
	$ids[] = $row->id;
}

$canDo = \Components\Courses\Helpers\Permissions::getActions();

// Initiate paging
$pageNav = $this->pagination(
	$this->total,
	$this->filters['start'],
	$this->filters['limit']
);

Html::behavior('modal');

$this->js();
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option  . '&controller=' . $this->controller); ?>" method="post" name="adminForm" id="adminForm">
	<table class="adminlist">
		<thead>
			<tr>
				<th colspan="4">
					<select name="asset">
						<option value="0"><?php echo Lang::txt('COM_COURSES_SELECT'); ?></option>
						<?php if ($this->assets) { ?>
							<?php
							foreach ($this->assets as $asset)
							{
								if (in_array($asset->id, $ids))
								{
									continue;
								}
							?>
							<option value="<?php echo $this->escape(stripslashes($asset->id)); ?>"><?php echo $this->escape(stripslashes($asset->title)); ?> (<?php echo $this->escape(stripslashes($asset->type)); ?>)</option>
							<?php } ?>
						<?php } ?>
					</select>
					<input type="submit" id="btn-attach" value="<?php echo Lang::txt('COM_COURSES_ATTACH_ASSET'); ?>" />
				</th>
				<th colspan="4" class="align-right">
					<a href="<?php echo Route::url('index.php?option=' . $this->option  . '&controller=' . $this->controller . '&task=add&scope=' . $this->filters['asset_scope'] . '&scope_id=' . $this->filters['asset_scope_id'] . '&course_id=' . $this->filters['course_id'] . '&tmpl=' . $this->filters['tmpl']); ?>" class="edit-asset" rel="{handler: 'iframe', size: {x: 570, y: 550}}"><?php echo Lang::txt('COM_COURSES_CREATE_ASSET'); ?></a>
				</th>
			</tr>
			<tr>
				<th scope="col"><?php echo Lang::txt('COM_COURSES_COL_ID'); ?></th>
				<th scope="col"><?php echo Lang::txt('COM_COURSES_COL_TITLE'); ?></th>
				<th scope="col"><?php echo Lang::txt('COM_COURSES_COL_TYPE'); ?></th>
				<th scope="col"><?php echo Lang::txt('COM_COURSES_COL_STATE'); ?></th>
				<th scope="col" colspan="3"><?php echo Lang::txt('COM_COURSES_COL_ORDERING'); ?></th>
				<th scope="col">X</th>
			</tr>
		</thead>
		<tbody>
<?php
$i = 0;
$k = 0;
$n = count($this->rows);
foreach ($this->rows as $row)
{
?>
			<tr class="<?php echo "row$k"; ?>">
				<td>
					<?php echo $this->escape($row->id); ?>
					<input class="invisible" type="checkbox" name="id[]" id="cb<?php echo $i; ?>" value="<?php echo $row->id; ?>" class="checkbox-toggle" />
				</td>
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a class="edit-asset" rel="{handler: 'iframe', size: {x: 570, y: 550}}" href="<?php echo Route::url('index.php?option=' . $this->option  . '&controller=' . $this->controller . '&task=edit&id=' . $row->id . '&scope=' . $this->filters['asset_scope'] . '&scope_id=' . $this->filters['asset_scope_id'] . '&course_id=' . $this->filters['course_id'] . '&tmpl=' . $this->filters['tmpl']); ?>">
						<?php echo $this->escape(stripslashes($row->title)); ?>
					</a>
				<?php } else { ?>
					<span>
						<?php echo $this->escape(stripslashes($row->title)); ?>
					</span>
				<?php } ?>
				</td>
				<td>
					<?php echo $this->escape(stripslashes($row->type)); ?>
				</td>
				<td>
					<?php if ($row->state == 2) { ?>
						<span class="state delete">
							<span class="text"><?php echo Lang::txt('COM_COURSES_TRASHED'); ?></span>
						</span>
					<?php } else if ($row->state == 1) { ?>
						<span class="state publish">
							<span class="text"><?php echo Lang::txt('COM_COURSES_PUBLISHED'); ?></span>
						</span>
					<?php } else { ?>
						<span class="state unpublish">
							<span class="text"><?php echo Lang::txt('COM_COURSES_UNPUBLISHED'); ?></span>
						</span>
					<?php } ?>
				</td>
				<td>
					<?php echo $pageNav->orderUpIcon($i, ($row->ordering != @$this->rows[$i-1]->ordering)); ?>
				</td>
				<td>
					<?php echo $pageNav->orderDownIcon($i, $n, ($row->ordering != @$this->rows[$i+1]->ordering)); ?>
				</td>
				<td>
					<?php echo $this->escape(stripslashes($row->ordering)); ?>
				</td>
				<td>
				<?php if ($canDo->get('core.edit')) { ?>
					<a class="state delete" href="<?php echo Route::url('index.php?option=' . $this->option  . '&controller=' . $this->controller . '&task=unlink&asset=' . $row->id . '&scope=' . $this->filters['asset_scope'] . '&scope_id=' . $this->filters['asset_scope_id'] . '&course_id=' . $this->filters['course_id'] . '&tmpl=' . $this->filters['tmpl'] . '&' . Session::getFormToken() . '=1'); ?>">
						<span><?php echo Lang::txt('COM_COURSES_REMOVE'); ?></span>
					</a>
				<?php } ?>
				</td>
			</tr>
<?php
	$i++;
	$k = 1 - $k;
}
?>
		</tbody>
	</table>

	<input type="hidden" name="course_id" value="<?php echo $this->escape($this->filters['course_id']); ?>" />
	<input type="hidden" name="tmpl" value="<?php echo $this->escape($this->filters['tmpl']); ?>" />
	<input type="hidden" name="scope" value="<?php echo $this->escape($this->filters['asset_scope']); ?>" />
	<input type="hidden" name="scope_id" value="<?php echo $this->escape($this->filters['asset_scope_id']); ?>" />
	<input type="hidden" name="option" value="<?php echo $this->option; ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>">
	<input type="hidden" name="task" id="task" value="" />
	<input type="hidden" name="boxchecked" value="0" />

	<?php echo Html::input('token'); ?>
</form>
