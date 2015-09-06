<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 04.09.2015
 * Time: 10:47
 */

namespace WifiCap;

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
     * AP constructor.
     * @param $conn
     */
    function __construct()
    {
        $this->mysqli = $this->connectToDatabase();
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
                return array("Status" => 0, "Error" => $stmt->error);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->error);
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
            } else {
                return array("Status" => 0, "Error" => $stmt->error);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->error);
        }
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
            return $id["Data"][0]["id"];
        }

        $Query = "INSERT INTO aps(AP_Mac) values(?) on duplicate key update AP_Mac=(?)";
        if ($stmt = $this->mysqli->prepare($Query)) {
            $stmt->bind_param("ss", $BSSID, $BSSID);
            if ($stmt->execute()) {

                $id = $stmt->insert_id;
                $stmt->close();
                return $id;
            } else {
                return array("Status" => 0, "Error" => $stmt->error);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->error);
        }
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
                return array("Status" => 0, "Error" => $stmt->error);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->error);
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
                return $id;
            } else {
                return array("Status" => 0, "Error" => $stmt->error);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->error);
        }
    }

    function addAPDetails($Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime, $idESSID)
    {
        $QUERY = "insert into aps_details(id_APs,Encryption_Type,Transmssion_Channel,Frequency,lat,lng,DateTime) VALUES(?,?,?,?,?,?,?)";
        if ($stmt = $this->mysqli->prepare($QUERY)) {
            $stmt->bind_param("sssssss", $idESSID, $Encryption, $TransmissionChannel, $Frequency, $lat, $lng, $DateTime);
            if ($stmt->execute()) {
                $stmt->close();
                return array("Status" => 1, "SUCCESS" => 1);
            } else {
                return array("Status" => 0, "Error" => $stmt->errno);
            }
        } else {
            return array("Status" => 0, "Error" => $stmt->errno);
        }

    }
}
