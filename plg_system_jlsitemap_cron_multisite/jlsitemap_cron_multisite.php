<?php
/**
 * @package    System - JLSitemap Cron Plugin
 * @version    @version@
 * @author     Joomline - joomline.ru
 * @copyright  Copyright (c) 2010 - 2019 Joomline. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://joomline.ru/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;
use Joomla\Filesystem\File;
use Joomla\Filesystem\Folder;

class PlgSystemJLSitemap_Cron_Multisite extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var boolean
	 *
	 * @since 0.0.2
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to add cron js
	 *
	 * @since 0.0.2
	 */
	public function onBeforeRender()
	{
		if ($this->params->get('client_enable') && $this->checkCacheTime())
		{
			$app  = Factory::getApplication();
			$mode = $this->params->get('client_mode', 'all');
			if ($mode === 'all' || ($mode === 'admin' && $app->isClient('administrator')) || ($mode === 'site' && $app->isClient('site')))
			{
				// Set params
				$site   = SiteApplication::getInstance('site');
				$router = $site->getRouter();
				$link   = 'index.php?option=com_ajax&plugin=jlsitemap_cron_multisite&group=system&format=json';
				$link   = str_replace('administrator/', '', $router->build($link)->toString());
				$link   = str_replace('/?', '?', $link);
				$link   = trim(Uri::root(true), '/') . '/' . trim($link, '/');

				$params = array('ajax_url' => $link);
				Factory::getDocument()->addScriptOptions('jlsitemap_cron_multisite', $params);

				// Add script
				HTMLHelper::_('script', 'media/plg_system_jlsitemap_cron_multisite/js/cron.min.js', array('version' => 'auto'));
			}
		}
	}

	/**
	 * Method to run cron
	 *
	 * @return mixed
	 *
	 * @since 0.0.2
	 */
	public function onAjaxJLSitemap_Cron_Multisite()
	{
		$app       = Factory::getApplication();
		$generate  = false;
		$error     = '';
		$clientRun = $this->params->get('client_enable');

		// Client checks
		if ($clientRun)
		{
			if ($this->checkCacheTime())
			{
				$generate = true;
			}
			else
			{
				$error = Text::_('PLG_SYSTEM_JLSITEMAP_GENERATION_ERROR_CACHE');
			}
		}

		// Server checks
		if (!$clientRun)
		{
			if (!$this->params->get('key_enabled'))
			{
				$generate = true;
			}
			elseif (!$generate = ($app->input->get('key', '') == $this->params->get('key')))
			{
				$error = Text::_('PLG_SYSTEM_JLSITEMAP_GENERATION_ERROR_KEY');
			}
		}

		$error = '';
		$generate = true;

		// Run generation
		if (!$error && $generate && $urls = $this->generate())
		{
			$success = Text::sprintf('PLG_SYSTEM_JLSITEMAP_GENERATION_SUCCESS', count($urls->includes),
				count($urls->excludes), count($urls->all));

			//  Prepare json response
			if ($app->input->get('format', 'raw') === 'json')
			{
				$success = explode('<br>', $success);
			}

			return $success;
		}
		elseif ($error)
		{
			throw new Exception(Text::sprintf('PLG_SYSTEM_JLSITEMAP_GENERATION_FAILURE', $error));
		}

		return false;
	}

	/**
	 * Method to generate site map
	 *
	 * @return boolean|object
	 *
	 * @since 0.0.2
	 */
	protected function generate()
	{
		try
		{
			// Update last run
			$this->params->set('last_run', Factory::getDate()->toSql());
			$plugin          = new stdClass();
			$plugin->type    = 'plugin';
			$plugin->element = 'jlsitemap_cron_multisite';
			$plugin->folder  = 'system';
			$plugin->params  = (string) $this->params;
			Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));

			// Run generation
			BaseDatabaseModel::addIncludePath(JPATH_SITE . '/components/com_jlsitemap/models');
			$model = BaseDatabaseModel::getInstance('Sitemap', 'JLSitemapModel', array('ignore_request' => true));

			if (!$urls = $model->generate())
			{
				throw new Exception(Text::sprintf('PLG_SYSTEM_JLSITEMAP_GENERATION_FAILURE', $model->getError()));
			}

			$this->splitUrlsForSubdomain($urls);
			return $urls;
		}
		catch (Exception $e)
		{
			throw new Exception(Text::sprintf('PLG_SYSTEM_JLSITEMAP_GENERATION_FAILURE', $e->getMessage()));
		}
	}


	/**
	 * @param $urls
	 *
	 *
	 * @throws Exception
	 * @since version
	 */
	protected function splitUrlsForSubdomain(&$urls)
	{
		$maps = [];
		$includes = $urls->includes;

		foreach ($includes as $url)
		{
			$locSplits = explode('/', $url->get('link'));
			unset($locSplits[0]);

			$mapIterator = &$maps;
			foreach ($locSplits as $locSplit)
			{

				if(empty($locSplit))
				{
					continue;
				}

				if(!isset($mapIterator[$locSplit]))
				{
					$mapIterator[$locSplit] = [
						's' => $url,
						'i' => []
					];
				}

				$mapIterator = &$mapIterator[$locSplit]['i'];
			}
		}

		$sitemapSource = JPATH_ROOT . DIRECTORY_SEPARATOR . 'sitemap.xml';
		if(file_exists($sitemapSource))
		{
			File::delete($sitemapSource);
		}

		$sitemapFolder = JPATH_ROOT . DIRECTORY_SEPARATOR . 'sitemaps';

		if(!file_exists($sitemapFolder))
		{
			Folder::create($sitemapFolder);
		}


		foreach ($maps as $domain => $map)
		{
			$sitemapFile = $sitemapFolder . DIRECTORY_SEPARATOR . $domain . '.xml';
			$rows = $this->buildMap($map);

			if(file_exists($sitemapFile))
			{
				File::delete($sitemapFile);
			}

			$xml = $this->getXml($rows);
			file_put_contents($sitemapFile, $xml);
		}

	}


	/**
	 * @param $map
	 *
	 * @return array
	 *
	 * @since version
	 */
	protected function buildMap($map)
	{
		JLoader::register('plgSystemMultisiteswitchHelper', JPATH_PLUGINS . '/system/multisiteswitch/helper.php');

		$config = Factory::getConfig();
		$https = $config->get('') ? 'https://' : 'http://';
		$subdomainDefault = plgSystemMultisiteswitchHelper::getSubdomainDefault();
		$output = [];
		$build = static function ($splitMap) use (&$output, &$build, $https, $subdomainDefault) {
			$item = $splitMap['s'];
			$link = '';
			$subdomain = '';
			$linkSource = explode('/', $item->get('link', ''));

			if(isset($linkSource[1]))
			{
				$subdomain = $linkSource[1];
				unset($linkSource[1]);
			}

			if($subdomainDefault->subdomain === $subdomain)
			{
				$subdomain = '';
			}
			else
			{
				$subdomain .= '.';
			}

			$link = implode('/', $linkSource);
			$loc = $https . $subdomain . $_SERVER['SERVER_NAME'] . $link;
			$item->set('link', $link);
			$item->set('loc', $loc);

			$output[] = $item;

			if(isset($splitMap['i']))
			{
				foreach ($splitMap['i'] as $split)
				{
					$build($split);
				}
			}

		};

		$build($map);
		return $output;
	}


	/**
	 * Method to get sitemap xml string
	 *
	 * @param   array  $rows  Include urls array
	 *
	 * @throws  Exception
	 *
	 * @return string
	 *
	 * @since 1.1.0
	 */
	protected function getXML($rows = array())
	{
		$rows = (empty($rows)) ? $this->getUrls()->includes : $rows;

		// Create sitemap
		$sitemap = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'
			. '<!-- JLSitemap ' . Factory::getDate()->toSql() . ' -->'
			. '<urlset'
			. ' xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"'
			. ' xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"'
			. ' xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd http://www.w3.org/1999/xhtml http://www.w3.org/2002/08/xhtml/xhtml1-strict.xsd"'
			. ' xmlns:xhtml="http://www.w3.org/1999/xhtml"'
			. ' xhtml="http://www.w3.org/1999/xhtml"'
			. '/>');

		// Add urls
		foreach ($rows as $row)
		{
			if ($loc = $row->get('loc', false))
			{
				$url = $sitemap->addChild('url');

				// Loc
				$url->addChild('loc', $loc);

				// Changefreq
				if ($changefreq = $row->get('changefreq', false))
				{
					$url->addChild('changefreq', $changefreq);
				}

				// Priority
				if ($priority = $row->get('priority', false))
				{
					$url->addChild('priority', $row->get('priority'));
				}

				// Lastmod
				if ($lastmod = $row->get('lastmod', false))
				{
					$url->addChild('lastmod', Factory::getDate($lastmod)->toISO8601());
				}

				// Alternates
				if ($alternates = $row->get('alternates', false))
				{
					// Add x-default
					if (!isset($alternates['x-default']) && isset($alternates[Factory::getLanguage()->getDefault()]))
					{
						$alternates['x-default'] = $alternates[Factory::getLanguage()->getDefault()];
					}

					foreach ($alternates as $lang => $href)
					{
						$alternate = $url->addChild('xhtml:link', '', 'http://www.w3.org/1999/xhtml');
						$alternate->addAttribute('rel', 'alternate');
						$alternate->addAttribute('hreflang', $lang);
						$alternate->addAttribute('href', $href);
					}
				}
			}
		}
		$xml = $sitemap->asXML();

		return $xml;
	}


	/**
	 * Method to check client cache time
	 *
	 * @return bool True if  run. False if don't  run
	 *
	 * @since 0.0.2
	 */
	protected function checkCacheTime()
	{
		if (!$lastRun = $this->params->get('last_run', false))
		{
			return true;
		}

		// Prepare cache time
		$offset = ' + ' . $this->params->get('client_cache_number', 1) . ' ' .
			$this->params->get('client_cache_value', 'day');
		$cache  = new Date($lastRun . $offset);

		return (Factory::getDate()->toUnix() >= $cache->toUnix());
	}


}