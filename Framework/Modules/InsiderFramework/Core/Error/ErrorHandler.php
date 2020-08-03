<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * Class that contains functions for handling errors
 *
 * @package Modules\InsiderFramework\Core\Error\ErrorHandler
 *
 * @author Marcello Costa
 */
class ErrorHandler
{
    /**
     * Function that allows you to trigger an error directly to the user
     * and stop the execution of php script
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $msg          Error message
     * @param int    $errorCode    Error code
     * @param string $outputFormat HTML or JSON
     *
     * @return array Returns the result
     */
    public static function primaryError(string $msg, int $errorCode = 500, string $outputFormat = 'JSON'): array
    {
        $requestSource = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'requestSource',
            'insiderFrameworkSystem'
        );

        // If it's an error inside a terminal (console)
        if ($requestSource === 'console') {
            die();
        }

        error_log($msg);
        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

        http_response_code($errorCode);

        if (strtoupper($outputFormat) === 'JSON') {
            $msgToUser = [];
            $msgToUser['error'] = $msg;
            $output = json_encode($msgToUser);
        } else {
            $output = "ERROR $errorCode: " . $msg;
        }

        // XML Output
        // $xml = new \SimpleXMLElement('<root/>');
        // $xml->addChild('error', $msg);
        // $output=$xml->asXML();
        // header("Content-type: text/xml; charset=utf-8");
        die($output);
    }

    /**
    * Trigger uncaught TypeError message
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @param int    $argumentNumber Argument number
    * @param string requiredClass   Argument number
    * @param string $givenClass     Argument number
    *
    * @return void
    */
    public static function uncaughtTypeError(int $argumentNumber, string $requiredClass, string $givenClass): void
    {
        $errorMessage = "Uncaught TypeError: Argument " .
        $argumentNumber .
        " passed to " .
        __METHOD__ . "() must be an instance of " .
        $requiredClass . "," .
        "instance of " .
        $givenClass .
        "given";

        \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister($errorMessage);
    }

    /**
     * Function that shows / register translated errors
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $message      Error message
     * @param string $domain       Domain of error message
     * @param string $linguas      Language of error message
     * @param string $type         Type of error
     * @param int    $responseCode Response code of error
     *
     * @return void
     */
    public static function i10nErrorRegister(
        string $message,
        string $domain,
        string $linguas = LINGUAS,
        string $type = "CRITICAL",
        int $responseCode = null
    ): void {
        $msgI10n = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate($message, $domain, $linguas);

        if ($msgI10n === "") {
            $msgI10n = str_replace("%", "", $message);
        }

        \Modules\InsiderFramework\Core\Error\ErrorHandler::errorRegister($msgI10n, $type, $responseCode);
    }

    /**
     * Register error in log file
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $message Error message
     *
     * @return string Returns the uniqid of the error
     */
    protected static function registerErrorInLogFile(string $message)
    {
        $rebuildLogFileName = function ($id) {
            return INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Cache" . DIRECTORY_SEPARATOR .
            "logs" . DIRECTORY_SEPARATOR .
            "logfile." . $id;
        };
        
        // Counter of log files
        $id = 0;

        // Avoid locked files
        $getValidLogFileName = function ($id) use ($rebuildLogFileName) {
            $logfilepath = $rebuildLogFileName($id);

            while (file_exists($logfilepath . ".lock")) {
                $id++;
                $logfilepath = $rebuildLogFileName($id);
            }

            $maxFileSizeMB = 5;
            $sizeOfLogFileExceded = true;
            while ($sizeOfLogFileExceded) {
                if (!file_exists($logfilepath)) {
                    $sizeOfLogFileExceded = false;
                } else {
                    $humanFileSize = filesize($logfilepath) / 1024 / 1024;
                    if ($humanFileSize > $maxFileSizeMB) {
                        $id++;
                        $logfilepath = $getValidLogFileName($id);
                    }
                    $sizeOfLogFileExceded = false;
                }
            }

            return $logfilepath;
        };

        $logfilepath = $getValidLogFileName($id);

        // Inserts and prefix in the message (for now, hard-coded)
        $date = new \DateTime('NOW');
        $dataFormat = $date->format('Y-m-d H:i:s');
        $message = $dataFormat . "    " . $message . PHP_EOL;

        // Writing in the log file
        \Modules\InsiderFramework\Core\FileTree::fileWriteContent($logfilepath, $message);

        // Returning the error ID
        return $id;
    }

    /**
     * Register/Show errors
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $message      Error message
     * @param string $type         Type of error
     * @param int    $responseCode Response code of the error
     *
     * @return void|string Returns the uniqid of the error if it's of type LOG
     */
    public static function errorRegister(string $message, string $type = "CRITICAL", int $responseCode = null): ?string
    {
        $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);

        $file = $debugbacktrace[0]['file'];
        $line = $debugbacktrace[0]['line'];
        if (isset($debugbacktrace[2])) {
            if (isset($debugbacktrace[2]['file']) && isset($debugbacktrace[2]['line'])) {
                $file = $debugbacktrace[2]['file'];
                $line = $debugbacktrace[2]['line'];
            }
        }

        $requestSource = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'requestSource',
            'insiderFrameworkSystem'
        );

        switch (strtoupper(trim($type))) {
            // This is just a warning error and will be displayed in debug_bar and
            // registered in the log file
            case "WARNING":
                if (DEBUG_BAR || $requestSource == "phpunit") {
                    $error = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                        'type' => $type,
                        'text' => $message,
                        'file' => $file,
                        'line' => $line,
                        'fatal' => false,
                        'subject' => 'Warning Error - Insider Framework report agent'
                    ));

                    $debug = new \Modules\InsiderFramework\Core\Debug();
                    $debug->debugBar("logWarningError", $error);
                }
                
                return ErrorHandler::registerErrorInLogFile($message);

                break;

            // This type of error just writes in the log file
            case "LOG":
                return ErrorHandler::registerErrorInLogFile($message);
                break;

            // Attack to the system
            case "ATTACK_DETECTED":
                if ($responseCode === null) {
                    $responseCode = 405;
                }

                // HTTP 405 response
                http_response_code($responseCode);

                // Building the error array
                $error = new \Modules\InsiderFramework\Core\Error\ErrorMessage(
                    array(
                        'type' => $type,
                        'text' => 'Attack detected',
                        'file' => $file,
                        'line' => $line,
                        'fatal' => true,
                        'subject' => 'Attack Error - Insider Framework report agent'
                    )
                );

                // Setting the global variable
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'fatalError' => true
                    ),
                    'insiderFrameworkSystem'
                );

                // Managing the error
                \Modules\InsiderFramework\Core\Error\ErrorHandler::manageError($error);
                break;

                // This is the kind of error which will return an JSON for
                // the user (usefull in ajax requests, for example)
            case 'JSON_PRE_CONDITION_FAILED':
                if ($responseCode === null) {
                    $responseCode = 412;
                }

                http_response_code($responseCode);

                $error = array(
                    'error' => $message
                );

                echo \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($error);
                exit();
                break;

                // This is the kind of error which will return an XML for
                // the user (usefull in ajax requests, for example)
            case 'XML_PRE_CONDITION_FAILED':
                if ($responseCode === null) {
                    $responseCode = 412;
                }

                http_response_code($responseCode);

                if (!\Modules\InsiderFramework\Core\Xml::isXML($message)) {
                    $error = array(
                        'error' => $message
                    );
                    $xmlObj = "";
                    $message = \Modules\InsiderFramework\Core\Xml::arrayToXML($error, $xmlObj);
                    if ($message === false) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            "Unable to convert error to XML when triggering error"
                        );
                    }
                    echo $message;
                }
                exit();
                break;

            // Critical / Default error
            case "CRITICAL":
            default:
                if ($responseCode === null) {
                    $responseCode = 500;
                }

                // HTTP 500 response
                http_response_code($responseCode);

                // Populate the error object
                $ErrorMessage = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => isset($subject) ? $subject : 'Critical Error - Insider Framework report agent'
                ));

                // Setting the global variable
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'fatalError' => true
                    ),
                    'insiderFrameworkSystem'
                );

                // Managing the error
                \Modules\InsiderFramework\Core\Error\ErrorHandler::manageError($ErrorMessage);
                break;
        }
    }

    /**
     * Use this to get the current state of debug
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @return bool Current state of debug
     */
    public static function getFrameworkDebugStatus(): bool
    {
        $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'contentConfig',
            'insiderFrameworkSystem'
        );

        // If the DEBUG it's not defined, the environment variables must be manually loaded
        // The path will not be mapped correctly with getcwd(), so the constante __DIR__
        // is recovery and treated accordingly
        $path = __DIR__;

        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to recover installation directory when trigger error"
            );
        }

        try {
            $path = implode(DIRECTORY_SEPARATOR, array_slice($path, 0, count($path) - 3));
        } catch (\Exception $e) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to rebuild installation directory when trigger error"
            );
        }

        $coreEnvFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                       'Framework' . DIRECTORY_SEPARATOR .
                       'Config' . DIRECTORY_SEPARATOR .
                       'core.json';

        if (!file_exists($coreEnvFile)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Env file does not exist when triggering error"
            );
        }
        
        $contentConfig = json_decode(file_get_contents($coreEnvFile));
        if ($contentConfig === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Specific core file does not contain environment information when triggering error"
            );
        }
        if (!property_exists($contentConfig, 'DEBUG')) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to set DEBUG state when trigger error"
            );
        }
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'contentConfig' => $contentConfig
            ),
            'insiderFrameworkSystem'
        );

        // Setting the debug manually to a variable
        $debugNow = $contentConfig->DEBUG;

        return $debugNow;
    }

    /**
    * Stops throwing errors on framework and kills the execution
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @return void
    */
    protected static function stopThrowingErrors(): void
    {
        $debugbacktrace = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'debugbacktrace',
            'insiderFrameworkSystem'
        );

        $finalErrorMsg = "Internal Server Error - Max errors on framework reached. Consult your web administrator.";

        // Setting the reponse code as 500
        http_response_code(500);

        // Writing the error details in the log
        error_log(json_encode($debugbacktrace));
        
        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

        // If the debug it's not enable
        if (!DEBUG) {
            if (is_array($debugbacktrace)) {
                $debugbacktrace = json_encode($debugbacktrace);
            }
            // Stopping the execution with a default message
            
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($finalErrorMsg, 500, $responseFormat);
        }
    }

    /**
    * Force stops throwing errors on framework and kills the execution
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @return void
    */
    protected static function forceStopThrowingErrors(): void
    {
        error_log(
            json_encode($error)
        );

        $systemicErrorFilePath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                                    "Apps" . DIRECTORY_SEPARATOR .
                                    "Sys" . DIRECTORY_SEPARATOR .
                                    "Views" . DIRECTORY_SEPARATOR .
                                    "error" . DIRECTORY_SEPARATOR .
                                    "primarySystemicError.html";

        if (file_exists($systemicErrorFilePath)) {
            $originalSystemicErrorContent = file_get_contents($systemicErrorFilePath);
                    
            if (DEBUG) {
                ob_start();
                print_r($error);
                $userMessage = ob_get_contents();
                ob_end_clean();
            } else {
                $userMessage = " Please consult your web administrator";
            }
                    
            $systemicErrorContent = str_replace('{errorContent}', $userMessage, $originalSystemicErrorContent);

            echo $systemicErrorContent;
            die();
        }

        die('Internal Server Error - Primary systemic error');
    }

    /**
    * Validate the max error number and register the current error in KernelSpace
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
    *
    * @return void
    */
    protected static function validateMaxErrorNumberAndRegisterInKernelSpace(
        \Modules\InsiderFramework\Core\Error\ErrorMessage $error
    ): void {
        $errorCount = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'errorCount',
            'insiderFrameworkSystem'
        );

        if ($errorCount === null) {
            $errorCount = 0;
        } else {
            $errorCount++;
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'errorCount' => $errorCount
            ),
            'insiderFrameworkSystem'
        );

        if ($errorCount > 10) {
            if ($errorCount > 11) {
                ErrorHander::forceStopThrowingErrors();
            }

            ErrorHander::stopThrowingErrors();
        }

        $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'registeredErrors',
            'insiderFrameworkSystem'
        );

        if (!is_array($registeredErrors)) {
            $registeredErrors = [];
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'registeredErrors' => $registeredErrors
                )
            );
        }
    }

    /**
     * Function that manage an error if it's a test (phpunit) request
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
     *
     * @return void
     */
    public static function manageErrorTestRequest(\Modules\InsiderFramework\Core\Error\ErrorMessage $error): void
    {
        // Initiazing console instance
        $console = \Modules\InsiderFramework\Console\Application::createConsoleInstance();
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'console' => $console
            ),
            'insiderFrameworkSystem'
        );

        // Manage error as console request
        errorHandler::manageErrorConsoleRequest($error);
    }

    /**
     * Function that manage an error if it's a console request
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
     *
     * @return void
     */
    public static function manageErrorConsoleRequest(\Modules\InsiderFramework\Core\Error\ErrorMessage $error): void
    {
        $console = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'console',
            'insiderFrameworkSystem'
        );
        $console->br();
        $console->setTextColor('red');
        $console->write($error->getSubject());
        $console->br();
        $console->write("Type: " . $error->getType());
        $console->br();
        $console->write("Message: " . $error->getMessageOrText());
        $console->br();
        $console->write("File: " . $error->getFile());
        $console->br();
        $console->write("Line: " . $error->getLine());
        $console->br();
        $console->write("Fatal: " . ($error->getFatal() ? 'true' : 'false'));
        $console->br();
        if ($error->getFatal()) {
            die();
        }
    }

    /**
    * Initialize global variable debugbacktrace
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @return array Array of debug back trace native function
    */
    public static function initializeDebugBackTrace(): array
    {
        $debugbacktrace = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'debugbacktrace',
            'insiderFrameworkSystem'
        );

        // In here the framework checks if this piece of code already been executed
        // with some fatal error. If so, they will display a message with the error
        // directly for the user and write a log with the detais.
        if ($debugbacktrace === null) {
            $debugbacktrace = debug_backtrace();
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'debugbacktrace' => $debugbacktrace
                ),
                'insiderFrameworkSystem'
            );
        }
            
        return $debugbacktrace;
    }

    /**
    * Get relative execution php script path
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @return string Relative execution path
    */
    public static function getRelativeScriptPath(): string
    {
        $relativePath = "";
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to recover installation directory when trigger error"
            );
        }

        try {
            $relativePath = implode(
                DIRECTORY_SEPARATOR,
                array_slice($path, 0, count($path) - 3)
            );
        } catch (\Exception $e) {
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to translate the relative installation directory when triggering error"
            );
        }

        return $relativePath;
    }

    /**
    * Send a message to user
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
    *
    * @return void
    */
    public static function sendMessageToUser(\Modules\InsiderFramework\Core\Error\ErrorMessage $error): void
    {
        $defaultMsg = 'Oops, something is wrong with this URL. See the error_log for details';
        $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'registeredErrors',
            'insiderFrameworkSystem'
        );

        if (!isset($registeredErrors['messageToUser']) || !in_array($defaultMsg, $registeredErrors['messageToUser'])) {
            $registeredErrors['messageToUser'][] = $defaultMsg;
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'registeredErrors' => $registeredErrors
                ),
                'insiderFrameworkSystem'
            );
        }

        // Getting the send mail policy
        $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'contentConfig',
            'insiderFrameworkSystem'
        );

        if ($contentConfig === null) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::getFrameworkDebugStatus();
            $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                'contentConfig',
                'insiderFrameworkSystem'
            );
        }

        if (!property_exists($contentConfig, 'ERROR_MAIL_SENDING_POLICY')) {
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Unable to read email sending policy when trigger error"
            );
        }

        switch (strtolower(trim($contentConfig->ERROR_MAIL_SENDING_POLICY))) {
            case "debug-off-only":
                // Sending the e-mail
                // If cannot be able to send the e-mail
                if (
                    !(\Modules\InsiderFramework\Core\Manipulation\Mail::sendMail(
                        MAILBOX,
                        MAILBOX,
                        MAILBOX_PASS,
                        $error->getSubject(),
                        $error->getText(),
                        $error->getText(),
                        MAILBOX_SMTP,
                        MAILBOX_SMTP_PORT,
                        MAILBOX_SMTP_AUTH,
                        MAILBOX_SMTP_SECURE
                    ))
                ) {
                    \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
                    // Record a message in the web server log
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                        "Unable to send an error message via email to 
                        the default mailbox when triggering an error!"
                    );
                }
                break;

            case "never":
                break;

            default:
                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
                $msg = 'Email sending policy \'' .
                        $contentConfig->ERROR_MAIL_SENDING_POLICY .
                        '\' not identified when trigger error';

                error_log($msg);
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($msg);
                break;
        }

        // Displaying the default error message
        $errorController = new \Apps\Sys\Controllers\ErrorController();
        switch ($error->getType()) {
            case 'ATTACK_DETECTED':
                $errorController->attackError();
                break;
            default:
                $errorController->genericError();
                break;
        }
    }

    /**
    * Send a message to admin
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Error\ErrorHandler
    *
    * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
    *
    * @return void
    */
    public static function sendMessageToAdmin(\Modules\InsiderFramework\Core\Error\ErrorMessage $error): void
    {
        // Handling the error path (to display the relative path)
        $relativePath = ErrorHandler::getRelativeScriptPath();
        
        // Data of error (for admin)
        $msgToAdmin = array(
            'jsonMessage' => \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($error),
            'subject' => $error->getSubject(),
            'errfile' => str_replace($relativePath, "", $error->getFile()),
            'errline' => $error->getLine(),
            'msgError' => str_replace($relativePath, "", $error->getMessageOrText())
        );

        $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'registeredErrors',
            'insiderFrameworkSystem'
        );

        // Recording the error in the kernelspace (to be accessed by the View)
        if (
            !isset($registeredErrors['messagesToAdmin']) ||
            !array_key_exists(
                $msgToAdmin['jsonMessage'],
                $registeredErrors['messagesToAdmin']
            )
        ) {
            $registeredErrors['messagesToAdmin'][$msgToAdmin['jsonMessage']] = $msgToAdmin;
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'registeredErrors' => $registeredErrors
                ),
                'insiderFrameworkSystem'
            );
        }

        $responseFormat = \Modules\InsiderFramework\Core\Response::getCurrentResponseFormat();
        switch ($responseFormat) {
            case 'XML':
                $xml = new \SimpleXMLElement('<error/>');
                unset($msgToAdmin['jsonMessage']);

                // Flipping the key and values of the array
                $msgToAdmin = array_flip($msgToAdmin);
                array_walk_recursive($msgToAdmin, array($xml, 'addChild'));
                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

                // All the XML errors must be displayed alone
                // (without any interferences). Otherwise the XML
                // will not be a valid one.
                exit($xml->asXML());
                break;

            case 'JSON':
                $msgError = array(
                    'error' => json_decode($msgToAdmin['jsonMessage'])
                );

                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

                // All the JSON errors must be displayed alone
                // (without any interferences). Otherwise the JSON
                // will not be a valid one.
                exit(json_encode($msgError));
                break;

            default:
                // Recovering the admin message to be handled
                $errorController = new \Apps\Sys\Controllers\ErrorController();

                $errorController->adminMessageError();
                break;
        }
    }

    /**
     * Function that manage an error
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param \Modules\InsiderFramework\Core\Error\ErrorMessage $error Object with error information
     *
     * @return void
     */
    public static function manageError(\Modules\InsiderFramework\Core\Error\ErrorMessage $error): void
    {
        ErrorHandler::validateMaxErrorNumberAndRegisterInKernelSpace($error);

        $requestSource = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'requestSource',
            'insiderFrameworkSystem'
        );

        switch ($requestSource) {
            case "console":
                ErrorHandler::manageErrorConsoleRequest($error);
                return;
            break;
            case "phpunit":
                ErrorHandler::manageErrorTestRequest($error);
                return;
            break;
        }

        error_log(\Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($error), 0);

        $responseFormat = \Modules\InsiderFramework\Core\Response::getCurrentResponseFormat();

        $fatal = $error->getFatal();

        if (!$fatal && $responseFormat === "HTML") {
            return;
        }

        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

        $debugbacktrace = ErrorHandler::initializeDebugBackTrace();

        // If DEBUG is not defined, it's some error inside the framework
        if (DEBUG === null) {
            define('DEBUG', \Modules\InsiderFramework\Core\Error\ErrorHandler::getFrameworkDebugStatus());
        }

        if (DEBUG) {
            ErrorHandler::sendMessageToAdmin($error);
        } else {
            ErrorHandler::sendMessageToUser($error);
        }

        // Killing the processing if it's a fatal error
        if ((isset($fatal) && $fatal === true) || $error->getFatal() === true) {
            exit();
        }
    }

    /**
     * When an file of a class cannot be found, this method is used to fire a exception
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $file        Name of the file of the class
     * @param string $soughtclass Class name how is requested the file
     * @param string $namespace   Namespace
     *
     * @return void
     */
    public static function classFileNotFound(string $file, string $soughtclass, string $namespace = null): void
    {
        if ($namespace !== null) {
            $text = "'" . $file . "' of class '" . $soughtclass .
                "' that belongs to the namespace '" . $namespace . "'";
        } else {
            $text = "'" . $file . "' of class '" . $soughtclass .
                "' (without declared namespace)";
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);

        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
            "The file " . $text . " was not found ! Details: " . json_encode($backtrace)
        );
    }
}
