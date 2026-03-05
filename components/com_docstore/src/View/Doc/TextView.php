<?php
namespace Softarius\Component\Docstore\Site\View\Doc;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Softarius\Component\Docstore\Site\Helper\DocHelper;
use Softarius\Component\Docstore\Site\Helper\MimeHelper;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\AbstractView;

class TextView extends AbstractView
{
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$id = $input->getInt('id');
		$model = $this->getModel();
		$doc = $model->getItem($id);
		if ($doc) {

			$app->setHeader('Content-type', 'text/plain', true);
			$disp = $input->getCmd('disp', MimeHelper::getDisposition('txt'));

			$fn = $doc->name . '.' . $doc->ext;
			$app->setHeader('Content-Disposition', "$disp; filename=\"$fn\"");
			$app->sendHeaders();
			echo $doc->fulltext;
		} else throw new \Exception( Text::_( 'COM_DOCSTORE_DOC_NOTFOUND' ), 404 );

	}

}