<?php defined('_JEXEC') or die;

JFormHelper::loadFieldClass('subform');
JLoader::register('ConfigHelper', JPATH_PLUGINS . '/system/multisiteswitch/helpers/ConfigHelper.php');


class JFormFieldListsubdomains extends JFormFieldSubform
{

	public function getInput()
	{
		$subdomains = ConfigHelper::get('subdomains', []);
		$i          = 0;
		$values     = $this->value;

		if (empty($values))
		{
			foreach ($subdomains as $subdomain)
			{
				$values['subdomains' . $i] = [
					'subdomain' => $subdomain->subdomain,
					'name'      => $subdomain->name,
					'enable'    => 1,
				];
				$i++;
			}
		}
		else
		{
			foreach ($values as &$value)
			{
				foreach ($subdomains as $subdomain)
				{

					if ($subdomain->subdomain === $value['subdomain'])
					{
						$value['name'] = $subdomain->name;
					}

				}

			}
		}


		$this->value = $values;

		return parent::getInput();
	}


}