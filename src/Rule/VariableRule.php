<?php
/**
* Alerte Météo
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2025 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Module\AlerteMeteo\Site\Rule;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Form\FormRule;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

class VariableRule extends FormRule
{
    public function test(\SimpleXMLElement $element, $value, $group = null, ?Registry $input = null, ?Form $form = null)
    {
        // get showon to find field name
        $showon = (string)$element['showon'];
        $el = explode(':', $showon);
        $type = $el[0];
        $params = $input->get('params');
        if ($params->$type  == 'pick') { // color picker : exit
            return true;
        }
        if (!$value) {
            Factory::getApplication()->enqueueMessage('La zone ne peut pas être vide.', 'error');
            return false;
        }
        if (substr($value, 0, 2) != '--') {
            Factory::getApplication()->enqueueMessage('Une couleur variable doit commencer par --', 'error');
            return false;
        }
        return true;

    }
}
