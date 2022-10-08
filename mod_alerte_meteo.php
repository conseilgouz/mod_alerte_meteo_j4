<?php
/**
* Alerte Météo
* Version			: 2.0.4
* Package			: Joomla 3.10.x and 4.x.x
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Version;
use ConseilGouz\Module\AlerteMeteo\Site\Helper\AlerteMeteoHelper;
$moduleclass_sfx = "";
if ($params->get('moduleclass_sfx')) {
	$moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
}
$j = new Version();
$version=substr($j->getShortVersion(), 0,1); 
if ($version != "4") { // Joomla 3.10 ?
	JLoader::registerNamespace('ConseilGouz\Module\AlerteMeteo\Site', JPATH_SITE . '/modules/mod_alerte_meteo/src', false, false, 'psr4');
}

$meteo = new AlerteMeteoHelper($params,"Etats de vigilance météorologique des départements (métropole et outre-mer) et territoires d'outre-mer français");
$alertes = $meteo->DonneesVigilance();

require ModuleHelper::getLayoutPath('mod_alerte_meteo', $params->get('layout', 'default'));
