<?php

Import::php("util.wrapper.String");
Import::php("util.OpenM_Log");

/**
 * used to modify header of http server response
 * @package OpenM 
 * @subpackage util/http
 * @author Gaël Saunier
 */
class OpenM_Header {

    /**
     * used to redirect client to another URL
     * @param String $url is targeted url of redirection
     * @throws InvalidArgumentException
     */
    public static function redirect($url) {
        if (!String::isString($url))
            throw new InvalidArgumentException("url must be a string");
        OpenM_Log::debug("to $url", __CLASS__, __METHOD__, __LINE__);
        header("Location: $url");
        exit(0);
    }

    public static function add($message, $code) {
        if (!String::isStringOrNull($message))
            throw new InvalidArgumentException("message must be a string");
        if (!is_int($code))
            throw new InvalidArgumentException("code must be an int");
        OpenM_Log::debug($message . "($code)", __CLASS__, __METHOD__, __LINE__);
        header($message, true, $code);
    }

    public static function error($code, $message = null) {
        self::add($_SERVER["SERVER_PROTOCOL"] . " $code $message", $code);
        header("Connection: close");
        exit(0);
    }

    public static function ok() {
        self::add($_SERVER["SERVER_PROTOCOL"] . " 200 OK", 200);
    }

}

?>