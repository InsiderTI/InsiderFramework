<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\FrameworkErrorType;

/**
* Class responsible for testing of the FrameworkErrorType class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\FrameworkErrorType
*/
class FrameworkErrorTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getValidFrameworkErrorTypes method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\FrameworkErrorTypeTest
    *
    * @return void
    */
    public function testGetValidFrameworkErrorTypes(): void
    {
        $errorTypes = FrameworkErrorType::getValidFrameworkErrorTypes(1);

        $validErrorTypesArray = false;
        if (is_array($errorTypes) && !empty($errorTypes)) {
            $validErrorTypesArray = true;
        }
        $this->assertEquals($validErrorTypesArray, true);
    }

    /**
    * validateFrameworkErrorType method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\FrameworkErrorTypeTest
    *
    * @return void
    */
    public function testValidateFrameworkErrorType(): void
    {
        $validError = FrameworkErrorType::validateFrameworkErrorTypeName('CRITICAL');
        $this->assertEquals($validError, true);
    }
}
