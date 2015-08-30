<?php

/**
 * Class wifiCapture
 */

class wifiCapture
{


    /**
     * Function to insert AP mac
     * @param $AP_MAC
     * @return array
     */
    function addAPMac($AP_MAC)
    {
        $mysqli = $this->connectToDatabase();
        $queryInsert = "Insert into aps(AP_Mac) values(?) on duplicate key update AP_Mac=(?)";
        if (!($stmt = $mysqli->prepare($queryInsert))) {
            return array("Status" => 0, "Message" => "Could not prepare query. Error: " . $stmt->error);
        } else {
            $stmt->bind_param("ss", $AP_MAC, $AP_MAC);
            if (!($stmt->execute())) {
                return array("Status" => 0, "Message" => "Could not execute query" . $stmt->error);
            } else {

                $lastInsertedId = $stmt->insert_id;
                $stmt->close();
                if ($lastInsertedId != 0) {
                    return array("Status" => 1, "Message" => array("Msg" => "Successfully Inserted MAC ADDRESS: " . $AP_MAC, "Return" => $lastInsertedId));
                } else {
                    $qryGetLastInsertedId = "SELECT * from aps where AP_Mac=(?)";
                    if (!($stmt = $mysqli->prepare($qryGetLastInsertedId))) {
                        return array("Status" => 0, "Message" => "Could not execute query " . $stmt->error);
                    } else {
                        $stmt->bind_param("s", $AP_MAC);
                        if (!($stmt->execute())) {
                            return array("Status" => 0, "Message" => "Could not execute query" . $stmt->error);
                        } else {
                            $result = $stmt->get_result();
                            $i = 0;
                            while ($row[$i] = $result->fetch_assoc()) {
                                ++$i;
                            }
                            $row = array_filter($row);
                            $stmt->close();
                            return array("Status" => 1, "Message" => array("Msg" => "Successfully Updated MAC ADDRESS: " . $AP_MAC, "Return" => $row[0]["id"]));
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

    function addAPName($AP_NAME,$AP_ID)
    {
        $mysqli = $this->connectToDatabase();
        $query = "Insert into aps_name(id_APs,Network_Name) values(?,?) on duplicate key update Network_Name=(?)";
        if(!($stmt=$mysqli->prepare($query))){
            return array("Status"=>0,"Message"=>"Could not prepare query. Error: ".$stmt->error);
        }
        else{
            $stmt->bind_param("sss", $AP_ID, $AP_NAME, $AP_NAME);
            if(!($stmt->execute()))
            {
                return array("Status"=>0,"Message"=>"Could not prepare query. Error: ".$stmt->error);
            }
            else{
                $id = $stmt->insert_id;
                $stmt->close();
                if($id!=0){
                    return array("Status"=>1,"Message"=>array("Msg"=>"Successfully Inserted SSID: ".$AP_NAME,"Return"=>$id));
                }
                else{
                    $query = "SELECT * from aps_name where Network_Name=(?)";
                    if (!($stmt = $mysqli->prepare($query))) {
                        return array("Status" => 0, "Message" => "Could not prepare query. Error: " . $stmt->error);
                    } else {
                        $stmt->bind_param("s", $AP_NAME);
                        if (!($stmt->execute())) {
                            return array("Status" => 0, "Message" => "Could not prepare query. Error: " . $stmt->error);
                        } else {
                            $result = $stmt->get_result();
                            $i = 0;
                            while ($row[$i] = $result->fetch_assoc()) {
                                ++$i;
                            }
                            $row = array_filter($row);
                            //print_r($row);
                            $stmt->close();
                            return array("Status" => 1, "Message" => array("Msg" => "Successfully Updated SSID: " . $AP_NAME, "Return" => $row[0]));

                        }
                    }

                }
            }
        }
    }


    function addApDetails($AP_Details)
    {

    }
    function addClient($Client_Details)
    {

    }
    function addClientProbes($Client_Probes)
    {

    }

    function getAPMac($AP_Id)
    {

    }
    function getAPByName($AP_Name)
    {

    }
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

$wifi = new wifiCapture();
$MAC = $wifi->addAPMac("11:11:11:11:11:12");
$mac_ID = $MAC["Message"]["Return"];
$AP_NAME = $wifi->addAPName("AlucardATEM", $mac_ID);
print_r($AP_NAME);