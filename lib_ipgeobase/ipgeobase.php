<?php

use Joomla\CMS\Factory;


/**
 * @package     ${NAMESPACE}
 *
 * @since version
 */
class JIpgeobase
{

	public static function get($ip = '')
	{
		JLoader::register('IPGeoBase', JPATH_ROOT . '/libraries/ipgeobase/ipgeobase/IPGeoBase.php');

		if(empty($ip))
		{
			$ip = $_SERVER['REMOTE_ADDR'];
		}

		$ipgeobase = new IPGeoBase();
		$ipgeo = $ipgeobase->getRecord($ip);
		$region = !empty($ipgeo['region']) ? $ipgeo['region'] : '';
		$city = !empty($ipgeo['city']) ? $ipgeo['city'] : '';

		$region = iconv('windows-1251', 'UTF-8', $region);
		$city = iconv('windows-1251', 'UTF-8', $city);

		return [
			'region' =>$region,
			'city' => $city,
		];
	}


	/**
	 * @param int $id
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function getCityFromId($id)
	{
		$db     = Factory::getDbo();
		$query = $db->getQuery(true)
			->select("title")
			->from('#__lib_ipgeobase_cities')
			->where('id = '. (int) $id);
		$city = $db->setQuery($query)->loadObject();

		if(!empty($city->title))
		{
			return $city->title;
		}
		else
		{
			return '';
		}

	}


	/**
	 * @param int $id
	 *
	 * @return string
	 *
	 * @since version
	 */
	public static function getRegionFromId($id)
	{
		$db     = Factory::getDbo();
		$query = $db->getQuery(true)
			->select("title")
			->from('#__lib_ipgeobase_regions')
			->where('id = '. (int) $id);
		$city = $db->setQuery($query)->loadObject();

		if(!empty($city->title))
		{
			return $city->title;
		}
		else
		{
			return '';
		}

	}

}