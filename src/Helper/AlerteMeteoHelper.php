<?php
/**
* Alerte Météo
* Version			: 2.0.6
* Package			: Joomla 4.x
* copyright 		: Copyright (C) 2023 ConseilGouz. All rights reserved.
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
	private $DETAIL;
    private $application;
	private $departement;
	private $token,$response;
    
	public function __construct($params)
	{
		$this->application = Factory::getApplication();
		$this->METEO_XML_DETAIL_URL = "https://public-api.meteofrance.fr/public/DPVigilance/v1/cartevigilance/encours"; // Détail des alertes
		$this->token = "eyJ4NXQiOiJZV0kxTTJZNE1qWTNOemsyTkRZeU5XTTRPV014TXpjek1UVmhNbU14T1RSa09ETXlOVEE0Tnc9PSIsImtpZCI6ImdhdGV3YXlfY2VydGlmaWNhdGVfYWxpYXMiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJwYXNjYWwubGVjb250ZUBjYXJib24uc3VwZXIiLCJhcHBsaWNhdGlvbiI6eyJvd25lciI6InBhc2NhbC5sZWNvbnRlIiwidGllclF1b3RhVHlwZSI6bnVsbCwidGllciI6IlVubGltaXRlZCIsIm5hbWUiOiJBbGVydGVNZXRlb0o0IiwiaWQiOjE5NDMsInV1aWQiOiI0ZjIwOWNlZi1iYWQyLTRmMjktODcwNS0yYzhkMjUyOThiNjcifSwiaXNzIjoiaHR0cHM6XC9cL3BvcnRhaWwtYXBpLm1ldGVvZnJhbmNlLmZyOjQ0M1wvb2F1dGgyXC90b2tlbiIsInRpZXJJbmZvIjp7IjYwUmVxUGFyTWluIjp7InRpZXJRdW90YVR5cGUiOiJyZXF1ZXN0Q291bnQiLCJncmFwaFFMTWF4Q29tcGxleGl0eSI6MCwiZ3JhcGhRTE1heERlcHRoIjowLCJzdG9wT25RdW90YVJlYWNoIjp0cnVlLCJzcGlrZUFycmVzdExpbWl0IjowLCJzcGlrZUFycmVzdFVuaXQiOiJzZWMifX0sImtleXR5cGUiOiJTQU5EQk9YIiwic3Vic2NyaWJlZEFQSXMiOlt7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJjYXJib24uc3VwZXIiLCJuYW1lIjoiRG9ubmVlc1B1YmxpcXVlc1ZpZ2lsYW5jZSIsImNvbnRleHQiOiJcL3B1YmxpY1wvRFBWaWdpbGFuY2VcL3YxIiwicHVibGlzaGVyIjoiYWRtaW4iLCJ2ZXJzaW9uIjoidjEiLCJzdWJzY3JpcHRpb25UaWVyIjoiNjBSZXFQYXJNaW4ifV0sImV4cCI6MTc3ODkwNTA1NSwiaWF0IjoxNjg0Mjk3MDU1LCJqdGkiOiJkZjdkYmNhMy00OTE3LTQ4NWYtYTc0My01YjM2NTNmNzBjOTcifQ==.IdpAqX6PNDnZ1WjZ9rv0kYkILUp8pbiUbIykLdVNzi14JP7CsoBYxVY9THuc6PQ-g58OrS_im3m4yEVQA51IfQ2afp3Y8f353aEpfmmQ9addBk6xVrAJs_Y_nC2rmSCsRNMd6HQFUVfMjHM17_m-fhbfNnRy5GsUypB_SqSq4MG_Jcd7H203QyFcsRU1ZoxDM4HzsnifueNI2cJ-znAnebhnpN1tcFqiJFTD9Xx65M-8IECBLiA7G_-Wi3B3XP5d4FiIgqGuNe6J9J4Xmo5DNZCiK_1hAoqQOjeCMXSSG3ceu_3NZ08_ZE6GIDHLM3AUiNhnwwlpUxAIsOgbTUlwvA==";
		$this->METEO_MINI_CARTE_GIF = "https://public-api.meteofrance.fr/public/DPVigilance/v1/vignettenationale-J/encours"; 
		// $this->METEO_ZIP_FILE = "https://vigilance2019.meteofrance.com/data/vigilance.zip";
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
		    if (!$this->MetropoleDetailFormat()) {
		        return false;
		    }
		} catch (\Exception $e) {
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
		
	private function CreateMetropoleHeader($json)
	{
		$label = $this->ToUTF8("bulletin_metropole");
		$this->HEADER[$label] = array(
									$this->ToUTF8("creation") => $this->ToUTF8($json->meta->generation_timestamp),
									$this->ToUTF8("mise_a_jour") => $this->ToUTF8($json->meta->product_datetime),
		    $this->ToUTF8("validite") => $this->ToUTF8($json->meta->product_datetime),
									$this->ToUTF8("version") => $this->ToUTF8($json->product->version_vigilance)
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
	private function get_curl($url) {
		if (extension_loaded("curl")) {
			$getContentCode = $this->getCurlContent($url);
			if ($getContentCode != 200) { 
				$getContentCode = $this->getHttpContent($url, $getContentCode);
				}
		} else {
			$getContentCode = $this->getHttpContent($url, $getContentCode);
		}
		if($getContentCode == 200) { // pas d'erreur
		    $content = $this->response;
		    if ($url == $this->METEO_MINI_CARTE_GIF) {
		          $fp = fopen($this->METEO_DIR.'/QGFR08_LFPW_.gif','x');
		          fwrite($fp, $content);
		          fclose($fp);
		          return true;
		    }
		    $xml = json_decode($content);
		    return $xml;
		} else {
			return false;
		}
		
	}
	private function getCurlContent($url)
	{
	    $curl = curl_init();
	    curl_setopt($curl, CURLOPT_HTTPHEADER, array('Accept: application/json','apikey: '.$this->token));
	    curl_setopt($curl, CURLOPT_TIMEOUT, 10);
	    curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
	    curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	    curl_setopt($curl, CURLOPT_URL, $url);
	    $this->response = curl_exec($curl);
	    $infos = curl_getinfo($curl);
	    curl_close ($curl);
	    return $infos['http_code'];
	}
	private function getHttpContent($url, $infos)
	{
	    if ($this->response = @file_get_contents($url)) {
	        return 200;
	    }
	    $infos = $http_response_header[0];
	    return '2000'.' '.$infos;
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
		$url = $this->METEO_XML_DETAIL_URL;
		$json = $this->get_curl($url);
		if (!$json) {
			$this->application->enqueueMessage('Erreur sur la récupération des informations météo', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
			return false;
		}
		// get image
		$url = $this->METEO_MINI_CARTE_GIF;
		$ret = $this->get_curl($url);
		if (!$ret) {
		    $this->application->enqueueMessage('Erreur sur la récupération de l\'image météo', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
		    return false;
		}
		
		foreach ($json->product->periods[0]->timelaps->domain_ids as $line)
		{	
			$type = $this->Filter($line);
			if (strcasecmp($type,"dep") == 0)
			{
				$this->DEP = $this->ToUTF8("dep_".$line->domain_id);
				if ($line->domain_id == $this->departement) {
					$this->AddDETAIL($line);
				}
			}
			if (strcasecmp($type,"cote") == 0)
			{
				$this->DEP = $this->ToUTF8("cote_".substr($line->domain_id,0,2));
				if ($line->domain_id == $this->departement) {
					$this->AddDETAIL($line);
				}
			}
		}
        $this->CreateMetropoleHeader($json);
		return true;
	}
	
	private function AddDETAIL($data)
	{
	    $risque = "";
	    $niveauMax = 1;
	    foreach($data->phenomenon_items as $item) {
	        $niveau = $item->phenomenon_max_color_id;
            if ($niveau > 1) {
                if ($niveau > $niveauMax ) $niveauMax = $niveau;
                $risque = $this->RisqueConcat($item->phenomenon_id,$risque);
		     }
	    }
	    if ($risque == "") $risque = "RAS";
		$this->DETAIL[] = array (
				$this->ToUTF8("niveau") => $this->ToUTF8($niveauMax), 
				$this->ToUTF8("alerte") => $this->ToUTF8($this->ConvertLevelToColor($niveauMax)),
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
		return mb_convert_encoding($str,'UTF-8');
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
		if (((strlen ($data->domain_id) == 2) && ($data->domain_id < 96)) || ($data->domain_id == 99)) 
			return 'dep';
		if ((strlen($data->domain_id) == 4) && (strcasecmp(substr($data->domain_id,-2),"10") == 0))
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

