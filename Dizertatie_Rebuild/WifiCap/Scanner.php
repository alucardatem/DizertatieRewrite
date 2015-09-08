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
    private $error;
    private $log;

    /**
     * Scanner constructor.
     * @param $error
     * @param $log
     */
    public function __construct($error, $log)
    {
        $this->error = $error;
        $this->log = $log;
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

            foreach ($data->{'wireless-network'} as $number => $network) {

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

                $wifi["AP"]["Encryption"] = $this->_extractEncryption($network);
                $wifi["Client"] = $this->_extractClientList($network);

                $list[] = $wifi;
            }
            return $list;
        }
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
            $setEncryption = "OPN";
            return $setEncryption;
        }
        if ((string)$network->SSID->encryption === "None" || (string)$network->SSID->encryption === "" OR (string)$network->SSID->encryption === " ") {
            $setEncryption = "OPN";
            return $setEncryption;
        }
        $encryption = "";
        foreach ($network->SSID as $Enc) {
            foreach ($Enc->encryption as $counterEnc => $EncValue) {
                $encryption .= $EncValue . " ";
            }
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
        foreach ($network->{'wireless-client'} as $client) {
            $wirelessClient["BSSID"] = (string)$network->BSSID;
            $wirelessClient["clientmac"][] = (string)$client->{"client-mac"};
            $wirelessClient["clientManuf"] = (string)$client->{"client-manuf"};
            $wirelessClient["channel"] = (string)$client->{"channel"};
            $wirelessClient["carrier"] = (string)$client->{"carrier"};
            $wirelessClient["encoding"] = (string)$client->{"encoding"};
            $wirelessClient["Probe"] = "";
            foreach ($client->SSID as $Probe) {

                if (isset($Probe->ssid)) {
                    $wirelessClient["Probe"][] = (string)$Probe->ssid;
                }
            }
            $wirelessClient["StationPower"] = (string)$client->{"snr-info"}->last_signal_dbm;
            $wirelessClient["lat"] = "";
            $wirelessClient["lng"] = "";
        }
        return $wirelessClient;
    }

    /**
     * @param $SearchList
     */
    function generateSearchMap($SearchList)
    {

    }


}


$LogInfoFile = "./logs/info.log";
$LogErrorFile = "./logs/error.log";

$LoggerInfo = new Logger($LogInfoFile);
$LoggerError = new Logger($LogErrorFile);

$dataBaseConnection = new MySQLi($LoggerError, $LoggerInfo);
$data = new Scanner($LoggerError, $LoggerInfo);


$ap = new AP($dataBaseConnection->_connection, $LoggerError, $LoggerInfo);
$client = new Client($ap, $dataBaseConnection->_connection, $LoggerError, $LoggerInfo);

$list = $data->parseXML('captures/');
$storeAP = $ap->add($list);
$addClient = $client->add($list);
$searchClient = $client->get();
$addProbes = $client->addProbes($list);
$NetworkList = $ap->searchNetwork("CODE932_GUEST");
$addPass = $ap->updateNetworkPassword($NetworkList, "guest932code");

echo "\n\nCAPTURED LIST:\n";
print_r($list);
echo "\n\nADD AN AP:\n";
print_r($storeAP);
echo "\n\nADD A CLIENT\n";
print_r($addClient);
echo "\n\nSEARCH FOR A CLIENT\n";
print_r($searchClient);
echo "\n\nSTORE CLIENT PROBES\n";
print_r($addProbes);
echo "\n\nSEARCH 4 NETWORK\n";
print_r($NetworkList);
echo "\n\nADD PASSWORD TO NETWORK\n";
print_r($addPass);
echo "\n\n";

