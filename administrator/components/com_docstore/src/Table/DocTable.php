<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Docstore
 * @author     yushinin <yushinins@mail.ru>
 * @copyright  2023 yushinin
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Softarius\Component\Docstore\Administrator\Table;
// No direct access
defined('_JEXEC') or die;

use \Joomla\CMS\Table\Table as Table;
use \Joomla\Database\DatabaseDriver;


class DocTable extends Table 
{


    protected $_supportNullValue = true;

	
	
	public function __construct(DatabaseDriver $db)
	{
		//$this->typeAlias = 'com_docstore.doc';
		parent::__construct('#__docstore_doc', 'id', $db);
		//$this->setColumnAlias('published', 'state');
		
	}

}