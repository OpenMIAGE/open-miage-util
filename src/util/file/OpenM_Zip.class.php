<?php

Import::php("util.wrapper.RegExp");

/**
 * used to zip file or directory recursivly
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
class OpenM_Zip {

    /**
     * used to zip file or directory recursivly
     * @param String $src is path of file or directory to zip
     * @param String $target is path of targeted zip
     * @throws InvalidArgumentException
     */
    public static function zip($src, $target) {
        if (substr($src, -1) === '/') {
            $src = substr($src, 0, -1);
        }
        $path_length = strlen($src) + (RegExp::ereg("/$", $src) ? 0 : 1);
        $zip = new ZipArchive;
        $res = $zip->open($target, ZipArchive::CREATE);
        if ($res !== TRUE) {
            throw new InvalidArgumentException('Error: Unable to create zip file');
        }
        if (is_file($src)) {
            $zip->addFile($src, substr($src, $path_length));
        } else {
            if (!is_dir($src)) {
                $zip->close();
                @unlink($target);
                throw new InvalidArgumentException("Error: File not found ($src)");
            }
            self::recurse_zip($src, $zip, $path_length);
        }
        $zip->close();
    }

    private static function recurse_zip($src, &$zip, $path_length) {
        $dir = opendir($src);
        while (false !== ( $file = readdir($dir))) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if (is_dir($src . '/' . $file)) {
                    $zip->addEmptyDir(substr($src . '/' . $file, $path_length));
                    self::recurse_zip($src . '/' . $file, $zip, $path_length);
                } else {
                    $zip->addFile($src . '/' . $file, substr($src . '/' . $file, $path_length));
                }
            }
        }
        closedir($dir);
    }

}

?>
