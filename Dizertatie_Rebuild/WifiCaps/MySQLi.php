<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 07.09.2015
 * Time: 15:58
 */

namespace WifiCap;
require_once "Logger.php";
use WifiCap\Logger;

class MySQLi
{

    public $_connection;
    private $_host;
    private $_user;
    private $_password;
    private $_database;
    /**
     * @var Logger
     */
    private $_error;
    private $_log;
    private $logPrefix;

    /**
     * MySQLi constructor.
     */
    public function __construct($_error, $_log)
    {
        $this->_host = "localhost";
        $this->_user = "root";
        $this->_password = "";
        $this->_database = "wifiaps";
        $this->_error = $_error;
        $this->_log = $_log;
        $this->logPrefix = "[" . __CLASS__ . "][" . __FUNCTION__ . "]";
        $this->_connection = $this->connectDB($this->_host, $this->_user, $this->_password, $this->_database);
    }

    /**
     *
     * @param $host
     * @param $user
     * @param $password
     * @param $database
     *
     */
    public function connectDB()
    {
        $connection = mysqli_connect($this->_host, $this->_user, $this->_password, $this->_database);
        if (!$connection) {
            $this->_log->error($this->logPrefix . "connectToDatabase: Error connecting to db");
        }
        return $connection;
    }


}
