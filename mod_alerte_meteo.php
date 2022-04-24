<?php
/**
* Alerte Météo
* Version			: 2.0.1
* Package			: Joomla 4.x.x
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use ConseilGouz\Module\AlerteMeteo\Site\Helper\AlerteMeteoHelper;

$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));

$meteo = new AlerteMeteoHelper($params,"Etats de vigilance météorologique des départements (métropole et outre-mer) et territoires d'outre-mer français");
$alertes = $meteo->DonneesVigilance();

require ModuleHelper::getLayoutPath('mod_alerte_meteo', $params->get('layout', 'default'));
