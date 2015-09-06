<?php
namespace WifiCap;

/**
 * Class wifiCapture
 */

require_once 'Logger.php';

class wifiCapture extends Logger
{

    protected $log;

    /**
     * Function to insert AP mac
     * @param $AP_MAC
     * @return array
     */


    function addAPMac($AP_MAC)
    {
        $this->log->log("TEST the logging functionality");
        return null;
        $queryInsert = "Insert into aps(AP_Mac) values(?) on duplicate key update AP_Mac=(?)";
        if (!($stmt = $this->mysqli->prepare($queryInsert))) {
            return array("Status" => 0, "Message" => "addAPMac: Could not prepare query. Error: " . $stmt->error);
        } else {
            $stmt->bind_param("ss", $AP_MAC, $AP_MAC);
            if (!($stmt->execute())) {
                return array("Status" => 0, "Message" => "addAPMac: Could not execute query" . $stmt->error);
            } else {
                $lastInsertedId = $stmt->insert_id;
                $stmt->close();
                if ($lastInsertedId != 0) {
                    return array("Status" => 1, "Message" => array("Msg" => "addAPMac: Successfully Inserted MAC ADDRESS: " . $AP_MAC, "Return" => $lastInsertedId));
                } else {
                    $qryGetLastInsertedId = "SELECT * from aps where AP_Mac=(?)";
                    if (!($stmt = $this->mysqli->prepare($qryGetLastInsertedId))) {
                        return array("Status" => 0, "Message" => "addAPMac: Could not execute query " . $stmt->error);
                    } else {
                        $stmt->bind_param("s", $AP_MAC);
                        if (!($stmt->execute())) {
                            return array("Status" => 0, "Message" => "addAPMac: Could not execute query" . $stmt->error);
                        } else {
                            $result = $stmt->get_result();
                            $i = 0;
                            while ($row[$i] = $result->fetch_assoc()) {
                                ++$i;
                            }
                            $row = array_filter($row);
                            $stmt->close();
                            return array("Status" => 1, "Message" => array("Msg" => "addAPMac: Successfully Updated MAC ADDRESS: " . $AP_MAC, "Return" => $row[0]["id"]));
                        }
                    }
                }
            }
        }

    }

    /**
     * Function to connecto to mysql database
     * @return mysqli|string
     */


    /**
     * @param $AP_NAME
     * @param $AP_ID
     * @return array
     */
    function addAPName($AP_NAME,$AP_ID)
    {

        $query = "Insert into aps_name(id_APs,Network_Name) values(?,?) on duplicate key update Network_Name=(?)";
        if (!($stmt = $this->mysqli->prepare($query))) {
            return array("Status" => 0, "Message" => "addAPName: Could not prepare query. Error: " . $stmt->error);
        }

        $stmt->bind_param("sss", $AP_ID, $AP_NAME, $AP_NAME);
        if (!($stmt->execute())) {
            return array("Status" => 0, "Message" => "addAPName: Could not prepare query. Error: " . $stmt->error);
        }

        else{
            $id = $stmt->insert_id;
            $stmt->close();
            if ($id != 0) {
                return array("Status" => 1, "Message" => array("Msg" => "addAPName: Successfully Inserted SSID: " . $AP_NAME, "Return" => $id));
            }
            else{
                $query = "SELECT * from aps_name where Network_Name=(?)";
                if (!($stmt = $this->mysqli->prepare($query))) {
                    return array("Status" => 0, "Message" => "Could not prepare query. Error: " . $stmt->error);
                } else {
                    $stmt->bind_param("s", $AP_NAME);
                    if (!($stmt->execute())) {
                        return array("Status" => 0, "Message" => "addAPName: Could not prepare query. Error: " . $stmt->error);
                    } else {
                        $result = $stmt->get_result();
                        $i = 0;
                        while ($row[$i] = $result->fetch_assoc()) {
                            ++$i;
                        }
                        $row = array_filter($row);
                        //print_r($row);
                        $stmt->close();
                        return array("Status" => 1, "Message" => array("Msg" => "addAPName: Successfully Updated SSID: " . $AP_NAME, "Return" => $row[0]));

                    }
                }

            }
        }
    }

    /**
     * @param $AP_Details
     * @return array
     */
    function addApDetails($AP_Details)
    {

        $apId = $AP_Details["id_APs"];
        $encryptionType = $AP_Details["Encryption_Type"];
        $transmissionChannel = $AP_Details["Transmission_Channel"];
        $frequency = $AP_Details["Frequency"];
        $latitude = $AP_Details["lat"];
        $longitude = $AP_Details["lng"];
        $password = $AP_Details["Password"];
        $timeStamp = $AP_Details["DateTime"];

        echo $query = "INSERT INTO aps_details (id_APs,Encryption_Type,Transmssion_Channel,Frequency,lat,lng,Password,DateTime) VALUES (?,?,?,?,?,?,?,?) on duplicate key update id_APs=(?)";

        if (!($stmt = $this->mysqli->prepare($query))) {
            return array("Status" => 0, "Message" => "addApDetails: Could not prepare query . $query Error: " . $stmt->error . " " . $stmt->errno);
        } else {
            $stmt->bind_param("isssssssi", $apId, $encryptionType, $transmissionChannel, $frequency, $latitude, $longitude, $password, $timeStamp, $apId);
            if (!($stmt->execute())) {
                return array("Status" => 0, "Message" => "addApDetails: Could not prepare query. Error: " . $stmt->error);
            } else {
                $id = $stmt->insert_id;
                $stmt->close();
                if ($id != 0) {
                    return array("Status" => 1, "Message" => array("Msg" => "addApDetails: Successfully Inserted AP Details: " . print_r($AP_Details, true), "Return" => $id));
                } else {
                    return array("Status" => 1, "Message" => array("Msg" => "addApDetails: Successfully Inserted AP Details.", "Return" => $AP_Details));
                }

            }
        }
    }

    function addClient($Client_Details)
    {
        $clientApId = $Client_Details["id_Ap"];
        $clientAPNameId = $Client_Details["id_Network_Name"];
        $clientMac = $Client_Details["Station_Mac"];
        $clientLatitude = $Client_Details["lat"];
        $clientLongitude = $Client_Details["lng"];
        $clientPower = $Client_Details["Station_Power"];

        $query = "INSERT INTO sniffed_stations(id_Ap,id_Network_Name,Station_Mac,lat,lng,Station_Power) VALUES(?,?,?,?,?,?) on duplicate key update id_Network_Name=(?), Station_Mac=(?), id_Ap=(?)";
        if (!($stmt = $this->mysqli->prepare($query))) {
            return array("Status" => 0, "Message" => "addClient: Could not prepare query " . $query . ". Error: " . $stmt->error);
        } else {

            $loggedData = "|--" . $clientApId . " | " . $clientAPName . " | " . $clientMac . " | " . $clientLatitude . " | " . $clientLongitude . " | " . $clientPower . " | " . $clientAPName . " | " . $clientMac . " | " . $clientApId . "--|";
            $this->log($loggedData);
            //die();
            $stmt->bind_param("sssssssss", $clientApId, $clientAPNameId, $clientMac, $clientLatitude, $clientLongitude, $clientPower, $clientAPNameId, $clientMac, $clientApId);
            if (!($stmt->execute())) {
                return array("Status" => 0, "Message" => "addClient: Could not prepare query. $query.  Error: " . $stmt->error);
            } else {
                $id = $stmt->insert_id;
                return array("Status" => 1, "Message" => array("Msg" => "addClient: Successfully Inserted Client Details: ", $Client_Details, "Return" => $id));
            }
        }
    }

    /**
     * @param $Client_Probes
     */
    function addClientProbes($Client_Probes, $client_mac)
    {
        Logger::log("test");
        /*foreach($Client_Probes as $key=>$probe)
        {
            $added = $this->addAPName($probe,0);
            //$id_net_name = $added["Message"]["Return"];



        }*/

    }


    /**
     * @param $AP_Name
     */
    function getAPByName($AP_Name)
    {

    }

    /**
     * @param $AP_Name
     */
    function getAPDetailsByName($AP_Name)
    {

    }
    function getClientLocations($Client_Mac)
    {

    }
    function getAPLocations()
    {

    }
    function getAPByType($AP_Type)
    {

    }
}


/*function xceptionHandler($Exception){
    echo "Exception:" . $Exception->getMessage()." occured on line: ".$Exception->getLine();

}
set_exception_handler('xceptionHandler');
try{

}
catch (Exception $e)
{
    print_r($e);
}*/


$logger = new Logger('./log_b.txt');
$logger2 = new LoggerDB('conn');


$wifi = new wifiCapture($logger);
$a = new A($logger2);

class A
{
    private $_logger;

    function __construct($logger)
    {
        $this->_logger = $logger;
    }

    function doSomething()
    {
        $this->_logger->log('doing something');
    }
}

/*$MAC = */
$wifi->addAPMac("11:11:11:11:11:12");
/*$mac_ID = $MAC["Message"]["Return"];
$AP_NAME = $wifi->addAPName("AlucardATEM", $mac_ID);


$id_AP_Name = $AP_NAME["Message"]["Return"];
$id_AP = $AP_NAME["Message"]["Return"];

$Network_Name = "AlucardATEM";

$client_Data["id_APs"] = $id_AP;
$client_Data["Encryption_Type"] = "WEP";
$client_Data["Transmission_Channel"] = 6;
$client_Data["Frequency"] = 300;
$client_Data["lat"] = 200.2;
$client_Data["lng"] = 500.1;
$client_Data["Password"] = "souten";
$client_Data["DateTime"] = date("Y-m-d H:i:s");

$ap_Details = $wifi->addApDetails($client_Data);


$Client_Details["id_Ap"] = $id_AP;
$Client_Details["id_Network_Name"] = $id_AP_Name;
$Client_Details["Station_Mac"] = "22:22:22:22:22:22";
$Client_Details["lat"] = 200.3;
$Client_Details["lng"] = 500.0;
$Client_Details["Station_Power"] = 30;


$addClient = $wifi->addClient($Client_Details);

$clientProbes = array("code932","souten","mangastream","Troll");
$addClientProbes = $wifi->addClientProbes($clientProbes,$Client_Details["Station_Mac"]);
print_r($addClientProbes);*/
