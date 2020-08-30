<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;

/**
 * Domains annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Domains
 */
class Domains implements IType
{
    /**
    * Handler for domains declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Domains
    *
    * @param string $id                 Identification of annotation
    * @param array  $annotationsData    Current mapped annotation data
    * @param string $commentLine        Current comment line
    *
    * @return void
    */
    public static function handler(
        string $id,
        array &$annotationsData,
        string $commentLine
    ): void {
        if (isset($annotationsData[$id]['domains'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Domains" . "% statement is duplicated " .
                "in the class declaration in the controller " .
                "%" . $id . "%",
                "app/sys"
            );
        }
        var_dump($annotationsData);
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        $domainsRoute = explode(',', $commentElements[0]['args']);
        $annotationsData[$id]['domains'] = $domainsRoute;
    }

    /**
    * Initialize the domains variable for the controller route
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Domains
    *
    * @param array  $routeAnnotationsData       Current annotations data array of the route
    * @param object $reflectionControllerObj    Controller object (created by reflection)
    * @param array  $controllerAnnotationsData  Controller annotations data array
    *
    * @return array Domains for the route
    */
    public static function initializeDomains(
        array &$routeAnnotationsData,
        $reflectionControllerObj,
        array $controllerAnnotationsData
    ): array {
        if (!isset($routeAnnotationsData['domains'])) {
            if (isset($controllerAnnotationsData[$reflectionControllerObj->name]['domains'])) {
                $routeAnnotationsData['domains'] = $controllerAnnotationsData[$reflectionControllerObj->name]['domains'];
            } else {
                $routeAnnotationsData['domains'] = [];
            }
        }
        return $routeAnnotationsData['domains'];
    }
}
