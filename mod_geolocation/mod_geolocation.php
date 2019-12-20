<?php
/**
 * @package    mod_geolocation
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Helper\ModuleHelper;

defined('_JEXEC') or die;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

require_once __DIR__ . '/helper.php';
require ModuleHelper::getLayoutPath('mod_geolocation', $params->get('layout', 'default'));
