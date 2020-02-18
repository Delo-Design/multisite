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

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Uri\Uri;

class PlgSystemYTurbo extends CMSPlugin
{
	/**
	 * Affects constructor behavior.
	 *
	 * @var  boolean
	 *
	 * @since  1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Method to actions.
	 *
	 * @throws  Exception
	 *
	 * @return  mixed
	 *
	 * @since  1.0.0
	 */
	public function onAjaxYTurbo()
	{
		$app = Factory::getApplication();
		if ($app->isClient('site') && $app->input->get('task') == 'generate')
		{
			try
			{
				JLoader::register('PlgSystemYTurboHelper', JPATH_SITE . '/plugins/system/yturbo/helper.php');
				PlgSystemYTurboHelper::clean();
				$result = PlgSystemYTurboHelper::generate();
				$msg    = ($result) ? Text::_('PLG_SYSTEM_YTURBO_GENERATE_COMPLETE')
					: Text::_('PLG_SYSTEM_YTURBO_GENERATE_FAILURE');

				if ($app->input->get('format') == 'html')
				{
					$app->enqueueMessage($msg, (!$result) ? 'error' : 'message');
					Factory::getDocument()->setTitle(Text::_('PLG_SYSTEM_YTURBO'));

					return LayoutHelper::render('plugins.system.yturbo.field.feeds',
						array('feeds' => PlgSystemYTurboHelper::getFeeds()));
				}

				return $msg;
			}
			catch (Exception $e)
			{
				throw new Exception($e->getMessage(), 500);
			}
		}

		if ($app->isClient('site') && $app->input->get('task') == 'send')
		{
			try
			{
				JLoader::register('PlgSystemYTurboHelper', JPATH_SITE . '/plugins/system/yturbo/helper.php');
				PlgSystemYTurboHelper::send();
				$msg = Text::_('PLG_SYSTEM_YTURBO_SEND_COMPLETE');

				if ($app->input->get('format') == 'html')
				{
					$app->enqueueMessage($msg);
					Factory::getDocument()->setTitle(Text::_('PLG_SYSTEM_YTURBO'));
				}

				return $msg;
			}
			catch (Exception $e)
			{
				$msg = Text::_('PLG_SYSTEM_YTURBO_SEND_FAILURE');;
				if ($app->input->get('format') == 'html')
				{
					$app->enqueueMessage($msg, 'error');
					Factory::getDocument()->setTitle(Text::_('PLG_SYSTEM_YTURBO'));
					echo $e->getMessage();
				}
				else
				{
					throw new Exception($e->getMessage(), 500);
				}
			}
		}

		return false;
	}

	/**
	 * Add generate button.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	public function onBeforeRender()
	{
		$app          = Factory::getApplication();
		$admin        = $app->isClient('administrator');
		$option       = $app->input->getCmd('option');
		$view         = $app->input->getCmd('view');
		$layout       = $app->input->getCmd('layout');
		$extension_id = (int) $app->input->get('extension_id');
		$plugin_id    = (int) PluginHelper::getPlugin('system', $this->_name)->id;

		if ($admin && (($option == 'com_plugins' && $view == 'plugin' && $layout == 'edit' && $extension_id === $plugin_id)
				|| $option == 'com_content' && ($view == 'articles' || empty($view))))
		{
			$toolbar = Toolbar::getInstance('toolbar');
			$root    = Uri::getInstance()->toString(array('scheme', 'host', 'port'));

			$url    = $root . '/index.php?option=com_ajax&plugin=yturbo&group=system&format=html&task=generate';
			$button = '<a href="' . $url . '" class="btn btn-small" target="_blank">'
				. '<span class="icon-play" aria-hidden="true"></span>'
				. Text::_('PLG_SYSTEM_YTURBO_GENERATE_BUTTON') . '</a>';
			$toolbar->appendButton('Custom', $button, 'generate');

			$url    = $root . '/index.php?option=com_ajax&plugin=yturbo&group=system&format=html&task=send';
			$button = '<a href="' . $url . '" class="btn btn-small" target="_blank">'
				. '<span class="icon-upload" aria-hidden="true"></span>'
				. Text::_('PLG_SYSTEM_YTURBO_SEND_BUTTON') . '</a>';
			$toolbar->appendButton('Custom', $button, 'generate');
		}
	}
}