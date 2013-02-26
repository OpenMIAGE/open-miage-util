<?php

Import::php("util.wrapper.String");
Import::php("util.OpenM_Log");

/**
 * used to modify header of http server response
 * @package OpenM 
 * @subpackage util/http
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
 * @author Gael Saunier
 */
class OpenM_Header {

    /**
     * used to redirect client to another URL
     * @param String $url is targeted url of redirection
     * @throws InvalidArgumentException
     */
    public static function redirect($url) {
        if (!String::isString($url))
            throw new InvalidArgumentException("url must be a string");
        OpenM_Log::debug("to $url", __CLASS__, __METHOD__, __LINE__);
        header("Location: $url");
        exit(0);
    }

    /**
     * add a header in HTTP response
     * @param String $message to push on HTTP response
     * @param int $code to push on HTTP response
     * @throws InvalidArgumentException
     */
    public static function add($message, $code) {
        if (!String::isStringOrNull($message))
            throw new InvalidArgumentException("message must be a string");
        if (!is_int($code))
            throw new InvalidArgumentException("code must be an int");
        OpenM_Log::debug($message . "($code)", __CLASS__, __METHOD__, __LINE__);
        header($message, true, $code);
    }

    /**
     * add a HTTP header in response with code and message given
     * @param int $code to push on HTTP response
     * @param String $message to push on HTTP response
     * @uses self::add
     * @throws InvalidArgumentException
     */
    public static function error($code, $message = null) {
        self::add($_SERVER["SERVER_PROTOCOL"] . " $code $message", $code);
        header("Connection: close");
        exit(0);
    }

    /**
     * add a Status 200 OK header
     */
    public static function ok() {
        self::add($_SERVER["SERVER_PROTOCOL"] . " 200 OK", 200);
    }

}

?>