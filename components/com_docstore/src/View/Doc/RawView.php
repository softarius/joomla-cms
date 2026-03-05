<?php

namespace Softarius\Component\Docstore\Site\View\Doc;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Softarius\Component\Docstore\Site\Helper\DocHelper;
use Softarius\Component\Docstore\Site\Helper\MimeHelper;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\View\AbstractView;

class RawView extends AbstractView
{
	public function display($tpl = null)
	{
		$app = Factory::getApplication();
		$input = $app->getInput();
		$id = $input->getInt('id');
		$model = $this->getModel();
		$doc = $model->getItem($id);


		if ($doc && !$doc->srcurl) {
			$disp = $input->getCmd('disp', MimeHelper::getDisposition($doc->ext));
			$app->setHeader('Content-type', MimeHelper::getContentType($doc->ext), true);

			$fn = $doc->name . '.' . $doc->ext;
			$app->setHeader('Content-Disposition', "$disp; filename=\"$fn\"");
			$app->sendHeaders();
			echo DocHelper::getFile($id);
		} else {
			if ($doc->srcurl) {
				header("Location: $doc->srcurl");
				exit;
				/*$content=file_get_contents($doc->srcurl);
			$fn = $doc->name . '.' . $doc->ext;
			$app->setHeader('Content-Disposition', "$disp; filename=\"$fn\"");
			$app->sendHeaders();
			echo $content;*/
			} else
				throw new \Exception(Text::_('COM_DOCSTORE_DOC_NOTFOUND'), 404);
		}
	}
}
