<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Read;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;

/**
 * Route annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Route
 */
class Route implements IType
{
    /**
    * Handler for route declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Route
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
        if (isset($annotationsData[$id]['route'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Route" . "% statement is duplicated in the " .
                "class declaration in the controller %" . $id . "%",
                "app/sys"
            );
        }

        preg_match_all(
            Read::$patternArgs,
            $commentLine,
            $declarationMatches,
            PREG_SET_ORDER,
            0
        );

        if (!isset($declarationMatches[0]['args']) || !isset($declarationMatches[0]['declaration'])) {
            return;
        }

        $args = $declarationMatches[0]['args'];

        preg_match_all(
            Read::$betweenCommasPattern,
            $args,
            $commentElements,
            PREG_SET_ORDER,
            0
        );

        $declaration = ucwords(str_replace(" ", "", $commentElements[0]['declaration']));

        foreach ($commentElements as $argK => $arg) {
            $argument = trim(strtolower($arg['Argument']));
            if (isset($annotationsData[$id]['route'][$argument])) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                    "The %" . "@" . $argument . "% argument is " .
                    "duplicated in statement " . $declaration . " " .
                    "in the controller %" . $id . "%",
                    "app/sys"
                );
            }

            $annotationsData[$id]['route'][$argument] = trim($arg['Data']);
        }
    }
}
