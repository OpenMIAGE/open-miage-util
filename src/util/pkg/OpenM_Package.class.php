<?php

Import::php("util.pkg.OpenM_PackageException");
Import::php("util.pkg.OpenM_Dependencies");

/**
 * 
 * @package OpenM 
 * @subpackage util\pkg
 * @copyright (c) 2013, www.open-miage.org
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
 * @author Gael SAUNIER
 */
class OpenM_Package {

    private static $version;
    private static $count;
    private static $temp = "temp/";

    public static function build($lib_path = null) {
        Import::php("util.file.OpenM_Dir");
        Import::php("util.file.OpenM_Zip");

        if ($lib_path == null)
            $lib_path = "../lib";

        if (!is_dir($lib_path))
            throw new OpenM_PackageException("$lib_path must be a valid directory");

        $file = "$lib_path/version";
        if (!is_file($file))
            throw new OpenM_PackageException("$file not found");
        $versionArray = explode("/", file_get_contents($file));
        $version = $versionArray[2];
        $file = "build.count";
        if (!is_file($file))
            throw new OpenM_PackageException("$file not found");
        self::$count = intval(file_get_contents($file));
        $dir = self::$temp . "lib/" . $versionArray[0] . "/" . $versionArray[1] . "/$version";
        if (is_dir(self::$temp)) {
            OpenM_Dir::rm(self::$temp);
            echo " - " . self::$temp . " <b>correctly removed</b><br>";
        }

        if (mkdir($dir, 0777, true))
            echo " - $dir <b>correctly created</b><br>";
        else
            die("$dir not correctly created");

        OpenM_Dir::cp("../src", $dir);
        echo " - ../src <b>correctly copied to</b> $dir<br>";
        $file = "build.file.lst";
        if (!is_file($file))
            throw new OpenM_PackageException("$file not found");
        $file_lst = file_get_contents($file);
        $file_array = explode("\r\n", $file_lst);
        $path = "../";
        foreach ($file_array as $value) {
            if (is_file($path . $value)) {
                copy($path . $value, self::$temp . $value);
                echo " - $value <b>correctly copied to</b> " . self::$temp . "$value<br>";
            } else if (is_dir($path . $value)) {
                OpenM_Dir::cp($path . $value, self::$temp . $value);
                echo " - $value <b>correctly copied to</b> " . self::$temp . "$value<br>";
            }
            else
                die("$path$value is not a file or a directory");
        }
        self::$version = $versionArray[0] . "." . $versionArray[1] . "_$version";
        if (mkdir(self::$version . "_" . self::$count))
            echo " - " . self::$version . "_" . self::$count . " <b>correctly created</b><br>";
        else
            die(self::$version . " not correctly created");
        $target_file_name = self::$version . "_" . self::$count . "/" . self::$version . ".zip";
        OpenM_Zip::zip(self::$temp, $target_file_name);
        echo " - " . self::$temp . " <b>correctly ziped to</b> $target_file_name<br>";
        OpenM_Dir::rm(self::$temp);
        echo " - " . self::$temp . " <b>correctly removed</b><br>";
        file_put_contents("build.count", self::$count + 1);
    }

    public static function build_full($lib_path = null) {
        self::build($lib_path);
        if ($lib_path == null)
            $lib_path = "../lib";
        $dependencies = new OpenM_Dependencies($lib_path);
        $dependencies->addInClassPath(OpenM_Dependencies::RUN);
        $dependencies->addInClassPath(OpenM_Dependencies::DISPLAY);
        $target_file_name = self::$version . "_" . self::$count . "/" . self::$version . ".zip";
        $target_full_file_name = self::$version . "_" . self::$count . "/" . self::$version . "_full.zip";
        $zip = new ZipArchive();
        $res = $zip->open($target_file_name);
        if ($res === TRUE) {
            if ($zip->extractTo(self::$temp))
                echo " - $target_file_name <b>unZip in</b> " . self::$temp . "<br>";
            else
                die("<h1>error occurs during unZip of $target_file_name</h1>");
        }
        else
            die('<h1>error occurs</h1>');

        $file = "build.full.file.lst";
        if (!is_file($file))
            throw new OpenM_PackageException("$file not found");
        $file_lst = file_get_contents($file);
        $file_array = explode("\r\n", $file_lst);
        $path = "../";
        foreach ($file_array as $value) {
            if (is_file($path . $value)) {
                copy($path . $value, self::$temp . $value);
                echo " - $value <b>correctly copied to</b> " . self::$temp . "$value<br>";
            } else if (is_dir($path . $value)) {
                OpenM_Dir::cp($path . $value, self::$temp . $value);
                echo " - $value <b>correctly copied to</b> " . self::$temp . "$value<br>";
            }
            else
                die("$path$value is not a file or a directory");
        }

        echo " - read $lib_path/openm.util.dependencies<br>";
        $dir = file_get_contents("$lib_path/openm.util.dependencies");
        if (is_dir("../../lib/$dir")) {
            OpenM_Dir::cp("../../lib/$dir", self::$temp . "lib/$dir");
            echo " - $dir <b>correctly copied to</b> " . self::$temp . "/lib<br>";
        }
        else
            die("$dir is not a directory");

        $e = $dependencies->explore(OpenM_Dependencies::RUN)->putAll($dependencies->explore(OpenM_Dependencies::DISPLAY))->keys();
        while ($e->hasNext()) {
            $dir = $e->next();
            if (is_dir("../../lib/$dir")) {
                OpenM_Dir::cp("../../lib/$dir", self::$temp . "lib/$dir");
                echo " - $dir <b>correctly copied to</b> " . self::$temp . "lib<br>";
            }
        }
        $d = $dependencies->explore(OpenM_Dependencies::RUN);
        $f = $d->keys();
        while ($f->hasNext()) {
            $dir = $f->next();
            if (RegExp::ereg("^OpenM", $dir)) {
                $value = $d->get($dir);
                $zip = explode("::", $value);
                if (copy($zip[0], self::$temp . "temp-bd.zip")) {
                    if (OpenM_Zip::unZip(self::$temp . "temp-bd.zip", self::$temp . "temp-bd")) {
                        if (is_dir(self::$temp . "temp-bd/bd")) {
                            OpenM_Dir::cp(self::$temp . "temp-bd/bd", self::$temp . "bd");
                            echo " - $dir :: /bd <b>correctly copied to</b> " . self::$temp . "bd<br>";
                        }
                    }
                    else
                        die(self::$temp . "/temp-bd.zip isn't a zip");
                    unlink(self::$temp . "/temp-bd.zip");
                    OpenM_Dir::rm(self::$temp . "/temp-bd");
                }
                else
                    die("$zip[0] not found on repository");
            }
        }

        OpenM_Zip::zip(self::$temp, $target_full_file_name);
        echo " - " . self::$temp . " <b>correctly ziped to</b> $target_full_file_name<br>";
        OpenM_Dir::rm(self::$temp);
        echo " - " . self::$temp . " <b>correctly removed</b><br>";

        $dirTarget = self::$version . "_" . self::$count . "/";

        $openm_dependencies = "../lib/" . OpenM_Dependencies::OpenM_DEPENDENCIES;
        if (is_file($openm_dependencies)) {
            if (copy("$openm_dependencies", $dirTarget . OpenM_Dependencies::OpenM_DEPENDENCIES))
                echo " - $openm_dependencies <b>correctly copied to</b> " . $dirTarget . OpenM_Dependencies::OpenM_DEPENDENCIES . " <br>";
            $explored_dependency_file = Properties::fromFile($openm_dependencies)->getAll();
            $e = $explored_dependency_file->keys();
            while ($e->hasNext()) {
                $file = $explored_dependency_file->get($e->next());
                if (is_file("../lib/" . $file)) {
                    if (copy("../lib/" . $file, $dirTarget . $file))
                        echo " - ../lib/$file <b>correctly copied to</b> " . $dirTarget . $file . " <br>";
                }
            }
        }
        else
            die("<b>../lib/openm.dependencies not Found</b>");
    }

}

?>