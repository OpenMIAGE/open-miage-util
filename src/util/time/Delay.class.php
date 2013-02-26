<?php

Import::php('util.wrapper.String');

/**
 * Class wrapper of delay
 * @package OpenM 
 * @subpackage util/time
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
class Delay {

    protected $delay;
    
    const YEAR = 31536000;
    const DAY = 86400;
    const HOUR = 3600;
    const MINUTE = 60;

    /**
     * used to build a delay
     * @param int $delay
     * @return void
     */
    public function __construct($delay = null) {
        if ($delay === null)
            $delay = 0;

        if ($delay instanceof String) {
            $delay = $delay->toInt();
        }

        if (is_string($delay)) {
            $delay = (int) $delay;
        }

        if (!is_int($delay))
            throw new InvalidArgumentException("delay must be an int");
        if ($delay < 0)
            throw new InvalidArgumentException("delay must be positive");

        $this->delay = $delay;
    }

    /**
     * used to recover number of seconds in delay
     * @return int number of seconds in delay
     */
    public function getSeconds() {
        return $this->delay;
    }

    /**
     * used to recover number of minutes in delay
     * @return float number of minutes in delay
     */
    public function getMinutes() {
        return $this->delay / 60;
    }

    /**
     * used to recover number of hours in delay
     * @return float number of hours in delay
     */
    public function getHours() {
        return $this->delay / 3600;
    }

    /**
     * used to recover number of days in delay
     * @return float number of days in delay
     */
    public function getDays() {
        return $this->delay / (3600 * 24);
    }

    /**
     * used to know how much delay are included in delay
     * @param Delay $delay to compare with delay source
     * @return float number of delay include in delay source
     */
    public function getNbOf(Delay $delay) {
        return $delay->getSeconds() / $this->getSeconds();
    }

    /**
     * used to convert delay to String
     * @return String delay in string format
     */
    public function toString() {
        if ($this->delay < 60) {
            $return = $this->delay . " seconde";
            $return .= ($this->delay > 1) ? "s" : "";
            return $return;
        } else if ($this->delay < 60 * 60) {
            $duree_temp = $this->delay;
            $return = floor($duree_temp / 60) . " minute";
            $return .= (floor($duree_temp / 60) > 1) ? "s " : " ";
            $duree_temp = $duree_temp % 60;
            $return .= $duree_temp . " seconde";
            $return .= ($duree_temp > 1) ? "s" : "";
            return $return;
        } else {
            $duree_temp = $this->delay;
            $return = floor($duree_temp / 3600) . " heure";
            $return .= (floor($duree_temp / 3600) > 1) ? "s " : " ";
            $duree_temp = $duree_temp % 3600;
            $return .= floor($duree_temp / 60) . " minute";
            $return .= (floor($duree_temp / 60) > 1) ? "s " : " ";
            $duree_temp = $duree_temp % 60;
            $return .= $duree_temp . " seconde";
            $return .= ($duree_temp > 1) ? "s" : "";
            return $return;
        }
    }

    /**
     * used to add two delay
     * @param Delay $delay to add
     * @return Delay delay result
     */
    public function plus(Delay $delay) {
        return new Delay($this->delay + $delay->delay);
    }

    /**
     * used to remove a delay
     * @param Delay $delay to remove
     * @return Delay delay result
     */
    public function less(Delay $delay) {
        if ($this->compareTo($delay) < 0)
            throw new InvalidArgumentException("delay must be smaller than source");
        return new Delay($this->getSeconds() - $delay->getSeconds());
    }

    /**
     * used to multiplicate a delay
     * @param float $n is number used to multiplicate delay
     * @return Delay new delay result
     */
    public function fois($n) {
        if (!is_numeric($n) || $n < 0)
            throw new InvalidArgumentException("n must be a numeric positive");
        return new Delay((int) $this->getSeconds() * $n);
    }

    /**
     * used to compare two delay
     * @param Delay $delay to compare with
     * @return int is difference between the two delay
     */
    public function compareTo(Delay $delay) {
        return $this->delay - $delay->delay;
    }

    /**
     * used to know if both delay are equal
     * @param Delay $delay to compare with
     * @return boolean true if both delay are equal, else false
     */
    public function equals(Delay $delay) {
        return ($this->compareTo($delay) == 0);
    }

    /**
     * used to copy delay
     * @return Delay copy of source
     */
    public function copy() {
        return new Delay($this->delay);
    }
    
    /**
     * build a Delay from number of years
     * @param int $n is number of years required
     * @return Delay result
     */
    public static function years($n=1){
        return new Delay($n*self::YEAR);
    }
    
    /**
     * build a Delay from number of days
     * @param int $n is number of days required
     * @return Delay result
     */
    public static function days($n=1){
        return new Delay($n*self::DAY);
    }
}

?>