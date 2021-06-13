<?php defined('_JEXEC') or die;
/**
 * @package    multisiteswitch
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Factory;


/**
 * Multisiteswitch script file.
 *
 * @package   multisiteswitch
 * @since     1.0.0
 */
class plgSystemModulelabelInstallerScript
{


	public function postflight($type, $parent)
	{
		// Enable plugin
		if ($type === 'install')
		{
			$this->enablePlugin($parent);
		}

		return true;
	}


	protected function enablePlugin($parent)
	{
		// Prepare plugin object
		$plugin          = new stdClass();
		$plugin->type    = 'plugin';
		$plugin->element = $parent->getElement();
		$plugin->folder  = (string) $parent->getParent()->manifest->attributes()['group'];
		$plugin->enabled = 1;

		// Update record
		Factory::getDbo()->updateObject('#__extensions', $plugin, ['type', 'element', 'folder']);
	}
	
}
