<?php

Import::php('util.time.Date.DateParseInt');
Import::php("util.wrapper.RegExp");

/**
 * Class wrapper of string
 * @package OpenM 
 * @subpackage util/wrapper
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
class String {

    /**
     * @desc string natif de php
     * @var string
     */
    protected $string;

    public function __construct($string = null) {
        if ($string == null)
            $string = "";
        else if ($string instanceof String)
            $string = "$string";
        else if (!is_string($string))
            throw new InvalidArgumentException("string must be a String");

        $this->string = $string;
    }

    /**
     * @ignore
     */
    public function charAt($index) {
        if (!is_int($index))
            throw new InvalidArgumentException("le paramètre doit être un int.");

        return substr($this->string, $index - 1, 1);
    }

    /**
     * used to concat two String
     * @param string $string to concat with source
     * @return String concatenated String result
     */
    public function concat($string) {
        if (!self::isString($string))
            throw new InvalidArgumentException("string must be a string");
        return new String($this->string . $string);
    }

    /**
     * @ignore
     */
    public function contains($CharSequence) {
        if (!self::isString($CharSequence))
            throw new InvalidArgumentException("le paramètre doit être un String.");
        return (bool) (strpos($this->string, $CharSequence) !== false);
    }

    /**
     * @ignore
     */
    public function contentEquals($CharSequence) {
        if (!self::isString($CharSequence))
            throw new InvalidArgumentException("le paramètre doit être un String.");
        return $this->equals(($CharSequence instanceOf String) ? $CharSequence : new String($CharSequence));
    }

    /**
     * @ignore
     */
    public function endsWith($suffix) {
        if (!self::isString($suffix))
            throw new InvalidArgumentException("le paramètre doit être un String.");
        return RegExp::ereg("$suffix$", $this->string);
    }

    /**
     * @ignore
     */
    public function equals($string) {
        return $this->compareTo($string) == 0;
    }

    /**
     * @ignore
     */
    public function equalsIgnoreCase($anotherString) {
        if (!self::isString($anotherString))
            throw new InvalidArgumentException("le paramètre doit être un String.");

        return $this->compareToIgnoreCase($anotherString) == 0;
    }

    /**
     * @ignore
     */
    public function indexOf($string, $fromIndex = 1) {
        if (!self::isString($string))
            throw new InvalidArgumentException("le premier paramètre doit être un String.");
        if (!is_int($fromIndex))
            throw new InvalidArgumentException("le second paramètre doit être un int.");

        return $fromIndex - 1 + strpos(substr($this->string, $fromIndex - 1), ($string instanceOf String) ? $string->__toString() : $string) + 1;
    }

    /**
     * used to recover the length of String
     * @return int number of characters
     */
    public function length() {
        return strlen($this->string);
    }

    /**
     * @ignore
     */
    public function replace($oldChar, $newChar) {
        return $this->replaceAll($oldChar, $newChar);
    }

    /**
     * @ignore
     */
    public function replaceAll($regex, $replacement) {
        if (!self::isString($regex))
            throw new InvalidArgumentException("le premier paramètre doit être un String.");
        if (!self::isString($replacement))
            throw new InvalidArgumentException("le second paramètre doit être un String.");

        return new String(str_replace(($regex instanceOf String) ? $regex->__toString() : $regex, ($replacement instanceOf String) ? $replacement->__toString() : $replacement, $this->string));
    }

    /**
     * @ignore
     */
    public function replaceFirst($regex, $replacement) {
        if (!self::isString($regex))
            throw new InvalidArgumentException("le premier paramètre doit être un String.");
        if (!self::isString($replacement))
            throw new InvalidArgumentException("le second paramètre doit être un String.");

        $debut = strpos(($regex instanceOf String) ? $regex->__toString() : $regex, $this->string);
        $longueur = ($regex instanceOf String) ? $regex->length() : strlen($regex);
        return new String(substr($this->string, 0, $debut) . $replacement . substr($this->string, $debut + $longueur));
    }

    /**
     * @ignore
     */
    public function split($regex, $limit = null) {
        if (!self::isString($regex))
            throw new InvalidArgumentException("regex must be a string");
        if (!is_int($limit) && $limit !== null)
            throw new InvalidArgumentException("limit must be an int");

        if ($limit === null)
            return explode(($regex instanceOf String) ? $regex->string : $regex, $this->string);
        else
            return explode(($regex instanceOf String) ? $regex->string : $regex, $this->string, $limit);
    }

    /**
     * @ignore
     */
    public function startsWith($prefix, $toffset = null) {
        if (!self::isString($prefix))
            throw new InvalidArgumentException("le paramètre doit être un String.");
        if (!is_int($toffset) && $toffset !== null)
            throw new InvalidArgumentException("le paramètre doit être un int.");

        return RegExp::ereg("^$prefix", ($toffset == null) ? $this->string : substr($this->string, $toffset - 1));
    }

    /**
     * wrapper function of substr
     * @param int $beginIndex
     * @param int $length
     * @return String
     * @ignore
     */
    public function substring($beginIndex, $length = null) {
        if (!is_int($beginIndex))
            throw new InvalidArgumentException("le premier paramètre doit être un int.");
        if (!is_int($length) && $length !== null)
            throw new InvalidArgumentException("le paramètre doit être un int.");

        if ($length === null)
            return new String(substr($this->string, $beginIndex - 1));
        else
            return new String(substr($this->string, $beginIndex - 1, $length));
    }

    /**
     * used to recover the String in lowercase
     * @return String String in lowercase result
     */
    public function toLowerCase() {
        return new String(strtolower($this->string));
    }

    /**
     * @ignore
     */
    public function toString() {
        return $this;
    }

    /**
     * used to recover the String in uppercase
     * @return String String in uppercase result
     */
    public function toUpperCase() {
        return new String(strtoupper($this->string));
    }

    /**
     * used to trim String (remove spaces after and before)
     * @return String trim String result
     */
    public function trim() {
        return new String(trim($this->string));
    }

    /**
     * used to cast it to int
     * @return int
     */
    public function toInt() {
        return (int) $this->string;
    }

    /**
     * used to cast it to float
     * @return float
     */
    public function toFloat() {
        return (float) $this->string;
    }

    /**
     * used to cast it to boolean
     * @return boolean
     */
    public function toBool() {
        return (bool) $this->string;
    }

    /**
     * @ignore
     */
    public function __toString() {
        return $this->string;
    }

    /**
     * @ignore
     */
    public function sqlSecure() {
        return new String(trim(htmlspecialchars(addslashes($this->string))));
    }

    /**
     * @ignore
     */
    public function iso() {
        return new String(utf8_decode($this->string));
    }

    /**
     * @ignore
     */
    public function utf8() {
        return new String(utf8_encode($this->string));
    }

    /**
     * @ignore
     */
    public function entiteHTML() {
        return new String(htmlentities($this->string));
    }

    /**
     * used to copy this String
     * @return String copy of String source
     */
    public function copy() {
        return new String($this->string);
    }

    /**
     * used to know if given string is a native string or a String
     * @param String|string $string is element to check
     * @return boolean true if it is a native string or a String, else false
     */
    public static function isString($string) {
        return is_string($string) || ($string instanceof String);
    }

    /**
     * used to know if given string is a native string or a String or null
     * @param String|string|null $string is element to check
     * @return boolean true if it is a native string or a String or null, else false
     * @uses self::isString
     */
    public static function isStringOrNull($string) {
        if ($string == null)
            return true;
        else
            return self::isString($string);
    }

    /**
     * convert value to string
     * @param type $value
     * @return String
     */
    public static function cast($value) {
        return "$value";
    }

}

?>
