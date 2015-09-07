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

                if ($network->SSID->essid === true OR $network->SSID->essid == "" OR $network->SSID->essid === " ") {
                    $essid = "[cloaked]";
                } else {
                    $essid = $network->SSID->essid;
                }

                $wifi["AP"]["BSSID"] = (string)$network->BSSID;
                $wifi["AP"]["ESSID"] = (string)$essid;
                $wifi["AP"]["manuf"] = (string)$network->manuf;

                $wifi["AP"]["Encryption"] = array();
                if (!isset($network->SSID)) {
                    $wifi["AP"]["Encryption"] = "OPN";
                } else {
                    if ((string)$network->SSID->encryption === "None" || (string)$network->SSID->encryption === "" OR (string)$network->SSID->encryption === " ") {
                        $wifi["AP"]["Encryption"] = "OPN";
                    } else {
                        $encryption = "";
                        foreach ($network->SSID as $Enc) {
                            foreach ($Enc->encryption as $counterEnc => $EncValue) {
                                $encryption .= $EncValue . " ";
                            }
                        }
                        $encryption = substr($encryption, 0, -1);
                        $wifi["AP"]["Encryption"] = $encryption;
                    }
                }
                $wifi["AP"]["Carrier"] = (string)$network->carrier;
                $wifi["AP"]["Encoding"] = (string)$network->encoding;
                $wifi["AP"]["TransmissionChannel"] = (string)$network->channel;
                $wifi["AP"]["Frequency"] = (string)$network->freqmhz;
                $wifi["AP"]["DateTime"] = date('d-m-Y H:i:s');
                $wifi["AP"]["lat"] = "";
                $wifi["AP"]["lng"] = "";
                $wifi["Client"] = array();
                foreach ($network->{'wireless-client'} as $client) {
                    $wifi["Client"]["BSSID"] = (string)$network->BSSID;
                    $wifi["Client"]["clientmac"][] = (string)$client->{"client-mac"};
                    $wifi["Client"]["clientManuf"] = (string)$client->{"client-manuf"};
                    $wifi["Client"]["channel"] = (string)$client->{"channel"};
                    $wifi["Client"]["carrier"] = (string)$client->{"carrier"};
                    $wifi["Client"]["encoding"] = (string)$client->{"encoding"};
                    $wifi["Client"]["Probe"] = "";
                    foreach ($client->SSID as $Probe) {

                        if (isset($Probe->ssid)) {
                            $wifi["Client"]["Probe"][] = (string)$Probe->ssid;
                        }
                    }
                    $wifi["Client"]["StationPower"] = (string)$client->{"snr-info"}->last_signal_dbm;
                    $wifi["Client"]["lat"] = "";
                    $wifi["Client"]["lng"] = "";
                }
                $list[] = $wifi;
            }
            return $list;
        }
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


$ap->storeAP($list);
$client->addClient($list);
$searchClient = $client->getClient("F8:2F:A8:B2:0F:F9");
//print_r($searchClient);

$client->addClientProbes($list);

