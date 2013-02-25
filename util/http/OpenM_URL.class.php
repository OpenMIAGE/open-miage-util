<?php

Import::php("util.wrapper.String");
Import::php("util.OpenM_Log");
Import::php("util.wrapper.RegExp");

/**
 * used to read different part of URL (local/remote)
 * @package OpenM 
 * @subpackage util/http
 * @author Gaël Saunier
 */
class OpenM_URL {

    /**
     * used to know complete server host
     * @return String server host
     */
    public static function getHost() {
        $port = $_SERVER['SERVER_PORT'];
        $s = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] ? 's' : '';
        $p = (($s && $port == "443") || (!$s && $port == "80")) ? '' : ":$port";
        $host = "http$s://" . $_SERVER['HTTP_HOST'] . $p;
        OpenM_Log::debug("host: $host", __CLASS__, __METHOD__, __LINE__);
        return $host;
    }

    /**
     * used to know the complete URL in browser
     * @return String URL in browser
     */
    public static function getURL() {
        $url = self::getHost() . $_SERVER['REQUEST_URI'];
        return $url;
    }

    /**
     * used to know the URL parent of URL given
     * @param String $url is an URL
     * @return String URL parent of URL given
     */
    public static function getDirURL($url = null) {
        $url = self::getURLwithoutParameters($url);
        if (RegExp::ereg("index.php$", $url))
            return substr($url, 0, -9);
        else if (RegExp::ereg("/$", $url)) {
            return $url;
        } else {
            $pos = -(strlen(strrchr($url, "/")));
            if ($pos != 0)
                $url = substr($url, 0, $pos + 1);
            return $url;
        }
    }

    /**
     * used to know the URL parent the URL parent of URL given
     * @param String $url is an URL
     * @return String the URL parent the URL parent of URL given
     */
    public static function getParentDirURL($url = null) {
        $dirURL = self::getDirURL($url);
        if (RegExp::ereg("/$", $dirURL))
            return self::getDirURL(substr($dirURL, 0, -1));
    }

    /**
     * used to know the URL wihtout any parameters
     * @param String $url is an URL
     * @return String URL without any parameter
     * @throws InvalidArgumentException
     */
    public static function getURLwithoutParameters($url = null) {
        if (!String::isStringOrNull($url))
            throw new InvalidArgumentException("url must be a string");
        if ($url == null)
            $url = self::getURL();
        if (RegExp::ereg(".+\?.*", $url)) {
            $url = strstr($url, "?", true);
            return $url;
        } else {
            return $url;
        }
    }

    /**
     * used to encode URL
     * @param String $uri is URL to encode
     * @return String URL encoded
     * @throws InvalidArgumentException
     */
    public static function encode($uri = null) {
        if ($uri === null)
            $uri = self::getURL();
        if (!String::isString($uri))
            throw new InvalidArgumentException("uri must be a string");

        return urlencode($uri);
    }

    /**
     * used to decode URL
     * @param String $uri is URL to decode
     * @return String URL decoded
     * @throws InvalidArgumentException
     */
    public static function decode($uri) {
        if (!String::isStringOrNull($uri))
            throw new InvalidArgumentException("uri must be a string");
        if ($uri == null)
            return null;
        return urldecode($uri);
    }

    /**
     * used to know the URL wihtout any parameters and no www and no http://
     * @param String $url is an URL
     * @return String URL without any parameter
     * @throws InvalidArgumentException
     */
    public static function getURLWithoutHttpAndWww($uri = null) {
        $url = self::getURLwithoutParameters($uri);
        if (RegExp::ereg("^https://", $url))
            $url = substr($url, 8);
        else if (RegExp::ereg("^http://", $url))
            $url = substr($url, 7);
        if (RegExp::ereg("www.", $url))
            $url = substr($url, 4);
        return $url;
    }

    /**
     * used to check if an URL is valid
     * @param String $url to check
     * @return boolean true if URL is valid, else false
     * @throws InvalidArgumentException
     */
    public static function isValid($url) {
        if (!String::isStringOrNull($url))
            throw new InvalidArgumentException("url must be a string");

        if ($url == null)
            return false;

        $pattern = "/^(http|https|ftp)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(:[a-zA-Z0-9]*)?\/?([a-zA-Z0-9\-\._\?\,\'\/\\\+&amp;%\$#\=~])*$/";
        return RegExp::preg($pattern, $url);
    }

}

?>