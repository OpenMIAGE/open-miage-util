<?php

Import::php("util.HashtableString");
Import::php("util.http.OpenM_URL");

/**
 * Used to read/write a property file
 * @package OpenM 
 * @subpackage util 
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
 * @author Gael Saunier
 */
class Properties {

    private $properties;
    private $filePath;
    private static $cacheProperties;
    private static $cachePropertiesByModificationTime;
    private static $instance;

    /**
     * @param string $propertyFilePath
     */
    public function __construct($propertyFilePath = null) {
        $this->properties = new HashtableString("String");
        if ($propertyFilePath != null)
            $this->load($propertyFilePath);
    }

    /**
     * Charge les parametres du fichier de conf passée
     * @param String $propertyFilePath chemin du fichier
     * @throws InvalidArgumentException
     */
    public function load($propertyFilePath) {
        if (!String::isString($propertyFilePath))
            throw new InvalidArgumentException("argument must be a string");

        if ($propertyFilePath instanceof String)
            $propertyFilePath .= "";

        $isUrl = false;
        if (!OpenM_URL::isValid($propertyFilePath)) {
            if (!is_file($propertyFilePath))
                throw new InvalidArgumentException("argument must be a valid directory path ($propertyFilePath)");
            $this->filePath = realpath($propertyFilePath);
        }
        else {
            $this->filePath = $propertyFilePath;
            $isUrl = true;
        }

        if (self::$cacheProperties == null) {
            self::$cacheProperties = new HashtableString();
            self::$cachePropertiesByModificationTime = new HashtableString();
        }

        if (self::$cacheProperties->containsKey($this->filePath)) {
            if ($isUrl || (!$isUrl && self::$cachePropertiesByModificationTime->get($this->filePath) == filemtime($this->filePath))) {
                $this->properties = self::$cacheProperties->get($this->filePath);
                return $this;
            }
        }

        $fileContent = file_get_contents($this->filePath);
        $fileContent_formated = str_replace("\r", '', $fileContent);
        $array = explode("\n", $fileContent_formated);
        $e = new Enum($array);
        while ($e->hasNext()) {
            $string = $e->next();
            $key = substr($string, 0, strpos($string, '='));
            $value = substr($string, strlen($key) + 1);
            if (strlen($key) > 0)
                $this->properties->put(trim($key), new String(trim($value)));
        }

        self::$cacheProperties->put($this->filePath, $this->properties);
        if (!$isUrl)
            self::$cachePropertiesByModificationTime->put($this->filePath, filemtime($this->filePath));
        
        return $this;
    }

    /**
     * @desc retourne la valeur de la propriété passée en parametre
     * @param type $key
     * @return type
     */
    public function get($key) {
        return $this->getRecursiveProperty($key);
    }

    private function getRecursiveProperty($key, ArrayList $from = null) {
        if (!String::isString($key))
            throw new InvalidArgumentException("argument must be a string");
        if ($from == null)
            $from = new ArrayList();
        if ($from->contains($key))
            throw new Exception("Infinite recursive property");
        $from->add($key);
        $p = $this->properties->get($key);
        if ($p == null)
            return null;
        $return = "";
        $recursive = false;
        $partial = "";
        for ($i = 0; $i < strlen($p); $i++) {
            if (!$recursive && substr($p, $i, 2) == '${') {
                $recursive = true;
                $i++;
            } else if ($recursive && substr($p, $i, 1) == '}') {
                $return .= $this->getRecursiveProperty($partial, $from->copy());
                $partial = "";
                $recursive = false;
            } else if ($recursive) {
                $partial .= substr($p, $i, 1);
            } else {
                $return .= substr($p, $i, 1);
            }
        }
        if ($partial != "")
            $return .= $partial;
        return $return;
    }

    /**
     * Ajoute ou remplace une propriété
     * @param type $key
     * @param type $value
     * @throws InvalidArgumentException
     */
    public function set($key, $value) {
        if (!String::isString($key))
            throw new InvalidArgumentException("first argument must be a string");
        if (!String::isString($value))
            throw new InvalidArgumentException("second argument must be a string");
        return $this->properties->put($key, $value);
    }

    /**
     * supprime une propriété
     * @param type $key
     * @throws InvalidArgumentException
     */
    public function remove($key) {
        if (!String::isString($key))
            throw new InvalidArgumentException("first argument must be a string");
        return $this->properties->remove($key);
    }

    /**
     * savegarde les propriétées dans le fichier $propertyFilePath. 
     * En cas d'abscance de parametre, le sauvegarde dans le chemin passée en parametre 
     * lors de son chargement. Sinon retourne une exception 
     * @param string $propertyFilePath
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function save($propertyFilePath = null) {
        if ($propertyFilePath == null && $this->filePath == null)
            throw new InvalidArgumentException("the property file path must be filed in entry");

        $data = "";
        $e = $this->properties->keys();
        while ($e->hasNext()) {
            $key = $e->next();
            $data .= $key . "=" . $this->properties->get($key) . "\n";
        }
        return (boolean) file_put_contents(($propertyFilePath != null) ? $propertyFilePath : $this->filePath, $data);
    }

    /**
     * retourne les Propriétes (copies) dans un HashTableString
     * @return HashtableString
     */
    public function getAll() {
        return $this->properties->copy();
    }

    /**
     * 
     * @param HashtableString $properties
     * @throws InvalidArgumentException
     * @return void
     */
    public function setAll(HashtableString $properties) {
        $e = $properties->enum();
        while ($e->hasNext()) {
            $value = $e->next();
            if (!String::isString($value))
                throw new InvalidArgumentException("argument must be a Map with all value as string");
        }
        $this->properties = $properties;
    }

    /**
     * retourne les nom des propriétés
     * @return type
     */
    public function propertyNames() {
        return $this->properties->keys();
    }

    /**
     * Retourne un objet Properties charger à partir du chemin $propertyilePath
     * @param String $propertyFilePath
     * @return Properties
     * @throws InvalidArgumentException
     */
    public static function fromFile($propertyFilePath) {
        if (!String::isString($propertyFilePath))
            throw new InvalidArgumentException("propertyFilePath must be a string");
        if ($propertyFilePath instanceof String)
            $propertyFilePath .= "";

        if (self::$instance == null)
            self::$instance = new HashtableString();

        $isUrl = false;
        if (OpenM_URL::isValid($propertyFilePath)) {
            $realPath = $propertyFilePath;
            $isUrl = true;
        } else {
            $realPath = realpath($propertyFilePath);
            if (!is_file($realPath)) {
                $realPath = Import::getAbsolutePath($propertyFilePath);
                if ($realPath == null)
                    throw new InvalidArgumentException("realPath must be the valid path of a relative/absolute file or of a file in class path");
            }
        }

        if (self::$instance->containskey($realPath)) {
            if ($isUrl || (!$isUrl && self::$cachePropertiesByModificationTime->get($realPath) == filemtime($realPath))) {
                return self::$instance->get($realPath);
            }
            else
                return self::$instance->get($realPath)->load($realPath);
        }
        else {
            $return = new Properties($propertyFilePath);
            self::$instance->put($realPath, $return);
            return $return;
        }
    }

}

?>