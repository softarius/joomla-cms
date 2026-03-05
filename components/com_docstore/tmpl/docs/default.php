<?php
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use Joomla\Component\Finder\Administrator\Indexer\Parser\Html;

$doc = Factory::getDocument();

$doctitle = '';
$p = $this->category;
while ($parent = $p->getParent()) {
	if ($parent->id != 'root') {
		$doctitle = $parent->title . ' / ' . $doctitle;
	}
	$p = $parent;
}
$doctitle = $doctitle . $this->category->title;

$siteName = Factory::getConfig()->get('sitename');
$doc->setTitle($siteName . ' - ' . $doctitle);
$doc->setGenerator($siteName);
if ($this->category->description)
	$doc->setDescription(strip_tags($this->category->description));

?>
<h3>

	<img width=32 src="<?php echo Uri::root() . 'media/com_docstore/css/folder.svg' ?>">
	<?php echo $this->category->title; ?>
</h3>
<h5>
	<?php echo $this->category->description; ?>
</h5>



<?php echo $this->loadTemplate('cats') ?>


<?php if ($this->items): ?>

	<form action="<?php echo Route::_("index.php?option=com_docstore&view=docs&cat={$this->category->id}"); ?>"
		method="post" name="adminForm" id="adminForm">
		<input name='ss' type='hidden' value="123">
		<p>
			<?php
			echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this));
			echo $this->pagination->getResultsCounter(); ?>
		</p>
		<table class="table table-striped table-hover">

			<thead>
				<tr>
					<th class='col-1'>№ п/п</th>
					<th>Название</th>
					<?php if ($show_version) echo '<th>Версия (номер)</th>'; ?>

					<?php if ($show_docdate): ?>
						<th>Дата</th>
					<?php endif; ?>
					<th class='col-2'>Опубликовано</th>
				</tr>

			</thead>
			<tbody>
				<?php
				function td($n)
				{

					$amount = $n > 0 ?
						number_format((float) $n, 2, Text::_('DECIMALS_SEPARATOR'), '&nbsp;') : '';
					return "<td style='text-align:right'>$amount</td>";
				}
				$cy = 0;
				foreach ($this->items as $i => $item) {

					echo '<tr>';
					if (!$item->ext && property_exists($item, 'srcurl') && $item->srcurl)
						$item->ext = pathinfo($item->srcurl, PATHINFO_EXTENSION);
					$target = (in_array($item->ext, ['doc', 'docx', 'pdf', 'xls', 'xlsx'])) ? 'blank_' : '';
					echo '<td class="col-1">', $this->pagination->getRowOffset($i), '</td>';

					echo '<td>',
					"<img width='32' src='" . Uri::root() . "media/com_docstore/css/{$item->ext}.svg' alt='$item->ext' title='$item->ext'> ",

					HTMLHelper::_('link', "?option=com_docstore&view=doc&format=raw&id=" . $item->id, $item->name, ['target' => $target]), '</td>';
					if ($show_version)
						echo '<td>', $item->version, '</td>';
					if ($show_docdate) echo '<td>', $item->docdate ? HTMLHelper::_('date', $item->docdate, 'd.m.Y') : '', '</td>';

					echo '<td>', HTMLHelper::_('date', $item->publish_up, 'H:i d.m.Y'), '</td>';
					echo "</tr>";
				}

				?>
			</tbody>
		</table>

		<?php
		$this->pagination->setAdditionalURLParam('cat', $this->category->id);
		echo $this->pagination->getListFooter(); ?>
	</form>
<?php endif; ?>