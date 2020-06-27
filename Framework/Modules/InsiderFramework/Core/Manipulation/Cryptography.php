<?php

namespace Modules\InsiderFramework\Core\Manipulation;

/**
 *  Methods responsible for handle cryptography
 *
 *  @author Marcello Costa
 *
 *  @package Modules\InsiderFramework\Core\Manipulation\Cryptography
 */
trait Cryptography
{
    /**
     * Encrypt a string
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Manipulation\Cryptography
     *
     * @param string $string String to be encripted
     * @param string $key    Encription key
     * @param bool   $md5    If this is true, return a MD5 string
     *
     * @return string Encripted string
     */
    public static function encryptString(string $string, string $key = null, bool $md5 = false): string
    {
        if ($string !== null) {
            if ($key === null) {
                $key = ENCRYPT_KEY;
            }
            $encrypted = Cryptor::encrypt($string, $key);

            if ($md5 === false) {
                return $encrypted;
            } else {
                return md5($encrypted);
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                'String for encryption has not been specified'
            );
        }
    }

    /**
     * Decrypt a string
     *
     * @author Marcello Costa
     * @package Modules\InsiderFramework\Core\Manipulation\Cryptography
     *
     * @param string $string String to be decripted
     * @param string $key    Decription key
     *
     * @return string Decrypted string
     */
    public static function decryptString(string $string, string $key = null): string
    {
        if ($string !== null) {
            if ($key === null) {
                $key = ENCRYPT_KEY;
            }
            $decrypted = Cryptor::Decrypt($string, $key);

            return $decrypted;
        }

        throw new \Exception('String for decryption has not been specified');
    }
}
