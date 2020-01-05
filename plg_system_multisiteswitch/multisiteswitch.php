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
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

//TODO http(s) выдирать из джумлы
/**
 * Multisiteswitch plugin.
 *
 * @package   multisiteswitch
 * @since     1.0.0
 */
class plgSystemMultisiteswitch extends CMSPlugin
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
	 * @var   string
	 * @since version
	 */
	public static $sourceURI;

	/**
	 * @var   string
	 * @since version
	 */
	public static $activeItem;

	/**
	 * @var
	 * @since version
	 */
	public static $listSubdomains;

	/**
	 * @var   string
	 * @since version
	 */
	public static $subDomain;


	/**
	 * @var
	 * @since version
	 */
	public static $defaultMenuItem;

	/**
	 *
	 * @return bool
	 *
	 * @throws Exception
	 * @since version
	 */
	public function onAfterInitialise()
	{
		$app = $this->app;
		$admin = $app->isClient('administrator');

		if($admin)
		{
			return false;
		}

		$redirectDomain = $app->input->get('redirectDomain', '');
		$domain = $_SERVER['SERVER_NAME'];
		$domainSplit = explode('.', $domain);
		$subDomains = $this->params->get('subdomains', []);
		$defaultSubDomain = false;
		self::$listSubdomains = $subDomains;

		if(!empty($redirectDomain))
		{
			$app->redirect('http://' . $redirectDomain . '.uk0.ru', 301);
		}

		//ищем дефолтный субдомен
		foreach ($subDomains as $subDomain)
		{
			if((int)$subDomain->default)
			{
				$defaultSubDomain = $subDomain;
			}
		}

		if(count($domainSplit) === 3) {
			$subDomainFromUrl = array_shift($domainSplit);

			if($defaultSubDomain)
			{
				if($defaultSubDomain->subdomain === $subDomainFromUrl)
				{
					$app->redirect('http://' . implode('.', $domainSplit), 301);
				}
			}

		}
		else
		{
			if($defaultSubDomain)
			{
				$subDomainFromUrl = $defaultSubDomain->subdomain;
			}
		}

		self::$subDomain = $subDomainFromUrl;

		if(substr_count($_SERVER['REQUEST_URI'], 'index.php') === 0)
		{
			self::$sourceURI = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI'] = '/' . self::$subDomain . $_SERVER['REQUEST_URI'];
		}
		else
		{
			self::$sourceURI = $_SERVER['REQUEST_URI'];
			$_SERVER['REQUEST_URI'] = '/' . self::$subDomain . str_replace('index.php', '', $_SERVER['REQUEST_URI']);
		}

		foreach ($subDomains as $subDomain)
		{
			$signSub = $subDomain->subdomain === self::$subDomain;
			$signEmpty = empty(self::$subDomain) && (int)$subDomain->default;

			if($signSub || $signEmpty)
			{
				self::$defaultMenuItem = $subDomain->menuitem;
				self::$activeItem = $subDomain;
				break;
			}
		}

		$this->loadFilesFromMenu();

	}

	public function onAfterRoute()
	{
		$admin = $this->app->isClient('administrator');

		if($admin)
		{
			return false;
		}

		$menu = $this->app->getMenu();
		$menu->setDefault(self::$defaultMenuItem);
		$theme = JHtml::_('theme');
		$theme->set('menu.positions.navbar', self::$subDomain);
		$theme->set('menu.positions.mobile', self::$subDomain);
	}

	public function onAfterRender()
	{
		$admin = $this->app->isClient('administrator');

		if($admin)
		{
			return false;
		}

		//TODO переписать на регулярное выражение
		$body = $this->app->getBody();
		$subDomains = $this->params->get('subdomains', []);
		foreach ($subDomains as $subDomain)
		{
			$body = preg_replace_callback('#(http\:\/\/.*?)?(\/' . $subDomain->subdomain . '\/?)(.)#i', function ($matches) {
				//проверяем не домен ли режем, нам надо резать только куда запрос идет
				if($matches[3] !== '.')
				{
					//добавленный срезанный символ
					return '/' . $matches[3];
				}
			}, $body);
		}

		$body = preg_replace_callback('#(http\:\/\/)?\/(uk0.ru)#i', function ($matches) {
			return '//' . self::$subDomain . '.' . $matches[2];
		}, $body);

		$this->app->setBody($body);
	}

	private function loadFilesFromMenu()
	{
		$url = preg_replace('#\?.*$#i','', self::$sourceURI);

		$query = $this->db->getQuery(true)
			->select($this->db->quoteName(['alias', 'params']))
			->from('#__menu')
			->where( 'id =' . (int)self::$defaultMenuItem);
		$extension = $this->db->setQuery( $query )->loadObject();
		$params = new Registry($extension->params);
		$document = Factory::getDocument();
		$files = $params->get('files', []);
		$metas = $params->get('metas', []);

		foreach ($files as $file)
		{
			if($url === ('/' . $file->url))
			{
				if(!empty($file->headercontenttype))
				{
					header("Content-Type: " . $file->headercontenttype);
				}

				echo $file->text;
				$this->app->close();
			}
		}

		if(method_exists($document, 'addCustomTag'))
		{
			foreach ($metas as $meta)
			{
				$document->addCustomTag('<meta name="' . $meta->name . '" content="' . $meta->content . '">');
			}

		}

	}


}
