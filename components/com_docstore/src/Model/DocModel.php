<?php
namespace Softarius\Component\Docstore\Site\Model;
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Model\BaseDatabaseModel;

class DocModel extends BaseDatabaseModel
{
    public function getItem($id)
    {
        $query = $this->getDbo()->getQuery(true);
        // Документ
        $query
            ->select("*")
            ->from("#__docstore_doc as d")
            ->where('publish_up<=current_timestamp')
            ->where('state>0')
			->where('(publish_down>=current_timestamp or publish_down is null)')

            ->where("d.id = $id");
      

        $this->getDbo()->setQuery($query);
        return $this->getDbo()->loadObject();
    }

    
    public function getFile($id)
    {
        $query = $this->getDbo()->getQuery(true);
        // Документ
        $query
            ->select("chunk")
            ->from("#__docstore_chunk as c")
            ->where("c.doc_id = $id")
            ->order('ord');
        $this->getDbo()->setQuery($query);
       
        return $this->getDbo()->loadObjectList();
    }

    



   

  

   

   

    public function getDecisions($inn)
    {

        $sql = "SELECT p.id, p.date_ as date, d.name, d.id as did  FROM `#__sro_pi3` p
			join #__sro_decision as d on d.id=p.decision_id
			WHERE firm_inn='$inn'
			order by p.date_, decision_id";
        $this->_db->setQuery($sql);
        return $this->_db->loadObjectList();
    }

}
