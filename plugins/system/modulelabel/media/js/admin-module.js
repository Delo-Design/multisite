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
		// Add  labels to head
		$('#jform_labels').closest('.control-group').appendTo('.form-inline.form-inline-header');

		// Remove labels tab
		var removeLabelsTab = setInterval(function () {
			var labels = $('#myTabTabs').find('a[href="#attrib-labels"]');
			if ($(labels).length > 0) {
				$(labels).parent().remove();
				clearInterval(removeLabelsTab);
			}
		}, 3);
	});
})(jQuery);