<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 04.09.2015
 * Time: 14:16
 */

namespace WifiCap;

require_once "AP.php";
require_once "Client.php";
require_once "Logger.php";
require_once "MySQLi.php";
use WifiCap\AP;
use WifiCap\Client;
use WifiCap\Logger;
use WifiCap\MySQLi;

class Scanner
{
    protected $_APList;
    protected $_ClientList;

    /**
     * @var Logger
     */
    private $error;
    private $log;
    private $logPrefix;

    /**
     * Scanner constructor.
     * @param $error
     * @param $log
     */
    public function __construct($error, $log)
    {
        $this->error = $error;
        $this->log = $log;
        $this->logPrefix = "[" . __CLASS__ . "][" . __FUNCTION__ . "]";
    }


    /**
     * @param $CaptureFolder
     * @return array
     */
    function parseXML($CaptureFolder)
    {
        $list = array();
        $scanned_directory = array_diff(scandir($CaptureFolder), array('..', '.'));
        $scanned_directory = array_values($scanned_directory);
        foreach ($scanned_directory as $key => $file) {

            $data = simplexml_load_file($CaptureFolder . "/" . $file);

            $Separated_Data = $this->getWirelessNetworkByType($data);
            //$list = $this->generateList($Separated_Data);
            return $Separated_Data;
        }
    }


    private function getWirelessNetworkByType($xmlData)
    {

        $ReturnData["probe"] = array();
        $ReturnData["infrastructure"] = array();
        foreach ($xmlData as $key => $network) {
            $type = $network->attributes()->{'type'};
            if ($type == "infrastructure") {
                $ReturnData["infrastructure"][] = $network;
            }
            if ($type == "probe") {
                $ReturnData["probe"][] = $network;
            }


        }
        return $ReturnData;


    }

    /**
     * @param $SearchList
     */
    function generateSearchMap($SearchList)
    {

    }

    /**
     * @param $separated_Data
     * @param $type | string: infrastructure/probe
     * @return array
     * @internal param $wifi
     * @internal param $list
     */
    public function generateList($separated_Data, $type = "infrastructure")
    {
        $acceptedType = array("infrastructure", "probe");
        $temp_type = $type;
        if (!in_array($temp_type, $acceptedType)) {
            $type = "infrastructure";
            return json_encode(array("Status" => 0, "ERROR" => "{$temp_type} not found, using {$type}"));
        }
        foreach ($separated_Data[$type] as $network) {
            //print_r($network);

            $essid = $this->_extractESSID($network);

            $wifi["AP"]["BSSID"] = (string)$network->BSSID;
            $wifi["AP"]["ESSID"] = (string)$essid;
            $wifi["AP"]["manuf"] = (string)$network->manuf;
            $wifi["AP"]["Carrier"] = (string)$network->carrier;
            $wifi["AP"]["Encoding"] = (string)$network->encoding;
            $wifi["AP"]["TransmissionChannel"] = (string)$network->channel;
            $wifi["AP"]["Frequency"] = (string)$network->freqmhz;
            $wifi["AP"]["DateTime"] = date('d-m-Y H:i:s');
            $wifi["AP"]["lat"] = "";
            $wifi["AP"]["lng"] = "";
            if ($this->_extractEncryption($network) != "") {
                $wifi["AP"]["Encryption"] = $this->_extractEncryption($network);
            }

            //continue;
            $wifi["Client"] = $this->_extractClientList($network);
            $list[] = $wifi;


        }
        return $list;
    }

    /**
     * @param $network
     * @return string
     */
    private function _extractESSID($network)
    {
        if ($network->SSID->essid === true OR $network->SSID->essid == "" OR $network->SSID->essid === " ") {
            $essid = "[cloaked]";
            return $essid;
        }
        return $network->SSID->essid;
    }

    /**
     * @param $network
     * @return mixed
     * @internal param $wifi
     */
    private function _extractEncryption($network)
    {

        if (!isset($network->SSID)) {
            $setEncryption = "";
            return $setEncryption;
        }
        if ((string)$network->SSID->encryption === "None" || (string)$network->SSID->encryption === "" OR (string)$network->SSID->encryption === " ") {
            $setEncryption = "";
            return $setEncryption;
        }
        $encryption = "";


        foreach ($network->SSID->encryption as $counterEnc => $EncValue) {
            $encryption .= $EncValue . " ";
        }
        $encryption = substr($encryption, 0, -1);
        $setEncryption = $encryption;
        return $setEncryption;


    }

    /**
     * @param $network
     * @return mixed
     * @internal param $wifi
     */
    private function _extractClientList($network)
    {
        $clientList = array();
        if (!(isset($network->{'wireless-client'}))) {
            $this->error->error($this->logPrefix . ": There is no client to be added to the list");
            return $clientList;
        }

        foreach ($network->{"wireless-client"} as $client) {
            $wirelessClient["BSSID"] = (string)$network->BSSID;
            $wirelessClient["clientmac"] = (string)$client->{'client-mac'};
            $wirelessClient["client-manuf"] = (string)$client->{'client-manuf'};
            $wirelessClient["channel"] = (string)$client->{'channel'};
            $wirelessClient["carrier"] = (string)$client->{'carrier'};
            $wirelessClient["encoding"] = (string)$client->{'encoding'};
            $wirelessClient["StationPower"] = (string)$client->{"snr-info"}->last_signal_dbm;
            $wirelessClient["lat"] = (string)$client->{"gps-info"}->{'min-lat'};
            $wirelessClient["lng"] = (string)$client->{"gps-info"}->{'min-lon'};
            if (!isset($client->SSID)) {
                $wirelessClient["Probe"] = "";
            }
            foreach ($client->SSID as $ssidKey => $SSID) {
                if (!isset($SSID->ssid)) {
                    continue;
                }
                $wirelessClient["Probe"][] = (string)$SSID->ssid;
            }

            $clientList[] = $wirelessClient;


        }
        return $clientList;
    }


}
