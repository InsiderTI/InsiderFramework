<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;
use Modules\InsiderFramework\Core\RoutingSystem\Read;

/**
 * Cache annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Cache
 */
class Cache implements IType
{
    /**
    * Handler for cache declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Cache
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
        if (isset($annotationsData[$id]['cache'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Cache" . "% statement is duplicated " .
                "in the class declaration in the controller " .
                "%" . $id . "%",
                "app/sys"
            );
        }

        preg_match_all(
            Read::$patternArgs,
            $commentLine,
            $cacheData,
            PREG_SET_ORDER,
            0
        );

        if (!is_array($cacheData) || count($cacheData) === 0) {
            return;
        }

        $annotationsData[$id]['cache'] = $cacheData[0]['args'];
    }
}
