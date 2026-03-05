<?php

namespace Softarius\Component\Docstore\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Categories\Categories;

defined('_JEXEC') or die;

/**
 * Работа с типами содержимого
 */
abstract class DocHelper
{
    static function getFile($id)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $config = Factory::getConfig();
        $query = $db->getQuery(true);
        if ($db->getServerType() == 'postgresql') {
            $query->select('filecontent')
                ->from('#__docstore_doc as a')
                ->where("a.id=$id");
            $db->setQuery($query);
            $oid = $db->loadObject();

            $t = $config->get('tmp_path') . '/out.bin';

            $dst = addslashes($t);
            $sql = "SELECT lo_export($oid->filecontent,'$dst')";
            $db->setQuery($sql);
            $res = $db->loadObject();
            return file_get_contents($t);
        } else {
            $query = $db->getQuery(true);
            // Документ
            $query
                ->select("chunk")
                ->from("#__docstore_chunk as c")
                ->where("c.doc_id = $id")
                ->order('ord');
            $db->setQuery($query);
            return implode('', $db->loadColumn(0));
        }
    }



    /**
     * Возвращает сведения о файле по его идентификатору
     * @param int $pk Целочисленный идентификатор файла
     */
    static function getFileInfo($pk)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query
            ->select("id, name, ext")
            ->from("#__docstore_doc as a")
            ->where('state>0')
            ->where('publish_up<=current_timestamp')
            ->where('(publish_down>=current_timestamp or publish_down is null)')
            ->where("a.id = $pk");

        $db->setQuery($query);
        $item = $db->loadObject();
        if ($item && !$item->ext && $item->srcurl)
            $item->ext = pathinfo($item->srcurl, PATHINFO_EXTENSION);
        return $item;
    }

    /**
     * Перечень файдов к категории
     */
    static function getQueryFiles($catid)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select('id, name, ext, version, docdate, publish_up, srcurl, ordering')
            ->from('#__docstore_doc d')
            ->where("catid={$catid}")
            ->where('state>0')
            ->where('publish_up<=current_timestamp')
            ->where('(publish_down>=current_timestamp or publish_down is null)')
            ->order('name ASC');
        $db->setQuery($query);

        return $query;
    }

    /**
     * Список файлов в категории
     */
    static function getFiles($catid)
    {
        $db = Factory::getContainer()->get('DatabaseDriver');
        $query = self::getQueryFiles($catid);

        $db->setQuery($query);
        $items = $db->loadObjectList();
        foreach ($items as &$file) {
            if (!$file->ext && $file->srcurl)
                $file->ext = pathinfo($file->srcurl, PATHINFO_EXTENSION);
        }
        return $items;
    }

    static function getCategory($catid = null)
    {
        $categories = Categories::getInstance("docstore");
        $app = Factory::getApplication();
        $input = Factory::getApplication()->input;
        $cat =  $input->get('cat', $catid, 'INT');
        if (!$cat) {
            $params = $app->getMenu()->getActive()->getParams();
            $cat = $params->get('catid');
        }
        $c = $categories->get($cat);

        if ($c->id == 'root') {
            $c = $c->getChildren()[0];
        }

        return $c;
    }
}
