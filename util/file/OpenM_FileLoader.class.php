<?php

Import::php("util.wrapper.RegExp");
Import::php("util.OpenM_Log");

/**
 * 
 * @package OpenM 
 * @subpackage util/file
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