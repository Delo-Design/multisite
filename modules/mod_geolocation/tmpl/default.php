<?php
/**
 * @package    testmodule
 *
 * @author     tsymb <your@email.com>
 * @copyright  A copyright
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @link       http://your.url.com
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die;

$find            = ModGeolocationHelper::findRoute();
$list            = ModGeolocationHelper::getAllSubdomains();
$active          = ModGeolocationHelper::getActive();
$domain          = ModGeolocationHelper::getDomain();
$config          = Factory::getConfig();
$https           = (int) $config->get('force_ssl', 0) === 2 ? 'https://' : 'http://';
$list_subdomains = $params->get('subdomains', []);
$list_enables    = [];
foreach ($list_subdomains as $list_subdomain)
{
	$list_enables[$list_subdomain->subdomain] = (int) $list_subdomain->enable;
}

if (
	isset($list_enables[$active->subdomain]) &&
	!$list_enables[$active->subdomain])
{
	return;
}

?>

<div class="uk-inline geolocation">
    <i class="location-icon" data-uk-icon="icon: location-full;"></i>
    <span>Ваш город</span>
	<?php
	if (
		($find && !isset($list_enables[$find->subdomain])) ||
		($find && isset($list_enables[$find->subdomain]) && $list_enables[$find->subdomain])
	) :
		?>
        <div class="uk-inline">
            <button class="uk-button uk-hidden" type="button"></button>
            <div class="find-regionsorcity" data-uk-dropdown="mode: click">
                <div class="question">Это ваш город?</div>
                <div class="city"><?php echo $find->name ?></div>
                <div class="buttons uk-grid-small" data-uk-grid>
                    <div class="uk-width-auto">
						<?php if ((int) $find->default) : ?>
                            <a href="<?php echo $https ?><?php echo $domain ?>"
                               class="uk-button uk-button-primary uk-width-1-1">Да</a>
						<?php else : ?>
                            <a href="<?php echo $https ?><?php echo $find->subdomain ?>.<?php echo $domain ?>"
                               class="uk-button uk-button-primary uk-width-1-1">Да</a>
						<?php endif; ?>
                    </div>
                    <div class="uk-width-expand">
                        <button class="uk-button uk-button-only-border button-change-city uk-width-1-1">Выбрать другой
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">

            setTimeout(function () {
                UIkit.dropdown('.find-regionsorcity').show();
            }, 300);

            document.querySelector('.button-change-city').addEventListener('click', function (ev) {
                document.querySelector('.find-regionsorcity').remove();
                UIkit.dropdown('.list-subdomains').show();
                ev.preventDefault();
            });

        </script>
	<?php endif; ?>
    <div class="uk-inline">
        <button class="uk-button uk-button-text" type="button"><?php echo $active->name ?> <i
                    data-uk-icon="icon: chevron-down;ratio:0.8"></i></button>
        <div class="list-subdomains" data-uk-dropdown="mode: click">
            <ul class="uk-nav uk-dropdown-nav">
				<?php foreach ($list as $item) : ?>
					<?php
					if (
						isset($list_enables[$item->subdomain]) &&
						!$list_enables[$item->subdomain]
					)
					{
						continue;
					}
					?>

					<?php if ((int) $item->default) : ?>
                        <li><a href="<?php echo $https ?><?php echo $domain ?>"><?php echo $item->name ?></a></li>
					<?php else : ?>
                        <li>
                            <a href="<?php echo $https ?><?php echo $item->subdomain ?>.<?php echo $domain ?>"><?php echo $item->name ?></a>
                        </li>
					<?php endif; ?>
				<?php endforeach; ?>
            </ul>
        </div>
    </div>
</div>


