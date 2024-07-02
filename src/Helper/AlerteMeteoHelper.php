<?php
/**
* Alerte Météo
* Package			: Joomla 4.x/5.x
* copyright 		: Copyright (C) 2024 ConseilGouz. All rights reserved.
* license    		: https://www.gnu.org/licenses/gpl-3.0.html GNU/GPL
*/

namespace ConseilGouz\Module\AlerteMeteo\Site\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\Folder;

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
    private $token;
    private $response;

    public function __construct($params)
    {
        $this->application = Factory::getApplication();
        $this->METEO_XML_DETAIL_URL = "https://public-api.meteofrance.fr/public/DPVigilance/v1/cartevigilance/encours"; // Détail des alertes
        $this->token = $params->get('token');
        if (!$this->token) { // not defined : use default
            $this->token = "eyJ4NXQiOiJZV0kxTTJZNE1qWTNOemsyTkRZeU5XTTRPV014TXpjek1UVmhNbU14T1RSa09ETXlOVEE0Tnc9PSIsImtpZCI6ImdhdGV3YXlfY2VydGlmaWNhdGVfYWxpYXMiLCJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJzdWIiOiJwYXNjYWwubGVjb250ZUBjYXJib24uc3VwZXIiLCJhcHBsaWNhdGlvbiI6eyJvd25lciI6InBhc2NhbC5sZWNvbnRlIiwidGllclF1b3RhVHlwZSI6bnVsbCwidGllciI6IlVubGltaXRlZCIsIm5hbWUiOiJBbGVydGVNZXRlb0o0IiwiaWQiOjE5NDMsInV1aWQiOiJkNmQ4NmVlOS01ZTBlLTQ0NDgtOGUxNC0yMDE4ODQwOGRiZGIifSwiaXNzIjoiaHR0cHM6XC9cL3BvcnRhaWwtYXBpLm1ldGVvZnJhbmNlLmZyOjQ0M1wvb2F1dGgyXC90b2tlbiIsInRpZXJJbmZvIjp7IjYwUmVxUGFyTWluIjp7InRpZXJRdW90YVR5cGUiOiJyZXF1ZXN0Q291bnQiLCJncmFwaFFMTWF4Q29tcGxleGl0eSI6MCwiZ3JhcGhRTE1heERlcHRoIjowLCJzdG9wT25RdW90YVJlYWNoIjp0cnVlLCJzcGlrZUFycmVzdExpbWl0IjowLCJzcGlrZUFycmVzdFVuaXQiOiJzZWMifX0sImtleXR5cGUiOiJQUk9EVUNUSU9OIiwic3Vic2NyaWJlZEFQSXMiOlt7InN1YnNjcmliZXJUZW5hbnREb21haW4iOiJjYXJib24uc3VwZXIiLCJuYW1lIjoiRG9ubmVlc1B1YmxpcXVlc1ZpZ2lsYW5jZSIsImNvbnRleHQiOiJcL3B1YmxpY1wvRFBWaWdpbGFuY2VcL3YxIiwicHVibGlzaGVyIjoiYWRtaW4iLCJ2ZXJzaW9uIjoidjEiLCJzdWJzY3JpcHRpb25UaWVyIjoiNjBSZXFQYXJNaW4ifV0sImV4cCI6MTc1MTM3NzgwMCwidG9rZW5fdHlwZSI6ImFwaUtleSIsImlhdCI6MTcxOTg0MTgwMCwianRpIjoiYzBjOTc4MjctZTFmMC00MGZmLTg3ZDEtNmU3ZGE2MTUwZGQxIn0=.O3liR7atXA8oCq9GKLqK2_QweL7cE6El8Y8Q9uzRH2TyamuJ1KPl5nV6xyvFsAvawYMio3e8AWFtQSjKycBe3S1EU_kLV_rZ53Ag35dJft0-YO9PUQKq6g3nIXfc6uxti1gccJMqzLEZD93RQvT-wOO3xgr3JqEQHIvOIP1mvm109_5271YpKCwveDFAkkDSuyM0kV6St-0qKggUTOsj-8Lld-xmgrzGUGEtGqZJBkJVL43usG04TTKiz42VLiHYSh2DC6B4Ui-eylV1JtKIpH6IlInJsJaFiM8lYvI9Dm9Xk8vKf56L9eGM6C2noy0_4vlw-XLP-ZA0YhRpAW4dRQ==";
        }
        $this->METEO_MINI_CARTE_GIF = "https://public-api.meteofrance.fr/public/DPVigilance/v1/vignettenationale-J/encours";

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

    public function getImage()
    {
        $img = "QGFR08_LFPW_.gif";
        $fnames = (Folder::files($this->METEO_DIR, $img));
        $fname = array_pop($fnames);
        if($fname) { // img ok : exit
            return $this->METEO_DIR.'/'.$img;
        }
        // get image
        $url = $this->METEO_MINI_CARTE_GIF;
        $ret = $this->get_curl($url);
        if (!$ret) {
            $this->application->enqueueMessage('Erreur sur la récupération de l\'image météo', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
            return false;
        } else {
            return $this->METEO_DIR.'/'.$img;
        }

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
        $year = substr($str, 0, 4);
        $month = substr($str, 4, 2);
        $day = substr($str, 6, 2);
        $hour = substr($str, 8, 2);
        $min = substr($str, 10, 2);

        return $day."-".$month."-".$year." ".$hour.":".$min;
    }
    private function get_curl($url)
    {
        if (extension_loaded("curl")) {
            $getContentCode = $this->getCurlContent($url);
            if ($getContentCode != 200) {
                $getContentCode = $this->getHttpContent($url, $getContentCode);
            }
        } else {
            $getContentCode = "";
            $getContentCode = $this->getHttpContent($url, $getContentCode);
        }
        if($getContentCode == 200) { // pas d'erreur
            $content = $this->response;
            if ($url == $this->METEO_MINI_CARTE_GIF) {
                $fp = fopen($this->METEO_DIR.'/QGFR08_LFPW_.gif', 'x');
                fwrite($fp, $content);
                fclose($fp);
                return true;
            }
            $xml = json_decode($content);
            $fp = fopen($this->METEO_DIR.'/alertes.xml', 'x');
            fwrite($fp, $content);
            fclose($fp);
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
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        $this->response = curl_exec($curl);
        $infos = curl_getinfo($curl);
        curl_close($curl);
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
    private function check_timestamp()
    {
        if (!is_dir($this->METEO_DIR)) {
            mkdir($this->METEO_DIR, 0755, true);
        }
        // check timestamp : if < 1 hour, use files, else recreate files
        $chkfile = 'alerte_checkfile';
        $fnames = (Folder::files($this->METEO_DIR, $chkfile.'.*'));
        $fname = array_pop($fnames);
        if(!$fname) { // no checkfile : exit
            return false;
        }

        $backuptime = substr($fname, -10, 10);
        $time = time();
        $limit = strtotime(date('Y-m-d H:i:s', $time));
        if ($limit > $backuptime) { // timestamp > limit : get infos from curl
            return false;
        }
        $content = file_get_contents($this->METEO_DIR.'/alertes.xml');
        $json = json_decode($content);
        return $json;

    }
    private function MetropoleDetailFormat()
    {
        if (!$json = $this->check_timestamp()) { // time limit exceeded : get info from curl
            // delete old files
            $files = glob($this->METEO_DIR.'/*'); //get all file names
            foreach($files as $file) {
                if(is_file($file)) {
                    unlink($file);
                } //delete file
            }
            // create checkfile
            $chkfile = 'alerte_checkfile';
            $time = time();
            $backuptime = strtotime(date('Y-m-d H:i:s', $time)) + 3600; //
            $fname = $this->METEO_DIR .'/'. $chkfile.'.'.$backuptime;
            if(touch($fname)) {
                $f = fopen($fname, 'w');
                fputs($f, 3600);
                fclose($f);
            }
            $url = $this->METEO_XML_DETAIL_URL;
            $json = $this->get_curl($url);
            if (!$json) {
                $this->application->enqueueMessage('Erreur sur la récupération des informations météo', 'Erreur sur le module Alerte m&eacute;t&eacute;o');
                return false;
            }
        }
        foreach ($json->product->periods[0]->timelaps->domain_ids as $line) {
            $type = $this->Filter($line);
            if (strcasecmp($type, "dep") == 0) {
                $this->DEP = $this->ToUTF8("dep_".$line->domain_id);
                if ($line->domain_id == $this->departement) {
                    $this->AddDETAIL($line);
                }
            }
            if (strcasecmp($type, "cote") == 0) {
                $this->DEP = $this->ToUTF8("cote_".substr($line->domain_id, 0, 2));
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
                if ($niveau > $niveauMax) {
                    $niveauMax = $niveau;
                }
                $risque = $this->RisqueConcat($item->phenomenon_id, $risque);
            }
        }
        if ($risque == "") {
            $risque = "RAS";
        }
        $this->DETAIL[] = array(
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

    private function RisqueConcat($risque, $risque_text = "")
    {
        if (($risque_libelle = $this->RisqueConvert($risque)) != "") {
            if (strlen($risque_text) > 0) {
                $risque_text .= ", ".ucfirst($risque_libelle);
            } else {
                $risque_text = ucfirst($risque_libelle);
            }
        }
        return $risque_text;
    }


    private function ToUTF8($str)
    {
        return mb_convert_encoding($str, 'UTF-8');
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
        if (((strlen($data->domain_id) == 2) && ($data->domain_id < 96)) || ($data->domain_id == 99)) {
            return 'dep';
        }
        if ((strlen($data->domain_id) == 4) && (strcasecmp(substr($data->domain_id, -2), "10") == 0)) {
            return 'cote';
        }
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
