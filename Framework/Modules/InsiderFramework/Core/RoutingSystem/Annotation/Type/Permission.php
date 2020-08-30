<?php

namespace Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type;

use Modules\InsiderFramework\Core\Registry;
use Modules\InsiderFramework\Core\RoutingSystem\Read;
use Modules\InsiderFramework\Core\RoutingSystem\Annotation\IType;

/**
 * Permission annotation handler class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Permission
 */
class Permission implements IType
{
    /**
    * Handler for permission declaration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Permission
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
        if (isset($annotationsData[$id]['permission'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "The %" . "@Permission" . "% statement is " .
                "duplicated in the class declaration in the " .
                "controller %" . $id . "%",
                "app/sys"
            );
        }

        preg_match_all(
            Read::$betweenCommasPattern,
            $commentLine,
            $commentElements,
            PREG_SET_ORDER,
            0
        );

        if (!isset($commentElements[0]['Data'])) {
            return;
        }

        $engine = ACL_DEFAULT_ENGINE;
        $rules = [];
        foreach ($commentElements as $commentElement) {
            switch (strtolower($commentElement['Argument'])) {
                case 'engine':
                    $engine = $commentElement['Data'];
                    break;
                default:
                    $rules = explode(',', strtolower(str_replace(" ", "", $commentElements[0]['Data'])));
                    break;
            }
        }

        $annotationsData[$id]['permission'] = array(
            'engine' => $engine,
            'rules' => $rules,
        );
    }

    /**
    * Initialize the permissions route array with the correct content
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\RoutingSystem\Annotation\Type\Permission
    *
    * @param array  $annotationsData  Current mapped annotation data
    *
    * @return array Array of permissions
    */
    public static function initializePermissionsArray(
        array &$annotationsData,
        $reflectionControllerObj,
        $controllerAnnotationsData
    ): array {
        if (!isset($annotationsData['permission'])) {
            $annotationsData['permission'] = [];
        }
        $permissionArray = $annotationsData['permission'];

        if (!isset($permissionArray['permission']['engine'])) {
            $permissionArray['permission']['engine'] = ACL_DEFAULT_ENGINE;
        }
        if (!isset($permissionArray['permission']['rules'])) {
            $permissionArray['permission']['rules'] = '';
        }
        $permissions = array(
            'permissionEngine' => $permissionArray['permission']['engine'],
            'rules' => $permissionArray['permission']['rules']
        );

        $annotationsData['permission'] = $permissions;

        if (array_key_first($annotationsData) == "Apps\Start\Controllers\MainController") {
            var_dump($controllerAnnotationsData);
            var_dump($permissions);
            var_dump($annotationsData);
            die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        }

        return $permissions;
    }
}
