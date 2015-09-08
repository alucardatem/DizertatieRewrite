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

    /**
     * @param $List
     */
    public function add($List)
    {
        foreach ($List as $key => $client_Array) {
            if (count($client_Array["Client"]) == 0) {
                $this->log->error($this->logPrefix . " There is no client to be added");
                continue;
            }
            foreach ($client_Array["Client"] as $clientKey => $ClientData) {
                $idAP = $this->AP->getBSSID($ClientData["BSSID"]);
                $idAPs = $idAP["Data"][0]["id"];
                $IdNetworkName = $this->AP->getESSIDByBSSIDId($idAPs);
                $query = " INSERT into sniffed_stations(id_Ap,id_Network_Name,Station_Mac,lat,lng,DateFirstSeen,DateLastSeen,clientManuf,carrier,channel,encoding,Station_Power) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)
                          on duplicate key update DateLastSeen=(?)";
                if ($stmt = $this->mysqli->prepare($query)) {
                    $date = date("Y-m-d");
                    $lastSeenDate = date("Y-m-d");
                    $stmt->bind_param("sssssssssssss",
                        $idAPs,
                        $IdNetworkName,
                        $ClientData["clientmac"],
                        $ClientData["lat"],
                        $ClientData["lng"],
                        $date,
                        $lastSeenDate,
                        $ClientData["client-manuf"],
                        $ClientData["carrier"],
                        $ClientData["channel"],
                        $ClientData["encoding"],
                        $ClientData["StationPower"],
                        $lastSeenDate);
                    if ($stmt->execute()) {

                        $id = $this->addProbes($ClientData);

                        $APID = $this->AP->getIdAPSByESSIDId($id);
                        $this->_associateClientWithProbes($APID, $id, $ClientData["clientmac"]);
                        $this->log->info($this->logPrefix . "SUCCESS INSERT " . $ClientData["clientmac"] . " | " . $idAPs . " | " . $IdNetworkName . " | " . $date . " | " . $lastSeenDate);
                    }

                }

            }
        }
    }

    /**
     * Store the client probes to network name database
     * @param $list
     */
    public function addProbes($list)
    {
        // print_r($list);
        if (!isset($list["Probe"])) {
            $this->log->error($this->logPrefix . " there are no probes set on Client");
            return false;
        }
        if ($list["Probe"] === "") {
            $this->log->error($this->logPrefix . " client has no probes");
            return false;
        }
        foreach ($list["Probe"] as $counter => $probe) {
            $this->log->info($this->logPrefix . "ADDED " . $probe . " to " . $list["clientmac"]);
            $data = $this->AP->addSSID($probe);
            //echo $list["clientmac"]." | ".$probe." | ".$data."\n";
            return $data;
        }
    }

    /**
     * @param $APID
     * @param $probeID
     * @param $clientMac
     */
    private function _associateClientWithProbes($APID, $probeID, $clientMac)
    {
        $date = date("Y-m-d");
        $query = "INSERT into sniffed_stations ( id_Ap,
                                                  id_Network_Name,
                                                  Station_Mac,
                                                  lat,
                                                  lng,
                                                  DateFirstSeen,
                                                  DateLastSeen,
                                                  clientManuf,
                                                  carrier,
                                                  channel,
                                                  encoding,
                                                  Station_Power)
                  SELECT  {$APID},
                          {$probeID},
                          Station_Mac,
                          lat,
                          lng,
                          DateFirstSeen,
                          DateLastSeen,
                          clientManuf,
                          carrier,
                          channel,
                          encoding,
                          Station_Power
                  from sniffed_stations where Station_Mac=?
                  on duplicate key update DateLastSeen=(?)";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("ss", $clientMac, $date);
            if ($stmt->execute()) {
                $this->log->info($this->logPrefix . "SUCCESSFULLY assigned client to probe");
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
        $query = "SELECT  `aps_name`.Network_Name,
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


}
