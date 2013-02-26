<?php

Import::php("util.wrapper.RegExp");
Import::php("util.OpenM_Log");

/**
 * 
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
 * @author Gaël Saunier
 */
class OpenM_FileLoader {

    const FILE_URL_PARAMETER = "f";

    public static function display($file) {
        if (is_file($file))
            die("Forbidden display");

        $path = Import::getAbsolutePath($file);
        if ($path == null)
            throw new Exception("file not found");
        else {
            if (!is_file($path))
                die("Forbidden display");
        }

        $ext = strtolower($path);
        $ext = substr($ext, strrpos($ext, ".") + 1);

        $image = false;
        switch ($ext) {
            case "css":
                header('Content-type: text/css');
                break;
            case "js":
                header('Content-type: text/javascript');
                break;
            case "png":
                header('Content-type: image/png');
                $image = true;
                break;
            case "jpg":
                header('Content-type: image/jpeg');
                $image = true;
                break;
            case "jpeg":
                header('Content-type: image/jpeg');
                $image = true;
                break;
            case "gif":
                header('Content-type: image/gif');
                $image = true;
                break;
            case "tif":
                header('Content-type: image/tiff');
                $image = true;
                break;
            case "tiff":
                header('Content-type: image/tiff');
                $image = true;
                break;
            default:
                die("Forbidden file extension $ext");
                break;
        }

        @readfile($path);
    }

    public static function handle() {
        if (isset($_GET[self::FILE_URL_PARAMETER])) {
            try {
                self::display($_GET[self::FILE_URL_PARAMETER]);
            } catch (Exception $e) {
                die($e->getMessage());
            }
        }
        else
            die("file not found");
    }

}

?>