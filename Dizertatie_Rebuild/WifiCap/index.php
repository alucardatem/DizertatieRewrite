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
    $base_dir = __DIR__ . '/class/';

    // does the class use the namespace prefix?
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // no, move to the next registered autoloader
        return;
    }

    // get the relative class name
    $relative_class = substr($class, $len);

    // replace the namespace prefix with the base directory, replace namespace
    // separators with directory separators in the relative class name, append
    // with .php
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // if the file exists, require it
    if (file_exists($file)) {
        require $file;
    }
});

use WifiCap\Logger;
use WifiCap\MySQLi;
use WifiCap\Scanner;
use WifiCap\Client;
use WifiCap\AP;

$LogInfoFile = "./logs/info.log";
$LogErrorFile = "./logs/error.log";

$LoggerInfo = new \WifiCap\Logger($LogInfoFile);
$LoggerError = new \WifiCap\Logger($LogErrorFile);

$dataBaseConnection = new \WifiCap\MySQLi($LoggerError, $LoggerInfo);
$data = new \WifiCap\Scanner($LoggerError, $LoggerInfo);


$ap = new \WifiCap\AP($dataBaseConnection->_connection, $LoggerError, $LoggerInfo);
$client = new \WifiCap\Client($ap, $dataBaseConnection->_connection, $LoggerError, $LoggerInfo);


$list = $data->parseXML('captures/');

$InfrastructureList = $data->generateList($list);
$ProbeList = $data->generateList($list, "probe");


$storeAPInfrastructure = $ap->add($InfrastructureList);
$storeAPProbes = $ap->add($ProbeList);

$addInfrastructureClient = $client->add($InfrastructureList);
$addProbesClient = $client->add($ProbeList);

$searchClient = $client->get();
$addProbes = $client->addProbes($list);
$NetworkList = $ap->searchNetwork("CODE932_GUEST");
$addPass = $ap->updateNetworkPassword($NetworkList, "guest932code");

echo "\n\n**************\n\n";
print_r($storeAPInfrastructure);
echo "\n\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n\n";
print_r($addInfrastructureClient);
echo "\n\n**************\n\n";

print_r($storeAPProbes);
echo "\n\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n\n";
print_r($addProbesClient);
echo "\n\n**************\n\n";

print_r($searchClient);
echo "\n\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n\n";
print_r($addProbes);
echo "\n\n**************\n\n";
print_r($NetworkList);
echo "\n\n%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%%\n\n";
print_r($addPass);
echo "\n\n**************\n\n";
