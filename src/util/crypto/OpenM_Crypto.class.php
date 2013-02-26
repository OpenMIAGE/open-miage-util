<?php

Import::php("util.OpenM_Log");

/**
 * used to encrypt string
 * @package OpenM 
 * @subpackage util/crypto
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
 * @author Gael Saunier
 */
class OpenM_Crypto {

    /**
     * used to encrypt string in md5
     * @param String $toEncrypt is string to encrypt
     * @return String string encrytped in md5
     */
    public static function md5($toEncrypt) {
        return self::hash("md5", $toEncrypt);
    }

    /**
     * used to encrypt string in sha1
     * @param String $toEncrypt is string to encrypt
     * @return String string encrytped in sha1
     */
    public static function sha1($toEncrypt) {
        return self::hash("sha1", $toEncrypt);
    }

    /**
     * used to encrypt string in sha256
     * @param String $toEncrypt is string to encrypt
     * @return String string encrytped in sha256
     */
    public static function sha256($toEncrypt) {
        return self::hash("sha256", $toEncrypt);
    }

    /**
     * used to encrypt string in sha512
     * @param String $toEncrypt is string to encrypt
     * @return String string encrytped in sha512
     */
    public static function sha512($toEncrypt) {
        return self::hash("sha512", $toEncrypt);
    }

    /**
     * used to know if given algo is valid
     * @param String $algo is algo name
     * @return boolean true if algo exist, else false
     */
    public static function isAlgoValid($algo) {
        return in_array($algo, hash_algos());
    }

    /**
     * used to encrypt string in algo given
     * @param String $algo name of algo used to encrypt
     * @param String $toEncrypt is string to encrypt
     * @return String string encrytped in sha512
     */
    public static function hash($algo, $toEncrypt) {
        if (!self::isAlgoValid($algo))
            throw new InvalidArgumentException("algo must be a valid algo");
        if (!String::isString($toEncrypt))
            throw new InvalidArgumentException("toEncrypt must be a string");

        if (is_file($toEncrypt)) {
            OpenM_Log::debug("hash file '$toEncrypt' in $algo", __CLASS__, __METHOD__, __LINE__);
            return hash_file($algo, $toEncrypt);
        } else {
            OpenM_Log::debug("hash '$toEncrypt' in $algo", __CLASS__, __METHOD__, __LINE__);
            return hash($algo, $toEncrypt);
        }
    }

}

?>