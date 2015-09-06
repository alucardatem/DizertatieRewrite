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
use WifiCap\AP;
use WifiCap\Client;

class Scanner
{
    protected $_APList;
    protected $_ClientList;

    function createClient($Ap)
    {
    }

    function createAP()
    {
    }

    function assignClient($client, $Ap)
    {
    }

    function parseXML($CaptureFolder)
    {

        $list = array();
        $scanned_directory = array_diff(scandir($CaptureFolder), array('..', '.'));
        $scanned_directory = array_values($scanned_directory);
        foreach ($scanned_directory as $key => $file) {

            $data = simplexml_load_file($CaptureFolder . "/" . $file);
            foreach ($data->{'wireless-network'} as $number => $network) {
                $essid = $network->SSID->essid;
                $string_Essid_Check = (string)$essid->attributes()->cloaked;
                if ($string_Essid_Check === 'true') {
                    $essid = "[cloaked]";
                }
                $wifi["AP"]["BSSID"] = (string)$network->BSSID;
                $wifi["AP"]["ESSID"] = (string)$essid;
                $wifi["AP"]["Encryption"] = (string)$network->SSID->encryption;
                $wifi["AP"]["TransmissionChannel"] = (string)$network->channel;
                $wifi["AP"]["Frequency"] = (string)$network->freqmhz;
                $wifi["AP"]["DateTime"] = date('d-m-Y H:i:s');
                $wifi["AP"]["lat"] = "";
                $wifi["AP"]["lng"] = "";
                $wifi["Client"] = array();
                foreach ($network->{'wireless-client'} as $client) {
                    $wifi["Client"]["BSSID"] = (string)$network->BSSID;
                    $wifi["Client"]["clientmac"][] = (string)$client->{"client-mac"};
                    $wifi["Client"]["lat"] = "";
                    $wifi["Client"]["lng"] = "";
                }


                $list[] = $wifi;
            }

            return $list;
        }
        // return $d;

    }


}

$data = new Scanner();
$list = $data->parseXML('captures/');
$ap = new AP();
$client = new Client();
$ap->storeAP($list);
$client->addClient($list);

