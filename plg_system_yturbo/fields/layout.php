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

use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Filesystem\Path;
use Joomla\CMS\Form\FormHelper;

FormHelper::loadFieldClass('list');

class JFormFieldLayout extends JFormFieldList
{
	/**
	 * The form field type.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $type = 'layout';

	/**
	 * Field options array.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $_options = null;

	/**
	 * Method to get the field options.
	 *
	 * @throws  Exception
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if ($this->_options === null)
		{
			$folders   = array('layouts/plugins/system/yturbo/content');
			$templates = Folder::folders(JPATH_SITE . '/templates');
			foreach ($templates as $template)
			{
				$folders[] = 'templates/' . $template . '/html/layouts/plugins/system/yturbo/content';
			}
			$layouts = array();
			foreach ($folders as $folder)
			{
				$path = Path::clean(JPATH_SITE . '/' . $folder);
				if (Folder::exists($path))
				{
					$files = Folder::files(JPATH_SITE . '/' . $folder, '.php', false);
					foreach ($files as $file)
					{
						$layouts[] = str_replace('.php', '', $file);
					}
				}
			}

			// Prepare options
			$options = parent::getOptions();
			foreach ($layouts as $layout)
			{
				$option        = new stdClass();
				$option->value = $layout;
				$option->text  = $layout;

				$options[] = $option;
			}

			$this->_options = $options;
		}

		return $this->_options;
	}
}