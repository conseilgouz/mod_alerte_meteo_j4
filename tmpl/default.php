<?php
/**
* Alerte M�t�o
* Version			: 2.0.1
* Package			: Joomla 4.x.x
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/

defined('_JEXEC') or die;
use Joomla\CMS\Filter\OutputFilter;

$alerte = "";
$niveau ="";
$colors = array('Bleu','<span style="background-color:green;">','<span style="background-color:yellow;">','<span style="background-color:orange;">','<span style="background-color:red;">','Violet','Gris');
$deps = array("01" => "Ain" ,"02" => "Aisne","03" => "Allier","04" => "Alpes de Haute Provence","05" => "Hautes Alpes","06" => "Alpes Maritimes","07" => "Ard�che","08" => "Ardennes","09" => "Ari�ge","10" => "Aube","11" => "Aude","12" => "Aveyron","13" => "Bouches du Rh�ne","14" => "Calvados","15" => "Cantal","16" => "Charente","17" => "Charente Maritime","18" => "Cher","19" => "Corr�ze","2A" => "Corse du Sud","2B" => "Haute Corse","21" => "C�te d'Or","22" => "C�tes d'Armor","23" => "Creuse","24" => "Dordogne","25" => "Doubs","26" => "Dr�me","27" => "Eure","28" => "Eure et Loir","29" => "Finist�re","30" => "Gard","31" => "Haute Garonne","32" => "Gers","33" => "Gironde","34" => "H�rault","35" => "Ille et Vilaine","36" => "Indre","37" => "Indre et Loire","38" => "Is�re","39" => "Jura","40" => "Landes","41" => "Loir et Cher","42" => "Loire","43" => "Haute Loire","44" => "Loire Atlantique","45" => "Loiret","46" => "Lot","47" => "Lot et Garonne","48" => "Loz�re","49" => "Maine et Loire","50" => "Manche","51" => "Marne","52" => "Haute Marne","53" => "Mayenne","54" =>"Meurthe et Moselle","55" => "Meuse","56" => "Morbihan","57" => "Moselle","58" => "Ni�vre","59" => "Nord","60" => "Oise","61" => "Orne","62" => "Pas de Calais","63" => "Puy de D�me","64" => "Pyr�n�es Atlantiques","65" => "Hautes Pyr�n�es","66" => "Pyr�n�es Orientales","67" => "Bas Rhin","68" => "Haut Rhin","69" => "Rh�ne","70" => "Haute Sa�ne","71" => "Sa�ne et Loire","72" => "Sarthe","73" => "Savoie","74" => "Haute Savoie","75" => "Paris et petite couronne","76" => "Seine Maritime","77" => "Seine et Marne","78" => "Yvelines","79" => "Deux S�vres","80" => "Somme","81" =>"Tarn","82" => "Tarn et Garonne","83" => "Var","84" => "Vaucluse","85" =>"Vend�e","86" => "Vienne","87" =>"Haute Vienne","88" =>"Vosges","89" => "Yonne","90" => "Territoire de Belfort","91" => "Essonne","95" => "Val d'Oise","99" => "Andorre");

$level_colors = array('Bleu','Vert','Jaune','Orange','Rouge','Violet','Gris');

// r�cup�ration des param�tres du module
$departement = $params->get('departement', '01');
$aff_minicarte = $params->get('aff_minicarte', 'false');
$aff_vide = $params->get('aff_vide', 'false');

// r�cup�ration des alertes du d�partement
if (!empty($alertes)) {
    $balerte = false;
	$bcrue = false;
    $jsonIterator = new RecursiveIteratorIterator(
    new RecursiveArrayIterator(json_decode($alertes, TRUE)),
    RecursiveIteratorIterator::SELF_FIRST);
	foreach ($jsonIterator as $k => $v) {
			if(is_array($v)) {
				foreach ($v as $k2 => $v2) {
				    if (($k2 == "niveau")) { 
				    	if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
							$niveau = (string)$v2;
				    	}
						if (strlen($niveau) == 0) $niveau = 0;
					}
					if (($k2 == "alerte") && ($niveau > 1)) {
					    $balerte = true;
					    if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
							$alerte =  $colors[$niveau].$alerte."Alerte $v2: ";
					    }
					}
					if (($k2 =="risque") && ($niveau > 1)) { // suivi des risques de crue
						if (!is_array($v2)) { // 1.0.12: ignore $v2 si array
							$alerte =  $alerte."$v2</span><br/>";
						}
					}
				}
			}
		}	
//	}
}
if  (($aff_minicarte == 'true') || (($aff_minicarte == 'alert') && (!empty($alerte)))){ 
		$url = "https://vigilance2019.meteofrance.com/data/QGFR08_LFPW_.gif";
?>
<p class="alerte_meteo_minicarte">
   <a href="//vigilance.meteofrance.com/" target="_blank" rel="noopener noreferrer"><img src = "<?php echo $url;?>" alt="Mini carte" style="display: block;margin-left: auto;margin-right: auto;"></a>
</p>
<?php } ?>
<?php if (!empty($alerte)) { ?>
	<p class="alerte-meteo-module<?php echo $moduleclass_sfx; ?>" style="text-align:center;color: <?php echo $params->get('font-color', '#000');?>; ">
		<span class="alerte-meteo-msg">Alerte m&eacute;t&eacute;o en cours</span><br/><?php 
		    echo $alerte;
			if ($balerte) {
				$dep = OutputFilter::stringURLSafe(str_replace("'", '-',$deps[$departement]));
		        echo '<a href="https://vigilance.meteofrance.fr/fr/'.$dep.'" target="_blank" rel="noopener noreferrer">Voir le bulletin d\'alerte</a><br/>';
			}
			if ($bcrue) {
		        echo '<a href="https://www.vigicrues.gouv.fr/" target="_blank" rel="noopener noreferrer">Voir le bulletin d\'alerte vigicrue</a><br/>';
			}

			?>
	</p>
<?php } else {
    if ($aff_vide == 'true') { echo "<p class='cg_noalert' style='text-align:center'>Pas d'alerte en cours sur le d&eacute;partement ".$departement."-".$deps[$departement]."</p>"; }
  } 
 ?>