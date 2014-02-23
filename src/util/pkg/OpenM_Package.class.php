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
            echo " - " . self::$temp . " <b>correctly removed</b><br/>";
        }

        if (mkdir($dir, 0777, true))
            echo " - $dir <b>correctly created</b><br/>";
        else
            die("$dir not correctly created");

        OpenM_Dir::cp("../src", $dir);
        echo " - ../src <b>correctly copied to</b> $dir<br/>";

        self::copyFromFile("build.file.lst");

        self::$version = $versionArray[0] . "." . $versionArray[1] . "_$version";
        if (mkdir(self::$version . "_" . self::$count))
            echo " - " . self::$version . "_" . self::$count . " <b>correctly created</b><br/>";
        else
            die(self::$version . " not correctly created");
        $target_file_name = self::$version . "_" . self::$count . "/" . self::$version . ".zip";
        OpenM_Zip::zip(self::$temp, $target_file_name);
        echo " - " . self::$temp . " <b>correctly ziped to</b> $target_file_name<br/>";
        OpenM_Dir::rm(self::$temp);
        echo " - " . self::$temp . " <b>correctly removed</b><br/>";
        file_put_contents("build.count", self::$count + 1);
    }

    private static function copyFromFile($file) {
        if (!is_file($file)) {
            echo " - <b>$file not found</b><br/>";
            return;
        }
        $path = "../";
        self::cp($file, $path, ".", self::$temp);
    }

    private static function cp($file, $path, $src, $target) {
        if (self::isIgnored($src))
            return;

        if (is_file($path . $src)) {
            if (self::isAllowed($file, $src)) {
                if (!is_dir(dirname($target)))
                    mkdir(dirname($target), 0777, true);
                if (copy($path . $src, $target)) {
                    echo " - $src <b>correctly copied to</b> " . self::$temp . "$src<br/>";
                    return;
                }
                else
                    die("$path$src is not a file or a directory");
            }
            else
                return;
        }

        if (!is_dir($path . $src))
            die("$path$src is not a file or a directory");
        if (!is_dir($target) && self::isAllowed($file, $src) && !self::isIgnored($src)) {
            if (mkdir($target, 0777, true))
                echo " - $path$src <b>correctly created</b><br/>";
            else
                die("$path$src not correctly created");
        }

        $dir = dir($path . $src);
        while (false !== $entry = $dir->read()) {
            if ($entry == '.' || $entry == '..' || self::isIgnored($src . "/" . $entry)) {
                continue;
            }
            self::cp($file, $path, "$src/$entry", "$target/$entry");
        }
        $dir->close();
    }

    private static $ignoreFixed = null;
    private static $ignoreRegExp = null;

    /**
     * @return HashtableString
     */
    private static function ignore() {
        if (self::$ignoreFixed !== null)
            return self::$ignoreFixed;
        self::$ignoreFixed = new HashtableString();
        self::$ignoreRegExp = new HashtableString();
        if (!is_file("build.ignore.file.lst")) {
            echo " - <b>build.ignore.file.lst not found</b><br/>";
            return self::$ignoreFixed;
        }
        $ignore = explode("\r\n", file_get_contents("build.ignore.file.lst"));
        foreach ($ignore as $value) {
            if (RegExp::preg("/\*/", $value)) {
                $pattern = "/^\.\/" . str_replace("*", ".*", str_replace(".", "\.", str_replace("/", "\/", $value))) . "$/";
                self::$ignoreRegExp->put($pattern, $value);
            }
            else
                self::$ignoreFixed->put("./" . $value, $value);
            if (!RegExp::preg("/\/$/", $value)) {
                $pattern = "/^\.\/" . str_replace("*", ".*", str_replace(".", "\.", str_replace("/", "\/", $value))) . "\/.*$/";
                self::$ignoreRegExp->put($pattern, $value);
            }
            echo " - <b>add</b> $value <b>to ignore list</b><br/>";
        }
        return self::$ignoreFixed;
    }

    private static $allowedFixed = null;
    private static $allowedRegExp = null;

    /**
     * @return HashtableString
     */
    private static function allowed($file) {
        if (self::$allowedFixed !== null)
            return self::$allowedFixed;
        self::$allowedFixed = new HashtableString();
        self::$allowedRegExp = new HashtableString();
        if (!is_file($file)) {
            echo " - <b>$file not found</b><br/>";
            return self::$allowedFixed;
        }
        $allowed = explode("\r\n", file_get_contents($file));
        self::$allowedFixed = new HashtableString();
        self::$allowedRegExp = new HashtableString();
        foreach ($allowed as $value) {
            if (RegExp::preg("/\*/", $value)) {
                $pattern = "/^\.\/" . str_replace("*", ".*", str_replace(".", "\.", str_replace("/", "\/", $value))) . "$/";
                self::$allowedRegExp->put($pattern, $value);
            }
            else
                self::$allowedFixed->put("./" . $value, $value);
            if (!RegExp::preg("/\/$/", $value)) {
                $pattern = "/^\.\/" . str_replace("*", ".*", str_replace(".", "\.", str_replace("/", "\/", $value))) . "\/.*$/";
                self::$allowedRegExp->put($pattern, $value);
            }
            echo " - <b>add</b> $value <b>to allowed list</b><br/>";
        }
        return self::$allowedFixed;
    }

    /**
     * @return HashtableString
     */
    private static function ignores() {
        self::ignore();
        return self::$ignoreRegExp;
    }

    /**
     * @return HashtableString
     */
    private static function alloweds($file) {
        self::allowed($file);
        return self::$allowedRegExp;
    }

    private static function isIgnored($path) {
        if (self::ignore()->containsKey($path)) {
            echo " - $path <b>is ignored</b><br/>";
            return true;
        }
        $e = self::ignores()->keys();
        while ($e->hasNext()) {
            $p = $e->next();
            if (RegExp::preg($p, $path)) {
                echo " - $path <b>is ignore by</b> " . self::ignores()->get($p) . "<br/>";
                return true;
            }
        }
        return false;
    }

    private static function isAllowed($file, $path) {
        if (self::allowed($file)->containsKey($path)) {
            echo " - $path <b>is allowed</b><br/>";
            return true;
        }
        $e = self::alloweds($file)->keys();
        while ($e->hasNext()) {
            $p = $e->next();
            if (RegExp::preg($p, $path)) {
                echo " - $path <b>is allowed by</b> " . self::alloweds($file)->get($p) . "<br/>";
                return true;
            }
        }
        return false;
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
                echo " - $target_file_name <b>unZip in</b> " . self::$temp . "<br/>";
            else
                die("<h1>error occurs during unZip of $target_file_name</h1>");
        }
        else
            die('<h1>error occurs</h1>');

        self::copyFromFile("build.full.file.lst");

        echo " - read $lib_path/openm.util.dependencies<br/>";
        $dir = file_get_contents("$lib_path/openm.util.dependencies");
        if (is_dir("../../lib/$dir")) {
            OpenM_Dir::cp("../../lib/$dir", self::$temp . "lib/$dir");
            echo " - $dir <b>correctly copied to</b> " . self::$temp . "/lib<br/>";
        }
        else
            die("$dir is not a directory");

        $e = $dependencies->explore(OpenM_Dependencies::RUN)->putAll($dependencies->explore(OpenM_Dependencies::DISPLAY))->keys();
        while ($e->hasNext()) {
            $dir = $e->next();
            if (is_dir("../../lib/$dir")) {
                OpenM_Dir::cp("../../lib/$dir", self::$temp . "lib/$dir");
                echo " - $dir <b>correctly copied to</b> " . self::$temp . "lib<br/>";
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
                            echo " - $dir :: /bd <b>correctly copied to</b> " . self::$temp . "bd<br/>";
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
        echo " - " . self::$temp . " <b>correctly ziped to</b> $target_full_file_name<br/>";
        OpenM_Dir::rm(self::$temp);
        echo " - " . self::$temp . " <b>correctly removed</b><br/>";

        $dirTarget = self::$version . "_" . self::$count . "/";

        $openm_dependencies = "../lib/" . OpenM_Dependencies::OpenM_DEPENDENCIES;
        if (is_file($openm_dependencies)) {
            if (copy("$openm_dependencies", $dirTarget . OpenM_Dependencies::OpenM_DEPENDENCIES))
                echo " - $openm_dependencies <b>correctly copied to</b> " . $dirTarget . OpenM_Dependencies::OpenM_DEPENDENCIES . " <br/>";
            $explored_dependency_file = Properties::fromFile($openm_dependencies)->getAll();
            $e = $explored_dependency_file->keys();
            while ($e->hasNext()) {
                $file = $explored_dependency_file->get($e->next());
                if (is_file("../lib/" . $file)) {
                    if (copy("../lib/" . $file, $dirTarget . $file))
                        echo " - ../lib/$file <b>correctly copied to</b> " . $dirTarget . $file . " <br/>";
                }
            }
        }
        else
            die("<b>../lib/openm.dependencies not Found</b>");
    }

    public static function deploy($lib_path = null) {
        if ($lib_path == null)
            $lib_path = "../lib";
        self::build_full($lib_path);

        $ftp_file = "build.full.deploy.ftp";
        if (!is_file($ftp_file))
            die("$ftp_file not found");
        $ftp_config = Properties::fromFile($ftp_file);
        $ftp = ftp_connect($ftp_config->get("ftp.host"));
        if (ftp_login($ftp, $ftp_config->get("ftp.login"), $ftp_config->get("ftp.password")))
            echo " - Login ftp OK<br/>";
        else
            die("ko to connect to ftp");

        $file = "$lib_path/version";
        if (!is_file($file))
            throw new OpenM_PackageException("$file not found");

        $version_path = file_get_contents($file);
        echo " - <b>check</b> $version_path on repository<br/>";
        if (ftp_chdir($ftp, $version_path) === false) {
            if (ftp_mkdir($ftp, $version_path))
                echo " - $version_path <b>created</b><br/>";
            else
                die("fail to create " . $version_path . " on repository");
        }
        else if (ftp_chdir($ftp, "/") === false)
            die("fail to reset current directory");
        else
            echo " - $version_path <b>already exist</b><br/>";


        $list = ftp_nlist($ftp, $version_path);
        if ($list !== false) {
            echo " - $version_path <b>check in progress</b><br/>";
            foreach ($list as $value) {
                if ($value == '.' || $value == '..')
                    continue;
                if (ftp_delete($ftp, $version_path . "/" . $value))
                    echo " - $version_path/$value <b>deleted</b><br/>";
                else
                    die("fail to delete $version_path/$value on repository");
            }
        }
        else
            die("fail to check $version_path");

        $local_dir = self::$version . "_" . self::$count;
        if (!$dh = @opendir($local_dir))
            die("$local_dir not found");
        while (false !== ( $obj = readdir($dh) )) {
            if ($obj == '.' || $obj == '..')
                continue;
            echo " - <b>try to push</b> $local_dir/$obj  <b>to</b> /$version_path/$obj<br/>";
            if (ftp_put($ftp, "/$version_path/$obj", "$local_dir/$obj", FTP_BINARY))
                echo " - $local_dir/$obj <b>correctly push on repository</b><br/>";
            else
                die("$version_path/$obj not correctly put on repository");
        }
        closedir($dh);
        ftp_close($ftp);

        $lib_local = "../../lib/$version_path";
        if (is_dir($lib_local)) {
            OpenM_Dir::rm($lib_local);
            echo " - <b>remove</b> $version_path in local lib";
        }
    }

}

?>