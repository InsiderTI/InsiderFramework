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
    * getUserAccessLevel method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Manipulation\AclTest
    *
    * @return void
    */
    public function testGetUserAccessLevel(): void
    {
        $routeData = \Modules\InsiderFramework\Core\RoutingSystem\Request::searchAndFillRouteData(
            "/",
            "",
            ""
        );

        $userAccessLevel = \Modules\InsiderFramework\InsiderAcl\AclMain::getUserAccessLevel($routeData);

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

        $permissions = \Modules\InsiderFramework\InsiderAcl\AclMain::validateACLPermission($routeData);

        $this->assertIsBool($permissions);
    }
}
