<?php

namespace Modules\InsiderFramework\Core\Error;

/**
 * KeyClass that contains functions for handling errors
 *
 * @package Modules\InsiderFramework\Core\Error\ErrorHandler
 *
 * @author Marcello Costa
 */
class ErrorHandler
{
    /**
     * Function that allows you to trigger an error directly to the user
     *
     * @author Marcello Costa
     * 
     * @package Modules\InsiderFramework\Core\Error\ErrorHandler
     *
     * @param string $msg       Error message
     * @param int    $errorCode Error code
     *
     * @return array Returns the result
     */
    public static function primaryError(string $msg, int $errorCode = 500): array
    {
        /*
        if ($kernelspace === null) {
            error_log($msg);
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            http_response_code($errorCode);
            $msgToUser = [];
            $msgToUser['error'] = $msg;
            $output = json_encode($msgToUser);
            die($output);
        }
        */
        $consoleRequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable('consoleRequest', 'insiderFrameworkSystem');

        // If it's an error inside a terminal (console)
        if ($consoleRequest) {
            die();
        }

        // Writing to the log
        error_log($msg);

        // Clear and restart the buffer
        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

        http_response_code($errorCode);

        // JSON Output
        $msgToUser = [];
        $msgToUser['error'] = $msg;
        $output = json_encode($msgToUser);

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
            __METHOD__."() must be an instance of " .
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
        $responseCode = null
    ): void {
        $msgI10n = \Modules\InsiderFramework\Core\Manipulation\I10n::getTranslate($message, $domain, $linguas);

        if ($msgI10n === "") {
            $msgI10n = str_replace("%", "", $message);
        }

        \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister($msgI10n, $type, $responseCode);
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

        switch (strtoupper(trim($type))) {
                // This type of error just writes in the log file
            case "LOG":
                // Generates and unique ID for this error
                $id = uniqid();

                // Creates a new log in case of the file already exists
                $logfilepath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                               "Framework" . DIRECTORY_SEPARATOR .
                               "cache" . DIRECTORY_SEPARATOR .
                               "logs" . DIRECTORY_SEPARATOR .
                               "logfile-" . $id;

                while (file_exists($logfilepath . ".lock")) {
                    $id = uniqid();
                    $logfilepath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                                   "Framework" . DIRECTORY_SEPARATOR .
                                   "cache" . DIRECTORY_SEPARATOR .
                                   "logs" . DIRECTORY_SEPARATOR .
                                   "logfile-" . $id;
                }

                // Inserts and prefix in the message (for now, hard-coded)
                $date = new \DateTime('NOW');
                $dataFormat = $date->format('Y-m-d H:i:s');
                $message = $dataFormat . "    " . $message;

                // Writing in the log file
                \Modules\InsiderFramework\Core\FileTree::fileWriteContent($logfilepath, $message);

                // Returning the error ID
                return $id;
                break;

            // Ataque to the system
            case "ATTACK_DETECTED":
                if ($responseCode === null) {
                    $responseCode = 405;
                }

                // HTTP 405 response
                http_response_code($responseCode);

                // Name of identify cookie
                $cookie1Name = md5('user_identify_cookie_insider');

                // IDSession cookie
                $cookie2Name = htmlspecialchars("idsession");

                // Getting the cookies values
                $cookie1Value = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookie1Name);
                $cookie2Value = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie($cookie2Name);

                // Building the message
                $message .= '- Cookies: (1)' . $cookie1Value . ' (2)' . $cookie2Value;

                // Building the error array
                $error = new \Modules\InsiderFramework\Core\Error\ErrorMessage(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => 'Attack Error - Report Agent InsiderFramework'
                ));

                // Setting the global variable
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('fatalError' => true), 'insiderFrameworkSystem');


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
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to convert error to XML when triggering error");
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
                    'subject' => 'Critical Error - Report Agent InsiderFramework'
                ));

                // Setting the global variable
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('fatalError' => true), 'insiderFrameworkSystem');

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
        $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable('contentConfig', 'insiderFrameworkSystem');

        // If the DEBUG it's not defined, the environment variables must be manually loaded
        // The path will not be mapped correctly with getcwd(), so the constante __DIR__
        // is recovery and treated accordingly
        $path = __DIR__;

        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to recover installation directory when trigger error");
        }

        try {
            $path = implode(DIRECTORY_SEPARATOR, array_slice($path, 0, count($path) - 3));
        } catch (\Exception $e) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to rebuild installation directory when trigger error");
        }

        $coreEnvFile = INSTALL_DIR . DIRECTORY_SEPARATOR .
                       'Framework' . DIRECTORY_SEPARATOR .
                       'config' . DIRECTORY_SEPARATOR .
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
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to set DEBUG state when trigger error");
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
        $consoleRequest = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'consoleRequest',
            'insiderFrameworkSystem'
        );

        // Registered errors
        $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable('registeredErrors', 'insiderFrameworkSystem');
        if (!is_array($registeredErrors)) {
            $registeredErrors = [];
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('registeredErrors' => $registeredErrors));
        }

        // The first thing to be done it's write the error in the web server log
        if (!$consoleRequest){
            error_log(\Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($error), 0);
        }

        $responseFormat = \Modules\InsiderFramework\Core\KernelSpace::getVariable('responseFormat', 'insiderFrameworkSystem');
        if ($responseFormat === "") {
            $responseFormat = DEFAULT_RESPONSE_FORMAT;
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('responseFormat' => $responseFormat), 'insiderFrameworkSystem');
        }

        // The first part it's displayed if the processing has not successful in the next lines
        \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

        // Writing the default message to the user
        $defaultMsg = 'Oops, something is wrong with this URL. See the error_log for details';
        if (!isset($registeredErrors['messageToUser']) || !in_array($defaultMsg, $registeredErrors['messageToUser'])) {
            $registeredErrors['messageToUser'][] = $defaultMsg;
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }

        // Recovering the fatal error variable
        $fatal = \Modules\InsiderFramework\Core\KernelSpace::getVariable('fatalError', 'insiderFrameworkSystem');

        // Recovering the error counter
        $errorCount = \Modules\InsiderFramework\Core\KernelSpace::getVariable('errorCount', 'insiderFrameworkSystem');

        $debugbacktrace = \Modules\InsiderFramework\Core\KernelSpace::getVariable('debugbacktrace', 'insiderFrameworkSystem');

        // In here the framework checks if this piece of code already been executed
        // with some fatal error. If so, they will display a message with the error
        // directly for the user and write a log with the detais.
        if ($debugbacktrace === null) {
            $debugbacktrace = debug_backtrace();
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('debugbacktrace' => $debugbacktrace), 'insiderFrameworkSystem');
        }

        // If there is not an error
        if ($errorCount === null) {
            $errorCount = 0;
        } else {
            $errorCount++;
        }
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('errorCount' => $errorCount), 'insiderFrameworkSystem');

        // If more than 10 errors as mappeded
        if ($errorCount > 10) {
            $finalErrorMsg = "Max log errors on framework";

            // Setting the reponse code as 500
            http_response_code(500);

            // Writing the error details in the log
            error_log(json_encode($debugbacktrace));

            
            // If the debug it's not enable
            if (!DEBUG) {
                if (is_array($debugbacktrace)){
                    $debugbacktrace = json_encode($debugbacktrace);
                }
                // Stopping the execution with a default message
                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($finalErrorMsg);
            } else {
                // Stopping the execution and displaying the error object
                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();

                echo '<< DEBUG ERROR DISPLAY >>';
                \Modules\InsiderFramework\Core\Manipulation\Development::PrintDump($debugbacktrace);
                die("FILE: " . __FILE__ . "<br/>LINE: ". __LINE__);
            }
        }

        // Handling the error path (to display the relative path)
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to recover installation directory when trigger error");
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

        // If DEBUG is not defined, is some error inside the framework
        if (DEBUG === null) {
            define('DEBUG', \Modules\InsiderFramework\Core\Error\ErrorHandler::getFrameworkDebugStatus());
        }
        
        // Data of error (for admin)
        $msgToAdmin = array(
            'jsonMessage' => \Modules\InsiderFramework\Core\Json::jsonEncodePrivateObject($error),
            'errfile' => str_replace($relativePath, "", $error->getFile()),
            'errline' => $error->getLine(),
            'msgError' => str_replace($relativePath, "", $error->getMessageOrText())
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
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }

        // If the DEBUG it's enable
        if (DEBUG) {
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
                    \Modules\InsiderFramework\Core\FileTree::requireOnceFile(
                        INSTALL_DIR . DIRECTORY_SEPARATOR .
                        'Apps' . DIRECTORY_SEPARATOR .
                        'sys' . DIRECTORY_SEPARATOR .
                        'controllers' . DIRECTORY_SEPARATOR .
                        'error_controller.php'
                    );
                    $C = new \Controllers\sys\ErrorController('\\Controllers\\sys\\sys', null, false);

                    $registeredErrors = \Modules\InsiderFramework\Core\KernelSpace::getVariable('registeredErrors', 'insiderFrameworkSystem');
                    $C->adminMessageError();
                    break;
            }
        } else {
            // Getting the send mail policy
            $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable('contentConfig', 'insiderFrameworkSystem');
            if ($contentConfig === null) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::getFrameworkDebugStatus();
                $contentConfig = \Modules\InsiderFramework\Core\KernelSpace::getVariable('contentConfig', 'insiderFrameworkSystem');
            }

            if (!property_exists($contentConfig, 'ERROR_MAIL_SENDING_POLICY')) {
                \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("Unable to read email sending policy when trigger error");
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
            \Modules\InsiderFramework\Core\FileTree::requireOnceFile(
                INSTALL_DIR . DIRECTORY_SEPARATOR . 'Apps' . DIRECTORY_SEPARATOR . 'sys' .
                    DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'error_controller.php'
            );
            $C = new \Controllers\sys\ErrorController('\\Controllers\\sys\\sys', null, false);
            \Modules\InsiderFramework\Core\Request::clearAndRestartBuffer();
            $C->genericError();
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

        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError("The file " . $text . " was not found ! Details: " . json_encode($backtrace));
    }
}
