<?php

namespace Modules\InsiderFramework\Core\RoutingSystem;

use Modules\InsiderFramework\Core\Registry;

/**
 * ACL functions class
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/manipulation#ACL
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Modules\InsiderFramework\Core\RoutingSystem\Acl
 */
class Acl
{
    /**
     * Function that returns user access level for a route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Acl
     *
     * @param RouteData $routeObj Object of the current route
     *
     * @return mixed Returns the access level
     */
    public static function getUserAccessLevelByRoute(
        \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj
    ) {
        $aclData = Registry::getAclDataRegistry(ACL_DEFAULT_ENGINE);
        $aclObj = new $aclData['ACL_DEFAULT_ENGINE_CLASS']();
        return $aclObj::getUserAccessLevelByRoute($routeObj);
    }

    /**
     * Function that validates the user access level for a route
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\RoutingSystem\Acl
     *
     * @param RouteData $routeObj  Object of the current route
     * @param mixed $currentUserAclPermission Current permissions for the current route
     *
     * @return bool Return true if user is allowed to access the route
     */
    public static function validateACLPermission(
        \Modules\InsiderFramework\Core\RoutingSystem\RouteData $routeObj
    ) {
        $permissions = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'currentUserAclPermission',
            'routingSystem'
        );

        $aclData = Registry::getAclDataRegistry(ACL_DEFAULT_ENGINE);
        $aclObj = new $aclData['ACL_DEFAULT_ENGINE_CLASS']();
        $test = $aclObj::validateACLPermission($routeObj, $permissions);
    }
}
