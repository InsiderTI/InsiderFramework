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

                $phpErrorType = \Modules\InsiderFramework\Core\Error\PhpErrorType::getPhpErrorTypeByErrorNumber($errno);
                $fatal = \Modules\InsiderFramework\Core\Error\ErrorFatal::getIfErrorIsFatalByErrorNumber($errno);

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'fatalError' => $fatal
                    ),
                    'insiderFrameworkSystem'
                );
                $error['fatal'] = $fatal;
                $error['frameworkErrorType'] = $phpErrorType;

                if ($fatal == true) {
                    $subject = "Fatal Error - Insider Framework report agent";
                } else {
                    $subject = "Non-fatal Error - Insider Framework report agent";
                }

                $error['subject'] = $subject;

                $responseFormat = \Modules\InsiderFramework\Core\Response::getCurrentResponseFormat();

                $ErrorMessage = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                    'phpErrorType' => $phpErrorType,
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
