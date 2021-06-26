<?php defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;


class ConfigHelper
{

	protected static $config = null;


	/**
	 *
	 * @return Registry
	 *
	 * @since version
	 */
	public static function getConfig()
	{
		if (self::$config === null)
		{
			self::autoSet();
		}

		return self::$config;
	}


	public static function setConfig($config)
	{
		self::$config = $config;
	}


	public static function get($name, $default = '')
	{
		return self::getConfig()->get($name, $default);
	}


	public static function set($name, $value)
	{
		return self::getConfig()->set($name, $value);
	}


	public static function autoSet()
	{
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select('params');
		$query->from('#__extensions');
		$query->where($db->qn('element') . ' = ' . $db->q('multisiteswitch'));
		$element = $db->setQuery($query)->loadObject();

		if (!empty($element->params))
		{
			self::setConfig(new Registry($element->params));
		}
	}

}