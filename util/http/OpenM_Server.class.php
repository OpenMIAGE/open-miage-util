<?php

Import::php("util.crypto.OpenM_Crypto");
Import::php("util.OpenM_Log");

/**
 * Server class to read SERVER properties.
 * @package OpenM 
 * @subpackage util/http
 * @author Gaël Saunier
 */
class OpenM_Server {
    
    /**
     * used to recover http client caller IP
     * @return String IP of http client caller
     */
    public static function getClientIp(){
        return $_SERVER["REMOTE_ADDR"];
    }
    
    /**
     * used to recover http client caller encrypted IP
     * @param String $algo is algo chosen to encrypt IP
     * @param String $secret is security secret included in hash calculation
     * @return String encrypted IP
     * @uses OpenM_Crypto::hash
     * @throws InvalidArgumentException
     */
    public static function getClientIpCrypted($algo, $secret=null){
        if(!String::isStringOrNull($secret))
            throw new InvalidArgumentException("secret must be a string");
        if(!OpenM_Crypto::isAlgoValid($algo))
            throw new InvalidArgumentException("algo must be a valid algorithm");
        if($secret==null)
            $secret = "";
        OpenM_Log::debug("encrypt", __CLASS__, __METHOD__, __LINE__);
        return OpenM_Crypto::hash($algo, $secret.(self::getClientIp()).$secret);
    }
}

?>
