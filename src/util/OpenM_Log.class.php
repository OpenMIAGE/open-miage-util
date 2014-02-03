<?php

Import::php("util.wrapper.String");

/**
 * OpenM_Log is the log class of OpenM package
 * @package OpenM 
 * @subpackage util 
 * @copyright (c) 2013, www.open-miage.org
 * @license http://www.apache.org/licenses/LICENSE-2.0 Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 * 
 *     http://www.apache.org/licenses/LICENSE-2.0
 * 
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 * @link http://www.open-miage.org
 * @author Gael Saunier
 */
final class OpenM_Log {

    private static $log;
    private static $level;
    private static $max_lengh;

    const LOG_FILE_NAME = "OpenM_Log.log";
    const LOG_FILE_NAME_PATERN = "^([a-zA-Z0-9]|_|-|\.)+$";
    const INFO = 1;
    const DEBUG = 0;
    const WARNING = 2;
    const ERROR = 3;
    const MESSAGE_DEFAULT_MAX_LENGH = 200;

    public static function init($logPath, $level = null, $logFileName = null, $max_lengh = null) {
        if (!String::isString($logPath))
            throw new InvalidArgumentException("logDirPath must be a string");
        if (!is_dir($logPath)) {
            if (!mkdir($logPath, 0700, true))
                throw new InvalidArgumentException("logAbsoluteDirPath dir not found and couldn't be created");
        }
        if (is_numeric($level))
            $level = $level . "";
        if (!String::isStringOrNull($level))
            throw new InvalidArgumentException("level must be a string or an integer");
        if (!String::isStringOrNull($logFileName))
            throw new InvalidArgumentException("logFileName must be a string");
        if (String::isString($max_lengh)) {
            if ($max_lengh instanceof String)
                $max_lengh = "$max_lengh";

            $max_lengh = intval($max_lengh);
        }
        if ($max_lengh === null)
            $max_lengh = self::MESSAGE_DEFAULT_MAX_LENGH;
        if (!is_int($max_lengh))
            throw new InvalidArgumentException("max_lengh must be an int");

        self::$max_lengh = $max_lengh;

        $log = realpath($logPath);

        switch ($level) {
            case "INFO":
                $level = self::INFO;
                break;
            case self::INFO . "":
                $level = self::INFO;
                break;
            case "DEBUG":
                $level = self::DEBUG;
                break;
            case self::DEBUG . "":
                $level = self::DEBUG;
                break;
            case "WARNING":
                $level = self::WARNING;
                break;
            case self::WARNING . "":
                $level = self::WARNING;
                break;
            case "ERROR":
                $level = self::ERROR;
                break;
            case self::ERROR . "":
                $level = self::ERROR;
                break;
            default:
                $level = self::INFO;
                break;
        }

        self::$level = $level;

        if ($logFileName == null)
            $fileName = self::LOG_FILE_NAME;
        else if (RegExp::ereg(self::LOG_FILE_NAME_PATERN, $logFileName)) {
            $fileName = $logFileName;
        }
        else
            throw new InvalidArgumentException("file name must be valid");

        self::$log = $log . "/" . $fileName;
    }

    private static function add($message) {
        if (!is_file(self::$log))
            file_put_contents(self::$log, self::getLine("Start") . "\r\n");
        $file = fopen(self::$log, "a");
        fwrite($file, self::getLine($message) . "\r\n");
        fclose($file);
    }

    private static function getLine($message) {
        $time = explode(".", microtime(true));
        return date("Y-m-d H:i:s") . ":" . $time[1] . (substr("000", 0, -strlen($time[1]))) . " " . $message;
    }

    private static function log($level, $message, $class = null, $method = null, $line = null) {
        if (strlen($message) > self::$max_lengh)
            $message = substr($message, 0, self::$max_lengh) . "...";
        self::add($level . (($class != null && $class != "") ? " $class" . (($method != null && $method != "") ?
                                "." . (str_replace($class . "::", "", $method)) . (($line != null && $line != "") ? "($line)" : "") : "") . " - " : "") . $message);
    }

    public static function debug($message, $class = null, $method = null, $line = null) {
        if (self::check())
            return;
        if (self::$level > self::DEBUG)
            return;
        self::log("[DEBUG]", $message, $class, $method, $line);
    }

    public static function info($message, $class = null, $method = null, $line = null) {
        if (self::check())
            return;
        if (self::$level > self::INFO)
            return;
        self::log("[INFO]", $message, $class, $method, $line);
    }

    public static function warning($message, $class = null, $method = null, $line = null) {
        if (self::check())
            return;
        if (self::$level > self::WARNING)
            return;
        self::log("[WARNING]", $message, $class, $method, $line);
    }

    public static function error($message, $class = null, $method = null, $line = null) {
        if (self::check())
            return;
        if (self::$level > self::ERROR)
            return;
        self::log("[ERROR]", $message, $class, $method, $line);
    }

    private static function check() {
        return (self::$log == null);
    }

}

?>