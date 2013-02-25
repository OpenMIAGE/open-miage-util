<?php

Import::php("util.wrapper.String");

/**
 * Class wrapper of float
 * @package OpenM 
 * @subpackage util/wrapper
 * @author Gael SAUNIER
 */
class Float {

    private $float;

    /**
     * used to build a Float
     * @param type $float is used to initialize Float value
     * @throws InvalidArgumentException
     */
    public function __construct($float = 0) {
        if (!is_numeric($float) && !is_string($float) && !String::isString($float))
            throw new InvalidArgumentException("float must be a numeric");

        if (is_numeric($float))
            $this->float = $float;

        if (String::isString($float)) {
            if (is_string($float))
                $float = (float) $float;
            else
                $this->float = $float->toFloat();
        }
    }

    /**
     * Compares two Float from each others.
     * @param Float $float
     * @return int
     */
    public function compareTo(Float $float) {
        return $this->float - $float->float;
    }

    /**
     * Compares this object against the specified object.
     * @param Float $float
     * @return boolean true if both float are equal
     */
    public function equals(Float $float) {
        return $this->compareTo($float) == 0;
    }

    /**
     * Returns a string representation of this Float object.
     * @return String string conversion of Float
     */
    public function toString() {
        return new String((string) $this->float);
    }

    /**
     * return the addition of instance and float given
     * @param Float $float to add
     * @return Float result
     */
    public function plus(Float $float) {
        return new Float($this->float + $float->float);
    }

    /**
     * return the difference between instance and float given
     * @param Float $float to substract
     * @return Float result
     */
    public function minus(Float $float) {
        return new Float($this->float - $float->float);
    }

    /**
     * used to know what number is the max number
     * @param $float is number to compare with
     * @return Float max number
     */
    public function max($float) {

        if (!is_numeric($float) && !($float instanceOf Float))
            throw new InvalidArgumentException("float must be a numeric or a Float");

        if ($float instanceOf Float) {
            if ($this->float > $float->float)
                return $this;
            else
                return $float;
        }

        if ($this->float > $float)
            return $this;
        else
            return new Float($float);
    }

    /**
     * used to copy Float source
     * @return Float new Float copy of source
     */
    public function copy() {
        $float = new Float();
        $float->float = $this->float;
        return $float;
    }

    /**
     * used to know if an element is a numeric or a Float
     * @param numeric|Float $number
     * @return boolean true if it's a numeric or a Float
     */
    public static function isNumber($number) {
        return is_numeric($number) || ($number instanceof Float);
    }

}

?>