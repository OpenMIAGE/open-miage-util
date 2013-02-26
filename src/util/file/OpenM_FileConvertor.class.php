<?php

/**
 * used to convert file to string
 * @package OpenM 
 * @subpackage util/file
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