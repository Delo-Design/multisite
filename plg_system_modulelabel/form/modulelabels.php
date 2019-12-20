<?php
/**
 * @package    System - Module Label Plugin
 * @version    1.1.0
 * @author     Nerudas  - nerudas.ru
 * @copyright  Copyright (c) 2013 - 2018 Nerudas. All rights reserved.
 * @license    GNU/GPL license: http://www.gnu.org/copyleft/gpl.html
 * @link       https://nerudas.ru
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Form\Field\TagField;

class JFormFieldModuleLabels extends TagField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  1.0.0
	 */
	public $type = 'ModuleLabels';

	/**
	 * The options array
	 *
	 * @var    array
	 * @since  1.0.0
	 */
	protected $options = null;

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement $element   The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed            $value     The form field value to validate.
	 * @param   string           $group     The field name group control value. This acts as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 *
	 * @since   1.0.0
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		$this->value = (!is_array($this->value)) ? (array) $this->value : $this->value;

		return $return;
	}

	/**
	 * Method to get the field options.
	 *
	 * @return  array  The field option objects.
	 *
	 * @since  1.0.0
	 */
	protected function getOptions()
	{
		if (!is_array($this->options))
		{
			$db    = Factory::getDbo();
			$query = $db->getQuery(true)
				->select('title')
				->from('#__modules');
			$db->setQuery($query);
			$titles = $db->loadColumn();

			$labels = array();
			preg_match_all('/\[.*?]/', implode(' ', $titles), $matches);
			if (!empty($matches[0]))
			{
				foreach ($matches[0] as $label)
				{
					$labels[] = trim(str_replace(array('[', ']'), '', $label));
				}
			}

			$options = array();
			foreach (array_unique($labels) as $label)
			{
				$option           = new stdClass();
				$option->value    = $label;
				$option->text     = $label;
				$option->selected = (in_array($label, $this->value));

				$options[] = $option;
			}

			$this->options = $options;
		}

		return $this->options;
	}
}