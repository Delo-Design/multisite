<?php defined('_JEXEC') or die;

/**
 * @package    multisiteswitch
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;

JLoader::register('ConfigHelper', JPATH_PLUGINS . '/system/multisiteswitch/helpers/ConfigHelper.php');

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


	public function onMultisiteAfterInit(&$subDomain, &$defaultMenu, &$defaultMenuItem, &$activeItem, &$sourceURI)
	{

		if (strpos($sourceURI, '/component') === 0)
		{
			return;
		}

		$subDomains = $this->params->get('subdomains', []);

		foreach ($subDomains as $subDomain_current)
		{
			if (
				$subDomain_current->subdomain === $subDomain &&
				!(int) $subDomain_current->enable
			)
			{
				return;
			}
		}

		if (substr_count($_SERVER['REQUEST_URI'], 'index.php') === 0)
		{
			$_SERVER['REQUEST_URI'] = '/' . $subDomain . $_SERVER['REQUEST_URI'];
		}
		else
		{
			$_SERVER['REQUEST_URI'] = '/' . $subDomain . str_replace('index.php', '', $_SERVER['REQUEST_URI']);
		}


		$router = JApplicationCms::getInstance('site')->getRouter('site');
		$router->attachBuildRule(array($this, 'postprocessBuildRule'), JRouter::PROCESS_AFTER);
		$router->attachParseRule(array($this, 'postprocessParseRule'), JRouter::PROCESS_BEFORE);

	}


	public function postprocessBuildRule(&$router, &$uri)
	{
		$admin = $this->app->isClient('administrator');
		//$customizer = !empty($this->app->input->get('customizer'));
		$customizer = false;

		if ($admin || $customizer)
		{
			return false;
		}

		$subDomains = ConfigHelper::get('subdomains', []);

		foreach ($subDomains as $subDomain)
		{
			$uri->setPath(str_replace('/' . $subDomain->subdomain, '', $uri->getPath()));
		}

	}


	public function postprocessParseRule(&$router, &$uri)
	{
		$admin = $this->app->isClient('administrator');
		//$customizer = !empty($this->app->input->get('customizer'));
		$customizer = false;

		if ($admin || $customizer)
		{
			return false;
		}

		$path = preg_replace("#\?.*$#isu", '', $_SERVER['REQUEST_URI']);
		$uri->setPath(substr($path, 1));

	}


	public function onMultisiteAfterRoute(&$subDomain, &$defaultMenu, &$defaultMenuItem, &$activeItem, &$sourceURI)
	{
		$subDomains = $this->params->get('subdomains', []);

		foreach ($subDomains as $subDomain_current)
		{
			if (
				$subDomain_current->subdomain === $subDomain &&
				!(int) $subDomain_current->enable
			)
			{

				// поиск на редирект
				$split = explode('/', $sourceURI);

				if (!isset($split[1]))
				{
					return;
				}

				$subdomain_for_redirect = $split[1];

				// проверяем является ли субдоменом
				$sub_domains_all = ConfigHelper::get('subdomains', []);
				$find            = false;

				foreach ($sub_domains_all as $subdomain_current)
				{
					if ($subdomain_for_redirect === $subdomain_current->subdomain)
					{
						$find = true;
						break;
					}
				}

				if (!$find)
				{
					return;
				}

				$active = '';
				$menu   = $this->app->getMenu();
				$tmp    = $menu->getActive();

				if (!is_string($tmp))
				{
					$tmp = (array) $tmp;

					if (isset($tmp['menutype']))
					{
						$active = $tmp['menutype'];
					}

				}
				else
				{
					$active = $tmp;
				}

				if (empty($active))
				{
					return;
				}

				if ($defaultMenu === $active)
				{
					return;
				}

				$config      = Factory::getConfig();
				$https       = (int) $config->get('force_ssl', 0) === 2 ? 'https://' : 'http://';
				$domain      = $_SERVER['SERVER_NAME'];
				$domainSplit = explode('.', $domain);

				if (count($domainSplit) === 3)
				{
					array_shift($domainSplit);
				}

				array_splice($split, 0, 2);

				$this->app->redirect(
					$https . $subdomain_for_redirect . '.' . implode('.', $domainSplit) . '/' . implode('/', $split),
					301
				);

				return;
			}
		}
	}


}