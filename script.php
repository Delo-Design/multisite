<?php

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerAdapter;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Version;

class pkg_multisiteInstallerScript
{
	/**
	 * Minimum PHP version required to install the extension.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $minimumPhp = '7.0';

	/**
	 * Minimum Joomla version required to install the extension.
	 *
	 * @var  string
	 *
	 * @since  1.0.0
	 */
	protected $minimumJoomla = '3.9.0';

	/**
	 * Runs right before any installation action.
	 *
	 * @param   string            $type    Type of PostFlight action.
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @return  boolean  Compatible current version or not.
	 *
	 * @since  1.0.0
	 */
	function preflight($type, $parent)
	{
		// Check compatible
		if (!$this->checkCompatible()) return false;

		// Download remote source
		$this->downloadRemotes($parent);
	}

	/**
	 * Method to download remotes.
	 *
	 * @param   InstallerAdapter  $parent  Parent object calling object.
	 *
	 * @throws  Exception
	 *
	 * @since  1.0.0
	 */
	protected function downloadRemotes($parent)
	{
		$attributes = $parent->getParent()->manifest->xpath('files');
		$source     = $parent->getParent()->getPath('source');

		if (!is_array($attributes) || empty($attributes[0])) return;
		foreach ($attributes[0] as $type => $value)
		{
			if (!empty($value->attributes()->download) && !empty($value[0]))
			{
				$src  = htmlspecialchars_decode((string) $value->attributes()->download);
				$dest = $source . '/' . $value[0];
				@file_put_contents($dest, file_get_contents($src));
			}
		}
	}

	/**
	 * Method to check compatible.
	 *
	 * @throws  Exception
	 *
	 * @return  bool True if compatible.
	 *
	 * @since  1.0.0
	 */
	protected function checkCompatible()
	{
		// Check old Joomla
		if (!class_exists('Joomla\CMS\Version'))
		{
			JFactory::getApplication()->enqueueMessage(JText::sprintf('PKG_MULTISITE_ERROR_COMPATIBLE_JOOMLA',
				$this->minimumJoomla), 'error');

			return false;
		}

		$app      = Factory::getApplication();
		$jversion = new Version();

		// Check PHP
		if (!(version_compare(PHP_VERSION, $this->minimumPhp) >= 0))
		{
			$app->enqueueMessage(Text::sprintf('PKG_MULTISITE_ERROR_COMPATIBLE_PHP', $this->minimumPhp),
				'error');

			return false;
		}

		// Check joomla version
		if (!$jversion->isCompatible($this->minimumJoomla))
		{
			$app->enqueueMessage(Text::sprintf('PKG_MULTISITE_ERROR_COMPATIBLE_JOOMLA', $this->minimumJoomla),
				'error');

			return false;
		}

		return true;
	}
}