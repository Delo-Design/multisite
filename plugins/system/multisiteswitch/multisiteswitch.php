<?php defined('_JEXEC') or die;


use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;


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
	public static $domain;


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
	public static $defaultMenu;


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
		$app   = $this->app;
		$admin = $app->isClient('administrator');

		if ($admin)
		{
			return false;
		}

		$config               = Factory::getConfig();
		$https                = (int) $config->get('force_ssl', 0) === 2 ? 'https://' : 'http://';
		$domain               = $_SERVER['SERVER_NAME'];
		$domainSplit          = explode('.', $domain);
		$subDomains           = $this->params->get('subdomains', []);
		$defaultSubDomain     = false;
		self::$listSubdomains = $subDomains;
		self::$sourceURI      = $_SERVER['REQUEST_URI'];


		//ищем дефолтный субдомен
		foreach ($subDomains as $subDomain)
		{
			if ((int) $subDomain->default)
			{
				$defaultSubDomain = $subDomain;
			}
		}


		if (count($domainSplit) === 3)
		{
			$subDomainFromUrl = array_shift($domainSplit);

			if ($defaultSubDomain)
			{
				if ($defaultSubDomain->subdomain === $subDomainFromUrl)
				{
					$app->redirect($https . implode('.', $domainSplit), 301);
				}
			}

		}
		else
		{
			if ($defaultSubDomain)
			{
				$subDomainFromUrl = $defaultSubDomain->subdomain;
			}
		}

		self::$subDomain = $subDomainFromUrl;

		if ((int) $defaultSubDomain->www && $subDomainFromUrl === 'www')
		{
			self::$subDomain = $defaultSubDomain->subdomain;
		}


		foreach ($subDomains as $subDomain)
		{
			$signSub   = $subDomain->subdomain === self::$subDomain;
			$signEmpty = empty(self::$subDomain) && (int) $subDomain->default;

			if ($signSub || $signEmpty)
			{
				self::$defaultMenu     = $subDomain->menu;
				self::$defaultMenuItem = $subDomain->menuitem;
				self::$activeItem      = $subDomain;
				break;
			}
		}

		//вызов триггера
		PluginHelper::importPlugin('multisite');
		$this->app->triggerEvent('onMultisiteAfterInit', [
			&self::$subDomain,
			&self::$defaultMenu,
			&self::$defaultMenuItem,
			&self::$activeItem,
			&self::$sourceURI
		]);

		$this->loadFilesFromMenu();

		if (self::$sourceURI === '/')
		{
			$menu = $this->app->getMenu();
			$menu->setDefault(self::$defaultMenuItem);
		}

	}


	public function onZnatokRedirectPrepare($params, &$redirect, &$current)
	{
		$admin = $this->app->isClient('administrator');
		//$customizer = !empty($this->app->input->get('customizer'));
		$customizer = false;

		if ($admin || $customizer)
		{
			return false;
		}

		$subDomains = $this->params->get('subdomains', []);

		foreach ($subDomains as $subDomain)
		{
			$current = str_replace('/' . $subDomain->subdomain, '', $current);
		}

	}


	public function onAfterRoute()
	{
		$admin = $this->app->isClient('administrator');

		if ($admin)
		{
			return false;
		}

		$this->app->triggerEvent('onMultisiteAfterRoute', [
			&self::$subDomain,
			&self::$defaultMenu,
			&self::$defaultMenuItem,
			&self::$activeItem,
			&self::$sourceURI
		]);

		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('manifest_cache');
		$query->from('#__extensions');
		$query->where($db->qn('folder') . ' = ' . $db->q('system'));
		$query->where($db->qn('element') . ' = ' . $db->q('yootheme'));
		$element = $db->setQuery($query)->loadObject();

		if (empty($element->manifest_cache))
		{
			return false;
		}

		$params  = new Registry($element->manifest_cache);
		$version = $params->get('version');

		if (version_compare((string) $version, '2.0', '>='))
		{
			JLoader::register('Yootheme2xHelper', JPATH_PLUGINS . '/system/multisiteswitch/helpers/positions/yootheme2x.php');
			Yootheme2xHelper::set(self::$defaultMenu);
		}
		else
		{
			JLoader::register('Yootheme1xHelper', JPATH_PLUGINS . '/system/multisiteswitch/helpers/positions/yootheme1x.php');
			Yootheme1xHelper::set(self::$defaultMenu);
		}

	}


	public function onAfterRender()
	{
		$admin      = $this->app->isClient('administrator');
		$customizer = !empty($this->app->input->get('customizer'));

		if ($admin || $customizer)
		{
			return false;
		}

		$domain = $_SERVER['SERVER_NAME'];

		$body       = $this->app->getBody();
		$subDomains = $this->params->get('subdomains', []);


		foreach ($subDomains as $subDomain)
		{

			if ($subDomain->subdomain !== self::$subDomain)
			{
				continue;
			}

			$body = preg_replace_callback("#(\/?\[s\])?(https?:\/\/)?(" . $domain . ")?(\/[a-zA-Z0-9\-\_]+?\/)?(" . $subDomain->subdomain . "\/?)(.)#i", static function ($matches) use ($subDomain, $domain) {

				$sep = substr_count($matches[5], '/') ? '/' : '';

				$matches[1] = str_replace('/', '', $matches[1]);

				if ($matches[1] === '[s]')
				{
					return str_replace('[s]', '', $matches[0]);
				}

				$dic = [];

				$check = empty($matches[3]) || strpos($matches[3], $domain) !== false;

				if(!empty($matches[4]) && $matches[4] !== $domain . '/')
				{
					$check = false;
				}

				if ($check)
				{
					if ((int) $subDomain->default)
					{
						$dic = ['.', '/', '"', '?'];
					}
					else
					{
						$dic = ['/', '"', '?'];
					}

					if (in_array($matches[6], $dic) || $sep === '/')
					{
						$matches[5] = '';
					}
				}


				$link = str_replace('//', '/', $matches[3] . $matches[4] . $matches[5] . $matches[6]);

				return $matches[2] . $link;

			}, $body);
		}

		$this->app->setBody($body);
	}


	private function loadFilesFromMenu()
	{
		$url = preg_replace('#\?.*$#i', '', self::$sourceURI);

		$query     = $this->db->getQuery(true)
			->select($this->db->quoteName(['alias', 'params']))
			->from('#__menu')
			->where('id =' . (int) self::$defaultMenuItem);
		$extension = $this->db->setQuery($query)->loadObject();
		$params    = new Registry($extension->params);
		$document  = Factory::getDocument();
		$files     = $params->get('files', []);
		$metas     = $params->get('metas', []);

		$sitemapDefault                    = new stdClass();
		$sitemapDefault->type              = 'file';
		$sitemapDefault->url               = 'sitemap.xml';
		$sitemapDefault->headercontenttype = 'text/xml';
		$sitemapDefault->file              = '/sitemaps/' . self::$subDomain . '.xml';

		$files   = (array) $files;
		$files[] = $sitemapDefault;

		foreach ($files as $file)
		{

			if ($url === ('/' . $file->url))
			{

				if ($file->type === 'text')
				{
					if (!empty($file->headercontenttype))
					{
						header("Content-Type: " . $file->headercontenttype);
					}

					echo $file->text;
					$this->app->close();
				}

				if ($file->type === 'file')
				{

					$path = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, JPATH_ROOT . $file->file);

					if (file_exists($path))
					{
						if (!empty($file->headercontenttype))
						{
							header("Content-Type: " . $file->headercontenttype);
						}
						echo file_get_contents($path);
					}
					else
					{
						throw new Exception(Text::_('JERROR_LAYOUT_PAGE_NOT_FOUND'), 404);
					}

					$this->app->close();
				}
			}

		}


		if (method_exists($document, 'addCustomTag'))
		{
			foreach ($metas as $meta)
			{
				$document->addCustomTag('<meta name="' . $meta->name . '" content="' . $meta->content . '">');
			}
		}

	}

}