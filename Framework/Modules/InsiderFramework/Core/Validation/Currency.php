<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
 * Validation methods for currency
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Validation\Currency
 */
trait Currency
{
    /**
     * Verifica se uma posição no array existe e está preenchida com um valor
     * em reais válido
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Currency
     *
     * @param array  $array Array onde está sendo buscado
     * @param string $key   Chave que será utilizada para verificação
     *
     * @return bool Se o elemento existe e é inteiro
    */
    public static function existAndIsMoney(array $array, string $key): bool
    {
        if (isset($array[$key]) && $array[$key] . "" !== "") {
            $value = \Modules\InsiderFramework\Core\Validation\Currency::getMoneyArray($array[$key]);
            if (trim($value['reais']) !== "" && trim($value['centavos']) !== "") {
                return true;
            }
        }
        return false;
    }

    /**
     * Extrai o valor em reais e os centavos de uma string, separando em um array
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Currency
     *
     * @param string $value  Valor a ser analisado
     * @param string $format Formato do dinheiro
     *
     * @return array|bool Array que contém 'reais' e 'centavos' como chaves
    */
    public static function getMoneyArray(string $value, string $format = LINGUAS)
    {
        switch (strtolower($format)) {
            case 'pt_br':
                $regex = "/(?P<reais>^[0-9]{1,3}([.]([0-9]{3}))*)[,](?P<centavos>([.]{0})[0-9]{0,2}$)/";
                preg_match($regex, $value, $matches);
                if (count($matches) > 0) {
                    if (trim($matches['reais']) !== "" && trim($matches['centavos']) !== "") {
                        return array(
                            'reais' => $matches['reais'],
                            'centavos' => $matches['centavos']
                        );
                    }
                }

                return false;
                break;

            default:
                \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister('Not implemented', "CRITICAL");
                break;
        }
    }
}
