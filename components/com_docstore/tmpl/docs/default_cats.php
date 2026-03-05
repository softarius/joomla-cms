<table class='table table-hover table-sm'>
	<?php foreach ($this->category->getChildren(false) as $subc): ?>

		<tr>
			<td class="col-4">
				<?php echo '<img width=24 src="' . JUri::root() . 'media/com_docstore/css/folder.svg"> ',
					Jhtml::_('link', "?option=com_docstore&view=docs&cat=" . $subc->id, $subc->title); ?>
			</td>
			<td>
				<?= $subc->description ?>

			</td>
		</tr>
	<?php endforeach; ?>
</table>