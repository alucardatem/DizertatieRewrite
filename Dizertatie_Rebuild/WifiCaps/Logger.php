<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 04.09.2015
 * Time: 10:49
 */

namespace WifiCap;
require_once "LogInterface.php";

class Logger implements LogInterface
{

    private $_file;

    /**
     * Logger constructor.
     * @param $_file
     */
    public function __construct($_file)
    {
        $this->_file = $_file;
    }

    public function info($msg)
    {
        $this->log($msg, "info");
    }

    /**
     * display log message
     * @param $log
     */
    public function log($log, $logType = "info")
    {
        $logTypeArray = array("info", "warning", "error", "exception");
        if (!in_array($logType, $logTypeArray)) {
            $logType = "info";
        }

        if (!(is_string($log))) {
            $log = json_encode($log);
        }

        $message = "[" . strtoupper($logType) . "][" . date("H:i:s") . "]-->" . $log . "\n";
        file_put_contents($this->_file, $message, FILE_APPEND | LOCK_EX);
    }

    /**
     * @param string $msg
     */
    public function error($msg)
    {
        $this->log($msg, "error");
    }

    public function warning($msg)
    {
        $this->log($msg, "warning");
    }

    public function exception($msg)
    {
        $this->log($msg, "exception");
    }


}

