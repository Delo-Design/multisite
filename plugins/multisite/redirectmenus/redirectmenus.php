<?php defined('_JEXEC') or die;

/**
 * @package    multisiteswitch
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;


/**
 * Multisiteswitch plugin.
 *
 * @package   multisiteswitch
 * @since     1.0.0
 */
class plgMultisiteRedirectmenus extends CMSPlugin
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


	public function onAfterMultisite(&$subDomain, &$defaultMenuItem, &$activeItem, &$sourceURI)
	{
		if (substr_count($_SERVER['REQUEST_URI'], 'index.php') === 0)
		{
			$sourceURI              = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI'] = '/' . $subDomain . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$sourceURI              = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI'] = '/' . $subDomain . str_replace('index.php', '', $_SERVER['REQUEST_URI']);
		}
	}

}