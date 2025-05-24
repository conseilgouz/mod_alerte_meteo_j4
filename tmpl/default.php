<?php
/**
* Alerte Météo
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

defined('_JEXEC') or die;
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Uri\Uri;
use ConseilGouz\Module\AlerteMeteo\Site\Helper\AlerteMeteoHelper;

$alerte = "";
$niveau = "";

$deps = array("01" => "Ain" ,"02" => "Aisne","03" => "Allier","04" => "Alpes de Haute Provence","05" => "Hautes Alpes","06" => "Alpes Maritimes","07" => "Ard&egrave;che","08" => "Ardennes","09" => "Ari&egrave;ge","10" => "Aube","11" => "Aude","12" => "Aveyron","13" => "Bouches du Rh&ocirc;ne","14" => "Calvados","15" => "Cantal","16" => "Charente","17" => "Charente Maritime","18" => "Cher","19" => "Corr&egrave;ze","2A" => "Corse du Sud","2B" => "Haute Corse","21" => "C�te d'Or","22" => "C&ocirc;tes d'Armor","23" => "Creuse","24" => "Dordogne","25" => "Doubs","26" => "Dr�me","27" => "Eure","28" => "Eure et Loir","29" => "Finist&egrave;re","30" => "Gard","31" => "Haute Garonne","32" => "Gers","33" => "Gironde","34" => "H&eacute;rault","35" => "Ille et Vilaine","36" => "Indre","37" => "Indre et Loire","38" => "Is&egrave;re","39" => "Jura","40" => "Landes","41" => "Loir et Cher","42" => "Loire","43" => "Haute Loire","44" => "Loire Atlantique","45" => "Loiret","46" => "Lot","47" => "Lot et Garonne","48" => "Loz&egrave;re","49" => "Maine et Loire","50" => "Manche","51" => "Marne","52" => "Haute Marne","53" => "Mayenne","54" => "Meurthe et Moselle","55" => "Meuse","56" => "Morbihan","57" => "Moselle","58" => "Ni&egrave;vre","59" => "Nord","60" => "Oise","61" => "Orne","62" => "Pas de Calais","63" => "Puy de D&ocirc;me","64" => "Pyr&eacute;n&eacute;es Atlantiques","65" => "Hautes Pyr&eacute;n&eacute;es","66" => "Pyr&eacute;n&eacute;es Orientales","67" => "Bas Rhin","68" => "Haut Rhin","69" => "Rh&ocirc;ne","70" => "Haute Sa&ocirc;ne","71" => "Sa&ocirc;ne et Loire","72" => "Sarthe","73" => "Savoie","74" => "Haute Savoie","75" => "Paris et petite couronne","76" => "Seine Maritime","77" => "Seine et Marne","78" => "Yvelines","79" => "Deux S&egrave;vres","80" => "Somme","81" => "Tarn","82" => "Tarn et Garonne","83" => "Var","84" => "Vaucluse","85" => "Vend&eacute;e","86" => "Vienne","87" => "Haute Vienne","88" => "Vosges","89" => "Yonne","90" => "Territoire de Belfort","91" => "Essonne","95" => "Val d'Oise","99" => "Andorre");
$deps_noaccent = array("01" => "Ain" ,"02" => "Aisne","03" => "Allier","04" => "Alpes de Haute Provence","05" => "Hautes Alpes","06" => "Alpes Maritimes","07" => "Ardeche","08" => "Ardennes","09" => "Ariege","10" => "Aube","11" => "Aude","12" => "Aveyron","13" => "Bouches du Rhone","14" => "Calvados","15" => "Cantal","16" => "Charente","17" => "Charente Maritime","18" => "Cher","19" => "Correze","2A" => "Corse du Sud","2B" => "Haute Corse","21" => "C�te d'Or","22" => "Cotes d'Armor","23" => "Creuse","24" => "Dordogne","25" => "Doubs","26" => "Drome","27" => "Eure","28" => "Eure et Loir","29" => "Finistere","30" => "Gard","31" => "Haute Garonne","32" => "Gers","33" => "Gironde","34" => "Herault","35" => "Ille et Vilaine","36" => "Indre","37" => "Indre et Loire","38" => "Isere","39" => "Jura","40" => "Landes","41" => "Loir et Cher","42" => "Loire","43" => "Haute Loire","44" => "Loire Atlantique","45" => "Loiret","46" => "Lot","47" => "Lot et Garonne","48" => "Lozere","49" => "Maine et Loire","50" => "Manche","51" => "Marne","52" => "Haute Marne","53" => "Mayenne","54" => "Meurthe et Moselle","55" => "Meuse","56" => "Morbihan","57" => "Moselle","58" => "Nievre","59" => "Nord","60" => "Oise","61" => "Orne","62" => "Pas de Calais","63" => "Puy de Dome","64" => "Pyrenees Atlantiques","65" => "Hautes Pyrenees","66" => "Pyrenees Orientales","67" => "Bas Rhin","68" => "Haut Rhin","69" => "Rhone","70" => "Haute Saone","71" => "Saone et Loire","72" => "Sarthe","73" => "Savoie","74" => "Haute Savoie","75" => "Paris et petite couronne","76" => "Seine Maritime","77" => "Seine et Marne","78" => "Yvelines","79" => "Deux Sevres","80" => "Somme","81" => "Tarn","82" => "Tarn et Garonne","83" => "Var","84" => "Vaucluse","85" => "Vendee","86" => "Vienne","87" => "Haute Vienne","88" => "Vosges","89" => "Yonne","90" => "Territoire de Belfort","91" => "Essonne","95" => "Val d'Oise","99" => "Andorre");

$fontcolor = $params->get('font-type', 'pick');
if ($params->get('font-type', 'pick') == 'pick') {
    $fontcolor = $params->get('font-color', 'black');
$colors = array('Bleu','<span style="background-color:green">'
                      ,'<span style="background-color:yellow">'
                      ,'<span style="background-color:orange">'
                      ,'<span style="background-color:red">'
                      ,'Violet','Gris');
} else {
    $fontcolor = 'var('.$params->get('font-var', '--bs-body-color').')';
    $colors = array('Bleu','<span style="background-color:var(--bs-green);color:var(--bs-dark)">'
                      ,'<span style="background-color:var(--bs-yellow);color:var(--bs-dark)">'
                      ,'<span style="background-color:var(--bs-orange);color:var(--bs-dark)">'
                      ,'<span style="background-color:var(--bs-red);color:var(--bs-dark)">'
                      ,'Violet','Gris');
}

$level_colors = array('Bleu','Vert','Jaune','Orange','Rouge','Violet','Gris');

// récupération des paramètres du module
$departement = $params->get('departement', '01');
$aff_minicarte = $params->get('aff_minicarte', 'false');
$aff_vide = $params->get('aff_vide', 'false');

$moduleclass_sfx = "";
if ($params->get('moduleclass_sfx')) {
    $moduleclass_sfx = htmlspecialchars($params->get('moduleclass_sfx'));
}

$meteo = new AlerteMeteoHelper($params, "Etats de vigilance météorologique des départements (métropole et outre-mer) et territoires d'outre-mer français");
$alertes = $meteo->DonneesVigilance();

// affichage des alertes du département
if (!empty($alertes)) {
    $balerte = false;
    $bcrue = false;
    $jsonIterator = new RecursiveIteratorIterator(
        new RecursiveArrayIterator(json_decode($alertes, true)),
        RecursiveIteratorIterator::SELF_FIRST
    );
    foreach ($jsonIterator as $k => $v) {
        if(is_array($v)) {
            foreach ($v as $k2 => $v2) {
                if (($k2 == "niveau")) {
                    if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
                        $niveau = (string)$v2;
                    }
                    if (strlen($niveau) == 0) {
                        $niveau = 0;
                    }
                }
                if (($k2 == "alerte") && ($niveau > 1)) {
                    $balerte = true;
                    if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
                        $alerte =  $colors[$niveau].$alerte."Alerte $v2: ";
                    }
                }
                if (($k2 == "risque") && ($niveau > 1)) { // suivi des risques de crue
                    if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
                        $alerte =  $alerte."$v2</span><br/>";
                    }
                }
            }
        }
    }
    //	}
}
if  (($aff_minicarte == 'true') || (($aff_minicarte == 'alert') && (!empty($alerte)))) {
    $url = $meteo->getImage();
    ?>
<p class="alerte_meteo_minicarte">
   <a href="//vigilance.meteofrance.com/" target="_blank" rel="noopener noreferrer"><img src = "<?php echo Uri::root().$url;?>" alt="Mini carte" style="display: block;margin-left: auto;margin-right: auto;max-width:<?php echo $params->get('max-width', '8');?>em"></a>
</p>
<?php } ?>
<?php if (!empty($alerte)) { ?>
	 <p class="alerte-meteo-module<?php echo $moduleclass_sfx; ?>" style="text-align:center;color: <?php echo $fontcolor;?>; ">
		<span class="alerte-meteo-msg">Alerte m&eacute;t&eacute;o en cours</span><br/><?php
                echo $alerte;
    if ($balerte) {
        $dep = OutputFilter::stringURLSafe(str_replace("'", '-', $deps_noaccent[$departement]));
        echo '<a href="https://vigilance.meteofrance.fr/fr/'.$dep.'" target="_blank" rel="noopener noreferrer">Voir le bulletin d\'alerte</a><br/>';
    }
    if ($bcrue) {
        echo '<a href="https://www.vigicrues.gouv.fr/" target="_blank" rel="noopener noreferrer">Voir le bulletin d\'alerte vigicrue</a><br/>';
    }

    ?>
	</p>
<?php } else {
    if ($aff_vide == 'true') {
        echo "<p class='cg_noalert' style='text-align:center'>Pas d'alerte en cours sur le d&eacute;partement ".$departement."-".$deps[$departement]."</p>";
    }
}
?>
