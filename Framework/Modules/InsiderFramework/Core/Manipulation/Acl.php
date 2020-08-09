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
        $aclClass = ACL_CLASS;
        $aclObj = new $aclClass();
        return $aclObj::getUserAccessLevel($routeObj);
    }

    /**
     * Function that validates the user access level for a route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Acl
     *
     * @param RouteData $routeObj  Object of the current route
     * @param mixed $permissionNow Current permissions for the current route
     *
     * @return bool Return true if user is allowed to access the route
     */
    public static function validateACLPermission(
        \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj
    ) {
        $permissions = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'permissionNow',
            'insiderFrameworkSystem'
        );

        $aclClass = ACL_CLASS;
        $aclObj = new $aclClass();
        $test = $aclObj::validateACLPermission($routeObj, $permissions);
    }
}
