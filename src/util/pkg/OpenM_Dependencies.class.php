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
    const INTERNAL_REPOSITORY_URL_KEY = "install.int.lib.prefix";

    /**
     *
     * @var HashtableString 
     */
    private $dependencies;
    private $dependencies_test;
    private $dependencies_test_loaded = false;
    private $dependencies_run;
    private $dependencies_run_loaded = false;
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
        $this->dependencies_run = new HashtableString();
        $this->dependencies_test = new HashtableString();
    }

    /**
     * used to recover dependencies list
     * @param boolean $test is true to recover test dependencies only else false
     * @return HashtableString
     */
    public function explore($test = false) {
        if ($test) {
            if ($this->dependencies_test_loaded)
                return $this->dependencies_test->copy();
            $this->dependencies = $this->dependencies_test;
            $this->dependencies_test_loaded = true;
        } else {
            if ($this->dependencies_run_loaded)
                return $this->dependencies_run->copy();
            $this->dependencies = $this->dependencies_run;
            $this->dependencies_run_loaded = true;
        }
        return $this->_explore($this->lib_path, $test)->copy();
    }

    private function _explore($explore_dir_path, $test = false) {
        $explore_dir_path_formated = $explore_dir_path . (RegExp::preg("/\/$/", $explore_dir_path) ? "" : "/");
        OpenM_Log::debug($explore_dir_path_formated . self::OpenM_DEPENDENCIES, __CLASS__, __METHOD__, __LINE__);
        $explored_dependency_file = Properties::fromFile($explore_dir_path_formated . self::OpenM_DEPENDENCIES)->getAll();
        $e = $explored_dependency_file->keys();
        while ($e->hasNext()) {
            $file_key = $e->next();
            if ($file_key == self::INTERNAL . ($test ? self::TEST : "")) {
                OpenM_Log::debug($explore_dir_path_formated . $explored_dependency_file->get($file_key), __CLASS__, __METHOD__, __LINE__);
                $internal_file = Properties::fromFile($explore_dir_path_formated . $explored_dependency_file->get($file_key));
                if ($internal_file->get(self::INTERNAL_REPOSITORY_URL_KEY) != null)
                    $repository_url = $internal_file->get(self::INTERNAL_REPOSITORY_URL_KEY);
                $lib_enum = $internal_file->getAll()->keys();
                while ($lib_enum->hasNext()) {
                    $dependency = $lib_enum->next();
                    if ($this->dependencies->containsKey($dependency))
                        continue;
                    if (!RegExp::preg("/^OpenM/", $dependency))
                        continue;
                    $file_path = $internal_file->get($dependency);
                    $remote_dir = $repository_url . $dependency . "/";
                    $this->dependencies->put($dependency, $remote_dir . $file_path . "::/lib/" . $dependency);
                    $this->_explore($remote_dir);
                }
            } else if ($file_key == self::EXTERNAL . ($test ? self::TEST : "")) {
                OpenM_Log::debug($explore_dir_path_formated . $explored_dependency_file->get($file_key), __CLASS__, __METHOD__, __LINE__);
                $external_file = Properties::fromFile($explore_dir_path_formated . $explored_dependency_file->get($file_key));
                $lib_enum = $external_file->getAll()->keys();
                while ($lib_enum->hasNext()) {
                    $dependency = $lib_enum->next();
                    $this->dependencies->put($dependency, $external_file->get($dependency));
                }
            }
        }
        return $this->dependencies;
    }

    /**
     * used to download and install all dependencies required
     * @param String $temp_path is a directory path required as temporary directory
     * @param boolean $display is true to activate follow-up in display else false
     * @throws InvalidArgumentException
     */
    public function install($temp_path, $display = false) {
        if (!String::isString($temp_path))
            throw new InvalidArgumentException("lib_path must be a string");
        if (!is_dir($temp_path) && !RegExp::preg("/^\//", $temp_path) && !RegExp::preg("/^\./", $temp_path))
            throw new InvalidArgumentException("lib_path must be a valid directory path");
        if (!is_bool($display))
            throw new InvalidArgumentException("display must be a boolean");
        if ($display)
            echo "Installation start:<br>";
        $temp_path_formated = (RegExp::preg("/\/$/", $temp_path) ? substr($temp_path, 0, -1) : $temp_path);
        $dependencies = $this->explore()->putAll($this->explore(true));
        if ($display)
            echo " - All dependencies <b>successfully explored</b><br>";
        $e = $dependencies->keys();
        while ($e->hasNext()) {
            if (is_dir($temp_path_formated))
                OpenM_Dir::rm($temp_path_formated);
            OpenM_Dir::mk($temp_path_formated);
            $dependency = $e->next();
            $dependency_values = explode("=", $dependencies->get($dependency));
            $dependency_paths = explode("::", $dependency_values[0]);
            $dependency_path = $dependency_paths[0];
            $dependency_dir = Import::LIB . (RegExp::preg("/\/$/", Import::LIB) ? "" : "/") . $dependency;
            if (is_dir($dependency_dir))
                OpenM_Dir::rm($dependency_dir);
            OpenM_Dir::mk($dependency_dir);
            if (RegExp::preg("/\.zip$/", $dependency_path)) {
                $dependency_name = time();
                copy($dependency_path, $temp_path_formated . "/" . $dependency_name);
                OpenM_Zip::unZip($temp_path_formated . "/" . $dependency_name, $temp_path_formated);
                unlink($temp_path_formated . "/" . $dependency_name);
                OpenM_Dir::cp("$temp_path/" . (isset($dependency_paths[1]) ? $dependency_paths[1] : ""), $dependency_dir);
                if ($display)
                    echo " - $dependency_path <b>successfully copied and unZip in</b> $dependency_dir<br>";
            } else {
                if (isset($dependency_values[1]) && $dependency_values[1] != "") {
                    copy($dependency_path, $dependency_dir . "/" . $dependency_values[1]);
                    if ($display)
                        echo " - $dependency_path <b>successfully copied to</b> $dependency_dir/$dependency_values[1]<br>";
                } else {
                    $target = $dependency_dir . "/" . substr($dependency_path, strrpos($dependency_path, "/") + 1);
                    copy($dependency_path, "$dependency_dir/$target");
                    if ($display)
                        echo " - $dependency_path <b>successfully copied to</b> $dependency_dir/$target<br>";
                }
            }
        }
        OpenM_Dir::rm($temp_path);
        OpenM_Dir::mk($temp_path);
        if ($display)
            echo "Installation <b>successfully ended</b>.<br>";
    }

}

?>