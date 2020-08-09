<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\ErrorMessage;

/**
* Class responsible for testing of the ErrorMessage class
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\ErrorMessage
*/
class ErrorMessageTest extends \PHPUnit\Framework\TestCase
{
    /**
    * Construct class test with framework error type
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorMessage
    *
    * @return void
    */
    public function testConstructWithFrameworkErrorType(): void
    {
        $frameworkErrorType = 'CRITICAL';
        $message = 'Error message test';
        $text = 'Error text test';
        $file = __FILE__;
        $line = __LINE__;
        $fatal = true;
        $subject = 'Critical Error - Insider Framework report agent';

        $arrayTest = array(
            'frameworkErrorType' => $frameworkErrorType,
            'message' => $message,
            'text' => $text,
            'file' => $file,
            'line' => $line,
            'fatal' => $fatal,
            'subject' => $subject
        );

        $errorMessageObj = new \Modules\InsiderFramework\Core\Error\ErrorMessage($arrayTest);
        
        $this->assertEquals($errorMessageObj->getFrameworkErrorType(), $frameworkErrorType);
        $this->assertEquals($errorMessageObj->getMessage(), $message);
        $this->assertEquals($errorMessageObj->getText(), $text);
        $this->assertEquals($errorMessageObj->getText(), $text);
        $this->assertEquals($errorMessageObj->getFile(), str_replace(INSTALL_DIR, '', $file));
        $this->assertEquals($errorMessageObj->getLine(), $line);
        $this->assertEquals($errorMessageObj->getFatal(), $fatal);
        $this->assertEquals($errorMessageObj->getSubject(), $subject);
    }

    /**
    * Construct class test with php error type
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorMessage
    *
    * @return void
    */
    public function testConstructWithPhpErrorType(): void
    {
        $phpErrorType = 'E_ERROR';
        $message = 'Error message test';
        $text = 'Error text test';
        $file = __FILE__;
        $line = __LINE__;
        $fatal = true;
        $subject = 'Critical Error - Insider Framework report agent';

        $arrayTest = array(
            'phpErrorType' => $phpErrorType,
            'message' => $message,
            'text' => $text,
            'file' => $file,
            'line' => $line,
            'fatal' => $fatal,
            'subject' => $subject
        );


        $errorMessageObj = new \Modules\InsiderFramework\Core\Error\ErrorMessage($arrayTest);
        
        $this->assertEquals($errorMessageObj->getPhpErrorType(), $phpErrorType);
        $this->assertEquals($errorMessageObj->getMessage(), $message);
        $this->assertEquals($errorMessageObj->getText(), $text);
        $this->assertEquals($errorMessageObj->getText(), $text);
        $this->assertEquals($errorMessageObj->getFile(), str_replace(INSTALL_DIR, '', $file));
        $this->assertEquals($errorMessageObj->getLine(), $line);
        $this->assertEquals($errorMessageObj->getFatal(), $fatal);
        $this->assertEquals($errorMessageObj->getSubject(), $subject);
    }
}
