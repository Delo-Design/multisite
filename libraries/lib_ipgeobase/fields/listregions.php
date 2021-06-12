<?php
/**
 * @package     Joomla.Legacy
 * @subpackage  Form
 *
 * @copyright   Copyright (C) 2005 - 2018 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormHelper;
use Joomla\CMS\HTML\HTMLHelper;

defined('JPATH_PLATFORM') or die;

FormHelper::loadFieldClass('text');

/**
 * Form Field class for the Joomla Platform.
 * Supports an HTML select list of categories
 *
 * @since  1.6
 */
class JFormFieldListregions extends JFormFieldText
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  1.6
     */
    public $type = 'ListRegions';

    /**
     *
     * @return string
     *
     * @since version
     */
    public function getInput()
    {
        HTMLHelper::_( 'jquery.framework' );
        $this->class .= " list-region";

        HTMLHelper::script('lib_ipgeobase/listregions.js', [
            'version' => filemtime ( __FILE__ ),
            'relative' => true,
        ]);

        HTMLHelper::_('stylesheet', 'lib_ipgeobase/autocomplete.css', [
            'version' => filemtime ( __FILE__ ),
            'relative' => true,
        ]);

        HTMLHelper::_('script','lib_ipgeobase/autocomplete.js', [
            'version' => filemtime ( __FILE__ ),
            'relative' => true,
        ]);

        return parent::getInput();
    }

}
