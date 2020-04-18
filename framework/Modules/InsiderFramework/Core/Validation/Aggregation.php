<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for aggregation (arrays)
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Aggregation
 */
trait Aggregation
{
    /**
     * Function thats makes a case insensitive search for a key inside an array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Aggregation
     * 
     * @param string $name  Name of the key
     * @param array  $array Target array of the search
     *
     * @return bool If key exists or not
     */
    public static function arrayKeyExistsCaseInsensitive(
        string $name,
        array $array
    ): bool {
        $name = strtolower($name);

        \Modules\InsiderFramework\Core\Manipulation\Aggregation::changeKeysToLowerCaseArray($array);
        if (isset($array[$name])) {
            return true;
        }

        return false;
    }

    /**
     * Verifica se uma posição no array existe e está preenchida com
     * um array não vazio, número, string não vazia, objeto ou resource
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Aggregation
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se o elemento existe e não é vazio
    */
    public static function existAndIsNotEmpty(array $array, string $key): bool
    {
        if (array_key_exists($key, $array)) {
            if (
                (
                    (is_array($array[$key]) && !empty($array[$key])) ||
                    ((is_string($array[$key]) || is_numeric($array[$key])) && $array[$key] !== "") ||
                    (is_resource($array))
                )
            ) {
                return true;
            }
        }
        return false;
    }
}
