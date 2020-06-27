<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for date
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Date
 */
trait Date
{
    /**
     * Verifica se um campo é uma data válida
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Date
     *
     * @param array  $array  Array onde está sendo buscado
     * @param string $key    Chave que será utilizada para verificação
     * @param string $format Formato da data sendo verificada
     *
     * @return bool Se é uma data válida, retorna true
    */
    public static function existAndIsDate(array $array, string $key, string $format = 'd/m/Y'): bool
    {
        if (isset($array[$key])) {
            $date = $array[$key];

            return \Modules\InsiderFramework\Core\Validation::IsDate($date, $format);
        }
        return false;
    }

    /**
     * Verifica se uma variável é uma data
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Date
     *
     * @param string $date   String a ser verificada
     * @param string $format Formato da data sendo verificada
     *
     * @return bool Se é uma data válida, retorna true
    */
    public static function isDate(string $date, string $format = 'Y-m-d'): bool
    {
        $d = \DateTime::createFromFormat($format, $date);
        if ($d && $d->format($format) == $date) {
            return true;
        }
        return false;
    }
}
