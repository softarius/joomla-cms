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
use \Joomla\CMS\Uri\Uri;
use \Joomla\CMS\Router\Route;
use \Joomla\CMS\Language\Text;

$wa = $this->document->getWebAssetManager();
$wa->useScript('keepalive')
	->useScript('form.validate');
HTMLHelper::_('bootstrap.tooltip');
?>

<form action="<?php echo Route::_('index.php?option=com_docstore&layout=edit&id=' . (int) $this->item->id); ?>"
	method="post" enctype="multipart/form-data" name="adminForm" id="doc-form" class="form-validate form-horizontal">

	<?php echo HTMLHelper::_('uitab.startTabSet', 'myTab', array('active' => 'default')); ?>
	<?php
	foreach ($this->form->getFieldsets() as $fieldset) {
		echo HTMLHelper::_('uitab.addTab', 'myTab', $fieldset->name, Text::_($fieldset->label, true));
		echo $this->form->renderFieldset($fieldset->name);
		echo HTMLHelper::_('uitab.endTab');
	}
	?>

	<?php echo HTMLHelper::_('uitab.endTabSet'); ?>




	<input type="hidden" name="task" value="" />
	<?php echo HTMLHelper::_('form.token'); ?>

</form>