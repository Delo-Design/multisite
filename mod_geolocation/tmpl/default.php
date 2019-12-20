<?php
/**
 * @package    testmodule
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

defined('_JEXEC') or die;

$find = ModGeolocationHelper::findRoute();
?>

<?php if($find) : ?>
	<div class="uk-inline">
        <span>Ваш город</span>
		<button class="uk-button uk-button-text" type="button">Москва <i data-uk-icon="icon: chevron-down"></i></button>
		<div class="find-regionsorcity" data-uk-dropdown="mode: click">
			<p class="uk-text-large">Найден сайт для вашего региона</p>
			<div>
				<a href="http://<?php echo $_SERVER['SERVER_NAME'] ?>?redirectDomain=<?php echo $find ?>" class="uk-button uk-button-primary">Перейти на сайт</a>
			</div>
		</div>
	</div>
	<script type="text/javascript">
        //UIkit.dropdown('.find-regionsorcity').show();
	</script>
<?php endif; ?>
