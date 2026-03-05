<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Docstore
 * @author     yushinin <yushinins@mail.ru>
 * @copyright  2023 yushinin
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

// No direct access
defined('_JEXEC') or die;


use \Joomla\CMS\HTML\HTMLHelper;
use \Joomla\CMS\Factory;
use Joomla\CMS\HTML\Helpers\Number;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;


HTMLHelper::_('bootstrap.tooltip');
HTMLHelper::_('behavior.multiselect');
$app = Factory::getApplication();
// Import CSS
$wa = $this->document->getWebAssetManager();
$wa->useStyle('com_docstore.admin')
	->useScript('com_docstore.admin')
	->useScript('com_docstore.admin-doclink-modal');

$user = $app->getIdentity();
$userId = $user->id;
$listOrder = $this->state->get('list.ordering');
$listDirn = $this->state->get('list.direction');
$canOrder = $user->authorise('core.edit.state', 'com_docstore');

$saveOrder = $listOrder == 'a.ordering';

if (!empty($saveOrder)) {
	$saveOrderingUrl = 'index.php?option=com_docstore&task=docs.saveOrderAjax&tmpl=component&' . Session::getFormToken() . '=1';
	HTMLHelper::_('draggablelist.draggable');
}

$editor = $app->getInput()->getCmd('editor', '');
if (!empty($editor)) {
	// This view is used also in com_menus. Load the xtd script only if the editor is set!
	$this->document->addScriptOptions('xtd-doclinc', ['editor' => $editor]);
	echo 'Editor';
}

?>

<form action="<?php echo Route::_('index.php?option=com_docstore&view=docs'); ?>" method="post" name="adminForm" id="adminForm">
	<div class="row">
		<div class="col-md-12">
			<div id="j-main-container" class="j-main-container">
				<?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>

				<div class="clearfix"></div>
				<table class="table table-striped" id="docList">
					<thead>
						<tr>
							<th>№ п/п</th>
							<th class="w-1 text-center">
								<input type="checkbox" autocomplete="off" class="form-check-input" name="checkall-toggle" value="" title="<?php echo Text::_('JGLOBAL_CHECK_ALL'); ?>" onclick="Joomla.checkAll(this)" />
							</th>

							<?php if (isset($this->items[0]->ordering)) : ?>
								<th scope="col" class="w-1 text-center">

									<?php echo HTMLHelper::_('searchtools.sort', '', 'a.ordering', $listDirn, $listOrder, null, 'asc', 'JGRID_HEADING_ORDERING', 'icon-menu-2'); ?>

								</th>
							<?php endif; ?>


							<th scope="col" class="w-1 text-center">
								<?php echo HTMLHelper::_('searchtools.sort', 'JSTATUS', 'a.state', $listDirn, $listOrder); ?>
							</th>
							<th scope="col">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_DOCSTORE_DOC_LABEL_NAME', 'a.name', $listDirn, $listOrder); ?>
							</th>
							<th scope="col w-10">
								Файл
							</th><th scope="col w-10">
								Текст
							</th>
							<th scope="col" class="w-10">
								<?php echo HTMLHelper::_('searchtools.sort', 'COM_DOCSTORE_CATEGORY_LABEL_NAME', 'category_name', $listDirn, $listOrder); ?>
							</th>


							<th scope="col" class="w-1">

								<?php echo HTMLHelper::_('searchtools.sort', 'JGRID_HEADING_ID', 'a.id', $listDirn, $listOrder); ?>
							</th>
						</tr>
					</thead>
					<tfoot>
						<tr>
							<td colspan="<?php echo isset($this->items[0]) ? count(get_object_vars($this->items[0])) : 10; ?>">
								<?php echo $this->pagination->getListFooter(); ?>
							</td>
						</tr>
					</tfoot>
					<tbody <?php if (!empty($saveOrder)) : ?> class="js-draggable" data-url="<?php echo $saveOrderingUrl; ?>" data-direction="<?php echo strtolower($listDirn); ?>" <?php endif; ?>>
						<?php foreach ($this->items as $i => $item) :
							$ordering = ($listOrder == 'a.ordering');
							$canCreate = $user->authorise('core.create', 'com_docstore');
							$canEdit = $user->authorise('core.edit', 'com_docstore');
							$canCheckin = $user->authorise('core.manage', 'com_docstore');
							$canChange = $user->authorise('core.edit.state', 'com_docstore');
						?>

							<tr class="row<?php echo $i % 2; ?>" data-draggable-group='1' data-transition>
								<td class="w-1">
									<?php echo $this->pagination->getRowOffset($i) ?>
								</td>
							   <td class="text-center col-1 w-1">
									<?php echo HTMLHelper::_('grid.id', $i, $item->id); ?>
								</td>

								<?php if (isset($this->items[0]->ordering)) : ?>

									<td class="text-center d-none d-md-table-cell">

										<?php

										$iconClass = '';

										if (!$canChange) {
											$iconClass = ' inactive';
										} elseif (!$saveOrder) {
											$iconClass = ' inactive" title="' . Text::_('JORDERINGDISABLED');
										} ?> <span class="sortable-handler<?php echo $iconClass ?>">
											<span class="icon-ellipsis-v" aria-hidden="true"></span>
										</span>
										<?php if ($canChange && $saveOrder) : ?>
											<input type="text" name="order[]" size="5" value="<?php echo $item->ordering; ?>" class="width-20 text-area-order hidden">
										<?php endif; ?>
									</td>
								<?php endif; ?>


								<td>
									<?php echo HTMLHelper::_('jgrid.published', $item->state, $i, 'docs.', $canChange, 'cb'); ?>
								</td>


								<td>
									<?php
									if (!$editor) {
										echo HTMLhelper::link('?option=com_docstore&task=doc.edit&id=' . $item->id, $item->name);
									} else {
										echo HTMLhelper::link('#', $item->name, [
											'title' => 'Выбрать документ',
											'data-id' => $item->id,
											'onClick' => 'docselect(this)'
										]);
									}

									?>

								</td>
								<td>
									<?php
									$img = "<img width='32' src='" . Uri::root() . "media/com_docstore/css/{$item->ext}.svg' alt='$item->ext' title='Открыть файл'> ";
									
									echo HTMLhelper::link(Uri::root() . '?option=com_docstore&view=doc&format=raw&id=' . $item->id, $img, ['target' => '_blank']);
									
									echo $item->file_size?Number::bytes($item->file_size):''; ?>
								</td>
								<td class="text-center">
									<?php echo $item->text_exists==1?'<i class="icon-publish success"/>':''; ?>
								</td>
								<td>
									<?php echo HTMLHelper::_('link',"index.php?option=com_docstore&view=docs&filter[catid]={$item->catid}", $item->category_name,['class'=>'filter']) ; ?>
								</td>

								<td>
									<?php echo $item->id; ?>

								</td>


							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>

				<input type="hidden" name="task" value="" />
				<input type="hidden" name="boxchecked" value="0" />
				<input type="hidden" name="list[fullorder]" value="<?php echo $listOrder; ?> <?php echo $listDirn; ?>" />
				<?php echo HTMLHelper::_('form.token'); ?>
			</div>
		</div>
	</div>
</form>