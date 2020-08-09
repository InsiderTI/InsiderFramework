<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\ErrorFatal;

/**
* Class responsible for testing of the ErrorFatal class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\ErrorFatal
*/
class ErrorFatalTest extends \PHPUnit\Framework\TestCase
{
    /**
    * getIfErrorIsFatalByErrorNumber method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorFatal
    *
    * @return void
    */
    public function testGetIfErrorIsFatalByErrorNumber(): void
    {
        $errorFatal = ErrorFatal::getIfErrorIsFatalByErrorNumber(1);
        $this->assertEquals($errorFatal, true);
    }
    
    /**
    * getListOfValidPhpErrorNumbers method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorFatal
    *
    * @return void
    */
    public function testGetListOfValidPhpErrorNumbers(): void
    {
        $validPhpErrorNumbers = ErrorFatal::getListOfValidPhpErrorNumbers();

        $valid = false;
        if (is_array($validPhpErrorNumbers) && !empty($validPhpErrorNumbers)) {
            $valid = true;
        }
        $this->assertEquals($valid, true);
    }
}
