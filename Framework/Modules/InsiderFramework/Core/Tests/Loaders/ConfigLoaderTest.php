<?php

namespace Modules\InsiderFramework\Core\Tests\Loaders;

use Modules\InsiderFramework\Core\Loaders\ConfigLoader;

/**
* Class responsible for testing of the configLoader class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Loaders\ConfigLoaderTest
*/
class ConfigLoaderTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getFrameworkConfigVariablesFromConfigFiles method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Loaders\ConfigLoaderTest
    *
    * @return void
    */
    public function testGetFrameworkConfigVariablesFromConfigFiles(): void
    {
        $configVariables = ConfigLoader::getFrameworkConfigVariablesFromConfigFiles();
        $valid = false;

        if (is_array($configVariables) && !empty($configVariables)) {
            $valid = true;
        }

        $this->assertEquals($valid, true);
    }

    /**
    * initializeConfigVariablesFromConfigFiles method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Loaders\ConfigLoaderTest
    *
    * @return void
    */
    public function testInitializeConfigVariablesFromConfigFiles(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ConfigLoader::initializeConfigVariablesFromConfigFiles: Method ' .
            'already was implict tested'
        );
    }

    /**
    * defineRepositoriesConstants method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Loaders\ConfigLoaderTest
    *
    * @return void
    */
    public function testDefineRepositoriesConstants(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ConfigLoader::defineRepositoriesConstants: Method ' .
            'already was implict tested'
        );
    }

    /**
    * getConfigFileData method test
    *
    * @author Marcello Costa
    *
    * @packageModules\InsiderFramework\Core\Tests\Loaders\ConfigLoaderTest
    *
    * @return void
    */
    public function testGetConfigFileData(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ConfigLoader::defineRepositoriesConstants: Method ' .
            'already was implict tested'
        );
    }
}
