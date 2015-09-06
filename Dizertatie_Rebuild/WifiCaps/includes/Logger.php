<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 03.09.2015
 * Time: 16:46
 */
namespace WifiCap;
class Logger
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

    /**
     * display log message
     * @param $log
     */
    function log($log)
    {
        echo 'writing to file ' . $this->_file;
    }
}
