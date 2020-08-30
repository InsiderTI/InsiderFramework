<?php

namespace Modules\InsiderFramework\InsiderAcl\Tests;

use Modules\InsiderFramework\InsiderAcl\InsiderAclMain;

/**
* Class responsible for testing InsiderAclMain class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\InsiderAcl\Tests\InsiderAclMainTest
*/
class InsiderAclMainTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getUserAccessLevelByRoute method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\InsiderAcl\Tests\InsiderAclMainTest
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
        $userAccessLevel = InsiderAclMain::getUserAccessLevelByRoute($routeData);
        $this->expectNotToPerformAssertions();
    }

    /**
    * validateACLPermission method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\InsiderAcl\Tests\InsiderAclMainTest
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

        var_dump($routeData);
        die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);

        $permissions = \Modules\InsiderFramework\InsiderAcl\InsiderAclMain::validateACLPermission($routeData);
        $this->assertIsBool($permissions);
    }
}
