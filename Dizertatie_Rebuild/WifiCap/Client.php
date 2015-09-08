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

    /**
     * @var AP
     */
    private $AP;

    /**
     * @var Logger
     */
    private $log;
    private $error;


    private $logPrefix;

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
        $this->logPrefix = "[" . __CLASS__ . "][" . __FUNCTION__ . "]";
        $this->mysqli = $databaseConnection;
    }


    function add($List)
    {
        foreach ($List as $key => $client_Array) {
            if (count($client_Array["Client"]) == 0) {
                $this->log->error($this->logPrefix . ": There is no client to be added");
                continue;
            }
            $idAP = $this->AP->getBSSID($client_Array["Client"]["BSSID"]);
            $idAPs = $idAP["Data"][0]["id"];
            $IdNetworkName = $this->AP->getESSIDByBSSIDId($idAP["Data"][0]["id"]);
            $client_Array["Client"]["lng"];
            foreach ($client_Array["Client"]["clientmac"] as $counter => $TerminalMac) {
                $query = " INSERT into sniffed_stations(id_Ap,id_Network_Name,Station_Mac,lat,lng,DateFirstSeen,DateLastSeen,clientManuf,carrier,channel,encoding,Station_Power) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                          on duplicate key update DateLastSeen=(?)";
                if ($stmt = $this->mysqli->prepare($query)) {
                    $date = date("Y-m-d H:i:s");
                    $lastSeenDate = date("Y-m-d H:i:s");
                    $stmt->bind_param("sssssssssssss", $idAPs, $IdNetworkName, $TerminalMac, $client_Array["Client"]["lat"], $client_Array["Client"]["lng"], $date, $lastSeenDate, $client_Array["Client"]["clientManuf"], $client_Array["Client"]["carrier"], $client_Array["Client"]["channel"], $client_Array["Client"]["encoding"], $client_Array["Client"]["StationPower"], $lastSeenDate);
                    if ($stmt->execute()) {
                        $this->log->info($this->logPrefix . "SUCCESS INSERT " . $TerminalMac . " | " . $idAPs . " | " . $IdNetworkName . " | " . $date . " | " . $lastSeenDate);
                    }
                }
            }
        }


    }


    /**
     * @val Client
     * Search for mac in the database
     * @param $client |string format ==> 00:00:00:00:00:00
     * @return string
     */
    function get($client = "")
    {
        $extraQuery = "";
        if ($client != "") {
            $extraQuery = " where sniffed_stations.Station_Mac=?";

        }
        echo $query = "SELECT  `aps_name`.Network_Name,
                          sniffed_stations.Station_Mac,
                          sniffed_stations.lat,
                          sniffed_stations.lng,
                          sniffed_stations.Station_Power,
                          sniffed_stations.clientManuf,
                          sniffed_stations.carrier,
                          sniffed_stations.channel,
                          sniffed_stations.encoding,
                          sniffed_stations.DateFirstSeen,
                          sniffed_stations.DateLastSeen
	            FROM sniffed_stations
	            INNER JOIN aps_name as aps_name on
	                    sniffed_stations.id_Network_Name = aps_name.id_APs" . $extraQuery;
        if ($stmt = $this->mysqli->prepare($query)) {
            if ($client != "") {
                $stmt->bind_param("s", $client);
            }

            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $i = 0;
                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();
                return json_encode($row);
            }
        }
    }

    /**
     * Store the client probes to network name database
     * @param $list
     */
    function addProbes($list)
    {
        foreach ($list as $counter => $lista) {
            if (!isset($lista["Client"]["Probe"])) {
                continue;
            }
            if ($lista["Client"]["Probe"] === "") {
                continue;
            }
            foreach ($lista["Client"]["Probe"] as $key => $value) {
                $this->AP->addSSID($value);
            }
        }
    }


}
