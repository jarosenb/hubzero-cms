<?php
/**
 * @package    hubzero-cms
 * @copyright  Copyright (c) 2005-2020 The Regents of the University of California.
 * @license    http://opensource.org/licenses/MIT MIT
 */

// No direct access.
defined('_HZEXEC_') or die();

$canDo = Components\Redirect\Helpers\Redirect::getActions();

Toolbar::title(Lang::txt('COM_REDIRECT_MANAGER_LINKS'), 'redirect');
if ($canDo->get('core.create'))
{
	Toolbar::addNew();
}
if ($canDo->get('core.edit'))
{
	Toolbar::editList();
}
if ($canDo->get('core.edit.state'))
{
	if ($this->filters['state'] != 2)
	{
		Toolbar::divider();
		Toolbar::publish('publish', 'JTOOLBAR_ENABLE', true);
		Toolbar::unpublish('unpublish', 'JTOOLBAR_DISABLE', true);
	}
	if ($this->filters['state'] != -1)
	{
		Toolbar::divider();
		if ($this->filters['state'] != 2)
		{
			Toolbar::archiveList('archive');
		}
		elseif ($this->filters['state'] == 2)
		{
			Toolbar::unarchiveList('publish', 'JTOOLBAR_UNARCHIVE');
		}
	}
}
if ($this->filters['state'] == -2 && $canDo->get('core.delete'))
{
	Toolbar::deleteList('', 'remove', 'JTOOLBAR_EMPTY_TRASH');
	Toolbar::divider();
}
elseif ($canDo->get('core.edit.state'))
{
	Toolbar::trash('trash');
	Toolbar::divider();
}
if ($canDo->get('core.admin'))
{
	Toolbar::preferences('com_redirect');
	Toolbar::divider();
}
Toolbar::help('links');

Html::behavior('tooltip');
Html::behavior('multiselect');

$this->css('.adminlist tr td {
	-ms-word-break: break-all;
	word-wrap: break-word;
	word-break: break-all;
	-webkit-hyphens: auto;
	-moz-hyphens: auto;
	hyphens: auto;
}');
?>

<form action="<?php echo Route::url('index.php?option=' . $this->option . '&view=links'); ?>" method="post" name="adminForm" id="adminForm">
	<fieldset id="filter-bar">
		<div class="grid">
			<div class="col span6">
				<label class="filter-search-lbl" for="filter_search"><?php echo Lang::txt('JSEARCH_FILTER_LABEL'); ?></label>
				<input type="text" name="search" id="filter_search" class="filter" value="<?php echo $this->escape($this->filters['search']); ?>" placeholder="<?php echo Lang::txt('COM_REDIRECT_SEARCH_LINKS'); ?>" />
				<button type="submit"><?php echo Lang::txt('JSEARCH_FILTER_SUBMIT'); ?></button>
				<button type="button" class="filter-clear"><?php echo Lang::txt('JSEARCH_FILTER_CLEAR'); ?></button>
			</div>
			<div class="col span6">
				<label for="filter_state"><?php echo Lang::txt('JOPTION_SELECT_PUBLISHED');?></label>
				<select name="state" id="filter_state" class="inputbox filter filter-submit">
					<?php echo Html::select('options', Components\Redirect\Helpers\Redirect::publishedOptions(), 'value', 'text', $this->filters['state'], true);?>
				</select>
			</div>
		</div>
	</fieldset>

	<table class="adminlist">
		<thead>
			<tr>
				<th scope="col">
					<input type="checkbox" name="checkall-toggle" value="" title="<?php echo Lang::txt('JGLOBAL_CHECK_ALL'); ?>" class="checkbox-toggle toggle-all" />
				</th>
				<th scope="col" class="title">
					<?php echo Html::grid('sort', 'COM_REDIRECT_HEADING_OLD_URL', 'old_url', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<?php if ($this->filters['type'] == 'redirect') { ?>
				<th scope="col">
					<?php echo Html::grid('sort', 'COM_REDIRECT_HEADING_NEW_URL', 'new_url', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<?php } else { ?>
				<th scope="col" class="priority-4">
					<?php echo Html::grid('sort', 'COM_REDIRECT_HEADING_REFERRER', 'referer', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<?php } ?>
				<th scope="col" class="priority-5">
					<?php echo Html::grid('sort', 'COM_REDIRECT_HEADING_CREATED_DATE', 'created_date', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<th scope="col" class="priority-2">
					<?php echo Html::grid('sort', 'JSTATUS', 'published', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<th scope="col" class="priority-3">
					<?php echo Html::grid('sort', 'COM_REDIRECT_HEADING_HITS', 'hits', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
				<th scope="col" class="priority-5 nowrap">
					<?php echo Html::grid('sort', 'JGRID_HEADING_ID', 'id', @$this->filters['sort_Dir'], @$this->filters['sort']); ?>
				</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<td colspan="7">
					<?php echo $this->rows->pagination; ?>
				</td>
			</tr>
			<tr>
				<td colspan="7">
					<p class="info">
						<?php if ($this->enabled) : ?>
							<span class="enabled"><?php echo Lang::txt('COM_REDIRECT_PLUGIN_ENABLED'); ?></span>
						<?php else : ?>
							<span class="disabled"><?php echo Lang::txt('COM_REDIRECT_PLUGIN_DISABLED'); ?></span>
						<?php endif; ?>
					</p>
				</td>
			</tr>
		</tfoot>
		<tbody>
		<?php
		$canCreate = User::authorise('core.create', $this->option);
		$canEdit   = User::authorise('core.edit', $this->option);
		$canChange = User::authorise('core.edit.state', $this->option);
		$i = 0;
		foreach ($this->rows as $item) :
			?>
			<tr class="row<?php echo $i % 2; ?>">
				<td class="center">
					<input type="checkbox" name="id[]" id="cb<?php echo $i;?>" value="<?php echo $item->id ?>" class="checkbox-toggle" />
				</td>
				<td>
					<?php if ($canEdit) : ?>
						<a href="<?php echo Route::url('index.php?option=' . $this->option . '&task=edit&id=' . $item->id);?>" title="<?php echo $this->escape($item->old_url); ?>">
							<?php
							$old = str_replace(Request::root(), '', $item->old_url);
							echo '<span class="smallsub">' . Lang::txt('COM_REDIRECT_ROOT') . '</span>/' . $this->escape(ltrim($old, '/')); ?>
						</a>
					<?php else : ?>
						<?php echo $this->escape(str_replace(Request::root(), '', $item->old_url)); ?>
					<?php endif; ?>
				</td>
				<?php if ($this->filters['type'] == 'redirect') { ?>
				<td>
					<?php
					if (substr($item->new_url, 0, strlen('http')) != 'http')
					{
						echo '<span class="smallsub">' . Lang::txt('COM_REDIRECT_ROOT') . '</span>/';
					}
					echo $this->escape(ltrim($item->new_url, '/'));
					?>
				</td>
				<?php } else { ?>
				<td class="priority-4">
					<?php echo $this->escape($item->referer); ?>
				</td>
				<?php } ?>
				<td class="priority-5 center">
					<?php echo Date::of($item->created_date)->toLocal(Lang::txt('DATE_FORMAT_LC4')); ?>
				</td>
				<td class="priority-2 center">
					<?php echo Components\Redirect\Helpers\Redirect::published($item->published, $i); ?>
				</td>
				<td class="priority-3 center">
					<?php echo (int) $item->hits; ?>
				</td>
				<td class="priority-5 center">
					<?php echo (int) $item->id; ?>
				</td>
			</tr>
			<?php
			$i++;
		endforeach;
		?>
		</tbody>
	</table>

	<?php if ($this->rows->count()) : ?>
		<?php echo $this->loadTemplate('addform'); ?>
	<?php endif; ?>

	<input type="hidden" name="option" value="<?php echo $this->option ?>" />
	<input type="hidden" name="controller" value="<?php echo $this->controller; ?>" />
	<input type="hidden" name="task" value="" autocomplete="off" />
	<input type="hidden" name="boxchecked" value="0" />
	<input type="hidden" name="filter_order" value="<?php echo $this->escape($this->filters['sort']); ?>" />
	<input type="hidden" name="filter_order_Dir" value="<?php echo $this->escape($this->filters['sort_Dir']); ?>" />

	<?php echo Html::input('token'); ?>
</form>
