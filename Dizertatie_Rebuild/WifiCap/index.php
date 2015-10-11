<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 09.09.2015
 * Time: 00:25
 */
spl_autoload_register(function ($class) {

    // project-specific namespace prefix
    $prefix = 'WifiCap\\';

    // base directory for the namespace prefix
    $base_dir = __DIR__ . '/core/';

    // does the core use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative core name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative core name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});



$LogInfoFile = "./logs/info.log";
$LogErrorFile = "./logs/error.log";

$LoggerInfo = new \WifiCap\Logger($LogInfoFile);
$LoggerError = new \WifiCap\Logger($LogErrorFile);

$dataBaseConnection = new \WifiCap\MySQLi($LoggerError, $LoggerInfo);
$data = new \WifiCap\Scanner($LoggerError, $LoggerInfo);


$ap = new \WifiCap\AP($dataBaseConnection->_connection, $LoggerError, $LoggerInfo);
$client = new \WifiCap\Client($ap, $dataBaseConnection->_connection, $LoggerError, $LoggerInfo);
$MapGeneratpr = new \WifiCap\MapGenerator();

$list = $data->parseXML('captures/');

if ($list != false) {
    $InfrastructureList = $data->generateList($list);
    $ProbeList = $data->generateList($list, "probe");
    $storeAPInfrastructure = $ap->add($InfrastructureList);
    $storeAPProbes = $ap->add($ProbeList);

    $addInfrastructureClient = $client->add($InfrastructureList);
    $addProbesClient = $client->add($ProbeList);
    $addProbes = $client->addProbes($list);

}
$GenerateNetwokMap = "";
$selected = "";
if (isset($_POST["Submit"])) {
    switch ($_POST["option"]) {
        case "client":
            $search = $client->get($_POST["Search"]);
            //    $selected = "selected='selected'";
            // print_r($_POST);
            // print_r($search);
            //die();
            break;
        case "network":
            $selected = "selected='selected'";
            //  $search = $ap->search($_POST["Search"]);
            break;
        case "encryption":
            $search = $ap->search($_POST["Search"]);
            // $selected = "selected='selected'";
            break;
    }
    $GenerateNetwokMap = $MapGeneratpr->generateMap($search);
}


?>
    <head>
        <script src='https://api.mapbox.com/mapbox.js/v2.2.2/mapbox.js'></script>
        <link href='https://api.mapbox.com/mapbox.js/v2.2.2/mapbox.css' rel='stylesheet'/>
        <script src='//api.tiles.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.2.0/leaflet-omnivore.min.js'></script>

        <style>
            body {
                margin: 0;
                padding: 0;
            }

            #map {
                position: absolute;
                top: 0;
                bottom: 0;
                width: 60%;
                height: 100%;
                border: 1px solid #000000;
            }
        </style>
    </head>

    <body>
    <script src='https://api.mapbox.com/mapbox.js/plugins/leaflet-omnivore/v0.2.0/leaflet-omnivore.min.js'></script>

    <div id="map"></div>
    <div id="content-window"></div>
    <div id="formSearch" style=" float:left; padding-top: 30px;margin-left: 61%;">
        <form method="post">
            <select id="option" name="option">
                <option value="encryption" <?php if (isset($_POST["option"]) AND $_POST["option"] == "encryption") echo "selected"; ?>>
                    Encryption
                </option>
                <option value="client" <?php if (isset($_POST["option"]) AND $_POST["option"] == "client") echo "selected"; ?>>
                    Client
                </option>
                <option value="network" <?php if (isset($_POST["option"]) AND $_POST["option"] == "network") echo "selected"; ?>>
                    Network
                </option>

            </select>
            <input type="text" name="Search" id="Search" value="<?php if (isset($_POST["Search"])) {
                echo $_POST["Search"];
            } ?>"/>
            <input type="submit" name="Submit" id="Submit" value="Search">
        </form>
    </div>
    <?= $MapGeneratpr->javascriptKMLMapDisplay($GenerateNetwokMap); ?>
    <script async defer
            src="https://maps.googleapis.com/maps/api/js?signed_in=true&callback=initMap">
    </script>
    </body>

<?php

