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
        $climate = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'climate',
            'insiderFrameworkSystem'
        );

        if ($climate === null) {
            $messageErrJson = \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($message);
            die("\nCannot get climate variable from insiderFrameworkSystem kernelspace context. Error detail: " . json_encode($messageErrJson) . "\n\n");
        }
        
        $fatalTextStatus = $message->getFatal() ? 'True' : 'False';

        $climate->br();
        $climate->to('error')->red($message->getSubject())->br();
        $climate->to('error')->red("Type: " . $message->getType())->br();
        $climate->to('error')->red("Message: " . $message->getMessageOrText())->br();
        $climate->to('error')->red("File: " . $message->getFile())->br();
        $climate->to('error')->red("Line: " . $message->getLine())->br();
        $climate->to('error')->red("Fatal: " . $fatalTextStatus)->br();

        if ($message->getFatal()) {
            die();
        }
    }
}
