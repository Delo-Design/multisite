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
$list = ModGeolocationHelper::getAllSubdomains();
$active = ModGeolocationHelper::getActive();
?>

<div class="uk-inline">
    <span><i data-uk-icon="icon: location;"></i> Ваш город</span>
	<?php if($find) : ?>
    <div class="uk-inline">
        <div class="find-regionsorcity" data-uk-dropdown="mode: click">
            <div class="uk-text-primary">Это ваш город?</div>
            <div><?php echo $find->name ?></div>
            <div>
                <a href="http://<?php echo $_SERVER['SERVER_NAME'] ?>?redirectDomain=<?php echo $find->subdomain ?>" class="uk-button uk-button-primary">Да</a>
                <button class="uk-button button-change-city">Выбрать другой</button>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        //UIkit.dropdown('.find-regionsorcity').show();

        document.querySelector('.button-change-city').addEventListener('click', function (ev) {
            document.querySelector('.find-regionsorcity').remove();
            UIkit.dropdown('.list-subdomains').show();
            ev.preventDefault();
        });

    </script>
    <?php endif; ?>
    <div class="uk-inline">
        <button class="uk-button uk-button-text" type="button"><?php echo $active->name ?> <i data-uk-icon="icon: chevron-down"></i></button>
        <div class="list-subdomains" data-uk-dropdown="mode: click">
            <ul class="uk-nav uk-dropdown-nav">
                <?php foreach ($list as $item) : ?>
                <li><a href="http://<?php echo $_SERVER['SERVER_NAME'] ?>?redirectDomain=<?php echo $item->subdomain ?>"><?php echo $item->name ?></a></li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>


