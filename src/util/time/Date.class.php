<?php

Import::php('util.time.Delay');

/**
 * Class wrapper of date
 * @package OpenM 
 * @subpackage util/time
 * @author Gael SAUNIER
 */
class Date {

    protected $time;
    protected static $timeZone = 'Europe/Paris';
    protected $timeZoneSleep = null;

    /**
     * used to build a date
     * @param int $time is timestamp unix
     * @throws InvalidArgumentException
     */
    public function __construct($time = null) {
        if ($time == null)
            $time = time();
        else if (!is_int($time))
            throw new InvalidArgumentException("le paramètre doit être un int");

        $this->time = $time;
    }

    /**
     * used to add Delay to a Date
     * @param Delay $delay is delay to add
     * @return Date date result
     */
    public function plus(Delay $delay) {
        return new Date($this->time + $delay->getSeconds());
    }

    /**
     * used to remove a delay from a date
     * @param Delay $delay is dealy to remove
     * @return Date date result
     */
    public function less(Delay $delay) {
        return new Date($this->time - $delay->getSeconds());
    }

    /**
     * used to know the delay between two date
     * @param Date $date to compare with source
     * @return Delay delay between the two date
     */
    public function difference(Date $date) {
        return new Delay(abs($this->time - $date->time));
    }

    /**
     * used to recover the unix timestamp of date
     * @return int timestamp unix
     */
    public function getTime() {
        return $this->time;
    }

    /**
     * used to convert a date to a String
     * @param String $format is format required for the output String
     * @return String is date in String format result
     */
    public function toString($format = "d/m/Y H:i:s") {
        if (!is_string($format))
            throw new InvalidArgumentException("Date->toString(String[optional] wait a string type");
        date_default_timezone_set(self::$timeZone);
        return date($format, $this->time);
    }

    /**
     * used to change timezone
     * @param String $timeZone is new timeZone
     * @throws InvalidArgumentException
     */
    public static function setDateDefaultTimezone($timeZone) {
        if (!String::isString($timeZone))
            throw new InvalidArgumentException("timeZone must be a string");
        self::$timeZone = $timeZone;
    }

    /**
     * used to compare two date
     * @param Date $date to compare with source
     * @return int difference between the two date (could be negative)
     */
    public function compareTo(Date $date) {
        return $this->time - $date->time;
    }

    /**
     * used to know if two date are equal
     * @param Date $Date to compare with source
     * @return boolean true if both date are equal, else false
     */
    public function equals(Date $date) {
        return ($this->compareTo($date) === 0);
    }

    /**
     * used to copy a date
     * @return Date copy of source
     */
    public function copy() {
        return new Date($this->time);
    }
    
    /**
     * return the current date
     * @return Date
     */
    public static function now(){
        return new Date();
    }

}

?>