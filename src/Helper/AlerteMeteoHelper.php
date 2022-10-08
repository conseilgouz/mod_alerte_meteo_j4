<?php
/**
* Alerte Météo
* Version			: 2.0.4
* Package			: Joomla 4.x.x
* copyright 		: Copyright (C) 2022 ConseilGouz. All rights reserved.
* license    		: http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
*/
namespace ConseilGouz\Module\AlerteMeteo\Site\Helper;
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class AlerteMeteoHelper
{
	private $DATA = array();
	private $HEADER = array();
	private $UPDATE;
	private $METEO_XML_DETAIL_URL;	
	private $METEO_MINI_CARTE_GIF;
	private $METEO_ZIP_FILE;
	private $METEO_DIR;
	private $DOM;
	private $RISQUE = array("","vent","pluie-inondation","orages","inondations","neige-verglas","canicule","grand-froid","avalanches","vagues-submersion","crues");
	private $DEP;
    private $application;
	private $departement;
    
	public function __construct($params)
	{
		$this->application = Factory::getApplication();
		$this->METEO_XML_DETAIL_URL = "http://vigilance2019.meteofrance.com/data/NXFR33_LFPW_.xml"; // Détail des alertes
		$this->METEO_MINI_CARTE_GIF = "http://vigilance2019.meteofrance.com/data/QGFR08_LFPW_.gif"; 
		$this->METEO_ZIP_FILE = "http://vigilance2019.meteofrance.com/data/vigilance.zip";
		$this->METEO_DIR = 'media/mod_alerte_meteo';
		$update = $this->ToUTF8("update");
		$updateval = $this->ToUTF8(date("d-m-Y H:i"));
		$this->UPDATE[$update] = $updateval;
		$this->departement = $params->get('departement', '01');
	}
	
    public function GetMeteoDir()
	{
		return $this->METEO_DIR;
	}

	public function DonneesVigilance()
	{
		try {
			$this->MetropoleDetailFormat();
		} catch (Exception $e) {
			$this->application->enqueueMessage('Erreur sur la récupération des informations météo', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
			return false;
		}
		// Fusion des tableaux d'entête et de données
		$this->SortAndMergeHeaderAndData();
		$root = $this->ToUTF8("vigilance");
		$arr = $this->DATA;
		$this->DATA = array();
		$this->DATA[$root] = $arr;
		
		return $this->OutputEncodeJSON();
	}
	
	private function SortAndMergeHeaderAndData()
	{
		// Fusion des tableaux entete et donn�es après tri du tableau des données
		$this->DataSort();
		$this->DATA = $this->DETAIL;
	}
		
	private function CreateMetropoleHeader($array_data)
	{
		$label = $this->ToUTF8("bulletin_metropole");
		$this->HEADER[$label] = array(
									$this->ToUTF8("creation") => $this->ToUTF8($this->ConvertLongDateToFRDate($array_data['dateinsert'])),
									$this->ToUTF8("mise_a_jour") => $this->ToUTF8($this->ConvertLongDateToFRDate($array_data['daterun'])),
									$this->ToUTF8("validite") => $this->ToUTF8($this->ConvertLongDateToFRDate($array_data['dateprevue'])),
									$this->ToUTF8("version") => $this->ToUTF8($array_data['noversion'])
									);
	}
	
	private function ConvertLongDateToFRDate($str)
	{
		// Date au format YYYYMMDDHHMMSS
		$year = substr($str,0,4);
		$month = substr($str,4,2);
		$day = substr($str,6,2);
		$hour = substr($str,8,2);
		$min = substr($str,10,2);
				
		return $day."-".$month."-".$year." ".$hour.":".$min;
	}
	private function MetropoleDetailFormat()
	{
		$local_zip_file = $this->METEO_DIR.'/tmp_file.zip';
		if (!is_dir($this->METEO_DIR)) {
			mkdir($this->METEO_DIR, 0755, true);
		}
		// delete old files
		$files = glob($this->METEO_DIR.'/*'); //get all file names
		foreach($files as $file){
			if(is_file($file))
				unlink($file); //delete file
		}
		// get zip file
		if (!copy($this->METEO_ZIP_FILE, $local_zip_file)) {
			$this->application->enqueueMessage('Erreur sur la r�cup�ration des informations m�t�o', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
			return false;
		}
		// extract zip file
		$zip = new \ZipArchive();
		if ($zip->open($local_zip_file, \ZIPARCHIVE::CREATE)) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$zip->extractTo($this->METEO_DIR, array($zip->getNameIndex($i)));
			}
			$zip->close();
			unlink($local_zip_file);
		}
		// get global alerte file
		$xml = @simplexml_load_file($this->METEO_DIR.'/NXFR33_LFPW_.xml');
		
		foreach ($xml->DV as $line)
		{	
			$type = $this->Filter($line);
			if (strcasecmp($type,"dep") == 0)
			{
				$this->DEP = $this->ToUTF8("dep_".$line['dep']);
				if ($line['dep'] == $this->departement) {
					$this->AddDETAIL($line);
				}
			}
			if (strcasecmp($type,"cote") == 0)
			{
				$this->DEP = $this->ToUTF8("cote_".substr($line['dep'],0,2));
				if ($line['dep'] == $this->departement) {
					$this->AddDETAIL($line);
				}
			}
		}
		$this->CreateMetropoleHeader($xml->EV);
	}
	
	private function AddDETAIL($data)
	{
		$NiveauMax = $data['coul'];
        $risque = "";
		if ($NiveauMax > 1) 
		{
			$risqueArr = $data->risque;
			foreach ($risqueArr as $r) {
				$risque = $this->RisqueConcat($this->ToUTF8($r['val']),$risque);
			}
		}
		else
			$risque = "RAS"; 

		$this->DETAIL[] = array (
				$this->ToUTF8("niveau") => $this->ToUTF8($NiveauMax), 
				$this->ToUTF8("alerte") => $this->ToUTF8($this->ConvertLevelToColor($NiveauMax)),
				$this->ToUTF8("risque") => $risque,
			);
	}
		
	private function RisqueConvert($risque)
	{
		$risque = (int)$risque;
		return $this->RISQUE[$risque];
	}
	
	private function RisqueConcat($risque,$risque_text = "")
	{
		if (($risque_libelle = $this->RisqueConvert($risque)) != "")
		{
			if (strlen($risque_text) > 0)
				$risque_text .= ", ".ucfirst($risque_libelle);
				else
					$risque_text = ucfirst($risque_libelle);
		}
		return $risque_text;
	}
	
	
	private function ToUTF8($str)
	{
		return utf8_encode($str);
	}
	
	private function ConvertDepartmentToNumber($dep)
	{
		$dep = strtolower($dep);
		$DepNumber = array("guadeloupe" => 971, "martinique" => 972, "guyane" => 973, "idn" => 977);
		return $DepNumber[$dep];
	}
	
	private function ConvertLevelToColor($level)
	{
		$level = (int)$level;
		$colors = array('Bleu','Vert','Jaune','Orange','Rouge','Violet','Gris');
		return $colors[$level];
	}
	
	private function ConvertColorToLevel($color)
	{
		$color = strtolower($color);
		$level = array('bleu' => 0,'vert' => 1,'jaune' => 2,'orange' => 3,'rouge' => 4,'violet' => 5,'gris' => 6);
		return $level[$color];
	}
	
	private function Filter($data)
	{
		// Filtrage des données (depts 99, 2A10, 4010, 3310, etc..) du fichier source de métropole
		if (((strlen ($data['dep']) == 2) && ($data['dep'] < 96)) || ($data['dep'] == 99)) 
			return 'dep';
		if ((strlen($data['dep']) == 4) && (strcasecmp(substr($data['dep'],-2),"10") == 0))
			return 'cote';
		return false;
	}
	
	private function DataSort()
	{
		ksort($this->DATA);
	}
	
	private function OutputEncodeJSON()
	{
		// Fonction d'encodage en JSON
		return json_encode($this->DATA);
	}
	

}

