<?php

namespace Modules\InsiderFramework\Core\Tests\Error;

use Modules\InsiderFramework\Core\Error\ErrorHandler;

/**
* Class responsible for testing of the error handler
*
* @author Marcello Costa
*
* @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
*/
class ErrorHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
    * Primary error method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testPrimaryError(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::PrimaryError: Method cannot be tested because ends the script execution'
        );
    }

    /**
    * uncaughtTypeError method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testUncaughtTypeError(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::uncaughtTypeError: Method cannot be ' .
            'tested because ends the script execution'
        );
    }

    /**
    * i10nErrorRegister method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testI10nErrorRegister(): void
    {
        $message = 'Test i10n error message';
        $domain = 'en';
        $linguas = 'en';
        $type = 'LOG';
        $responseCode = 200;

        ErrorHandler::i10nErrorRegister(
            $message,
            $domain,
            $linguas,
            $type,
            $responseCode
        );

        $this->expectNotToPerformAssertions();
    }

    /**
    * errorRegister as warning method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testErrorRegisterWarning(): void
    {
        $message = "Error register test";
        $type = "WARNING";
        $responseCode = 200;

        ErrorHandler::errorRegister(
            $message,
            $type,
            $responseCode
        );

        $warnings = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'warnings',
            'insiderFrameworkSystem'
        );

        $assert = false;
        if (is_array($warnings) && !empty($warnings)) {
            $assert = true;
        }

        $this->assertTrue($assert);
    }

    /**
    * getFrameworkDebugStatus method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testGetFrameworkDebugStatus(): void
    {
        $debugStatus = ErrorHandler::getFrameworkDebugStatus();

        $this->assertIsBool($debugStatus);
    }

    /**
    * manageErrorTestRequest method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testManageErrorTestRequest(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::manageErrorTestRequest: Method ' .
            'already was implict tested'
        );
    }

    /**
    * manageErrorConsoleRequest method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testManageErrorConsoleRequest(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::manageErrorConsoleRequest: Method ' .
            'already was implict tested'
        );
    }

    /**
    * initializeDebugBackTrace method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testInitializeDebugBackTrace(): void
    {
        $debugbacktrace = ErrorHandler::initializeDebugBackTrace();

        $debugbacktraceKernelSpace = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'debugbacktrace',
            'insiderFrameworkSystem'
        );

        $assert = false;
        if (is_array($debugbacktraceKernelSpace) && is_array($debugbacktrace)) {
            $assert = true;
        }

        $this->assertTrue($assert);
    }

    /**
    * getRelativeScriptPath method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testGetRelativeScriptPath(): void
    {
        $relativeScriptPath = ErrorHandler::getRelativeScriptPath();

        $expectedPath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                        "Framework" . DIRECTORY_SEPARATOR .
                        "Modules";

        $this->assertEquals($relativeScriptPath, $expectedPath);
    }

    /**
    * sendMessageToUser method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testSendMessageToUser(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::sendMessageToUser: Method cannot be ' .
            'tested because ends the script execution'
        );
    }

    /**
    * sendMessageToAdmin method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testSendMessageToAdmin(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::sendMessageToAdmin: Method cannot be ' .
            'tested because ends the script execution'
        );
    }

    /**
    * manageError method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testManageError(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::manageError: Method ' .
            'already was implict tested'
        );
    }

    /**
    * classFileNotFound method test
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Tests\Error\ErrorHandler
    *
    * @return void
    */
    public function testClassFileNotFound(): void
    {
        $this->markTestSkipped(
            'Cannot run tests for ErrorHandler::classFileNotFound: Method cannot be ' .
            'tested because ends the script execution'
        );
    }
}
