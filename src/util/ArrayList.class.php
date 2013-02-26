<?php

Import::php("util.Enum");
Import::php("util.wrapper.String");

/**
 * Class wrapper of array with value only.
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
 * @author Gael SAUNIER
 */
class ArrayList {

    private $vector;
    private $size;
    private $type;

    /**
     * ArrayList could be typable
     * @param String $type is a class or an interface name
     */
    public function __construct($type = null) {
        $this->vector = array();
        $this->size = 0;

        if (!String::isStringOrNull($type))
            throw new InvalidArgumentException("type must be a string");
        if($type != null && !class_exists($type) && !interface_exists($type))
            throw new InvalidArgumentException("type must be a valide class name or a valid interface name");
        $this->type = $type;
    }

    /**
     * used to add an element in array
     * @param type $element could be everything.
     * RQ: in case of ArrayList is typed, $element must be compliant with
     * this type
     * @return ArrayList
     */
    public function add($element) {
        if ($this->isTyped()) {
            if (!($element instanceOf $this->type))
                throw new InvalidArgumentException("element must be a descendant of: $this->type)");
        }
        array_push($this->vector, $element);
        $this->size++;

        return $this;
    }

    /**
     * used to add all element of 
     * @param ArrayList $ArrayList
     * @return ArrayList
     */
    public function addAll(ArrayList $ArrayList) {
        $e = new Enum($ArrayList->toArray());
        while ($e->hasNext())
            $this->add($e->next());

        return $this;
    }

    /**
     * used to clean all value of ArrayList
     * @return ArrayList itself
     */
    public function clear() {
        $this->vector = array();
        $this->size = 0;
        return $this;
    }

    /**
     * used to know if un element is contain by Arraylist
     * @return boolean true if element given is contain by ArrayList, else false
     */
    public function contains($element) {
        return in_array($element, $this->vector, true);
    }

    /**
     * used to know if all elements are contain by Arraylist
     * @param ArrayList $ArrayList
     * @return boolean true if all elements of ArrayList given 
     * are contain by ArrayList, else false
     */
    public function containsAll(ArrayList $ArrayList) {
        $e = new Enum($ArrayList->toArray());
        while ($e->hasNext()) {
            if (!$this->contains($e->next()))
                return false;
        }
        return true;
    }
    
    /**
     * used to know if two ArrayList are equal
     * @param ArrayList $arrayList
     * @return boolean true if both arrayList are equal
     */
    public function equals(ArrayList $arrayList) {
        return ($this->toArray() === $arrayList->toArray());
    }

    /**
     * used to know how many element are contain by arrayList
     * @return int number of element in arrayList
     */
    public function size() {
        return $this->size;
    }

    /**
     * used to recover the element in position n in arrayList
     * @param int position of element searched
     * @return mixed element found in position n
     */
    public function get($n) {
        if (!is_int($n))
            throw new InvalidArgumentException("n must be an int");
        if (array_key_exists($n - 1, $this->vector))
            return $this->vector[$n - 1];
        else
            return null;
    }

    /**
     * get the enum on array of ArrayList
     * @return Enum
     */
    public function enum() {
        return new Enum($this->toArray());
    }

    /**
     * used to know if contains no elements
     * @return boolean true if contains no element, else false
     */
    public function isEmpty() {
        return $this->size() == 0;
    }

    /**
     * used to remove an element from arrayList
     * @return boolean true if element found and removed, else false
     */
    public function remove($element) {
        $rang = array_search($element, $this->vector, true);
        if (is_numeric($rang)) {
            unset($this->vector[$rang]);
            $this->size--;
            return true;
        }
        return false;
    }

    /**
     * used to remove all element given in an ArrayList from arrayList
     * @param ArrayList $ArrayList
     * @return boolean true if elements found and removed, else false
     */
    public function removeAll(ArrayList $ArrayList) {
        $removeOk = true;
        $e = new Enum($ArrayList->toArray());
        while ($e->hasNext()) {
            if (!$this->remove($e->next()))
                $removeOk = false;
        }
        return $removeOk;
    }

    /**
     * used to typed the ArrayList
     * @param String $type is a name op existing class or interface
     */
    public function setType($type) {

        if (!String::isString($type))
            throw new InvalidArgumentException("le paramètre doit être un String");

        if ($this->size() > 0) {
            $enum = $this->enum();
            while ($enum->hasNext()) {
                $Object = $enum->next();
                if (!$Object->isInstanceOf((string) $type))
                    throw new InvalidArgumentException("conflit de type.");
            }
        }

        $this->type = $type;
    }

    /**
     * used to recover type of ArrayList if exist
     * @return String $type is name of existing class or interface
     * @see self::setType
     * @see self::__construct
     */
    public function getType() {
        if ($this->isTyped())
            return $this->type;
        else
            return null;
    }

    /**
     * used to know if ArrayList is typed
     * @return boolean true if ArrayList is typed, else false
     * @see self::setType
     * @see self::__construct
     * @see self::getType
     */
    public function isTyped() {
        return $this->type != null;
    }

    /**
     * used to recover the array base of ArrayList
     * @return array native base of ArrayList
     */
    public function toArray() {
        return $this->vector;
    }

    /**
     * used to copy the ArrayList
     * @return ArrayList copy of ArrayList source
     */
    public function copy() {
        $arrayList = new ArrayList();

        $arrayList->vector = $this->vector;
        $arrayList->size = $this->size;

        if ($this->isTyped())
            $arrayList->setType($this->getType());

        return $arrayList;
    }

    /**
     * used to create an ArrayList from an array
     * @return ArrayList build from array given
     * @throws InvalidArgumentException
     */
    public static function from($array) {
        if (!is_array($array))
            throw new InvalidArgumentException("array must be an array");

        $return = new ArrayList();
        $return->vector = $array;
        $return->size = sizeOf($array);
        return $return;
    }

    /**
     * used to know if an element is an array or an ArrayList
     * @param array|ArrayList $array
     * @return boolean true if element is an array or an ArrayList, else false
     */
    public static function isArray($array) {
        return (is_array($array) || ($array instanceof ArrayList));
    }

    /**
     * used to know if an element is an array or an ArrayList or null
     * @param null|array|ArrayList $array
     * @return boolean true if element is an array or an ArrayList or null, else false
     */
    public static function isArrayOrNull($array) {
        if ($array == null)
            return true;
        else
            return (is_array($array) || ($array instanceof ArrayList));
    }

}

?>