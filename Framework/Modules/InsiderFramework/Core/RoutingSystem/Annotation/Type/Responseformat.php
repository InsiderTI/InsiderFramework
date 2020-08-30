<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;

/**
 * ResponseFormat annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Responseformat
 */
class Responseformat implements IType
{
    /**
    * Handler for ResponseFormat declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Responseformat
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
        if (isset($annotationsData[$id]['responseformat'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Responseformat" . "% statement is duplicated " .
                "in the class declaration in the controller %" . $id . "%",
                "app/sys"
            );
        }
        var_dump($commentLine);
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        $annotationsData[$id]['responseformat'] = $commentElements[0]['args'];
    }

    /**
    * Initialize the response format variable for the controller route
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Responseformat
    *
    * @param array  $routeAnnotationsData       Current annotations data array of the route
    * @param object $reflectionControllerObj    Controller object (created by reflection)
    * @param array  $controllerAnnotationsData  Controller annotations data array
    *
    * @return string Response format for the route
    */
    public static function initializeResponseformat(
        array &$routeAnnotationsData,
        $reflectionControllerObj,
        array $controllerAnnotationsData
    ): string {
        if (!isset($routeAnnotationsData['responseformat'])) {
            if (isset($controllerAnnotationsData[$reflectionControllerObj->name]['responseformat'])) {
                $routeAnnotationsData['responseformat'] = $controllerAnnotationsData
                                        [$reflectionControllerObj->name]
                                        ['responseformat'];
            } else {
                $routeAnnotationsData['responseformat'] = DEFAULT_RESPONSE_FORMAT;
            }
        }
        return $routeAnnotationsData['responseformat'];
    }
}
