<?php

namespace Modules\InsiderFramework\Core\Tests\Loaders;

/**
* Class responsible for testing of the ModuleLoader class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Loaders\ModuleLoader
*/
class ModuleLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
    * loadModulesFromJsonConfigFile method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Loaders\ModuleLoader
    *
    * @return void
    */
    public function testLoadModulesFromJsonConfigFile(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ModuleLoader::loadModulesFromJsonConfigFile: Method ' .
            'already was implict tested'
        );
    }
}
