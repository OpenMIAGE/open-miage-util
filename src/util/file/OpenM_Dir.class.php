<?php

/**
 * used to manage directories
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
class OpenM_Dir {
    
    /**
     * used to remove a directory recursively
     * @param String $dir is directory path to remove
     * @param boolean $DeleteMe if true delete directory given,
     * else only childs of directory given
     */
    public static function rm($dir, $DeleteMe = TRUE) {
        if (!$dh = @opendir($dir))
            return;
        while (false !== ( $obj = readdir($dh) )) {
            if ($obj == '.' || $obj == '..')
                continue;
            if (!@unlink($dir . '/' . $obj))
                self::rm($dir . '/' . $obj, true);
        }

        closedir($dh);
        if ($DeleteMe) {
            @rmdir($dir);
        }
    }
    
    /**
     * used to copy a directory recursively
     * @param String $src is path of directory source
     * @param String $target is targeted path
     * @return boolean true if copy succed, else false
     */
    public static function cp($src, $target) {
        if (is_file($src)) {
            return copy($src, $target);
        }
 
        if (!is_dir($target)) {
            mkdir($target,0777,true);
        }           
        
        $dir = dir($src);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..') {
                continue;
            }
            self::cp("$src/$entry", "$target/$entry");
        }
        $dir->close();
        return true;
    }
    
}
?>