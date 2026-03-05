<?php
namespace Softarius\Component\Docstore\Site\Model;

use Softarius\Component\Docstore\Site\Helper\DocHelper;

defined('_JEXEC') or die('Restricted access');



use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;



class DocsModel extends \Joomla\CMS\MVC\Model\ListModel
{

	public function __construct($config = array())
	{
		if (empty($config['filter_fields'])) {
			$config['filter_fields'] = array(
				'state', 'f.name'

			);
		}
		$this->db = Factory::getDBO();
		parent::__construct($config);
	}


	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	public function getListQuery()
	{
		return DocHelper::getQueryFiles(DocHelper::getCategory()->id);
	}

	function getCategory($cat=null)
	{
		return DocHelper::getCategory($cat);
	}
}
