<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Docstore
 * @author     yushinin <yushinins@mail.ru>
 * @copyright  2023 yushinin
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Softarius\Component\Docstore\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\MVC\Controller\FormController;

/**
 * Doc controller class.
 *
 * @since  1.0.0
 */
class DocController extends FormController
{
	protected $view_list = 'docs';

	function fulltext()
	{
		$cids = $this->input->post->get('cid', array(), 'array');
		$model = $this->getModel();
		$item = [];
		foreach ($cids as $cid) {
			$item['id'] = $cid;
			$item['fulltext'] = $model->getPDFDocAsText($cid);
			$model->save($item);
		}
		$this->setMessage('Установлено текстовое содержимое документа размером '. \Joomla\CMS\HTML\Helpers\Number::bytes(strlen($item['fulltext'])) );
		$this->setRedirect('index.php?option=com_docstore&view=docs');
	
	}
}
