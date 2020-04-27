<?php

namespace Modules\InsiderFramework\Core\Validation;

/**
  * Methods responsible for handle requests
  *
  * @package Modules\InsiderFramework\Core\Validation\Request
  *
  * @author Marcello Costa
 */
trait Request
{
    /**
     * Verifica se é uma requisição ajax
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Request
     *
     * @return bool Se é uma requisição ajax ou não
    */
    public static function isAjaxRequest(): bool
    {
        $ajaxrequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable('ajaxrequest', 'RoutingSystem');
        return $ajaxrequest;
    }

    /**
     * Verifica se o responseFormat é o esperado
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Validation\Request
     *
     * @return bool Se true, está correto
    */
    public static function isResponseFormat($responseFormatExpected): bool
    {
        $responseFormatNow = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'responseFormat',
            'insiderFrameworkSystem'
        );
        
        if (strtoupper($responseFormatExpected) === strtoupper($responseFormatNow)) {
            return true;
        }
        
        return false;
    }
}
