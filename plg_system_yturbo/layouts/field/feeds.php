<?php
/**
 * @package    DachaDacha Package
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

use Joomla\CMS\Language\Text;

defined('_JEXEC') or die;

extract($displayData);

/**
 * Layout variables
 * -----------------
 *
 * @var  array $feeds Feeds list.
 */

?>
<div class="help-inline">
	<?php if (!empty($feeds)): ?>
		<ul class="unstyled">
			<?php foreach ($feeds as $feed): ?>
				<li><a href="<?php echo $feed; ?>" target="_blank"><?php echo $feed; ?></a></li>
			<?php endforeach; ?>
		</ul>
	<?php else: ?>
		<div class="text-error"><?php echo Text::_('PLG_SYSTEM_YTURBO_ERROR_FEEDS_NOT_FOUND'); ?></div>
	<?php endif; ?>
</div>