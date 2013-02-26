<?php

Import::php("util.OpenM_Log");

/**
 * Class wrapper of $_SESSION to use session as an object
 * @package OpenM 
 * @subpackage util/session
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
class OpenM_SessionController {
    
    private static $isOpen = false;
    
    /**
     * used to open a php session.
     * it's possible to open a session with different name
     * @param String $name is session name chosen
     * @return boolean return true if session is open, else false
     */
    public static function open($name=null){
        if(self::isOpened())
            return true;
        
        if($name!=null && !is_string($name))
            session_name($name);
        OpenM_Log::debug("Open session", __CLASS__, __METHOD__, __LINE__);
        self::$isOpen = session_start();
        return self::$isOpen;
    }
    
    /**
     * used to know if a php session is opened
     * @return boolean true if a php session is opened
     */
    public static function isOpened(){
        return session_id()!="";
    }
    
    /**
     * used to close the php session
     * @return boolean true if php session is closed, else false
     */
    public static function close(){
        if(!self::$isOpen)
           return true;
        
       session_write_close();
       self::$isOpen = false;
       return !self::$isOpen;
    }
    
    /**
     * used to recover a property from php session
     * @param String $key is name of property searched
     * @return null|mixed value of searche key, else null
     */
    public static function get($key){
        self::open();
        return isset($_SESSION["$key"])?$_SESSION["$key"]:null;
    }
    
    /**
     * used to set a property value into php session
     * @param String $key is name of property
     * @param String $value is value of property
     */
    public static function set($key, $value){
        self::open();
        $_SESSION["$key"] = $value;
    }
    
    /**
     * used to remove property name/value from php session
     * @param String $key is name of property
     */
    public static function remove($key) {
        self::open();
        if($key instanceof String)
            $key = "$key";
        if(self::contains($key))
            unset($_SESSION[$key]);
    }
    
    /**
     * used to know if php session contains given property
     * @param String $key is name of property
     * @return boolean true if php session contains property, else false
     */
    public static function contains($key) {
        self::open();
        return isset($_SESSION["$key"]) && $_SESSION["$key"]!=null;
    }
}
?>