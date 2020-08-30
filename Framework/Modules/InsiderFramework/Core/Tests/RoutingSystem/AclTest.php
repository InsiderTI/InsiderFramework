<?php

namespace Modules\InsiderFramework\Core\Tests\Manipulation;

use Modules\InsiderFramework\Core\Manipulation\Acl;
use Modules\InsiderFramework\Core\RoutingSystem\RouteData;

/**
* Class responsible for testing of the AclTest class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Manipulation\AclTest
*/
class AclTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getUserAccessLevelByRoute method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Manipulation\AclTest
    *
    * @return void
    */
    public function testGetUserAccessLevelByRoute(): void
    {
        $routeData = \Modules\InsiderFramework\Core\RoutingSystem\Request::searchAndFillRouteData(
            "/",
            "",
            ""
        );

        $userAccessLevel = \Modules\InsiderFramework\InsiderAcl\InsiderAclMain::getUserAccessLevelByRoute($routeData);

        $this->expectNotToPerformAssertions();
    }

    /**
    * validateACLPermission method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Manipulation\AclTest
    *
    * @return void
    */
    public function testValidateACLPermission(): void
    {
        $routeData = \Modules\InsiderFramework\Core\RoutingSystem\Request::searchAndFillRouteData(
            "/",
            "",
            ""
        );

        $permissions = \Modules\InsiderFramework\InsiderAcl\InsiderAclMain::validateACLPermission($routeData);

        $this->assertIsBool($permissions);
    }
}
