<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

/**
 * Classe de manipulação de annotations
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Bootstrap
 */
class Bootstrap
{
    /**
    * Initialize method for Routing System
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Bootstrap
    *
    * @return void
    */
    public static function initialize(): void
    {
        $routingConfig = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR . 'Framework' . DIRECTORY_SEPARATOR .
            'Config' . DIRECTORY_SEPARATOR . 'routingsystem.json'
        );
        
        if (!is_array($routingConfig) || !isset($routingConfig['settings']) || !isset($routingConfig['actions'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('Error reading RoutingSystemConfig file');
        }
        
        if (
            !isset($routingConfig['settings']) ||
            !isset($routingConfig['settings']['routeCaseSensitive']) ||
            !is_bool($routingConfig['settings']['routeCaseSensitive'])
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                'Error reading settings from RoutingSystemConfig'
            );
        }
        
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'routingSettings' => $routingConfig['settings']
            ),
            'RoutingSystem'
        );
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'routingActions' => $routingConfig['actions']
            ),
            'RoutingSystem'
        );
        
        $defaultActions = $routingConfig['actions'];

        // Verificando cada uma das default actions
        foreach ($defaultActions as $daK => $dA) {
            if (
                (!isset($dA['app']) || trim($dA['app']) === "") ||
                (!isset($dA['controller']) || trim($dA['controller']) === "") ||
                (!isset($dA['route']) || trim($dA['route']) === "") ||
                (!isset($dA['method']) || trim($dA['method']) === "") ||
                (!isset($dA['responsecode']) || (int) ($dA['responsecode']) === 0)
            ) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    "Default action '" . $daK . "' not configured correctly"
                );
            }
        }
        if (isset($daK)) {
            unset($daK);
            unset($dA);
        }

        // Validando se a rota 404 está configurada
        if (!isset($defaultActions['NotFound'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Default action 'NotFound' not configured in defaultActions"
            );
        }
        if (!isset($defaultActions['NotAuth'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Default action 'NotAuth' not configured in defaultActions"
            );
        }
        
        // Verificando a configuração de erro
        if (!isset($defaultActions['CriticalError'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Default action 'CriticalError' not configured in defaultActions"
            );
        }
        
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'defaultActions' => $defaultActions
            ),
            'RoutingSystem'
        );
        unset($defaultActions);
        
        // Routing Object
        $read = new \Modules\InsiderFramework\Core\RoutingSystem\Read();
        
        // Reading the routes
        $read->readControllerRoutes();
        unset($read);
    }
}
