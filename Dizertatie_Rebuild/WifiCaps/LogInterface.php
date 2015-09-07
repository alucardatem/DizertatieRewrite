<?php
/**
 * Created by PhpStorm.
 * User: alucardatem
 * Date: 07.09.2015
 * Time: 10:52
 */
namespace WifiCap;
interface LogInterface
{
    public function info($msg);

    public function error($msg);

    public function warning($msg);

    public function log($msg, $logType);
}
