<?php

namespace Modules\InsiderFramework\Core\Tests\Loaders;

/**
* Class responsible for testing of the initializeAutoLoader class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Loaders\initializeAutoLoaderTest
*/
class InitializeAutoLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
    * initializeAutoLoader method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Loaders\initializeAutoLoaderTest
    *
    * @return void
    */
    public function testInitializeAutoLoader(): void
    {
        $autoLoaderObj = new \Modules\InsiderFramework\Core\Loaders\AutoLoader();
        $this->assertInstanceOf('Modules\InsiderFramework\Core\Loaders\AutoLoader', $autoLoaderObj);
    }

    /**
    * getAppClassFilePath method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Loaders\initializeAutoLoaderTest
    *
    * @return void
    */
    public function testGetAppClassFilePath(): void
    {
        $appClassName = 'Apps\Start\Controllers\MainController';

        $appClassFilePath = \Modules\InsiderFramework\Core\Loaders\AutoLoader::getAppClassFilePath($appClassName);
        $appClassFilePathWithoutInstallDir = str_replace(INSTALL_DIR, '', $appClassFilePath);

        $this->assertEquals(
            $appClassFilePathWithoutInstallDir,
            '/Apps/Start/Controllers/MainController.php'
        );
    }

    /**
    * getFrameworkClassFilePath method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Loaders\initializeAutoLoaderTest
    *
    * @return void
    */
    public function testGetFrameworkClassFilePath(): void
    {
        $appClassName = 'Modules\InsiderFramework\Core\Tests\Loaders\initializeAutoLoaderTest';

        $frameworklassFilePath = \Modules\InsiderFramework\Core\Loaders\AutoLoader::getFrameworkClassFilePath(
            $appClassName
        );

        $frameworklassFilePathWithoutInstallDir = str_replace(INSTALL_DIR, '', $frameworklassFilePath);

        $this->assertEquals(
            $frameworklassFilePathWithoutInstallDir,
            '/Framework/Modules/InsiderFramework/Core/Tests/Loaders/initializeAutoLoaderTest.php'
        );
    }
}
