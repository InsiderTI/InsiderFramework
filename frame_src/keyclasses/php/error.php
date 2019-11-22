<?php

/**
  KeyClass\Error
 */

namespace KeyClass;

/**
  KeyClass that contains functions for handling errors
 
  @package KeyClass\Error

  @author Marcello Costa
 */
class Error {
    /**
      Function that shows / register translated errors

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $message             Error message
      @param  string  $domain              Domain of error message
      @param  string  $linguas             Language of error message
      @param  string  $type                Type of error
      @param  int     $responseCode        Response code of error

      @return void
     */
    public static function i10nErrorRegister(string $message, string $domain, string $linguas = LINGUAS, string $type = "CRITICAL", $responseCode = null) : void {
        $msgI10n = \KeyClass\I10n::getTranslate($message, $domain, $linguas);
        \KeyClass\Error::errorRegister($msgI10n, $type, $responseCode);
    }

    /**
      Register/Show errors

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $message             Error message
      @param  string  $type                Type of error
      @param  int     $responseCode        Response code of the error

      @return void|string Returns the uniqid of the error if it's of type LOG
     */
    public static function errorRegister(string $message, string $type = "CRITICAL", int $responseCode = null) : ?string {
        global $kernelspace;
        $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3);
        
        $file = $debugbacktrace[0]['file'];
        $line = $debugbacktrace[0]['line'];
        if (isset($debugbacktrace[2])){
            if (isset($debugbacktrace[2]['file']) && isset($debugbacktrace[2]['line'])){
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
                $logfilepath = INSTALL_DIR . "/frame_src/cache/logs/logfile-" . $id;
                while (file_exists($logfilepath . ".lock")) {
                    $logfilepath = INSTALL_DIR . "/frame_src/cache/logs/logfile-" . $id;
                }

                // Inserts and prefix in the message (for now, hard-coded)
                $date = new \DateTime('NOW');
                $dataFormat = $date->format('Y-m-d H:i:s');
                $message = $dataFormat . "    " . $message;

                // Writing in the log file
                \KeyClass\FileTree::fileWriteContent($logfilepath, $message);

                // Returning the error ID
                return $id;
            break;

            // Critical error
            case "CRITICAL":
                if ($responseCode === null) {
                    $responseCode = 500;
                }

                // HTTP 500 response
                http_response_code($responseCode);

                // Populate the error object
                $manageErrorMsg = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => 'Critical Error - Report Agent InsiderFramework'
                ));

                // Setting the global variable
                $kernelspace->setVariable(array('fatalError' => true), 'insiderFrameworkSystem');

                // Managing the error
                \KeyClass\Error::manageError($manageErrorMsg);
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
                $cookie1Value = \KeyClass\Security::getCookie($cookie1Name);
                $cookie2Value = \KeyClass\Security::getCookie($cookie2Name);

                // Building the message
                $message .= '- Cookies: (1)' . $cookie1Value . ' (2)' . $cookie2Value;

                // Building the error array
                $error = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => true,
                    'subject' => 'Attack Error - Report Agent InsiderFramework'
                ));

                // Setting the global variable
                $kernelspace->setVariable(array('fatalError' => true), 'insiderFrameworkSystem');


                // Managing the error
                \KeyClass\Error::manageError($error);
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

                echo \KeyClass\JSON::jsonEncodePrivateObject($error);
                exit();
            break;

            // This is the kind of error which will return an XML for
            // the user (usefull in ajax requests, for example)
            case 'XML_PRE_CONDITION_FAILED':
                if ($responseCode === null) {
                    $responseCode = 412;
                }

                http_response_code($responseCode);

                $error = array(
                    'error' => $message
                );

                if (!\KeyClass\XML::isXML($error)) {
                    $xmlObj = "";
                    $message = \KeyClass\XML::arrayToXML($error, $xmlObj);
                    if ($message === false) {
                        primaryError("Unable to convert error to XML when triggering error");
                    }
                    echo $message;
                }
                exit();
            break;

            // Standard error
            default:
                if ($responseCode !== null) {
                    http_response_code($responseCode);
                }

                // Building the error array
                $error = new \Modules\insiderErrorHandler\manageErrorMsg(array(
                    'type' => $type,
                    'text' => $message,
                    'file' => $file,
                    'line' => $line,
                    'fatal' => false,
                    'subject' => 'Standard Error - Report Agent InsiderFramework'
                ));

                // Setting the global variable
                $kernelspace->setVariable(array('fatalError' => false), 'insiderFrameworkSystem');

                // Managing the error
                \KeyClass\Error::manageError($error);
            break;
        }
    }

    /**
      Use this to get the current state of debug

      @author Marcello Costa

      @package KeyClass\Error

      @return bool Current state of debug
     */
    public static function getFrameworkDebugStatus() : bool {
        global $kernelspace;

        $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
        
        // If the DEBUG it's not defined, the environment variables must be manually loaded
        // The path will not be mapped correctly with getcwd(), so the constante __DIR__
        // is recovery and treated accordingly
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            primaryError("Unable to recover installation directory when trigger error");
        }

        try {
            $path = implode(array_slice($path, 0, count($path) - 3), DIRECTORY_SEPARATOR);
        } catch (\Exception $e) {
            primaryError("Unable to rebuild installation directory when trigger error");
        }

        $coreEnvFile = $path . DIRECTORY_SEPARATOR . 'frame_src' . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'core.json';

        if (!file_exists($coreEnvFile)) {
            primaryError("Env file does not exist when triggering error");
        }
        $contentConfig = json_decode(file_get_contents($coreEnvFile));
        if ($contentConfig === null) {
            primaryError("Specific core file does not contain environment information when triggering error");
        }
        if (!property_exists($contentConfig, 'DEBUG')) {
            primaryError("Unable to set DEBUG state when trigger error");
        }
        $kernelspace->setVariable(array('contentConfig' => $contentConfig), 'insiderFrameworkSystem');
        
        // Setting the debug manually to a variable
        $debugNow = $contentConfig->DEBUG;

        return $debugNow;
    }

    /**
      Function that manage an error

      @author Marcello Costa

      @package KeyClass\Error

      @param  \Modules\insiderErrorHandler\manageErrorMsg  $error    Object with error information

      @return void
     */
    public static function manageError(\Modules\insiderErrorHandler\manageErrorMsg $error) : void {
        // Initializing the error variable (if not exists yet)
        global $kernelspace;
 
        // Registered errors
        $registeredErrors = $kernelspace->getVariable('registeredErrors', 'insiderFrameworkSystem');
        if (!is_array($registeredErrors))
        {
            $registeredErrors = [];
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors));
        }

        // The first thing to be done it's write the error in the web server log
        error_log(\KeyClass\JSON::jsonEncodePrivateObject($error), 0);

        $responseFormat = $kernelspace->getVariable('responseFormat', 'insiderFrameworkSystem');
        if ($responseFormat === "") {
          $responseFormat = DEFAULT_RESPONSE_FORMAT;
          $kernelspace->setVariable(array('responseFormat' => $responseFormat), 'insiderFrameworkSystem');
        }
        
        // The first part it's displayed if the processing has not successful in the next lines
        clearAndRestartBuffer();
        
        // Writing the default message to the user
        $defaultMsg = 'Oops, something is wrong with this URL. See the error_log for details';
        if (!isset($registeredErrors['messageToUser']) || !in_array($defaultMsg, $registeredErrors['messageToUser'])){
            $registeredErrors['messageToUser'][]=$defaultMsg;
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }

        // Recovering the fatal error variable
        $fatal = $kernelspace->getVariable('fatalError', 'insiderFrameworkSystem');
        
        // Recovering the error counter
        $errorCount = $kernelspace->getVariable('errorCount', 'insiderFrameworkSystem');
        
        $debugbacktrace = $kernelspace->getVariable('debugbacktrace', 'insiderFrameworkSystem');

        // In here the framework checks if this piece of code already been executed
        // with some fatal error. If so, they will display a message with the error
        // directly for the user and write a log with the detais.
        if ($debugbacktrace === null){
            $debugbacktrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 50);
            $kernelspace->setVariable(array('debugbacktrace' => $debugbacktrace), 'insiderFrameworkSystem');
        }

        // If there is not an error
        if ($errorCount === null){
            $errorCount = 0;
        }
        else{
            $errorCount++;
        }
        $kernelspace->setVariable(array('errorCount' => $errorCount), 'insiderFrameworkSystem');

        // If more than 10 errors as mappeded
        if ($errorCount > 10){
            $finalErrorMsg = "Max log errors on framework";

            // Setting the reponse code as 500
            http_response_code(500);

            // Writing the error details in the log
            error_log(json_encode($debugbacktrace));

            // If the debug it's not enable
            if (!DEBUG){
                // Stopping the execution with a default message
                clearAndRestartBuffer();
                primaryError($finalErrorMsg);
            }
            // If the debug it's enable
            else{
                // Stopping the execution and displaying the error object
                clearAndRestartBuffer();
                primaryError($finalErrorMsg);
            }
        }

        // Handling the error path (to display the relative path)
        $path = __DIR__;
        $path = explode(DIRECTORY_SEPARATOR, $path);
        if (count($path) === 0) {
            clearAndRestartBuffer();
            primaryError("Unable to recover installation directory when trigger error");
        }

        try {
            $relativePath = implode(array_slice($path, 0, count($path) - 3), DIRECTORY_SEPARATOR);
        } catch (\Exception $e) {
            clearAndRestartBuffer();
            primaryError("Unable to translate the relative installation directory when triggering error");
        }

        // If DEBUG is not defined, is some error inside the framework
        if (DEBUG === null) {
            define('DEBUG', \KeyClass\Error::getFrameworkDebugStatus());
        }

        // Data of error (for admin)
        $msgToAdmin = array(
            'jsonMessage' => \KeyClass\JSON::jsonEncodePrivateObject($error),
            'errfile' => str_replace($relativePath, "", $error->getFile()),
            'errline' => $error->getLine(),
            'msgError' => str_replace($relativePath, "", $error->getMessageOrText())
        );


        // Recording the error in the kernelspace (to be accessed by the View)
        if (!isset($registeredErrors['messagesToAdmin']) || !array_key_exists($msgToAdmin['jsonMessage'], $registeredErrors['messagesToAdmin'])){
            $registeredErrors['messagesToAdmin'][$msgToAdmin['jsonMessage']]=$msgToAdmin;
            $kernelspace->setVariable(array('registeredErrors' => $registeredErrors), 'insiderFrameworkSystem');
        }
        
        // If the DEBUG it's enable
        if (DEBUG){                        
            switch ($responseFormat) {
                case 'XML':
                    $xml = new \SimpleXMLElement('<error/>');
                    unset($msgToAdmin['jsonMessage']);

                    // Flipping the key and values of the array
                    $msgToAdmin=array_flip($msgToAdmin);
                    array_walk_recursive($msgToAdmin, array($xml, 'addChild'));
                    clearAndRestartBuffer();

                    // All the XML errors must be displayed alone
                    // (without any interferences). Otherwise the XML
                    // will not be a valid one.
                    exit($xml->asXML());
                break;

                case 'JSON':
                    $msgError = array(
                        'error' => json_decode($msgToAdmin['jsonMessage'])
                    );
                        
                    clearAndRestartBuffer();

                    // All the JSON errors must be displayed alone
                    // (without any interferences). Otherwise the JSON
                    // will not be a valid one.
                    exit(json_encode($msgError));
                break;

                default:
                    // Recovering the admin message to be handled
                    \KeyClass\FileTree::requireOnceFile(INSTALL_DIR . DIRECTORY_SEPARATOR . 'packs' . DIRECTORY_SEPARATOR . 'sys' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'error_controller.php');
                    $C = new \Controllers\sys\Error_Controller('\\Controllers\\sys\\sys', null, false);
                    
                    $registeredErrors = $kernelspace->getVariable('registeredErrors', 'insiderFrameworkSystem');

                    $C->adminMessageError();
                break;
            }
        }

        // If the DEBUG it's not enable
        else{
            // Getting the send mail policy
            $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
            if ($contentConfig === null){
                \KeyClass\Error::getFrameworkDebugStatus();
                $contentConfig = $kernelspace->getVariable('contentConfig', 'insiderFrameworkSystem');
            }
            
            if (!property_exists($contentConfig, 'ERROR_MAIL_SENDING_POLICY')) {
                clearAndRestartBuffer();
                primaryError("Unable to read email sending policy when trigger error");
            }

            switch (strtolower(trim($contentConfig->ERROR_MAIL_SENDING_POLICY))) {
                case "debug-off-only":
                    // Sending the e-mail
                    // If cannot be able to send the e-mail
                    if (!(\KeyClass\Mail::sendMail(MAILBOX, MAILBOX, MAILBOX_PASS, $error['subject'], $htmlMessageToAdmin, $htmlMessageToAdmin, MAILBOX_SMTP, MAILBOX_SMTP_PORT, MAILBOX_SMTP_AUTH, MAILBOX_SMTP_SECURE))) {
                        clearAndRestartBuffer();
                        // Record a message in the web server log
                        primaryError("Unable to send an error message via email to the default mailbox when triggering an error!");
                    }
                break;

                case "never":
                break;

                default:
                    clearAndRestartBuffer();
                    $msg = 'Email sending policy \'' . $contentConfig->ERROR_MAIL_SENDING_POLICY . '\' not identified when trigger error';
                    error_log($msg);
                    primaryError($msg);
                break;
            }

            // Displaying the default error message
            \KeyClass\FileTree::requireOnceFile(INSTALL_DIR . DIRECTORY_SEPARATOR . 'packs' . DIRECTORY_SEPARATOR . 'sys' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'error_controller.php');
            $C = new \Controllers\sys\Error_Controller('\\Controllers\\sys\\sys', null, false);
            clearAndRestartBuffer();
            $C->genericError();
        }

        // Killing the processing if it's a fatal error
        if ((isset($fatal) && $fatal === true) || $error->getFatal() === true) {
            exit();
        }
    }

    /**
      When a class cannot be found, this method is used to fire a exception

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $class    Name of the class
      @param  string  $file     Name of the file of the class

      @return void  Without return
     */
    public static function classNotFound(string $class, string $file) : void {
        primaryError("Class " . $class . " not found in file " . $file . " !");
    }

    /**
      When an file of a class cannot be found, this method is used to fire a exception

      @author Marcello Costa

      @package KeyClass\Error

      @param  string  $file           Name of the file of the class
      @param  string  $soughtclass    Class name how is requested the file
      @param  string  $namespace      Namespace

      @return void  Without return
     */
    public static function classFileNotFound(string $file, string $soughtclass, string $namespace = null) : void {
        if ($namespace !== null) {
            $text = "'" . $file . "' of class '" . $soughtclass . "' that belongs to the namespace '" . $namespace . "'";
        } else {
            $text = "'" . $file . "' of class '" . $soughtclass . "' (without declared namespace)";
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 5);
        
        primaryError("The file " . $text . " was not found ! Details: ".json_encode($backtrace));
    }
}
