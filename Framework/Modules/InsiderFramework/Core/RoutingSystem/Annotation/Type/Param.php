<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Read;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;

/**
 * Param annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Param
 */
class Param implements IType
{
    public static $phpDocParamRegex = '/@(?P<declaration>([^ ]+)) (?P<type>([^ ]+)) (?P<variable>([^ ]+)) (?P<description>.*)/';
    public static $routingSystemParamRegex = '/@(?P<declaration>([^ ]+)) (?P<type>([^ ]+)) (?P<variable>([^ ]+)) (?P<description>.*)/';

    /**
    * Handler for param declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Param
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
        if (!isset($annotationsData[$id]['param'])) {
            $annotationsData[$id]['param'] = [];
        }

        preg_match_all(
            Param::$phpDocParamRegex,
            $commentLine,
            $phpDocParamMatches,
            PREG_SET_ORDER,
            0
        );

        if (!is_array($phpDocParamMatches) || count($phpDocParamMatches) === 0) {
            return;
        }

        preg_match_all(
            Read::$patternArgs,
            $commentLine,
            $dataOfRouteSystemParam,
            PREG_SET_ORDER,
            0
        );

        if (!is_array($dataOfRouteSystemParam) || count($dataOfRouteSystemParam) === 0) {
            return;
        }

        var_dump($dataOfRouteSystemParam);
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        if (!isset($declarationMatches[0]['args']) || !isset($declarationMatches[0]['declaration'])) {
            return;
        }

        $paramRoute = explode(',', $commentElements[0]['args']);

        foreach ($paramRoute as $pR) {
            preg_match_all(
                Read::$betweenCommasPattern,
                $pR,
                $pRMatches,
                PREG_SET_ORDER,
                0
            );

            if (count($pRMatches) > 0) {
                $annotationsData[$id]['param'][$pRMatches[0]['Argument']] = $pRMatches[0]['Data'];
            }
        }
    }

    /**
    * Initialize the param variable for the controller route
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Param
    *
    * @param array  $routeAnnotationsData       Current annotations data array of the route
    *
    * @return array Array of params for the route (or null if do not exist)
    */
    public static function initializeParams(
        array &$routeAnnotationsData
    ): ?array {
        if (!isset($routeAnnotationsData['param'])) {
            $routeAnnotationsData['param'] = null;
        }
        return $routeAnnotationsData['param'];
    }
}
