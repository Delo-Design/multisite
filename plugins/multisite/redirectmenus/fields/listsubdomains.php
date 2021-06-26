<?php defined('_JEXEC') or die;

JFormHelper::loadFieldClass('subform');
JLoader::register('ConfigHelper', JPATH_PLUGINS . '/system/multisiteswitch/helpers/ConfigHelper.php');


class JFormFieldListsubdomains extends JFormFieldSubform
{

	public function getInput()
	{
		$subdomains = ConfigHelper::get('subdomains', []);
		$i          = 0;
		$values     = [];

		foreach ($subdomains as $subdomain)
		{
			$values['subdomains' . $i] = [
				'subdomain' => $subdomain->subdomain,
				'name'      => $subdomain->name,
				'enable'    => 1,
			];
			$i++;
		}

		if (!empty($this->value))
		{
			foreach ($values as &$value)
			{
				foreach ($this->value as $value_saved)
				{

					if ($value['subdomain'] === $value_saved['subdomain'])
					{
						$value['enable'] = $value_saved['enable'];
					}

				}

			}
		}


		$this->value = $values;

		return parent::getInput();
	}


}