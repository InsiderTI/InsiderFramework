<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for numbers
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Number
 */
trait Number
{
    /**
     * Verifica se uma posição no array existe e está preenchida com um número
     * positivo
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Number
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se o elemento existe e é positivo (numeral)
    */
    public static function existAndIsPositive(array $array, string $key): bool
    {
        if (isset($array[$key]) && $array[$key] . "" !== "") {
            if (floatval($array[$key]) > 0) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }

    /**
     * Verifica se uma posição no array existe e está preenchida com um número
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Number
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se o elemento existe e é numérico
    */
    public static function existAndIsNumeric(array $array, string $key): bool
    {
        if (isset($array[$key]) && $array[$key] . "" !== "") {
            if (is_numeric($array[$key])) {
                return true;
            } else {
                return false;
            }
        }
        return false;
    }
}
