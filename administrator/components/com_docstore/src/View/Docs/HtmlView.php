<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Docstore
 * @author     yushinin <yushinins@mail.ru>
 * @copyright  2023 yushinin
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Softarius\Component\Docstore\Administrator\View\Docs;

// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\MVC\View\ListView;
use \Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;

/**
 * View class for a list of Docs.
 *
 * @since  1.0.0
 */
class HtmlView extends ListView {

	var $option = 'COM_DOCSTORE';

	protected function addToolBar() {

		
		$this->canDo = ContentHelper::getActions('com_docstore', 'component');
		$this->supportsBatch = false;
		ToolbarHelper::custom('doc.fulltext', 'star', '', 'Заполнить текст');

		parent::addToolbar();

		//ToolbarHelper::title(JText::_(SROHelper::$name . '_' . self::$name), self::$name);
		/*ToolbarHelper::addNew('doc.add');
			  ToolbarHelper::editList('doc.edit');
			  ToolbarHelper::deleteList('',  'docs.delete');*/
	}
}
