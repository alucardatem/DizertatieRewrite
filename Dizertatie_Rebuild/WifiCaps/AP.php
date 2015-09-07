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
    function __construct($error, $log)
    {
        $this->log = $log;
        $this->error = $error;
        $this->logPrefix = "[" . __CLASS__ . "][" . __FUNCTION__ . "]";
        $this->mysqli = $this->connectToDatabase();
    }

    /**
     * @param mixed $logPrefix
     */




    function connectToDatabase()
    {
        $_hostname = "localhost";
        $_username = "root";
        $_password = "";
        $_database = "wifiaps";
        $connection = mysqli_connect($_hostname, $_username, $_password, $_database);
        if (!$connection) {
            $this->log->error("[AP][connectToDatabase]connectToDatabase: Error connecting to db");
        }
        return $connection;
    }

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
                $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
                return array("Status" => "ERROR");
            }
        } else {
            $this->log->error($this->logPrefix . ": ERROR PREPARING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }

    }

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
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
            return array("Status" => "ERROR");

        }
        $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
        return array("Status" => "ERROR");

    }

    function storeAP($apList)
    {
        foreach ($apList as $key => $value) {
            $idBSSID = $this->addBSSID($value["AP"]["BSSID"]);
            $idESSID = $this->addSSID($value["AP"]["ESSID"], $idBSSID);
            $this->addAPDetails($value["AP"]["Encryption"],
                $value["AP"]["TransmissionChannel"],
                $value["AP"]["Frequency"],
                $value["AP"]["lat"],
                $value["AP"]["lng"],
                $value["AP"]["DateTime"],
                $idESSID);
        }
    }

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
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error);
            return array("Status" => "ERROR");
        }
        $this->log->info($this->logPrefix . ": ERROR PREPARING STATEMENT: " . $stmt->error);
        return array("Status" => "ERROR");

    }

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
                $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR");
            }
        } else {
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }
    }

    /**
     * @param $SSID
     * @param $idBSSID
     * @return array|int
     */
    function addSSID($SSID, $idBSSID)
    {

        $Query = "INSERT INTO aps_name(id_APs,Network_Name) values(?,?) on duplicate key update Network_Name=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("sss", $idBSSID, $SSID, $SSID);
            if ($stmt->execute()) {
                $id = $stmt->insert_id;
                $stmt->close();
                $this->log->info($this->logPrefix . ": SUCCESSFULLY ADDED " . $SSID . " to database");
                return $id;
            } else {
                $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__, "error");
                return array("Status" => "ERROR");
            }
        } else {
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__, "error");
            return array("Status" => "ERROR");
        }
    }

    function addAPDetails($Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime, $idESSID)
    {

        $QUERY = "insert into aps_details(id_APs,Encryption_Type,Transmssion_Channel,Frequency,lat,lng,DateTime) VALUES(?,?,?,?,?,?,?) on duplicate KEY update id_APS=?,Encryption_Type=?";
        if ($stmt = $this->mysqli->prepare($QUERY)) {
            $stmt->bind_param("sssssssss", $idESSID, $Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime, $idESSID, $Encryption);
            if ($stmt->execute()) {

                $this->log->info($this->logPrefix . ": SUCCESSFULLY ADDED " . $Encryption . " | " . $TransmissionChannel . " | " . $Frequency . " | " . $lat . " | " . $lng . " | " . $DateTime . " | " . $idESSID . " to database");

                return array("Status" => 1, "SUCCESS" => 1);
            } else {
                $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR");
            }
        } else {
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }


    }

    public function searchByEncryption($Encryption)
    {

        $Query = "SELECT aps_name.Network_Name,
                            aps.AP_Mac,
                            aps_details.Encryption_Type,
                            aps_details.Transmssion_Channel,
                            aps_details.Frequency,
                            aps_details.lat,
                            aps_details.lng,
                            aps_details.Password from aps_details
                  INNER JOIN aps_name as aps_name on aps_name.id_APs=aps_details.id_APs
                  INNER JOIN aps on aps.id=aps_details.id_APs where aps_details.Encryption_Type like ?";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $Encryption = '%' . $Encryption . '%';
            $stmt->bind_param("s", $Encryption);
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $i = 0;
                while ($row[$i] = $result->fetch_assoc()) {
                    ++$i;
                }
                $row = array_filter($row);
                return json_encode($row);
                $stmt->close();
            } else {
                $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
                return array("Status" => "ERROR");
            }
        } else {
            $this->log->error($this->logPrefix . ": ERROR EXECUTING STATEMENT: " . $stmt->error . " ON " . __FILE__ . " LINE: " . __LINE__);
            return array("Status" => "ERROR");
        }

    }




}
