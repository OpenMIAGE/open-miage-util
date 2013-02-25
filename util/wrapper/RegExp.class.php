<?php

/**
 * Class wrapper of ereg/preg to use preg instead of ereg
 * @package OpenM 
 * @subpackage util/wrapper
 * @author Gael SAUNIER
 */
class RegExp {

    /**
     * function wrapper of ereg deprecated in php and replaced by preg
     * @param String $regularExpression is the regular expression search in subject
     * @param String $subject is subject search in
     * @uses self::preg
     * @return boolean, true if pattern found else false
     */
    public static function ereg($regularExpression, $subject) {
        return self::preg("/" . str_replace('/', '\/', $regularExpression) . "/", $subject);
    }

    /**
     * function wrapper of preg
     * @param String $pattern is the pattern search in subject
     * @param String $subject is subject search in
     * @return boolean, true if pattern found else false
     */
    public static function preg($pattern, $subject) {
        return (preg_match($pattern, $subject)===1);
    }

}

?>