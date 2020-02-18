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
use Joomla\CMS\Filesystem\Folder;
use Joomla\CMS\Installer\InstallerAdapter;

class PlgSystemYTurboInstallerScript
{
	/**
	 * Plugin folders.
	 *
	 * @var  array
	 *
	 * @since  1.0.0
	 */
	protected $folders = array('yturbo');

	/**
	 * Runs right after any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action. Possible values are:
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	function postflight($type, $parent)
	{
		// Enable plugin
		if ($type == 'install')
		{
			$this->enablePlugin($parent);
		}

		// Install layouts
		$this->installLayouts($parent);

		// Create folders
		$this->createFolders();
	}

	/**
	 * Enable plugin after installation.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function enablePlugin($parent)
	{
		// Prepare plugin object
		$plugin          = new stdClass();
		$plugin->type    = 'plugin';
		$plugin->element = $parent->getElement();
		$plugin->folder  = (string) $parent->getParent()->manifest->attributes()['group'];
		$plugin->enabled = 1;

		// Update record
		Factory::getDbo()->updateObject('#__extensions', $plugin, array('type', 'element', 'folder'));
	}

	/**
	 * Method to install/update extension layouts
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function installLayouts($parent)
	{
		$root   = JPATH_ROOT . '/layouts';
		$source = $parent->getParent()->getPath('source');

		// Get attributes
		$attributes = $parent->getParent()->manifest->xpath('layouts');
		if (!is_array($attributes) || empty($attributes[0])) return;

		// Get destination
		$destination = (!empty($attributes[0]->attributes()->destination)) ?
			(string) $attributes[0]->attributes()->destination : false;
		if (!$destination) return;

		// Remove old layouts
		if (Folder::exists($root . '/' . trim($destination, '/')))
		{
			Folder::delete($root . '/' . trim($destination, '/'));
		}

		// Get folder
		$folder = (!empty($attributes[0]->attributes()->folder)) ?
			(string) $attributes[0]->attributes()->folder : 'layouts';
		if (!Folder::exists($source . '/' . trim($folder, '/'))) return;

		// Prepare src and dest
		$src  = $source . '/' . trim($folder, '/');
		$dest = $root . '/' . trim($destination, '/');

		// Check destination
		$path = $root;
		$dirs = explode('/', $destination);
		array_pop($dirs);

		if (!empty($dirs))
		{
			foreach ($dirs as $i => $dir)
			{
				$path .= '/' . $dir;
				if (!Folder::exists($path))
				{
					Folder::create($path);
				}
			}
		}

		// Move layouts
		Folder::move($src, $dest);
	}

	/**
	 * Create plugin folder if not exist.
	 *
	 * @since  1.0.0
	 */
	protected function createFolders()
	{
		foreach ($this->folders as $folder)
		{
			$path = JPATH_SITE . '/' . $folder;
			if (!Folder::exists($path))
			{
				Folder::create($path);
			}
		}
	}

	/**
	 * This method is called after extension is uninstalled.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	public function uninstall($parent)
	{
		// Uninstall layouts
		$this->uninstallLayouts($parent);

		// Delete folders
		$this->deleteFolders();
	}

	/**
	 * Method to uninstall extension layouts
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @since  1.0.0
	 */
	protected function uninstallLayouts($parent)
	{
		$attributes = $parent->getParent()->manifest->xpath('layouts');
		if (!is_array($attributes) || empty($attributes[0])) return;

		$destination = (!empty($attributes[0]->attributes()->destination)) ?
			(string) $attributes[0]->attributes()->destination : false;
		if (!$destination) return;

		$dest = JPATH_ROOT . '/layouts/' . trim($destination, '/');

		if (Folder::exists($dest))
		{
			Folder::delete($dest);
		}
	}

	/**
	 * Delete plugin folder if  exist.
	 *
	 * @since  1.0.0
	 */
	protected function deleteFolders()
	{
		foreach ($this->folders as $folder)
		{
			$path = JPATH_SITE . '/' . $folder;
			if (Folder::exists($path))
			{
				Folder::delete($path);
			}
		}
	}
}