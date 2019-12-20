/*
 * @package   System - Module Label Plugin
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

(function ($) {
	$(document).ready(function () {
		$('#moduleList').find('tr a').each(function () {
			var pattern = /\[(.*?)]/g,
				html = $(this).html();
			if (pattern.test(html)) {
				$(this).html(html.replace(pattern, '<span class="label label-inverse">$1</span>'));
			}
		});
	});
})(jQuery);
