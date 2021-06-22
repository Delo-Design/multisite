<?php use Joomla\CMS\HTML\HTMLHelper;

defined('_JEXEC') or die;


class Yootheme1xHelper
{

	public static function set($menu)
	{
		$theme = HTMLHelper::_('theme');
		$theme->set('menu.positions.navbar', $menu);
		$theme->set('menu.positions.mobile', $menu);
	}

}

