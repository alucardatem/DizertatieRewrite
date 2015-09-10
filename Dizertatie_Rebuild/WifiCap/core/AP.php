<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 04.09.2015
 * Time: 10:47
 */

namespace WifiCap;
require_once "Logger.php";
use WifiCap\Logger;



class AP
{

    protected $_ESSID; // network name
    protected $_BSSID; // mac address
    protected $_Encryption;
    protected $_Password;
    protected $_PWR;
    protected $_TransmissionChannel;
    protected $_Frequency;
    protected $_Latitude;
    protected $_Longitude;
    protected $_DateTime;
    protected $_ClientList;
    protected $_manuf;
    protected $_carrier;
    protected $_encoding;

    /**
     * @var MySQLi
     */
    protected $mysqli;
    /**
     * @var Logger
     */
    protected $log;
    /**
     * @var Logger
     */
    protected $error;
    private $logPrefix;

    /**
     * AP constructor.
     * @param $conn
     */
    function __construct($databaseConnection, $error, $log)
    {
        $this->log = $log;
        $this->error = $error;
        $this->logPrefix = "[" . __CLASS__ . "][" . __FUNCTION__ . "]";
        $this->mysqli = $databaseConnection;
    }

    /**
     * Search for Essid based on BSSID id
     * @param $BSSIDId
     * @return array|int
     */
    public function getESSIDByBSSIDId($BSSIDId)
    {
        $Query = "SELECT id from aps_name where id_APs=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("s", $BSSIDId);
            if ($stmt->execute()) {

                $result = $stmt->get_result();
                $i = 0;
                while ($row[] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();

                if (count($row) > 0) {
                    return $row[0]["id"];
                } else {
                    return 0;
                }
            } else {
                $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
                return array("Status" => "ERROR");
            }
        } else {
            $this->error->error($this->logPrefix . ": ERROR PREPARING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }

    }

    /**
     * Store AcessPoints
     * @param $apList
     */
    function add($apList)
    {
        //print_r($apList);
        foreach ($apList as $value) {

            /* echo
                 $value["AP"]["ESSID"]." | ".
                 $value["AP"]["BSSID"]." | ".
                 $value["AP"]["Encryption"]." | ".
                 $value["AP"]["TransmissionChannel"]." | ".
                 $value["AP"]["Frequency"]." | ".
                 $value["AP"]["lat"]." | ".
                 $value["AP"]["lng"]." | ".
                 $value["AP"]["DateTime"]." | ".
                 $value["AP"]["manuf"]." | ".
                 $value["AP"]["Carrier"]." | ".
                 $value["AP"]["Encoding"]."\n";*/
            if (!isset($value["AP"]["Encryption"])) {
                continue;
            }

            $idBSSID = $this->addBSSID($value["AP"]["BSSID"]);
            $idESSID = $this->addSSID($value["AP"]["ESSID"], $idBSSID);


            $this->addDetails($value["AP"]["Encryption"],
                $value["AP"]["TransmissionChannel"],
                $value["AP"]["Frequency"],
                $value["AP"]["lat"],
                $value["AP"]["lng"],
                $value["AP"]["DateTime"],
                $idBSSID,
                $value["AP"]["manuf"],
                $value["AP"]["Carrier"],
                $value["AP"]["Encoding"]
            );
        }
    }

    /**
     * @param $BSSID
     * @return array|int
     */
    function addBSSID($BSSID)
    {
        $id = $this->getBSSID($BSSID);
        if ($id != 0) {
            $this->log->log($this->logPrefix . ": THE " . $BSSID . " already is in the database");
            return $id["Data"][0]["id"];
        }

        $Query = "INSERT INTO aps(AP_Mac) values(?) on duplicate key update AP_Mac=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("ss", $BSSID, $BSSID);
            if ($stmt->execute()) {

                $id = $stmt->insert_id;
                $stmt->close();
                $this->log->info($this->logPrefix . ": SUCCESSFULLY ADDED " . $BSSID . " to database");
                return $id;
            }
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
            return array("Status" => "ERROR");
        }
        $this->error->info($this->logPrefix . ": ERROR PREPARING STATEMENT: " . $stmt->error);
        return array("Status" => "ERROR");

    }

    /**
     * @param $BSSID
     * @return array|int
     */
    public function getBSSID($BSSID)
    {
        $Query = "SELECT * from aps where AP_MAC=(?)";

        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("s", $BSSID);
            if ($stmt->execute()) {

                $result = $stmt->get_result();
                $i = 0;
                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();

                if (count($row) > 0) {
                    return array("Status" => 1, "Data" => $row);
                } else {
                    return 0;
                }
            } else {
                $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR");
            }
        } else {
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }
    }

    /**
     * @param $SSID
     * @param $idBSSID
     * @return array|int
     */
    function addSSID($SSID, $idBSSID = "0")
    {
        $Check = $this->getESSID($SSID);
        if (isset($Check["Data"][0]["Network_Name"]) AND $Check["Data"][0]["Network_Name"] == $SSID) {
            return $Check["Data"][0]["id"];
        }
        $Query = "INSERT INTO aps_name(id_APs,Network_Name) values(?,?) on duplicate key update Network_Name=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("sss", $idBSSID, $SSID, $SSID);
            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $stmt->close();
                $this->log->info($this->logPrefix . ": SUCCESSFULLY ADDED " . $SSID . " to database");
                return $id;
            } else {
                $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__, "error");
                return array("Status" => "ERROR");
            }
        } else {
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__, "error");
            return array("Status" => "ERROR");
        }
    }

    /**
     * @param $ESSID
     * @return array|int
     */
    public function getESSID($ESSID)
    {
        $Query = "SELECT * from aps_name where Network_Name=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("s", $ESSID);
            if ($stmt->execute()) {

                $result = $stmt->get_result();
                $i = 0;
                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();

                if (count($row) > 0) {
                    return array("Status" => 1, "Data" => $row);
                } else {
                    return 0;
                }
            }
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
            return array("Status" => "ERROR");

        }
        $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
        return array("Status" => "ERROR");

    }

    /**
     * @param $Encryption
     * @param $TransmissionChannel
     * @param $Frequency
     * @param $lat
     * @param $lng
     * @param $DateTime
     * @param $idESSID
     * @return array|mixed
     */
    //TODO: make array param and validate the keys

    function addDetails($Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime, $idESSID, $manuf, $Carrier, $Encoding)
    {

        $QUERY = "insert into aps_details(id_APs,Encryption_Type,Transmssion_Channel,Frequency,lat,lng,DateTime,manuf,Carrier,Encoding) VALUES(?,?,?,?,?,?,?,?,?,?) on duplicate KEY update id_APs=?,Encryption_Type=?";
        if ($stmt = $this->mysqli->prepare($QUERY)) {
            $stmt->bind_param("ssssssssssss", $idESSID, $Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime, $manuf, $Carrier, $Encoding, $idESSID, $Encryption);
            if ($stmt->execute()) {

                $this->log->info($this->logPrefix . ": SUCCESSFULLY ADDED " . $Encryption . " | " . $TransmissionChannel . " | " . $Frequency . " | " . $lat . " | " . $lng . " | " . $DateTime . " | " . $idESSID . " | " . $manuf . " | " . $Carrier . " | " . $Encoding . " to database");

                return array("Status" => 1, "SUCCESS" => 1);
            } else {
                $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR");
            }
        } else {
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }


    }

    /**
     * @param $Encryption
     * @return array|string
     */
    public function search($Encryption)
    {

        $Query = "SELECT aps_name.Network_Name,
                            aps.AP_Mac,
                            aps_details.* from aps_details
                  INNER JOIN aps_name as aps_name on aps_name.id_APs=aps_details.id_APs
                  INNER JOIN aps on aps.id=aps_details.id_APs where aps_details.Encryption_Type like ? OR aps_name.Network_Name like ?";
        if ($stmt = $this->mysqli->prepare($Query)) {

            $Encryption = '%' . $Encryption . '%';
            $stmt->bind_param("ss", $Encryption, $Encryption);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $i = 0;

                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();
                //return $row[0];
                return json_encode($row);

            } else {
                $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR1");
            }
        } else {
            $this->error->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR2");
        }

    }

    /**
     * Add a password to a network based on the search result from searchNetwork
     * @param $networkData
     * @param $networkPassword
     * @return array|string
     */
    function updateNetworkPassword($networkData, $networkPassword)
    {
        $networkData_array = json_decode($networkData, true);
        // print_R($networkData_array);
        foreach ($networkData_array as $networkCounter => $network) {
            //print_r($network);
            $query = "UPDATE aps_details set Password=? where id=?";
            if ($stmt = $this->mysqli->prepare($query)) {
                $stmt->bind_param("ss", $networkPassword, $network["id"]);
                if ($stmt->execute()) {
                    return $this->searchNetwork($network["Network_Name"]);
                }
            }
        }
    }

    /**
     * @param string $networkName
     * @return array|string
     */
    /* public function searchNetwork($networkName = "")
     {
         $extraQuery = "";
         if ($networkName != "") {
             $networkName = '%'.$networkName.'%';
             $extraQuery = " WHERE `aps_name`.Network_Name like ?";
         }
         $QUERY = "SELECT
                           `aps_name`.Network_Name,aps_details.*
                   FROM `aps_details`
                   INNER JOIN aps_name as aps_name
                   ON aps_name.id_APs=aps_details.id_APs" . $extraQuery;
         if ($stmt = $this->mysqli->prepare($QUERY)) {
             if ($networkName != "") {
                 $stmt->bind_param("s", $networkName);
             }
             if ($stmt->execute()) {
                 $result = $stmt->get_result();
                 $i = 0;
                 while ($row[$i] = $result->fetch_assoc()) {
                     ++$i;
                 }
                 $row = array_filter($row);
                 $stmt->close();
                 return $row[0];
                // return json_encode($row);
             }

         }
     }*/

    function getIdAPSByESSIDId($id)
    {
        $query = "SELECT id_APs from aps_name where id=?";
        if ($stmt = $this->mysqli->prepare($query)) {
            $stmt->bind_param("s", $id);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $i = 0;
                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                $stmt->close();
                if (count($row) == 0) {
                    return 0;
                }
                return $row[0]["id_APs"];
            } else {
                $this->error->error($this->logPrefix . "ERROR EXECUTING " . $query);
            }

        } else {
            $this->error->error($this->logPrefix . "ERROR PREPARING STATEMENT FOR  " . __FUNCTION__);

        }
    }

}
