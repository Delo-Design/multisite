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

use Joomla\CMS\Form\FormField;


class JFormFieldFeeds extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'feeds';

	/**
	 * Name of the layout being used to render the field.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $layout = 'plugins.system.yturbo.field.feeds';

	/**
	 * Feeds array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_feeds = null;

	/**
	 * Method to get feeds.
	 *
	 * @throws  Exception
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getFeeds()
	{
		if ($this->_feeds === null)
		{
			JLoader::register('PlgSystemYTurboHelper', JPATH_SITE . '/plugins/system/yturbo/helper.php');
			$this->_feeds = PlgSystemYTurboHelper::getFeeds();
		}

		return $this->_feeds;
	}

	/**
	 * Method to get the data to be passed to the layout for rendering.
	 *
	 * @throws  Exception
	 *
	 * @return  array
	 *
	 * @since 1.0.0
	 */
	protected function getLayoutData()
	{
		$data          = parent::getLayoutData();
		$data['feeds'] = $this->getFeeds();

		return $data;
	}
}