<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Monitor and error redirector. The ErrorHandler function is currently
 * only function to initialize the error checking in the code.
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Error
 */
class ErrorMonitor
{
    /**
    * Initialize the error handler function
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error
    *
    * @return void
    */
    public static function initialize(): void
    {
        register_shutdown_function([
            "\Modules\InsiderFramework\Core\Error\ErrorMonitor",
            "errorHandler"
        ]);
    }

    /**
     * Function that handles all possible errors that occur during execution
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error
     *
     * @return void
     */
    public static function errorHandler(): void
    {
        try {
            // Types of PHP errors

            /*
            Fatal:
            1       E_ERROR (integer)
            4       E_PARSE (integer)
            16      E_CORE_ERROR (integer)
            64      E_COMPILE_ERROR
            4096    E_RECOVERABLE_ERROR

            Not fatal:
            2       E_WARNING (integer)
            8       E_NOTICE (integer)
            32      E_CORE_WARNING
            128     E_COMPILE_WARNING
            256     E_USER_ERROR
            512     E_USER_WARNING
            1024    E_USER_NOTICE
            2048    E_STRICT
            8192    E_DEPRECATED
            16384   E_USER_DEPRECATED
            32767   E_ALL
            */

            // Getting the last error
            $error = error_get_last();

            if ($error !== null && error_reporting() !== 0) {
                $errno = $error["type"];
                $message = $error["message"];
                $file = $error["file"];
                $line = $error["line"];

                $fatal = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'fatalError',
                    'insiderFrameworkSystem'
                );

                if ($fatal === null) {
                    $fatal = false;
                }

                switch ($errno) {
                    case 1:
                        $errorType = 'E_ERROR';
                        $fatal = true;
                        break;

                    case 2:
                        $errorType = 'E_WARNING';
                        break;

                    case 4:
                        $errorType = 'E_PARSE';
                        $fatal = true;
                        break;

                    case 8:
                        $errorType = 'E_NOTICE';
                        break;

                    case 16:
                        $errorType = 'E_CORE_ERROR';
                        $fatal = true;
                        break;

                    case 32:
                        $errorType = 'E_CORE_WARNING';
                        break;

                    case 64:
                        $errorType = 'E_COMPILE_ERROR';
                        $fatal = true;
                        break;

                    case 128:
                        $errorType = 'E_COMPILE_WARNING';
                        break;

                    case 256:
                        $errorType = 'E_USER_ERROR';
                        break;

                    case 512:
                        $errorType = 'E_USER_WARNING';
                        break;

                    case 1024:
                        $errorType = 'E_USER_NOTICE';
                        break;

                    case 2048:
                        $errorType = 'E_STRICT';
                        break;

                    case 4096:
                        $errorType = 'E_RECOVERABLE_ERROR';
                        $fatal = true;
                        break;

                    case 8192:
                        $errorType = 'E_DEPRECATED';
                        break;

                    case 16384:
                        $errorType = 'E_USER_DEPRECATED';
                        break;
                }

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'fatalError' => $fatal
                    ),
                    'insiderFrameworkSystem'
                );
                $error['fatal'] = $fatal;
                $error['type'] = $errorType;

                if ($fatal == true) {
                    $subject = "Fatal Error - Insider Framework report agent";
                } else {
                    $subject = "Non-fatal Error - Insider Framework report agent";
                }

                $error['subject'] = $subject;

                $responseFormat = \Modules\InsiderFramework\Core\Response::getCurrentResponseFormat();

                $ErrorMessage = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                    'type' => $errorType,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => $fatal,
                    'subject' => $subject
                ));

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'errorMessage' => $ErrorMessage
                    ),
                    'insiderFrameworkSystem'
                );
                \Modules\InsiderFramework\Core\Error\ErrorHandler::manageError($ErrorMessage);
            }
        } catch (\Exception $e) {
            var_dump($e);
            die("FILE: " . __FILE__ . "<br/>LINE: " . __LINE__);
        }
    }
}
