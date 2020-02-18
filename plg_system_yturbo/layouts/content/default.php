<?php
/**
 * @package    DachaDacha Package
 * @version    __DEPLOY_VERSION__
 * @author     Septdir Workshop - septdir.com
 * @copyright  Copyright (c) 2018 - 2020 Septdir Workshop. All rights reserved.
 * @license    GNU/GPL license: https://www.gnu.org/copyleft/gpl.html
 * @link       https://www.septdir.com/
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filter\InputFilter;

$item = $displayData;

if (empty(trim(strip_tags($item->text)))) return false;

// Fix headers
$headers = array(
	'h1' => 'h2',
	'h2' => 'h3',
	'h3' => 'h4',
	'h4' => 'h3',
	'h5' => 'h6',
	'h6' => 'div',
);
if (preg_match('/<h1/', $item->text))
{
	foreach (array_reverse($headers) as $old => $new)
	{
		$item->text = str_replace('<' . $old, '<' . $new, $item->text);
		$item->text = str_replace('</' . $old, '</' . $new, $item->text);
	}
}
if (preg_match('/<h2/', $item->text))
{
	foreach (array_reverse($headers) as $old => $new)
	{
		$item->text = str_replace('<' . $old, '<' . $new, $item->text);
		$item->text = str_replace('</' . $old, '</' . $new, $item->text);
	}
}

// Clean content
$tags    = array('br', 'img', 'script', 'meta', 'iframe', 'hr', 'source', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'p', 'a',
	'table', 'th', 'tr', 'td', 'figure', 'ul', 'li', 'code', 'pre');
$attrs   = array('href', 'src', 'http-equiv', 'content', 'charset');
$filter  = new InputFilter($tags, $attrs);
$content = $filter->clean($item->text, 'html');

// Correct links
$pattern     = array('/href\s*=\s*\"\s*(?!http:\/\/|https:\/\/|ftp:\/\/|mailto:)/', '/src\s*=\s*\"\s*(?!http:\/\/|https:\/\/|ftp:\/\/)/');
$replacement = array('href="' . $item->root . '/', 'src="' . $item->root . '/');
$content     = preg_replace($pattern, $replacement, $content);
$content     = htmlspecialchars_decode($content);
?>
	<header>
		<h1><?php echo $item->sitename; ?></h1>
		<h2><?php echo $item->title; ?></h2>
		<?php if ($intro = $item->images->get('image_intro')): ?>
			<figure>
				<img src="<?php echo $item->root . '/' . $intro; ?>"/>
			</figure>
		<?php endif; ?>
	</header>
<?php echo $content; ?>