<?php

namespace Modules\InsiderFramework\Console;

use Modules\InsiderFramework\Core\Error\ErrorMessage;

/**
 * Main class of console error handler
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Console\ConsoleError
 */
class ConsoleError
{
    /**
    * Error register function for console commands
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Console\ConsoleError
    *
    * @param object $errorMessage Error message object
    *
    * @return void
    */
    public static function errorRegister(ErrorMessage $message): void
    {
        $console = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'console',
            'insiderFrameworkSystem'
        );

        if ($console === null) {
            $messageErrJson = \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($message);
            die("\nCannot get console variable from insiderFrameworkSystem kernelspace context. Error detail: "
            . json_encode($messageErrJson) . "\n\n");
        }
        
        $fatalTextStatus = $message->getFatal() ? 'True' : 'False';

        $console->br();
        $console->setTextColor('red');
        $console->write($message->getSubject())->br();
        $console->setTextColor('red');
        $console->write("Type: " . $message->getType())->br();
        $console->setTextColor('red');
        $console->write("Message: " . $message->getMessageOrText())->br();
        $console->setTextColor('red');
        $console->write("File: " . $message->getFile())->br();
        $console->setTextColor('red');
        $console->write("Line: " . $message->getLine())->br();
        $console->setTextColor('red');
        $console->write("Fatal: " . $fatalTextStatus)->br();

        if ($message->getFatal()) {
            die();
        }
    }
}
