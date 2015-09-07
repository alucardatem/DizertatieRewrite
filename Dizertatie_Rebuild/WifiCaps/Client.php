<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 04.09.2015
 * Time: 10:47
 */

namespace WifiCap;


class Client
{

    protected $_Station;
    protected $_PWR;
    protected $_Probes;
    protected $_Latitude;
    protected $_Longitude;
    protected $_Ap_SSID_Id;
    protected $_Ap_BSSID_Id;
    private $AP;

    private $log;
    private $error;

    /**
     * protected $mysqli;
     * /**
     * AP constructor.
     * @param $conn
     */
    function __construct($APObj, $databaseConnection, $error, $log)
    {

        $this->AP = $APObj;
        $this->error = $error;
        $this->log = $log;
        $this->mysqli = $databaseConnection;
    }



    function addClient($List)
    {

        foreach ($List as $key => $client_Array) {

            if (count($client_Array["Client"]) == 0) {
                $this->log->log("[Client][addClient]: There is no client to be added");
                continue;
            }

            $idAP = $this->AP->getBSSID($client_Array["Client"]["BSSID"]);
            $idAPs = $idAP["Data"][0]["id"];
            $IdNetworkName = $this->AP->getESSIDByBSSIDId($idAP["Data"][0]["id"]);
            $client_Array["Client"]["lng"];
            foreach ($client_Array["Client"]["clientmac"] as $counter => $TerminalMac) {
                $query = " INSERT into sniffed_stations(id_Ap,id_Network_Name,Station_Mac,lat,lng,DateFirstSeen,DateLastSeen) VALUES (?,?,?,?,?,?,?)
                          on duplicate key update DateLastSeen=(?)";
                if ($stmt = $this->mysqli->prepare($query)) {
                    $date = date("Y-m-d H:i:s");
                    $lastSeenDate = date("Y-m-d H:i:s");
                    $stmt->bind_param("ssssssss", $idAPs, $IdNetworkName, $TerminalMac, $client_Array["Client"]["lat"], $client_Array["Client"]["lng"], $date, $lastSeenDate, $lastSeenDate);
                    if ($stmt->execute()) {
                        $this->log->log("[Client][addClient]: SUCCESS INSERT " . $TerminalMac . " | " . $idAPs . " | " . $IdNetworkName . " | " . $date . " | " . $lastSeenDate);
                    }
                }
            }
        }


    }

    function getClient($Client)
    {

    }


    function getAllClients()
    {

    }

    function addClientProbes($list)
    {

    }


}
