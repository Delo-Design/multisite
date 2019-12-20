<?php
/**
 * @package    System - Module Label Plugin
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\HTML\HTMLHelper;

class plgSystemModuleLabel extends CMSPlugin
{
	/**
	 * Affects constructor behavior. If true, language files will be loaded automatically.
	 *
	 * @var    boolean
	 * @since 1.0.0
	 */
	protected $autoloadLanguage = true;

	/**
	 * Add field to admin module form
	 *
	 * @param  Form  $form The form to be altered.
	 * @param  mixed $data The associated data for the form.
	 *
	 * @return  boolean
	 *
	 * @since 1.0.0
	 */
	function onContentPrepareForm($form, $data)
	{
		$app = Factory::getApplication();

		if ($app->isAdmin() && $app->input->get('option', '') == 'com_modules' && $form->getName() == 'com_modules.module')
		{
			// Prepare title
			if (is_object($data) && !empty($data->title))
			{
				$data->labels = array();
				preg_match_all('/\[.*?]/', $data->title, $matches);
				if (!empty($matches[0]))
				{
					foreach ($matches[0] as $label)
					{
						$data->labels[] = trim(str_replace(array('[', ']'), '', $label));;
						$data->title = trim(str_replace($label, '', $data->title));
					}
				}
			}

			// Add filed to form
			Form::addFormPath(__DIR__ . '/form');
			Form::addFieldPath(__DIR__ . '/form');
			$form->loadFile('form', false);
		}

		return true;
	}

	/**
	 * Add labels to title
	 *
	 * @param  Form  $form The form to be altered.
	 * @param  mixed $data The associated data for the form.
	 *
	 * @return bool
	 *
	 * @since 1.0.0
	 */
	function onUserBeforeDataValidation($form, &$data)
	{
		if ($form->getName() == 'com_modules.module' && is_array($data) && !empty($data['labels']))
		{
			$labels = array();
			foreach (str_replace('#new#', '', $data['labels']) as $label)
			{
				$labels[] = '[' . $label . ']';
			}

			$data['title'] = trim(implode(' ', $labels) . ' ' . $data['title']);
		}

		return true;
	}

	/**
	 * Add scripts & styles
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	function onBeforeRender()
	{
		$app       = Factory::getApplication();
		$component = $app->input->get('option', '');
		if ($app->isAdmin() && $component == 'com_modules')
		{
			$view = $app->input->get('view', '');

			if ($view == 'modules' || empty($view))
			{
				HTMLHelper::script('media/plg_system_modulelabel/js/admin-modules.min.js', array('version' => 'auto'));
			}

			if ($view == 'module')
			{
				HTMLHelper::script('media/plg_system_modulelabel/js/admin-module.min.js', array('version' => 'auto'));
				HTMLHelper::stylesheet('media/plg_system_modulelabel/css/admin-module.min.css', array('version' => 'auto'));
			}
		}
	}

	/**
	 *  Replace labels in modules array
	 *
	 * @param   $modules  The module object.
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	function onAfterModuleList(&$modules)
	{
		foreach ($modules as $key => &$module)
		{
			$module->title = trim(preg_replace('~\[(.?)*\]~', '', $module->title));
		}
	}

	/**
	 * Replace labels on module render
	 *
	 * @param  object $module  The module object.
	 * @param  array  $attribs The render attributes
	 *
	 * @return  void
	 *
	 * @since 1.0.0
	 */
	function onRenderModule(&$module, &$attribs)
	{
		$module->title = trim(preg_replace('~\[(.?)*\]~', '', $module->title));
	}
}
