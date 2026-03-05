<?php

/**
 * @version    CVS: 1.0.0
 * @package    Com_Docstore
 * @author     yushinin <yushinins@mail.ru>
 * @copyright  2023 yushinin
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Softarius\Component\Docstore\Administrator\Model;

require_once(JPATH_COMPONENT_ADMINISTRATOR . '/vendor/autoload.php');


// No direct access.
defined('_JEXEC') or die;

use \Joomla\CMS\Table\Table;
use \Joomla\CMS\Factory;
use \Joomla\CMS\Language\Text;
use \Joomla\CMS\Plugin\PluginHelper;
use \Joomla\CMS\MVC\Model\AdminModel;
use \Joomla\CMS\Helper\TagsHelper;
use \Joomla\CMS\Filter\OutputFilter;
use Softarius\Component\Docstore\Site\Helper\DocHelper;

/**
 * Doc model.
 *
 * @since  1.0.0
 */
class DocModel extends AdminModel
{
	/**
	 * @var    string  The prefix to use with controller messages.
	 *
	 * @since  1.0.0
	 */
	protected $text_prefix = 'COM_DOCSTORE';

	/**
	 * @var    string  Alias to manage history control
	 *
	 * @since  1.0.0
	 */
	public $typeAlias = 'com_docstore.doc';

	/**
	 * @var    null  Item data
	 *
	 * @since  1.0.0
	 */
	protected $item = null;

	/**
	 * Содержимое документа по его номеру
	 */
	public function getFile($id)
	{
		return DocHelper::getFile($id);
	}

	public function getPDFDocAsText($id)
	{
		$content = $this->getFile($id);
		return $this->getPDFAsText($content);
	}

	public function getPDFAsText($content)
	{
		$parser = new \Smalot\PdfParser\Parser();
		$pdfo = $parser->parseContent($content);
		$text = $pdfo->getText();
		$t = explode(chr(10), $text);
		// убираем повторы пробелов
		foreach ($t as &$l) {
			$l = trim(preg_replace('!\s+!', ' ', $l));
		}
		return implode("\n", $t);
	}

	/**
	 * Returns a reference to the a Table object, always creating it.
	 *
	 * @param   string  $type    The table type to instantiate
	 * @param   string  $prefix  A prefix for the table class name. Optional.
	 * @param   array   $config  Configuration array for model. Optional.
	 *
	 * @return  Table    A database object
	 *
	 * @since   1.0.0
	 */
	public function getTable($type = 'Doc', $prefix = 'Administrator', $config = array())
	{
		return parent::getTable($type, $prefix, $config);
	}

	public function LoImport($filename)
	{
		$config = Factory::getConfig();

		$dst = $config->get('tmp_path') . '/file.bin';
		copy($filename, $dst);
		$dst = addslashes($dst);
		$db = $this->getDbo();
		$sql = "SELECT lo_import('$dst')";
		$db->setQuery($sql);
		return $db->loadColumn(0)[0];
	}

	public function save($data)
	{


		$db = $this->getDbo();

		$jinput = Factory::getApplication()->input;
		$files = $jinput->files->get('jform');
		$data = array_filter($data);

		/* файл является ссылкой */
		if ($data['srcurl']) {
			 
			$data['ext'] = end(explode(".", $data['srcurl'])); // Определяем расширение
			if (!$data['file_size'] || !$data['fulltext']) {
				$curl = curl_init($data['srcurl']);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
				curl_setopt($curl, CURLOPT_PROXY, '192.168.0.1:3128');
				$output = curl_exec($curl);
				curl_close($curl);
				Factory::getApplication()->enqueueMessage("Успешно сохранена ссылка на документ <a target='_blank' href='{$data['srcurl']}'>{$data['srcurl']}</a>");
				$data['fulltext'] = $this->getPDFAsText($output);
				$data['file_size'] = mb_strlen($output, '8bit');
			}
		}

		$pdf = null;

		if ($files && key_exists('content', $files))
			$pdf = $files['content'];



		if ($pdf && $pdf['size'] > 0) {
			$fninfo = pathinfo($pdf['name']);
			$data['file_size'] = $pdf['size'];
			$data['md5'] = md5_file($pdf['tmp_name']);
			$data['ext'] = $fninfo['extension'];

			// Извлекаем текстовое содержимое PDF
			/*	if (!key_exists('fulldata', $data) || (!$data['fulltext'] && $data['ext'] == 'pdf')) {
				$content = file_get_contents($pdf['tmp_name']);
				$data['fulltext'] = $this->getPDFAsText($content);
			}
*/
			$data['name'] = $data['name'] ? $data['name'] : $fninfo['filename'];
			if ($db->getServerType() == 'postgresql') {
				$data['filecontent'] = $this->LoImport($pdf["tmp_name"]);
			}

			$sr = parent::save($data);
			if ($db->getServerType() != 'postgresql') {
				if (!$data['id']) {
					$db->setQuery('select max(id) as n from #__docstore_doc');
					$r = $db->loadObject();
					$data['id'] = (int) $r->n;
				}

				$handle = fopen($pdf['tmp_name'], "rb");
				$n = 0;
				$ch = new \stdClass();
				$ch->doc_id = $data['id'];
				$db->setQuery("delete from #__docstore_chunk where doc_id={$data['id']}");
				$db->execute();
				while (!feof($handle)) {
					$ch->chunk = addslashes(fread($handle, 16777215)); // BLOB MySQL
					$ch->ord = $n;
					$sql = 'insert into #__docstore_chunk values (' .
						$data['id'] . ', ' . $n . ', "' . $ch->chunk . '")';

					print_r($ch);

					$db->setQuery($sql);
					$db->execute();
					$n++;
				}
			}

			Factory::getApplication()->enqueueMessage('Успешно сохранено содержимое файла ' . $data['md5']);
			return $sr;
		} else
			return parent::save($data);
	}

	/**
	 * Method to get the record form.
	 *
	 * @param   array    $data      An optional array of data for the form to interogate.
	 * @param   boolean  $loadData  True if the form is to load its own data (default case), false if not.
	 *
	 * @return  \JForm|boolean  A \JForm object on success, false on failure
	 *
	 * @since   1.0.0
	 */
	public function getForm($data = array(), $loadData = true)
	{
		// Initialise variables.
		$app = Factory::getApplication();

		// Get the form.
		$form = $this->loadForm(
			'com_docstore.doc',
			'doc',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);



		if (empty($form)) {
			return false;
		}

		return $form;
	}



	/**
	 * Method to get the data that should be injected in the form.
	 *
	 * @return  mixed  The data for the form.
	 *
	 * @since   1.0.0
	 */
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$data = Factory::getApplication()->getUserState('com_docstore.edit.doc.data', array());

		if (empty($data)) {
			if ($this->item === null) {
				$this->item = $this->getItem();
			}

			$data = $this->item;
		}

		return $data;
	}

	/**
	 * Method to get a single record.
	 *
	 * @param   integer  $pk  The id of the primary key.
	 *
	 * @return  mixed    Object on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public function getItem($pk = null)
	{

		if ($item = parent::getItem($pk)) {
			if (isset($item->params)) {
				$item->params = json_encode($item->params);
			}

			// Do any procesing on fields here if needed
		}

		return $item;
	}

	/**
	 * Method to duplicate an Doc
	 *
	 * @param   array  &$pks  An array of primary key IDs.
	 *
	 * @return  boolean  True if successful.
	 *
	 * @throws  Exception
	 */
	public function duplicate(&$pks)
	{
		$app = Factory::getApplication();
		$user = $app->getIdentity();

		// Access checks.
		if (!$user->authorise('core.create', 'com_docstore')) {
			throw new \Exception(Text::_('JERROR_CORE_CREATE_NOT_PERMITTED'));
		}

		$context = $this->option . '.' . $this->name;

		// Include the plugins for the save events.
		PluginHelper::importPlugin($this->events_map['save']);

		$table = $this->getTable();

		foreach ($pks as $pk) {

			if ($table->load($pk, true)) {
				// Reset the id to create a new record.
				$table->id = 0;

				if (!$table->check()) {
					throw new \Exception($table->getError());
				}


				// Trigger the before save event.
				$result = $app->triggerEvent($this->event_before_save, array($context, &$table, true, $table));

				if (in_array(false, $result, true) || !$table->store()) {
					throw new \Exception($table->getError());
				}

				// Trigger the after save event.
				$app->triggerEvent($this->event_after_save, array($context, &$table, true));
			} else {
				throw new \Exception($table->getError());
			}
		}

		// Clean cache
		$this->cleanCache();

		return true;
	}

	/**
	 * Prepare and sanitise the table prior to saving.
	 *
	 * @param   Table  $table  Table Object
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 */
	protected function prepareTable($table)
	{
		jimport('joomla.filter.output');

		if (empty($table->id)) {
			// Set ordering to the last item if not set
			if (@$table->ordering === '') {
				$db = $this->getDbo();
				$db->setQuery('SELECT MAX(ordering) FROM #__docstore_doc');
				$max = $db->loadResult();
				$table->ordering = $max + 1;
			}
		}
	}
}
