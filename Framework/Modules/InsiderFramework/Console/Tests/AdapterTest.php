<?php

namespace Modules\InsiderFramework\Console\Tests;

/**
* Class responsible for basic testing of the adapter for console
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Console\Tests\AdapterTest
*/
class AdapterTest extends \PHPUnit\Framework\TestCase
{
    /**
    * Construct test for adapter
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\Tests\AdapterTest
    *
    * @return void
    */
    public function testConstruct(): void
    {
        $classname = '\Modules\InsiderFramework\Console\Adapter';

        // Get mock, without the constructor being called
        $mock = $this->getMockBuilder($classname)
          ->disableOriginalConstructor()
          ->getMock();

        // Set expectations for constructor calls
        $mock->expects($this->once())
          ->method('usage')
          ->with(
              $this->equalTo(4)
          );

        // Now call the constructor
        $reflectedClass = new \ReflectionClass($classname);
        $constructor = $reflectedClass->getConstructor();
        $constructor->invoke($mock, 4);
      
        $consoleAdapter = new \Modules\InsiderFramework\Console\Adapter();
    }
}
