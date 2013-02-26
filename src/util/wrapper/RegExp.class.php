<?php

/**
 * Class wrapper of ereg/preg to use preg instead of ereg
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