<?php

Import::php("util.wrapper.String");
Import::php("util.Enum");

/**
 * Class wrapper of array with key/value.
 * @package OpenM 
 * @subpackage util 
 * @author Gael SAUNIER
 */
class HashtableString {

    private $hashtable;
    private $size;
    private $typeValue;

    /**
     * @param String $typeValue is required type of values (a class/interface name).
     * If not null, all values will must be descendant of the given type, else
     * no constraints
     */
    public function __construct($typeValue = null) {
        $this->hashtable = array();
        $this->size = 0;

        if (!String::isStringOrNull($typeValue))
            throw new InvalidArgumentException("typeValue must be a String");

        if ($typeValue != null && !class_exists($typeValue) && !interface_exists($typeValue))
            throw new InvalidArgumentException("typeValue must be an existing class or interface name");

        $this->typeValue = (string) $typeValue;
    }

    /**
     * used to remove all key/value
     */
    public function clear() {
        $this->hashtable = array();
        $this->size = 0;
    }

    /**
     * it's containsValue alias
     * @see self::containsValue
     */
    public function contains($value) {
        return $this->containsValue($value);
    }

    /**
     * @used to know if an element is a key of this array
     * @param String|numeric $key is key searched in array
     * @return boolean true if key given is a key in array, else false
     */
    public function containsKey($key) {
        if (!String::isString($key) && !is_numeric($key))
            throw new InvalidArgumentException("le paramètre doit être un string ou un nombre");
        if ($key instanceof String)
            $key = "$key";
        return array_key_exists($key, $this->hashtable);
    }

    /**
     * used to know if an element is a value of this array
     * @param mixed $value is value searched in array
     * @return boolean true if value given is a value in array, else false
     */
    public function containsValue($value) {
        return in_array($value, $this->hashtable, true);
    }

    /**
     * used to recover an iterator on values of array
     * @return Enum iterator on values
     */
    public function enum() {
        return new Enum($this->hashtable);
    }

    /**
     * used to recover the value associated to the key given
     * @param String|numeric $key has to be an existing key of array
     * @return null|mixed associated value if exist, else null
     */
    public function get($key) {
        if (!String::isString($key) && !is_numeric($key))
            throw new InvalidArgumentException("key must be a String or a numeric");
        if ($key instanceof String)
            $key = "$key";
        if (!$this->containsKey($key))
            return null;
        return $this->hashtable[$key];
    }

    /**
     * used to know the type of array
     * @return String contains the type of array if typed, else empty
     */
    public function getType() {
        return $this->typeValue;
    }

    /**
     * used to know if array is typed
     * @return boolean true if type was defined, else false
     */
    public function isTyped() {
        return $this->typeValue != null;
    }

    /**
     * used to know if size equals to 0
     * @return boolean true if size = 0, else false
     * @uses self::size
     */
    public function isEmpty() {
        return $this->size() == 0;
    }

    /**
     * used to recover an iterator on key of array
     * @return Enum is an iterator on key of array
     */
    public function keys() {
        return new Enum(array_keys($this->hashtable));
    }

    /**
     * used to recover only values in an ArrayList
     * @return ArrayList constains all values of array
     */
    public function values() {
        return ArrayList::from($this->hashtable);
    }

    /**
     * used to recover only keys in an ArrayList
     * @return ArrayList constains all keys of array
     */
    public function keySet() {
        return ArrayList::from(array_keys($this->hashtable));
    }

    /**
     * used to add a key/value in array
     * @param String $key is key of added value
     * @param mixed $value is value associated to the key given
     * @return HashtableString array itself
     */
    public function put($key, $value) {
        if (!String::isString($key) && !is_numeric($key))
            throw new InvalidArgumentException("key must be a string or a numeric");
        if ($key instanceof String)
            $key = "$key";
        if ($this->typeValue != null && !(($value instanceOf $this->typeValue) || (is_string($value) && $this->typeValue == "String")))
            throw new InvalidArgumentException("type conflict, value must be an instance of " . $this->typeValue);

        if (!$this->containsKey($key))
            $this->size++;

        $this->hashtable[$key] = $value;

        return $this;
    }

    /**
     * used to add a list of key/value in array
     * @param HashtableString $hashtable is a list of key/value to add
     * @return HashtableString array itself
     */
    public function putAll(HashtableString $hashtable) {
        $e = $hashtable->keySet()->enum();
        while ($e->hasNext()) {
            $key = $e->next();
            $this->put($key, $hashtable->get($key));
        }

        return $this;
    }

    /**
     * used to remove a key/value from array
     * @param String|numeric $key is an existing key of array
     * @return mixed|null value associated to the key if exist else null
     */
    public function remove($key) {
        if (!String::isString($key) && !is_numeric($key))
            throw new InvalidArgumentException("key must be a string or a numeric");

        if (!$this->containsKey("$key"))
            return $this;

        unset($this->hashtable["$key"]);
        $this->size--;
        return $this;
    }

    /**
     * used to know how many key/value are added in array
     * @return int count of key/value in array
     */
    public function size() {
        return $this->size;
    }

    /**
     * used to build a new HashtableString from an array
     * @param array $array is a key/value native array
     * @param String $typeValue is a class or interface name to type array
     * @return HashtableString built HashtableString from array
     */
    public static function from($array, $typeValue = null) {
        if (!is_array($array))
            throw new InvalidArgumentException("array must be an array");

        if (!String::isStringOrNull($typeValue))
            throw new InvalidArgumentException("typeValue String");

        $hashtableString = new HashtableString($typeValue);
        foreach ($array as $key => $value) {
            if (is_string($value))
                $value = new String($value);
            if ($value != null)
                $hashtableString->put($key, $value);
        }

        return $hashtableString;
    }

    /**
     * used to recover the native array
     * @return array the native array
     */
    public function toArray() {
        return $this->hashtable;
    }

    /**
     * used to copy array
     * @return HashtableString
     */
    public function copy() {
        $return = new HashtableString();
        $return->hashtable = $this->hashtable;
        $return->size = $this->size;
        $return->typeValue = $this->typeValue;
        return $return;
    }

}

?>