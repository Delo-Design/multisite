<?php

use Joomla\CMS\Factory;

/**
 * @package     ${NAMESPACE}
 * @subpackage
 *
 * @copyright   A copyright
 * @license     A "Slug" license name e.g. GPL2
 */

class plgSystemMultisiteswitchHelper
{


	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function getSubdomainDefault()
	{
		$sudomains = self::getSubdomains();
		foreach ($sudomains as $sudomain)
		{
			if((int)$sudomain->default)
			{
				return $sudomain;
			}
		}
	}


	/**
	 *
	 * @return mixed
	 *
	 * @since version
	 */
	public static function getSubdomains()
	{
		$output = [];
		$db = Factory::getDbo();
		$query = $db->getQuery(true)
			->select($db->quoteName(array('params')))
			->from('#__extensions')
			->where( 'element=' . $db->quote('multisiteswitch'));
		$extension = $db->setQuery( $query )->loadObject();
		$params = new \Joomla\Registry\Registry($extension->params);
		$subDomains = $params->get('subdomains', []);
		return $subDomains;
	}


	/**
	 * @param null $host
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function getDomain($host = null)
	{
		if($host === null)
		{
			$host = 'http://' . $_SERVER['SERVER_NAME'];
		}

		$pieces = parse_url($host);
		$domain = isset($pieces['host']) ? $pieces['host'] : '';
		if (preg_match('/(?P<domain>[a-z0-9][a-z0-9\-]{1,63}\.[a-z\.]{2,6})$/i', $domain, $regs))
		{
			return $regs['domain'];
		}

		return false;
	}


}