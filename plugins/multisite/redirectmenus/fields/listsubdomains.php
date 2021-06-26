<?php defined('_JEXEC') or die;

JFormHelper::loadFieldClass('subform');
JLoader::register('ConfigHelper', JPATH_PLUGINS . '/system/multisiteswitch/helper/ConfigHelper.php');


class JFormFieldListsubdomains extends JFormFieldSubform
{

	public function getInput()
	{

		$subdomains = ConfigHelper::get('subdomains', []);
		$i          = 0;
		foreach ($subdomains as $subdomain)
		{
			$scopesForInput['subdomains' . $i] = [
				'subdomain' => $subdomain->subdomain,
				'name'      => $subdomain->name,
				'enable'    => 1,
			];
			$i++;
		}

		return parent::getInput();
	}


}