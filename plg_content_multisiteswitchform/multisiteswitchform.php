<?php
/**
 * @package    multisiteswitch
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;

defined('_JEXEC') or die;

/**
 * Multisiteswitchform plugin.
 *
 * @package   multisiteswitch
 * @since     1.0.0
 */
class plgContentMultisiteswitchform extends CMSPlugin
{
	/**
	 * Application object
	 *
	 * @var    CMSApplication
	 * @since  1.0.0
	 */
	protected $app;

	/**
	 * Database object
	 *
	 * @var    DatabaseDriver
	 * @since  1.0.0
	 */
	protected $db;

	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Adds addition meta title
	 *
	 * @param  JForm $form The form to be altered.
	 * @param  mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since   1.0
	 */
	function onContentPrepareForm($form, $data)
	{
		$app = Factory::getApplication();
		$component = $app->input->get('option');
		$layout = $app->input->get('layout');
		if ($app->isClient('administrator') && $component === 'com_menus' && $layout === 'edit')
		{
			Form::addFormPath(__DIR__);
			$form->loadFile('contentmultiswitch', false);
		}

		return true;
	}

}
