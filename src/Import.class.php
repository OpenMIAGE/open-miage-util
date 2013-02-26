<?php

if (!defined("OpenM_LIB_PATH"))
    define("OpenM_LIB_PATH", dirname(__FILE__) . "/");

if (!defined("OpenM_EXT_LIB_PATH")) {
    $dir = dirname(dirname(dirname(__FILE__)));
    if (is_dir($dir . "/lib"))
        define("OpenM_EXT_LIB_PATH", $dir . "/lib/");
    else
        define("OpenM_EXT_LIB_PATH", dirname($dir) . "/");
}

/**
 * OpenM system class for class and interface loading
 * Manage a [list of] class path.
 * @package OpenM 
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
class Import {

    const IMPORTED = "imported";
    const PATH = OpenM_LIB_PATH;
    const LIB = OpenM_EXT_LIB_PATH;

    /**
     * @var ArrayList
     */
    private static $classPathList;

    /**
     * @var array
     */
    private static $importList;

    /**
     * @var array
     */
    private static $importResourceList;

    /**
     * @ignore
     */
    public static function resource($import) {
        if (!is_string($import))
            throw new InvalidArgumentException("argument must be a string");
        $imported = self::resourceOnClassPath($import);
        if ((!$imported && self::$classPathList !== null)
                || (preg_match("/\/\*$/", $import) && self::$classPathList !== null)) {
            $e = self::$classPathList->enum();
            while ($e->hasNext()) {
                $relativePath = $e->next();
                if (self::resourceOnClassPath($import, $relativePath . ""))
                    return true;
            }
            return false;
        }
        else
            return $imported;
    }

    /**
     * used to know if a resource (no php file) is in classpath
     * @param String $import is searched resource
     * @param String $relativePath is used by Import class to search in all classpaths
     * @return boolean true if resource is in class path, else false
     */
    private static function resourceOnClassPath($import, $relativePath = "") {
        if (self::$importResourceList == null)
            self::$importResourceList = array();

        if (isset(self::$importResourceList[$import]) && self::$importResourceList[$import] == self::IMPORTED)
            return true;

        $OpenM_LIB_PATH = ($relativePath == "") ? OpenM_LIB_PATH : (!preg_match("/\/$/", $relativePath) ? ($relativePath . "/") : $relativePath);
        if (is_file($OpenM_LIB_PATH . $import)) {
            if (!preg_match("/\.php$/", $import)) {
                self::$importResourceList[$import] = self::IMPORTED;
                return true;
            }
            else
                return self::phpOnClassPath($import, $relativePath);
        } else if (preg_match("/\/\*$/", $OpenM_LIB_PATH . $import) && is_dir(substr($OpenM_LIB_PATH . $import, 0, -2))) {
            $import = substr($import, 0, -1);
            $dir = @opendir($OpenM_LIB_PATH . $import);
            if ($dir) {
                while (($file = readdir($dir)) !== false) {
                    if (!preg_match("/^\./", $file)) {
                        if (is_file($OpenM_LIB_PATH . $import . $file)) {
                            self::resourceOnClassPath($import . $file, $relativePath);
                        } else if (is_dir($OpenM_LIB_PATH . $import . $file)) {
                            self::resourceOnClassPath($import . $file . "/*", $relativePath);
                        }
                    }
                }
                closedir($dir);
            }
            return true;
        }
        else
            return false;
    }

    /**
     * @ignore
     * @param String $import
     * @throws Exception
     */
    public static function lib($import) {
        if (is_dir(self::LIB))
            self::phpOnClassPath($import, self::LIB);
        else
            throw new Exception("OpenM_EXT_LIB_PATH not defined and default path not found");
    }

    /**
     * used to import a class or an interface or a direcotyr in class path
     * @param String $import class or interface or a directory path in class path
     * @return boolean true if class or interface or direcotyr correctly imported from class path
     * @throws InvalidArgumentException
     */
    public static function php($import) {
        if (!is_string($import))
            throw new InvalidArgumentException("argument must be a string");
        $imported = self::phpOnClassPath($import);
        if ((!$imported && self::$classPathList !== null)
                || (preg_match("/\.\*$/", $import) && self::$classPathList !== null)) {
            $e = self::$classPathList->enum();
            while ($e->hasNext()) {
                $relativePath = $e->next();
                if (self::phpOnClassPath($import, $relativePath . ""))
                    return true;
            }
            return false;
        }
        else
            return $imported;
    }

    /**
     * used to add a library directory path
     * Ex.: 'openid/2.0.2'
     * @param String $relativePath relative path from library directory 
     * to specific library you need to add in class path
     * RQ: OpenM_EXT_LIB_PATH must be define
     * @throws Exception if OpenM_EXT_LIB_PATH not correctly defined
     */
    public static function addLibPath($relativePath) {
        if (!is_dir(OpenM_EXT_LIB_PATH))
            throw new Exception("OpenM_EXT_LIB_PATH not correctly define, OpenM_EXT_LIB_PATH must target the 'lib' path, OpenM_EXT_LIB_PATH must be define (if necessary) before require_once Import class");
        if (!is_dir(self::LIB . "/$relativePath"))
            throw new Exception("$relativePath not found in '" . self::LIB . "' path");
        self::addClassPath(self::LIB . "/$relativePath");
    }

    /**
     * used to add a new class path in declared class path list
     * @param String $absoluteRootClassPath
     * @throws InvalidArgumentException
     */
    public static function addClassPath($absoluteRootClassPath = null) {
        if ($absoluteRootClassPath == null)
            $absoluteRootClassPath = ".";
        if (!is_string($absoluteRootClassPath))
            throw new InvalidArgumentException("argument must be a string");
        if (self::$classPathList == null) {
            self::php("util.ArrayList");
            self::php("util.wrapper.String");
            self::$classPathList = new ArrayList();
        }

        if (!is_dir($absoluteRootClassPath))
            throw new InvalidArgumentException("argument must be a valid directory path");

        $absoluteRootClassPath = realpath($absoluteRootClassPath);

        if (!self::$classPathList->contains($absoluteRootClassPath)) {
            self::$classPathList->add($absoluteRootClassPath);
        }
    }

    /**
     * used to add a php class path.
     * For example, if exist ./a/b/myFile.php and adding ./a/b with addInPhpClassPath,
     * I'll could read myFile.php from require 'myFile.php';
     * It's used to load external library that required to set the current path to
     * root of library
     * @param String $path is path of classpath added
     */
    public static function addInPhpClassPath($path) {
        $absolutePath = self::getAbsolutePath($path);
        $paths = ini_get('include_path');
        if (!in_array($absolutePath, explode(PATH_SEPARATOR, $paths))) {
            $paths = $paths . PATH_SEPARATOR . $absolutePath;
            ini_set('include_path', $paths);
        }
    }

    private static function phpOnClassPath($import, $relativePath = "") {

        $importNormalized = str_replace(".", "/", $import);

        if (self::$importList == null)
            self::$importList = array();

        if (isset(self::$importList[$importNormalized]) && self::$importList[$importNormalized] == self::IMPORTED)
            return true;

        $OpenM_LIB_PATH = ($relativePath == "") ? OpenM_LIB_PATH : (!preg_match("/\/$/", $relativePath) ? ($relativePath . "/") : $relativePath);
        if (is_file($OpenM_LIB_PATH . $import)) {
            if (preg_match("/\.php$/", $import)) {
                require_once $OpenM_LIB_PATH . $import;
                self::$importList[$import] = self::IMPORTED;
                return true;
            }
            else
                return self::resourceOnClassPath($import, $relativePath);
        } else {
            $import = $importNormalized;
            if (is_file($OpenM_LIB_PATH . $import . ".class.php")) {
                require_once $OpenM_LIB_PATH . $import . ".class.php";
            } else if (is_file($OpenM_LIB_PATH . $import . ".interface.php")) {
                require_once $OpenM_LIB_PATH . $import . ".interface.php";
            } else if (preg_match("/\/\*$/", $OpenM_LIB_PATH . $import) && is_dir(substr($OpenM_LIB_PATH . $import, 0, -2))) {
                $import = substr($import, 0, -1);
                $dir = @opendir($OpenM_LIB_PATH . $import);
                if ($dir) {
                    while (($file = readdir($dir)) !== false) {
                        if (!preg_match("/^\./", $file)) {
                            if (is_file($OpenM_LIB_PATH . $import . $file)) {
                                if (preg_match("/\.class\.php$/", $file))
                                    self::phpOnClassPath($import . substr($file, 0, -10), $relativePath);
                                else if (preg_match("/\.interface\.php$/", $file))
                                    self::phpOnClassPath($import . substr($file, 0, -14), $relativePath);
                                else
                                    self::php($import . $file);
                            }
                            else if (is_dir($OpenM_LIB_PATH . $import . $file)) {
                                self::phpOnClassPath($import . $file . ".*", $relativePath);
                            }
                        }
                    }
                    closedir($dir);
                }
            }
            else
                return false;
        }
        self::$importList[$importNormalized] = self::IMPORTED;
        return true;
    }

    /**
     * could be used to know if a php file was imported by Import class
     * @param String $imported
     * @return boolean
     */
    public static function isImported($imported) {
        if (self::$importList == null)
            return false;

        return self::$importList->contains($imported);
    }

    /**
     * @ignore
     */
    public static function getPhpImported() {
        $array = new ArrayList();
        if (self::$importList == null)
            return $array;
        foreach (self::$importList as $key => $value)
            $array->add($key);

        return $array;
    }

    /**
     * @ignore
     */
    public static function getImportedResources() {
        $array = new ArrayList();
        if (self::$importResourceList == null)
            return $array;
        foreach (self::$importResourceList as $key => $value)
            $array->add($key);

        return $array;
    }

    /**
     * @ignore
     */
    public static function getPhpImportedAbsolutePath() {
        $array = new ArrayList();
        if (self::$importList == null)
            return $array;
        foreach (self::$importList as $key => $value) {
            $path = self::getAbsolutePath($key . ".class.php");
            if ($path == null)
                $path = self::getAbsolutePath($key . ".interface.php");
            if ($path == null)
                $path = self::getAbsolutePath($key);
            if ($path != null)
                $array->add($path);
        }
        return $array;
    }

    /**
     * @ignore
     */
    public static function getImportedResourcesAbsolutePath() {
        $array = new ArrayList();
        if (self::$importResourceList == null)
            return $array;
        foreach (self::$importResourceList as $key => $value) {
            $path = self::getAbsolutePath($key);
            if ($path != null)
                $array->add($path);
        }
        return $array;
    }

    /**
     * used to know list of class path out of Import parent directory
     * @return ArrayList contains class path added list
     */
    public static function getClassPathList() {
        return self::$classPathList->copy();
    }

    /**
     * used to recover the absolute path of a file if exist in one of
     * class path
     * @param String $path is a relative path (could use class loading format)
     * @throws InvalidArgumentException
     */
    public static function getAbsolutePath($path) {
        if (is_file($path) || is_dir($path))
            return realpath($path);

        if ($path == null)
            return null;

        if (self::$importList == null)
            return null;

        self::php("util.wrapper.String");
        if (!String::isStringOrNull($path))
            throw new InvalidArgumentException("the path must be a string");

        if (is_file(OpenM_LIB_PATH . $path) || (is_dir(OpenM_LIB_PATH . $path)))
            return OpenM_LIB_PATH . $path;

        if (self::$classPathList == null)
            return null;

        $e = self::$classPathList->enum();
        while ($e->hasNext()) {
            $classPath = $e->next();
            $absPath = preg_match("/\/$/", $classPath) ? $classPath . $path : $classPath . "/" . $path;
            if (is_file($absPath) || is_dir($absPath))
                return $absPath;
        }
    }

}

Import::php("Import");
Import::php("ImportException");
?>