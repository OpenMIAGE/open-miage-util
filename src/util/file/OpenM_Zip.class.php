<?php

Import::php("util.wrapper.RegExp");

/**
 * used to zip file or directory recursivly
 * @package OpenM 
 * @subpackage util/file
 * @author Gaël Saunier
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
