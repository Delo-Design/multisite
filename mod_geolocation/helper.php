<?php

use Joomla\CMS\Factory;

class ModGeolocationHelper
{

	public static function findRoute()
	{
		JLoader::register('JIpgeobase', JPATH_LIBRARIES . '/ipgeobase/ipgeobase.php');
		JLoader::register('plgSystemMultisiteswitch', JPATH_PLUGINS . '/system/multisiteswitch/multisiteswitch.php');

		$list = self::getRouteRegionsAndCityFromParams();
		$current = JIpgeobase::get();
		$findSubDomain = '';
		foreach ($list as $subdomain => $value)
		{
			$findRegion = $current['region'] === $value['region'];
			$findCity = $current['city'] === $value['city'];
			if($findRegion || $findCity)
			{
				$findSubDomain = $subdomain;
			}
		}

		if($findSubDomain !== plgSystemMultisiteswitch::$subDomain)
		{
			return $findSubDomain;
		}

		return false;
	}


	private static function getRouteRegionsAndCityFromParams()
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

		foreach ($subDomains as $subDomain)
		{
			$output[$subDomain->subdomain] = [];
			if(!empty($subDomain->region))
			{
				$output[$subDomain->subdomain]['region'] = JIpgeobase::getRegionFromId((int)$subDomain->region);
			}
			else
			{
				$output[$subDomain->subdomain]['region'] = '';
			}

			if(!empty($subDomain->city))
			{
				$output[$subDomain->subdomain]['city'] = JIpgeobase::getCityFromId((int)$subDomain->city);
			}
			else
			{
				$output[$subDomain->subdomain]['city'] = '';
			}

		}

		return $output;
	}

}