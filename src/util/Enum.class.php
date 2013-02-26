<?php

/**
 * Enum is an iterator on array
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
class Enum {

    private $vector;
    private $curval;
    private $arrayKeys;

    /**
     * used to build an Enum from an array
     * @param array $array
     * @return void 
     */
    public function __construct(array $elements) {
        $this->vector = $elements;
        $this->curval = -1;
        $this->arrayKeys = array_keys($elements);
    }

    /**
     * used to recover the next element of array 
     * @return mixed next element of array
     */
    public function next() {
        $this->curval +=1;
        return $this->vector[$this->arrayKeys[$this->curval]];
    }

    /**
     * used to recover the current element of array
     * @return mixed current element of array
     */
    public function current() {
        return $this->vector[$this->arrayKeys[$this->curval]];
    }

    /**
     * used to know if array contain a next element
     * @return boolean true if array contain a next element, else false
     */
    public function hasNext() {
        return array_key_exists($this->curval + 1, $this->arrayKeys);
    }

    /**
     * used to recover the element at position n
     * @param int $n is position of required element
     * @return mixed searched element
     * @throws InvalidArgumentException
     */
    public function get($n) {
        if (!is_int($n))
            throw new InvalidArgumentException("Argument must be an integer");
        return $this->vector[$this->arrayKeys[$n - 1]];
    }

    /**
     * used to copy Enum
     * @return Enum copy of Enum source
     */
    public function copy() {
        return new Enum($this->vector);
    }

}

?>