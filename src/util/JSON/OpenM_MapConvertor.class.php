<?php

Import::php("util.HashtableString");
Import::php("util.Enum");

/**
 * used to convert array, HashtableString and JSON
 * @package OpenM 
 * @subpackage util/JSON
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
 * @author Gaël Saunier
 */
class OpenM_MapConvertor {

    /**
     * used to convert HashtableString to json
     * @param HashtableString $array to json encode
     * @return String json encoded
     */
    public static function mapToJSON(HashtableString $array) {
        return self::arrayToJSON(self::mapToArray($array));
    }

    /**
     * used to encode array to json
     * @param array $array to json encode
     * @return String is json encoded
     */
    public static function arrayToJSON(array $array) {
        return json_encode($array);
    }

    /**
     * used to json encode HashtableString
     * @param HashtableString $array to json encode
     * @return String json encoded
     * @throws InvalidArgumentException
     */
    public static function mapToArray(HashtableString $array) {
        $return = array();
        $e = $array->keys();
        while ($e->hasNext()) {
            $key = $e->next();
            $value = $array->get($key);
            if (String::isStringOrNull($value) || ($value instanceof HashtableString) || is_numeric($value))
                $return[$key] = ($value instanceof HashtableString) ? self::mapToArray($value) : utf8_encode(($value instanceof String) ? "$value" : $value);
            else
                throw new InvalidArgumentException("map must contain recursivly HashtableString or String");
        }
        return $return;
    }

    /**
     * used to convert array to HashtableString
     * @param array $array to convert to HashtableString
     * @return null|HashtableString HashtableString converted from array, else null
     */
    public static function arrayToMap($array) {
        if (!is_array($array))
            return null;
        $return = new HashtableString();
        foreach ($array as $key => $value) {
            if (is_array($value))
                $return->put($key, self::arrayToMap($value));
            else
                $return->put($key, utf8_decode($value));
        }
        return $return;
    }

    /**
     * used to decode json to HashtableString
     * @param String is json to convert
     * @return HashtableString is json converted
     */
    public static function JSONToMap($string) {
        return self::arrayToMap(self::JSONToArray($string));
    }

    /**
     * used to decode json to array
     * @param String $string
     * @return array json decoded
     */
    public static function JSONToArray($string) {
        return json_decode($string, true);
    }

}

?>