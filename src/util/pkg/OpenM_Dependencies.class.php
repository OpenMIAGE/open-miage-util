<?php

Import::php("util.Properties");
Import::php("util.OpenM_Log");
Import::php("util.wrapper.RegExp");
Import::php("util.file.OpenM_Zip");
Import::php("util.file.OpenM_Dir");

/**
 * Dependencies management tool, used to download dependencies and transitive dependencies.
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
class OpenM_Dependencies {

    const OpenM_DEPENDENCIES = "openm.dependencies";
    const INTERNAL = "internal";
    const EXTERNAL = "external";
    const TEST = ".test";
    const RUN = ".run";
    const DISPLAY = ".display";
    const COMPILED_SUFFIX = ".compiled";
    const CHECK_SUFFIX = ".check";
    const INTERNAL_REPOSITORY_URL_KEY = "openm.internal.repository";

    /**
     *
     * @var HashtableString 
     */
    private $dependencies_test;
    private $dependencies_test_loaded = false;
    private $dependencies_run;
    private $dependencies_run_loaded = false;
    private $dependencies_display;
    private $dependencies_display_loaded = false;
    private $lib_path;

    /**
     * @param String $lib_path is directory path that contain openm.dependencies
     * @throws InvalidArgumentException
     */
    public function __construct($lib_path) {
        if (!String::isString($lib_path))
            throw new InvalidArgumentException("lib_path must be a string");
        if (!is_dir($lib_path))
            throw new InvalidArgumentException("lib_path must be a valid directory path");
        $this->lib_path = realpath($lib_path);
        OpenM_Log::debug("initialize dependencies in $lib_path", __CLASS__, __METHOD__, __LINE__);
        $this->dependencies_run = new HashtableString();
        $this->dependencies_test = new HashtableString();
        $this->dependencies_display = new HashtableString();
    }

    /**
     * used to recover dependencies list
     * @param boolean $test is true to recover test dependencies only else false
     * @return HashtableString
     */
    public function explore($type = self::RUN) {
        OpenM_Log::debug("explore $type", __CLASS__, __METHOD__, __LINE__);
        switch ($type) {
            case self::RUN:
                if ($this->dependencies_run_loaded)
                    return $this->dependencies_run->copy();
                else
                    $this->dependencies_run_loaded = true;
                return $this->_explore($this->lib_path, $this->dependencies_run, self::RUN)->copy();
                break;
            case self::TEST:
                if ($this->dependencies_test_loaded)
                    return $this->dependencies_test->copy();
                else
                    $this->dependencies_test_loaded = true;
                return $this->_explore($this->lib_path, $this->dependencies_test, self::TEST)->copy();
                break;
            case self::DISPLAY:
                if ($this->dependencies_display_loaded)
                    return $this->dependencies_display->copy();
                else
                    $this->dependencies_display_loaded = true;
                return $this->_explore($this->lib_path, $this->dependencies_display, self::DISPLAY)->copy();
                break;
            default:
                throw new InvalidArgumentException("type bad value");
                break;
        }
    }

    private function _explore($explore_dir_path, HashtableString $dependencies, $type = self::RUN) {
        $explore_dir_path_formated = $explore_dir_path . (RegExp::preg("/\/$/", $explore_dir_path) ? "" : "/");
        OpenM_Log::debug("$type/read: " . $explore_dir_path_formated . self::OpenM_DEPENDENCIES, __CLASS__, __METHOD__, __LINE__);
        $explored_dependency_file = Properties::fromFile($explore_dir_path_formated . self::OpenM_DEPENDENCIES)->getAll();
        $e = $explored_dependency_file->keys();
        while ($e->hasNext()) {
            $file_key = $e->next();
            if ($file_key == self::INTERNAL . $type) {
                OpenM_Log::debug("$type/read: " . $explore_dir_path_formated . $explored_dependency_file->get($file_key), __CLASS__, __METHOD__, __LINE__);
                $internal_file = Properties::fromFile($explore_dir_path_formated . $explored_dependency_file->get($file_key));
                if ($internal_file->get(self::INTERNAL_REPOSITORY_URL_KEY) != null)
                    $repository_url = $internal_file->get(self::INTERNAL_REPOSITORY_URL_KEY);
                $lib_enum = $internal_file->getAll()->keys();
                while ($lib_enum->hasNext()) {
                    $dependency = $lib_enum->next();
                    if ($dependencies->containsKey($dependency))
                        continue;
                    if ($dependency == self::INTERNAL_REPOSITORY_URL_KEY)
                        continue;
                    $file_path = $internal_file->get($dependency);
                    $remote_dir = $repository_url . $dependency . "/";
                    OpenM_Log::debug("add $dependency=$remote_dir$file_path::/lib/$dependency", __CLASS__, __METHOD__, __LINE__);
                    $dependencies->put($dependency, $remote_dir . $file_path . "::/lib/" . $dependency);
                    $this->_explore($remote_dir, $dependencies);
                }
            } else if ($file_key == self::EXTERNAL . $type) {
                OpenM_Log::debug("$type/read: " . $explore_dir_path_formated . $explored_dependency_file->get($file_key), __CLASS__, __METHOD__, __LINE__);
                $external_file = Properties::fromFile($explore_dir_path_formated . $explored_dependency_file->get($file_key));
                $lib_enum = $external_file->getAll()->keys();
                while ($lib_enum->hasNext()) {
                    $dependency = $lib_enum->next();
                    OpenM_Log::debug("add $dependency=" . $external_file->get($dependency), __CLASS__, __METHOD__, __LINE__);
                    $dependencies->put($dependency, $external_file->get($dependency));
                }
            }
        }
        return $dependencies;
    }

    /**
     * used to download and install all dependencies required
     * @param String $temp_path is a directory path required as temporary directory
     * @param boolean $display is true to activate follow-up in display else false
     * @throws InvalidArgumentException
     */
    public function install($temp_path, $type = self::RUN, $display = false) {
        if (!String::isString($temp_path))
            throw new InvalidArgumentException("lib_path must be a string");
        if (!is_dir($temp_path) && !RegExp::preg("/^\//", $temp_path) && !RegExp::preg("/^\./", $temp_path))
            throw new InvalidArgumentException("lib_path must be a valid directory path");
        if (!is_bool($display))
            throw new InvalidArgumentException("display must be a boolean");
        if (!String::isString($type) && !is_array($type))
            throw new InvalidArgumentException("type must be a string or an array");
        if (!$this->isValid($type))
            throw new InvalidArgumentException("type must be a valid type");
        if ($display)
            echo "Installation start:<br>";
        $temp_path_formated = (RegExp::preg("/\/$/", $temp_path) ? substr($temp_path, 0, -1) : $temp_path);
        if (String::isString($type))
            $dependencies = $this->explore($type);
        else {
            $dependencies = new HashtableString();
            foreach ($type as $value) {
                $dependencies->putAll($this->explore($value));
            }
        }
        OpenM_Log::debug("install with temp: $temp_path for type: $type", __CLASS__, __METHOD__, __LINE__);
        if ($display)
            echo " - All dependencies <b>successfully explored</b><br>";
        $e = $dependencies->keys();
        while ($e->hasNext()) {
            if (is_dir($temp_path_formated)) {
                OpenM_Dir::rm($temp_path_formated);
                OpenM_Log::debug("remove $temp_path_formated", __CLASS__, __METHOD__, __LINE__);
            }
            $dependency = $e->next();
            $dependency_values = explode("=", $dependencies->get($dependency));
            $dependency_paths = explode("::", $dependency_values[0]);
            $dependency_path = $dependency_paths[0];
            $dependency_dir = Import::LIB . (RegExp::preg("/\/$/", Import::LIB) ? "" : "/") . $dependency;
            OpenM_Log::debug("dependency dir: $dependency_dir", __CLASS__, __METHOD__, __LINE__);
            if (is_dir($dependency_dir))
                continue;
            OpenM_Log::debug("create $temp_path_formated", __CLASS__, __METHOD__, __LINE__);
            OpenM_Dir::mk($temp_path_formated);
            OpenM_Log::debug("create $dependency_dir", __CLASS__, __METHOD__, __LINE__);
            OpenM_Dir::mk($dependency_dir);
            if (RegExp::preg("/\.zip$/", $dependency_path)) {
                OpenM_Log::debug("unZip", __CLASS__, __METHOD__, __LINE__);
                $dependency_name = time();
                OpenM_Log::debug("copy($dependency_path, $temp_path_formated/$dependency_name)", __CLASS__, __METHOD__, __LINE__);
                copy($dependency_path, $temp_path_formated . "/" . $dependency_name);
                OpenM_Log::debug("unZip($temp_path_formated/$dependency_name, $temp_path_formated)", __CLASS__, __METHOD__, __LINE__);
                OpenM_Zip::unZip($temp_path_formated . "/" . $dependency_name, $temp_path_formated);
                OpenM_Log::debug("remove $temp_path_formated/$dependency_name", __CLASS__, __METHOD__, __LINE__);
                unlink($temp_path_formated . "/" . $dependency_name);
                OpenM_Log::debug("$temp_path/(isset($dependency_paths[1]) ? $dependency_paths[1] : ''), $dependency_dir", __CLASS__, __METHOD__, __LINE__);
                OpenM_Dir::cp("$temp_path/" . (isset($dependency_paths[1]) ? $dependency_paths[1] : ""), $dependency_dir);
                OpenM_Log::debug("$dependency_path successfully copied and unZip in $dependency_dir", __CLASS__, __METHOD__, __LINE__);
                if ($display)
                    echo " - $dependency_path <b>successfully copied and unZip in</b> $dependency_dir<br>";
            } else {
                if (isset($dependency_values[1]) && $dependency_values[1] != "") {
                    OpenM_Log::debug("copy($dependency_path, $dependency_dir/$dependency_values[1])", __CLASS__, __METHOD__, __LINE__);
                    copy($dependency_path, $dependency_dir . "/" . $dependency_values[1]);
                    OpenM_Log::debug("$dependency_path successfully copied to $dependency_dir/$dependency_values[1]", __CLASS__, __METHOD__, __LINE__);
                    if ($display)
                        echo " - $dependency_path <b>successfully copied to</b> $dependency_dir/$dependency_values[1]<br>";
                } else {
                    $target = $dependency_dir . "/" . substr($dependency_path, strrpos($dependency_path, "/") + 1);
                    OpenM_Log::debug("copy($dependency_path, $dependency_dir/$target)", __CLASS__, __METHOD__, __LINE__);
                    if (copy($dependency_path, "$dependency_dir/$target"))
                        OpenM_Log::debug("$dependency_path successfully copied to $dependency_dir/$target", __CLASS__, __METHOD__, __LINE__);
                    if ($display)
                        echo " - $dependency_path <b>successfully copied to</b> $dependency_dir/$target<br>";
                }
            }
        }
        OpenM_Log::debug("remove $temp_path", __CLASS__, __METHOD__, __LINE__);
        OpenM_Dir::rm($temp_path);
        OpenM_Log::debug("create $temp_path", __CLASS__, __METHOD__, __LINE__);
        OpenM_Dir::mk($temp_path);

        if (String::isString($type))
            $type = array($type);

        foreach ($type as $value) {
            $dependencies_compiled = "";
            $e = $this->explore($value)->keys();
            while ($e->hasNext())
                $dependencies_compiled .= $e->next() . "\r\n";
            file_put_contents($this->lib_path . "/" . self::OpenM_DEPENDENCIES . $value . self::COMPILED_SUFFIX, $dependencies_compiled);
            OpenM_Log::debug(self::OpenM_DEPENDENCIES . $value . self::COMPILED_SUFFIX . "successfully created", __CLASS__, __METHOD__, __LINE__);
            if ($display)
                echo " - " . self::OpenM_DEPENDENCIES . $value . self::COMPILED_SUFFIX . " <b>successfully created</b><br>";

            OpenM_Log::debug("load " . $this->lib_path . "/" . self::OpenM_DEPENDENCIES, __CLASS__, __METHOD__, __LINE__);
            $explored_dependency_file = Properties::fromFile($this->lib_path . "/" . self::OpenM_DEPENDENCIES)->getAll();
            $e = $explored_dependency_file->keys();
            $checkFile = self::OpenM_DEPENDENCIES . "=" . filemtime($this->lib_path . "/" . self::OpenM_DEPENDENCIES) . "\r\n";
            while ($e->hasNext()) {
                $key = $e->next();
                if (!RegExp::preg("/" . $value . "$/", $key))
                    continue;
                $file = $explored_dependency_file->get($key);
                OpenM_Log::debug("add line in file for $file", __CLASS__, __METHOD__, __LINE__);
                $checkFile .= $file . "=" . filemtime($this->lib_path . "/" . $file) . "\r\n";
            }
            file_put_contents($this->lib_path . "/" . self::OpenM_DEPENDENCIES . $value . self::CHECK_SUFFIX, $checkFile);
            OpenM_Log::debug(self::OpenM_DEPENDENCIES . $value . self::CHECK_SUFFIX . "successfully created", __CLASS__, __METHOD__, __LINE__);
            if ($display)
                echo " - " . self::OpenM_DEPENDENCIES . $value . self::CHECK_SUFFIX . " <b>successfully created</b><br>";
        }

        OpenM_Log::debug("Installation successfully ended", __CLASS__, __METHOD__, __LINE__);
        if ($display)
            echo "Installation <b>successfully ended</b>.<br>";
    }

    /**
     * used to dynamically add dependencies in class path
     * if dependencies are not present, this will launch installation before adding
     * @param String $type is type of class path required (Ex RUN)
     * @param boolean $autoDownload 
     * @throws InvalidArgumentException
     * @trows ImportException
     */
    public function addInClassPath($type = self::RUN, $autoDownload = true) {
        if ($type != self::RUN && $type != self::DISPLAY && $type != self::TEST)
            throw new InvalidArgumentException("type must be a valid type");
        if (!is_bool($autoDownload))
            throw new InvalidArgumentException("autoDownload must be a boolean");
        $file = $this->lib_path . "/" . self::OpenM_DEPENDENCIES . $type . self::COMPILED_SUFFIX;
        OpenM_Log::debug("check compiled file: $file", __CLASS__, __METHOD__, __LINE__);
        if (is_file($file)) {
            OpenM_Log::debug("file found", __CLASS__, __METHOD__, __LINE__);
            if ($autoDownload) {
                OpenM_Log::debug("autoDownload activated", __CLASS__, __METHOD__, __LINE__);
                $checkFile = $this->lib_path . "/" . self::OpenM_DEPENDENCIES . $type . self::CHECK_SUFFIX;
                OpenM_Log::debug("check $checkFile", __CLASS__, __METHOD__, __LINE__);
                if (!is_file($checkFile))
                    return $this->autoDownload($type);

                $checkFileContent = Properties::fromFile($checkFile)->getAll();
                $e = $checkFileContent->keys();
                while ($e->hasNext()) {
                    $key = $e->next();
                    if (filemtime($this->lib_path . "/" . $key) != $checkFileContent->get($key)->toInt())
                        return $this->autoDownload($type);
                }
            }
            else
                OpenM_Log::debug("autoDownload not activated", __CLASS__, __METHOD__, __LINE__);
            OpenM_Log::debug("read $file", __CLASS__, __METHOD__, __LINE__);
            $file_content_array = explode("\r\n", file_get_contents($file));
            foreach ($file_content_array as $value) {
                if ($value != "") {
                    OpenM_Log::debug("check $value", __CLASS__, __METHOD__, __LINE__);
                    if (is_dir(Import::LIB . "/$value")) {
                        OpenM_Log::debug("addLibPath($value)", __CLASS__, __METHOD__, __LINE__);
                        Import::addLibPath($value);
                    }
                    else
                        return $this->autoDownload($type);
                }
            }
        } else if ($autoDownload) {
            OpenM_Log::debug("file not found but autoDownload activated", __CLASS__, __METHOD__, __LINE__);
            return $this->autoDownload($type);
        }
        else
            throw new ImportException("dependencies installation not OK, thanks to install dependencies before or activate autoDownload");
    }

    private function autoDownload($type) {
        OpenM_Log::debug("install " . $this->lib_path . "/temp, for type: $type", __CLASS__, __METHOD__, __LINE__);
        $this->install($this->lib_path . "/temp", $type);
        OpenM_Log::debug("remove " . $this->lib_path . "/temp", __CLASS__, __METHOD__, __LINE__);
        OpenM_Dir::rm($this->lib_path . "/temp");
        OpenM_Log::debug("addInClassPath($type, true)", __CLASS__, __METHOD__, __LINE__);
        $this->addInClassPath($type, true);
    }

    private function isValid($type) {
        if (is_array($type)) {
            foreach ($type as $value) {
                if (!$this->isValid($value))
                    return false;
            }
            return true;
        }
        else {
            if (!String::isString($type))
                return false;
            switch ($type) {
                case self::RUN:
                    return true;
                    break;
                case self::TEST:
                    return true;
                    break;
                case self::DISPLAY:
                    return true;
                    break;
                default:
                    return false;
                    break;
            }
        }
    }

    public static function ALL() {
        return array(self::RUN, self::TEST, self::DISPLAY);
    }

    public static function RUN_AND_TEST() {
        return array(self::RUN, self::TEST);
    }

    public static function RUN_AND_DISPLAY() {
        return array(self::RUN, self::DISPLAY);
    }

}

?>