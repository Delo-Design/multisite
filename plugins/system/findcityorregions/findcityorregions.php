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
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Database\DatabaseDriver;
use Joomla\Registry\Registry;

defined('_JEXEC') or die;

/**
 * Findcityorregions plugin.
 *
 * @package   multisiteswitch
 * @since     1.0.0
 */
class plgSystemFindcityorregions extends CMSPlugin
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

	public function onAjaxFindcityorregions()
	{
		$action = $this->app->input->get('action', '');

		if(method_exists($this, $action))
		{
			$this->$action();
		}

	}

	private function getCitiesSearch()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$data = $input->getArray();
		$q = $data['q'];

		HTMLHelper::addIncludePath(JPATH_LIBRARIES . '/ipgeobase/helpers');

		$filters['filter.q'] = $q;
		$filters['filter.limit'] = 15;

		if ($filters === [])
		{
			$options = HTMLHelper::_('city.options');
		}
		else
		{
			$options = HTMLHelper::_('city.options', $filters);
		}


		echo json_encode($options);

		$app->close();
	}

	private function getCitiesByIds()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$data = $input->getArray();

		if(!isset($data['ids']))
		{
			JErrorPage::render();
		}

		$ids = explode(',', $data['ids']);

		if(count($ids) === 0)
		{
			JErrorPage::render();
		}

		for($i=0;$i<count($ids);$i++)
		{
			$ids[$i] = (int)$ids[$i];
		}

		HTMLHelper::addIncludePath(JPATH_LIBRARIES . '/ipgeobase/helpers');

		$filters['filter.ids'] = $ids;

		if ($filters === [])
		{
			$options = HTMLHelper::_('city.options');
		}
		else
		{
			$options = HTMLHelper::_('city.options', $filters);
		}


		echo json_encode($options);

		$app->close();
	}

	private function getRegionsSearch()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$data = $input->getArray();
		$q = $data['q'];

		HTMLHelper::addIncludePath(JPATH_LIBRARIES . '/ipgeobase/helpers');

		$filters['filter.q'] = $q;
		$filters['filter.limit'] = 15;

		if ($filters === [])
		{
			$options = HTMLHelper::_('region.options');
		}
		else
		{
			$options = HTMLHelper::_('region.options', $filters);
		}


		echo json_encode($options);

		$app->close();
	}

	private function getRegionsByIds()
	{
		$app = Factory::getApplication();
		$input = $app->input;
		$data = $input->getArray();

		if(!isset($data['ids']))
		{
			JErrorPage::render();
		}

		$ids = explode(',', $data['ids']);

		if(count($ids) === 0)
		{
			JErrorPage::render();
		}

		for($i=0;$i<count($ids);$i++)
		{
			$ids[$i] = (int)$ids[$i];
		}

		HTMLHelper::addIncludePath(JPATH_LIBRARIES . '/ipgeobase/helpers');

		$filters['filter.ids'] = $ids;

		if ($filters === [])
		{
			$options = HTMLHelper::_('region.options');
		}
		else
		{
			$options = HTMLHelper::_('region.options', $filters);
		}


		echo json_encode($options);

		$app->close();
	}
}
