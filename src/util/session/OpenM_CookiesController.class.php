<?php

Import::php("util.OpenM_Log");

/**
 * Class wrapper of $_COOKIE to use cookies as an object
 * @package OpenM
 * @subpackage util/session
 * @author Gaël Saunier
 */
class OpenM_CookiesController {

    /**
     * used to recover value of cookie key given
     * @param String $key is name of stored property cookies
     * @return String value of associated property
     */
    public static function get($key) {
        OpenM_Log::debug("$key", __CLASS__, __METHOD__, __LINE__);
        return $_COOKIE[$key];
    }

    /**
     * used to store a property in cookies
     * @param String $key name of property stored in cookies
     * @param String $value value of property stored in cookies
     * @param String $expire expiration date of property in cookies
     * @param String $path
     * @param String $domain
     */
    public static function set($key, $value, $expire, $path = null, $domain = null) {
        OpenM_Log::debug("$key=$value ($expire)", __CLASS__, __METHOD__, __LINE__);
        if (!setcookie($key, $value, $expire, $path, $domain))
            OpenM_Log::debug("cookie not stored...", __CLASS__, __METHOD__, __LINE__);
    }

    /**
     * 
     * @param String $key
     */
    public static function remove($key) {
        OpenM_Log::debug("$key", __CLASS__, __METHOD__, __LINE__);
        setcookie($key, null);
    }

    /**
     * 
     * @param String $key
     * @return boolean
     */
    public static function contains($key) {
        OpenM_Log::debug("$key='".$_COOKIE[$key]."'", __CLASS__, __METHOD__, __LINE__);
        return isset($_COOKIE[$key]) && $_COOKIE[$key] != null;
    }

}

?>