<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for mails
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Mail
 */
trait Mail
{
    /**
     * Verifica se uma posição no array existe e é um email válido
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Mail
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se o elemento existe e é um email válido
    */
    public static function existAndIsEmail(array $array, string $key)
    {
        if (isset($array[$key]) && $array[$key] . "" !== "") {
            return \Modules\InsiderFramework\Core\Validate::checkEmail($array[$key]);
        }
        return false;
    }

    /**
     * Checks if a string is an valid e-mail address
     *
     * @param string $email E-mail to validated
     *
     * @author Marcello Costa <marcello88costa@yahoo.com.br>
     *
     * @package Modules\InsiderFramework\Core\Validation\Mail
     *
     * @return bool Validation result
     */
    public static function checkEmail(string $email): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if (strpos($email, '@') === false) {
                return false;
            }

            $pattern_alternative_email = "^([\p{L}\.\-\d]+)@([\p{L}\-\.\d]+)((\.(\p{L}) {2,63})+)$";

            if (!(preg_match($pattern_alternative_email, $email))) {
                return false;
            }
        }

        return true;
    }
}
