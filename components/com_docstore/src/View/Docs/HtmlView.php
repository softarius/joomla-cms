<?php

namespace Softarius\Component\Docstore\Site\View\Docs;

use Joomla\CMS\Factory as CMSFactory;
use Joomla\CMS\Log\Log;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\ListView;

class HtmlView extends ListView
{
	var $option = 'COM_DOCSTORE';
	public function __construct(array $config)
	{
		parent::__construct($config);
		if (JDEBUG)
		Log::addLogger(array('text_file' => 'com_docstore.log.php'), Log::ALL, array('com_docstore')
		);
	}

	public function display($tpl = null)
	{

		$this->category = $this->get('Category');
		$app = CMSFactory::getApplication();
		$this->params = $app->getMenu()->getActive()->getParams();

		$doc = CMSFactory::getDocument();
		$doctitle = '';

		$pathway = $app->getPathway();


		$p = $this->category;
		$pw = [];

		while ($parent = $p->getParent()) {
			if ($parent->id != 'root') {
				$t = new \StdClass;
				$t->name = $parent->title;
				$doctitle = $parent->title . ' / ' . $doctitle;
				$t->link = "?option=com_docstore&view=docs&cat=" . $parent->id;
				$pw[] = $t;
			}
			$p = $parent;
		}
		$doctitle = $doctitle . $this->category->title;
		$doc->setTitle($doc->getTitle() . ' ' . $doctitle);
		$pathway->setPathway(array_reverse($pw));
		$pathway->addItem($this->category->title);

		Log::add($doctitle, Log::INFO, 'com_docstore');
		parent::display($tpl);
	}
}
