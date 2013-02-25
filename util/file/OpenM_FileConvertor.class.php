<?php

/**
 * used to convert file to string
 * @package OpenM 
 * @subpackage util/file
 * @author Gaël Saunier
 */
class OpenM_FileConvertor {
    
    /**
     * used to convert file to string 64 bytes encoded
     * @param String $path is file path to encode
     * @return String is 64 bytes encoded file
     * @throws InvalidArgumentException if given path doesn't exist
     */
    public static function fileTo64($path){
        if(!is_file($path))
            throw new InvalidArgumentException("path must be a valid path");
        return base64_encode(file_get_contents($path));
    }
    
    /**
     * 
     * @param type $string
     * @param type $path
     * @return type
     */
    public static function stringtoFile($string, $path){
        return file_put_contents($path, base64_decode($string));
    }
}

?>