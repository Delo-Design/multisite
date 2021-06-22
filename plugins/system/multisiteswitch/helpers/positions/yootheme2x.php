<?php defined('_JEXEC') or die;

use YOOtheme\Config;
use function YOOtheme\app;

class Yootheme2xHelper
{

	public static function set($menu)
	{
		app(Config::class)->set('~theme.menu.positions.navbar', $menu);
		app(Config::class)->set('~theme.menu.positions.mobile', $menu);
	}

}

