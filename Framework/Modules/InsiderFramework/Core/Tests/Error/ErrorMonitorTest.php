<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\ErrorMonitor;

/**
* Class responsible for testing of the ErrorMonitor class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\ErrorMonitor
*/
class ErrorMonitorTest extends \PHPUnit\Framework\TestCase
{
    /**
    * initialize method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorMonitor
    *
    * @return void
    */
    public function testInitialize(): void
    {
        ErrorMonitor::initialize();
        $this->expectNotToPerformAssertions();
    }

    /**
    * errorHandler method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorMonitor
    *
    * @return void
    */
    public function testErrorHandler(): void
    {
        ErrorMonitor::errorHandler();
        $this->expectNotToPerformAssertions();
    }
}
