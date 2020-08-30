<?php

namespace Modules\InsiderFramework\Core\Tests;

/**
* Class responsible for testing Bootstrap of the framework
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\BootstrapTest
*/
class BootstrapTest extends \PHPUnit\Framework\TestCase
{
    /**
    * initializeFramework method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\BootstrapTest
    *
    * @return void
    */
    public function testInitializeFramework(): void
    {
        $bootstrapClassExists = false;
        if (class_exists("Modules\InsiderFramework\Core\Bootstrap")) {
            $bootstrapClassExists = true;
        }

        \Modules\InsiderFramework\Core\Loaders\AutoLoader::initializeAutoLoader();

        \Modules\InsiderFramework\Core\Loaders\ConfigLoader::initializeConfigVariablesFromConfigFiles();
        
        \Modules\InsiderFramework\Core\Loaders\ModuleLoader::loadModulesFromJsonConfigFile();

        \Modules\InsiderFramework\Core\Error\ErrorMonitor::initialize();

        Bootstrap::initializeDebugBarCounter();

        Bootstrap::initializeGlobalObjectPageVariables();
        
        Bootstrap::initalizeGlobalHttpVerbsVariables();

        Bootstrap::initalizeUserAgentVariable();

        \Modules\InsiderFramework\Core\RoutingSystem\Bootstrap::initialize();

        Bootstrap::initializeHeaderRequestVariable();

        Bootstrap::startPhpSession();

        Bootstrap::initializeRequestSourceVariable();

        Bootstrap::sessionHijackingProtection();

        Bootstrap::initializeGlobalServerVariable();

        Bootstrap::initializeErrorsVariables();

        Bootstrap::initializeResponseFormatVariable();
    }
}
