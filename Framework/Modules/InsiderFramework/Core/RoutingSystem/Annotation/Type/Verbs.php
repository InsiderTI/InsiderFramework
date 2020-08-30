<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;
use Modules\InsiderFramework\Core\RoutingSystem\Read;

/**
 * Verbs annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Verbs
 */
class Verbs implements IType
{
    /**
    * Handler for verbs declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Verbs
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
        if (isset($annotationsData[$id]['verbs'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Verbs" . "% statement is duplicated " .
                "in the class declaration in the controller %" . $id . "%",
                "app/sys"
            );
        }
        preg_match_all(
            Read::$patternArgs,
            $commentLine,
            $commentElements,
            PREG_SET_ORDER,
            0
        );

        if (!isset($commentElements[0]['args'])) {
            return;
        }

        $verbsRoute = explode(',', strtolower(str_replace(" ", "", $commentElements[0]['args'])));
        $annotationsData[$id]['verbs'] = $verbsRoute;
    }

    /**
    * Initialize the verbs array with the correct content
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Verbs
    *
    * @param array  $annotationsData              Current mapped annotation data
    * @param object $reflectionControllerObj      Controller object (created by reflection)
    * @param array  $controllerAnnotationsData    Controller annotations data array
    *
    * @return array Verbs array initialized
    */
    public static function initializeVerbsArray(
        array &$annotationsData,
        $reflectionControllerObj,
        array $controllerAnnotationsData
    ): array {
        if (!isset($annotationsData['verbs'])) {
            if (isset($controllerAnnotationsData[$reflectionControllerObj->name]['verbs'])) {
                $annotationsData['verbs'] = $controllerAnnotationsData[$reflectionControllerObj->name]['verbs'];
            } else {
                $annotationsData['verbs'] = [];
            }
        }
        return $annotationsData['verbs'];
    }
}
