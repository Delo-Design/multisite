<?php


defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Utility class for categories
 *
 * @since  1.5
 */
abstract class JHtmlCity
{
    /**
     * Cached array of the category items.
     *
     * @var    array
     * @since  1.5
     */
    protected static $items = array();

    /**
     * Returns an array of categories for the given extension.
     *
     * @param   string  $extension  The extension option e.g. com_something.
     * @param   array   $config     An array of configuration options. By default, only
     *                              published and unpublished categories are returned.
     *
     * @return  array
     *
     * @since   1.5
     */
    public static function options($config = [])
    {
        $hash = md5('lib_ipgeobase' . '.' . serialize($config));

        if (!isset(static::$items[$hash]))
        {
            $config = (array) $config;
            $db     = Factory::getDbo();
            $user   = Factory::getUser();
            $groups = implode(',', $user->getAuthorisedViewLevels());

            $query = $db->getQuery(true)
                ->select("cit.id, cit.title as title")
                ->from('#__lib_ipgeobase_cities AS cit');


            // Filter on the language
            if (isset($config['filter.q']))
            {
                $search = $db->Quote( '%' . $db->escape( $config['filter.q'], true ) . '%' );

                $query->where("title LIKE " . $search);
            }

            // Filter on the language
            if (isset($config['filter.limit']))
            {
                $query->setLimit((int)$config['filter.limit']);
            }

            // Filter on the language
            if (isset($config['filter.ids']))
            {
                $ids = $db->escape( implode(',', $config['filter.ids']), true );
                $query->where("cit.id in (" . $ids . ')');
            }

            $query->order("title ASC");

            $db->setQuery($query);
            $items = $db->loadObjectList();

            // Assemble the list options.
            static::$items[$hash] = array();

            foreach ($items as &$item)
            {
                static::$items[$hash][] = HTMLHelper::_('select.option', $item->id, $item->title);
            }
        }

        return static::$items[$hash];
    }


}
