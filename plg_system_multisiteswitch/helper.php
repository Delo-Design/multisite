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
}