<?php

namespace Modules\InsiderFramework\Core\Manipulation;

use ioncube\phpOpensslCryptor\Cryptor;

/**
 * Trait containing ACL functions
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/manipulation#ACL
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\Acl
 */
trait Acl
{
    /**
     * Function that returns user access level for a route
     *
     * @author Marcello Costa
     * 
     * @package Modules\InsiderFramework\Core\Acl
     *
     * @param RouteData $routeObj Object of the current route
     *
     * @return mixed Returns the access level
     */
    public static function getUserAccessLevel(
        \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj
    ) {
        
        $ajaxrequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable('ajaxrequest', 'RoutingSystem');
        $permissions = $routeObj->getPermissions();

        if (
            file_exists(
                INSTALL_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'sys' .
                    DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'security_controller.php'
            )
        ) {
            switch (strtolower($permissions['type'])) {
                case 'native':
                    return \Modules\InsiderFramework\Core\RoutingSystem\Permission::getNativeAccessLevel();
                    break;
                case 'custom':
                    \Modules\InsiderFramework\Core\FileTree::requireOnceFile(
                        INSTALL_DIR . DIRECTORY_SEPARATOR . 'apps' . DIRECTORY_SEPARATOR . 'sys' .
                            DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'security_controller.php'
                    );
                    $SecurityController = new \Controllers\sys\SecurityController('sys', null, $ajaxrequest);

                    return $SecurityController->getCustomAccessLevel();
                    break;
                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError('ACL_METHOD not recognized');
                    break;
            }
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                "Security controller not found for user privilege verification",
                "app/sys"
            );
        }
    }
}
