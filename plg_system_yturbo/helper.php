<?php
/**
 * @package    DachaDacha Package
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Registry\Registry;
use Joomla\Utilities\ArrayHelper;

class PlgSystemYTurboHelper
{
	/**
	 * The mapping via plugin params.
	 *
	 * @var  object
	 *
	 * @since  1.0.0
	 */
	protected static $_mapping = null;

	/**
	 * The plugin params.
	 *
	 * @var  Registry
	 *
	 * @since  1.0.0
	 */
	protected static $_params = null;

	/**
	 * Exist feeds.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected static $_feeds = null;

	/**
	 * All items.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected static $_items = null;

	/**
	 * All items.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected static $_xml = array();

	/**
	 * Method to generate feeds.
	 *
	 * @throws  Exception
	 *
	 * @since   1.0.0
	 */
	public static function generate()
	{
		$folder = JPATH_SITE . '/yturbo';
		$offset = 0;
		$params = self::getParams();
		$limit  = (int) $params->get('limit', 10);

		while ($items = self::getItems($offset, $limit))
		{
			// Generate xml
			if ($xml = self::getXML($items))
			{
				$file = $folder . '/feed_' . $offset . '.xml';
				if (File::exists($file))
				{
					File::delete($file);
				}
				File::append($file, $xml);
			}

			// Next part
			$offset += $limit;
		}

		return true;
	}

	/**
	 * @param   array  $rows
	 *
	 * @throws  Exception
	 *
	 * @return  string  String on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	public static function getXML($rows = array())
	{
		$params = self::getParams();

		// Create feed
		$feed = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?>'
			. '<rss'
			. ' xmlns:yandex="http://news.yandex.ru"'
			. ' xmlns:media="http://search.yahoo.com/mrss/"'
			. ' xmlns:turbo="http://turbo.yandex.ru"'
			. ' version="2.0"'
			. '/>');

		// Add chanel
		$channel = $feed->addChild('channel');
		$channel->addAttribute('title', $params->get('channel_title'));
		$channel->addAttribute('link', $params->get('channel_link'));
		$channel->addAttribute('description', $params->get('channel_description'));
		$channel->addAttribute('language', $params->get('channel_language'));

		if (!empty($rows))
		{
			foreach ($rows as $row)
			{
				$item = $channel->addChild('item');
				$item->addAttribute('turbo', 'true');
				$item->addChild('title', $row->title);
				$item->addChild('turbo:topic', $row->title, 'http://turbo.yandex.ru');
				$item->addChild('link', $row->link);
				$item->addChild('turbo:source', $row->link);
				$item->addChild('pubDate', $row->pubDate);

				$content      = $item->addChild('turbo:content', '', 'http://turbo.yandex.ru');
				$contentNode  = dom_import_simplexml($content);
				$contentOwner = $contentNode->ownerDocument;
				$contentNode->appendChild($contentOwner->createCDATASection($row->content));
			}
		}

		return $feed->asXML();
	}

	/**
	 * Method to clean feeds.
	 *
	 * @since 1.0.0
	 */
	public static function clean()
	{
		$folder = JPATH_SITE . '/yturbo';
		if (Folder::exists($folder))
		{
			Folder::delete($folder);
		}
		Folder::create($folder);
	}

	/**
	 * Method to get items for feeds.
	 *
	 * @param   integer  $limitstart  Offset.
	 * @param   integer  $limit       The number of records.
	 *
	 * @throws  Exception
	 *
	 * @return  array|false Items objects array on success, false on failure.
	 *
	 * @since   1.0.0
	 */
	protected static function getItems($limitstart = 0, $limit = 0)
	{
		try
		{
			$items      = array();
			$mapping    = self::getMapping();
			$categories = $mapping->categories;
			$layouts    = $mapping->layouts;

			if (empty($categories))
			{
				throw new Exception(Text::_('PLG_SYSTEM_YTURBO_ERROR_CATEGORIES_NOT_SELECT'), 404);
			}

			$access          = array_unique(Factory::getUser(0)->getAuthorisedViewLevels());
			$defaultLanguage = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');
			$app             = Factory::getApplication();
			$db              = Factory::getDbo();
			$nullDate        = $db->getNullDate();
			$now             = Factory::getDate()->toSql();
			$root            = Uri::getInstance()->toString(array('scheme', 'host', 'port'));

			// Load Language
			$language = Factory::getLanguage();
			$language->load('plg_system_yturbo', JPATH_ADMINISTRATOR, $language->getTag());
			$language->load('com_content', JPATH_SITE, $language->getTag());

			// Get items from database
			$query = $db->getQuery(true)
				->select(array('a.id', 'a.title', 'a.alias', 'a.publish_up', 'a.language',
					'a.introtext', 'a.fulltext', 'a.images', 'a.attribs',
					'c.id as category_id', 'c.alias as category_alias'))
				->from($db->quoteName('#__content', 'a'))
				->join('LEFT', $db->quoteName('#__categories', 'c')
					. ' ON c.id = a.catid')
				->where('a.access IN (' . implode(',', $access) . ')')
				->where('c.access IN (' . implode(',', $access) . ')')
				->where('a.state = 1')
				->where('c.published = 1')
				->where('(' . $db->quoteName('a.publish_down') . ' = ' . $db->quote($nullDate) . ' OR '
					. $db->quoteName('a.publish_down') . '  >= ' . $db->quote($now) . ')')
				->where('(' . $db->quoteName('a.publish_up') . ' <> ' . $db->quote($nullDate) . ' AND '
					. $db->quoteName('a.publish_up') . '  <= ' . $db->quote($now) . ')')
				->where('c.id IN (' . implode(',', $categories) . ')')
				->group('a.id')
				->order($db->escape('a.ordering') . ' ' . $db->escape('asc'));

			if (!$rows = $db->setQuery($query, $limitstart, $limit)->loadObjectList())
			{
				return false;
			}

			PluginHelper::importPlugin('content');
			foreach ($rows as &$row)
			{
				// Prepare object
				$row->params = new Registry($row->attribs);
				$row->text   = $row->introtext . ' ' . $row->fulltext;
				$app->triggerEvent('onContentPrepare', array('com_content.article', &$row, &$row->params, 0));

				$row->event = new stdClass;

				$results                       = $app->triggerEvent('onContentAfterTitle',
					array('com_content.article', &$row, &$row->params, 0));
				$row->event->afterDisplayTitle = trim(implode("\n", $results));

				$results = $app->triggerEvent('onContentBeforeDisplay',
					array('com_content.article', &$row, &$row->params, 0));

				$row->event->beforeDisplayContent = trim(implode("\n", $results));

				$results                         = $app->triggerEvent('onContentAfterDisplay',
					array('com_content.article', &$row, &$row->params, 0));
				$row->event->afterDisplayContent = trim(implode("\n", $results));

				$row->images = new Registry($row->images);

				$row->slug    = $row->id . ':' . $row->alias;
				$row->catslug = $row->category_id . ':' . $row->category_alias;
				$row->link    = Route::_('index.php?option=com_content&view=article&id=' . $row->slug . '&catid=' . $row->catslug);

				$row->language = (empty($row->language) || $row->language == '*') ? $defaultLanguage : $row->language;

				$row->layout = (!empty($layouts[$row->category_id])) ? $layouts[$row->category_id] : $layouts[1];

				$row->root = $root;

				$row->sitename = self::getParams()->get('channel_title');

				// Create item object
				$item           = new stdClass();
				$item->title    = $row->title;
				$item->link     = $root . $row->link;
				$item->pubDate  = HTMLHelper::_('date', $row->publish_up, 'r');
				$item->language = explode('-', $row->language)[0];
				$item->content  = LayoutHelper::render('plugins.system.yturbo.content.' . $row->layout, $row);

				// Clean content
				$item->content = htmlspecialchars_decode($item->content);
				$item->content = str_replace('&nbsp;', ' ', $item->content);
				while (preg_match('/[\s]{2,}/', $item->content))
				{
					$item->content = preg_replace('/[\s]{2,}/', ' ', $item->content);
				}
				$item->content = preg_replace('/<p[^>]*><\\/p[^>]*>/', '', $item->content);
				$item->content = str_replace('<p> </p>', '', $item->content);
				$item->content = str_replace('<p></p>', '', $item->content);
				$item->content = str_replace('<p />', '', $item->content);
				$item->content = str_replace('<p/>', '', $item->content);
				if (!empty($item->content))
				{
					$items[] = $item;
				}
			}

			return $items;
		}
		catch (Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'error');

			return false;
		}
	}

	/**
	 * Method to get feeds links.
	 *
	 * @param   bool  $system  Get system path.
	 *
	 * @return  array Feeds links.
	 *
	 * @since  1.0.0
	 */
	public static function getFeeds($system = false)
	{
		if (self::$_feeds === null)
		{
			$path = JPATH_SITE . '/yturbo';
			if (!Folder::exists($path))
			{
				Folder::create($path);
			}
			$feeds = array();
			$root  = ($system) ? JPATH_ROOT : Uri::getInstance()->toString(array('scheme', 'host', 'port'));
			foreach (Folder::files($path, '.xml') as $file)
			{
				$feeds[] = $root . '/yturbo/' . $file;
			}
			self::$_feeds = $feeds;
		}

		return self::$_feeds;
	}

	/**
	 * Method to get mapping.
	 *
	 * @return  stdClass Categories ids and layouts arrays.
	 *
	 * @since  1.0.0
	 */
	public static function getMapping()
	{
		if (self::$_mapping === null)
		{
			$layouts    = array();
			$categories = array();

			// Get values from plugin.
			$params  = self::getParams();
			$options = $params->get('mapping', new stdClass());
			foreach (ArrayHelper::fromObject($options, false) as $option)
			{
				if ($catid = (int) $option->category)
				{
					if ($catid !== 1)
					{
						$categories[$catid] = $catid;
					}
					$layouts[$catid] = $option->layout;
				}
			}

			// Set default layout
			if (!isset($mapping->layouts[1]))
			{
				$layouts[1] = 'default';
			}

			// Get categories
			if ($categories)
			{
				$db    = Factory::getDbo();
				$query = $db->getQuery(true)
					->select(array('sub.id', 'sub.parent_id'))
					->from($db->quoteName('#__categories', 'sub'))
					->innerJoin($db->quoteName('#__categories', 'this') .
						' ON sub.lft > this.lft AND sub.rgt < this.rgt')
					->where('this.id IN (' . implode(',', $categories) . ')'
						. ' OR sub.id IN (' . implode(',', $categories) . ')');
				$rows  = $db->setQuery($query)->loadObjectList('id');

				// Set layouts
				foreach ($rows as $row)
				{
					if (isset($layouts[$row->id]))
					{
						continue;
					}

					$layout = false;
					$search = $row;
					while ($search->parent_id > 1 && isset($rows[$search->parent_id]))
					{
						$parent = $rows[$search->parent_id];
						if (isset($layouts[$parent->id]))
						{
							$layout = $layouts[$parent->id];

							break;
						}
						else
						{
							$search = $parent;
						}
					}
					$layouts[$row->id] = ($layout) ? $layout : $layouts[1];
				}
			}
			else
			{
				$rows = array();
			}

			// Create mapping object
			$mapping             = new stdClass();
			$mapping->categories = array_keys($rows);
			$mapping->layouts    = $layouts;

			self::$_mapping = $mapping;
		}

		return self::$_mapping;
	}

	/**
	 * Method to get plugin params.
	 *
	 * @return  Registry Plugin params.
	 *
	 * @since  1.0.0
	 */
	public static function getParams()
	{
		if (self::$_params === null)
		{
			$plugin   = PluginHelper::getPlugin('system', 'yturbo');
			$config   = Factory::getConfig();
			$params   = new Registry($plugin->params);
			$language = ComponentHelper::getParams('com_languages')->get('site', 'en-GB');

			$params->set('channel_title', $config->get('sitename'));
			$params->set('channel_description', $config->get('MetaDesc'));
			$params->set('channel_link', Uri::root());
			$params->set('channel_language', explode('-', $language)[0]);

			self::$_params = $params;

		}

		return self::$_params;
	}

	/**
	 * Method to send feed via api.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public static function send()
	{
		$rootUrl = 'https://api.webmaster.yandex.net/v4';
		$feeds   = self::getFeeds(true);

		// Check sends file
		$sends     = array();
		$sendsPath = JPATH_CACHE . '/ytubro/sends.json';
		if (File::exists($sendsPath))
		{
			$sends = json_decode(file_get_contents($sendsPath));
			File::delete($sendsPath);
		}
		if (end($feeds) === end($sends))
		{
			$sends = array();
		}

		// Get user
		$url     = $rootUrl . '/user';
		$user    = self::apiRequest($url);
		$user_id = $user->get('user_id', 0);

		// Prepare upload link
		$uri    = Uri::getInstance();
		$scheme = $uri->getScheme();
		$host   = $uri->getHost();
		$port   = $uri->getPort();
		if (empty($port))
		{
			$port = ($scheme === 'https') ? 443 : 80;
		}
		$host_id = $scheme . ':' . $host . ':' . $port;

		// Send feed
		$max    = 10;
		$i      = 1;
		$totime = array();
		foreach ($feeds as $feed)
		{
			if (!in_array($feed, $sends) && $i <= $max)
			{
				$url        = $rootUrl . '/user/' . $user_id . '/hosts/' . $host_id . '/turbo/uploadAddress';
				$uploadLink = self::apiRequest($url);
				try
				{
					$context = file_get_contents($feed);
					$task    = self::apiRequest($uploadLink->get('upload_address'), $context);
				}
				catch (Exception $e)
				{
					echo '<pre>', print_r($feed . ': .' . $e->getMessage(), true), '</pre>';
					break;
				}

				$i++;
				$sends[]  = $feed;
				$totime[] = $feed;
			}
		}

		// Save sends
		File::append($sendsPath, json_encode($sends));

		echo '<pre>', print_r('--- ToTime ---', true), '</pre>';
		echo '<pre>', print_r($totime, true), '</pre>';
		echo '<pre>', print_r('--- Total ---', true), '</pre>';
		echo '<pre>', print_r($sends, true), '</pre>';

		return $totime;
	}

	/**
	 * Method to send yandex webmaster api request.
	 *
	 * @param   string  $url   Api url.
	 * @param   string  $feed  Feed content.
	 *
	 * @throws  Exception
	 *
	 * @return false|Registry  Response registry on success, false or exception on failure.
	 *
	 * @since  1.0.0
	 */
	protected static function apiRequest($url = null, $feed = null)
	{
		$params = self::getParams();
		$token  = $params->get('api_token');
		if (empty($token))
		{
			throw new Exception('EMPTY API token', 500);
		}
		if ($curl = curl_init())
		{
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			if (!empty($feed))
			{
				curl_setopt($curl, CURLOPT_HTTPHEADER, array(
					'Authorization: OAuth ' . $token,
					'Content-Type: application/rss+xml'));
				curl_setopt($curl, CURLOPT_POSTFIELDS, $feed);
			}
			else
			{
				curl_setopt($curl, CURLOPT_HTTPHEADER, array(
					'Authorization: OAuth ' . $token,
				));
			}
			$response = curl_exec($curl);
			curl_close($curl);

			$response = new Registry($response);

			if ($response->get('error_message'))
			{
				throw new Exception($response->get('error_message'), 500);
			}

			return $response;
		}
		else
		{
			throw new  Exception('Curl Init', 500);
		}
	}
}