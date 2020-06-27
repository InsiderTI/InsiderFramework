<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for date
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\DateTime
 */
trait DateTime
{
    /**
     * Verifica se um campo é uma data válida
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\DateTime
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se é uma data válida, retorna true
    */
    public static function existAndIsDateTime(array $array, string $key)
    {
        if (isset($array[$key])) {
            $datetime = str_replace('/', '-', $array[$key]);
            return \DateTime('Y-m-d H:i:s', strtotime($datetime));
        }
        return false;
    }
}
