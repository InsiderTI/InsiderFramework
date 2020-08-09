<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\PhpErrorType;

/**
* Class responsible for testing of the ErrorType class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\PhpErrorType
*/
class PhpErrorTypeTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getValidPhpErrorTypes method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\PhpErrorType
    *
    * @return void
    */
    public function testGetValidPhpErrorTypes(): void
    {
        $errorTypes = PhpErrorType::getValidPhpErrorTypes();

        $validErrorTypesArray = false;
        if (is_array($errorTypes) && !empty($errorTypes)) {
            $validErrorTypesArray = true;
        }
        $this->assertEquals($validErrorTypesArray, true);
    }

    /**
    * getPhpErrorTypeByErrorNumber method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\PhpErrorType
    *
    * @return void
    */
    public function testGetPhpErrorTypeByErrorNumber(): void
    {
        $errorType = PhpErrorType::getPhpErrorTypeByErrorNumber(1);
        $this->assertEquals($errorType, "E_ERROR");
    }

    /**
    * validatePhpErrorTypeName method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\PhpErrorType
    *
    * @return void
    */
    public function testValidatePhpErrorTypeName(): void
    {
        $errorType = PhpErrorType::validatePhpErrorTypeName('E_ERROR');
        $this->assertEquals($errorType, true);

        $errorType = PhpErrorType::validatePhpErrorTypeName('E_ERROR_2');
        $this->assertEquals($errorType, false);
    }
}
