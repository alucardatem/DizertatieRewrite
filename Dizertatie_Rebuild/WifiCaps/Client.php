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

    /**
     * protected $mysqli;
     * /**
     * AP constructor.
     * @param $conn
     */
    function __construct()
    {
        $this->mysqli = $this->connectToDatabase();
        $this->AP = new AP();
    }

    function connectToDatabase()
    {
        $_hostname = "localhost";
        $_username = "root";
        $_password = "";
        $_database = "wifiaps";
        $connection = mysqli_connect($_hostname, $_username, $_password, $_database);
        if (!$connection) {
            return "Error connecting to db";
        }
        return $connection;
    }

    function addClient($List)
    {

        foreach ($List as $key => $client_Array) {

            if (count($client_Array["Client"]) == 0) {
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
                        echo "SUCCESS INSERT " . $TerminalMac . " | " . $idAPs . " | " . $IdNetworkName . " | " . $date . " | " . $lastSeenDate . "\n";
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
