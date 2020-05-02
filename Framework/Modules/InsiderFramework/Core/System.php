<?php

// Activate strict types
declare(strict_types=1);

namespace Modules\InsiderFramework\Core;

/**
 * Class for the system functions
 *
 * @author Marcello Costa
 * 
 * @package Modules\InsiderFramework\Core\System
 */
class System
{
    /**
    * Initializes framework variables and enviroment classes
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\System
    *
    * @return void
    */
    public static function initializeFramework(): void
    {
        // Activates the output buffer. This is necessary in case of internal errors.
        // So the framework will not let you return something that breaks the return JSON
        // of the error.
        ob_start();
        \Modules\InsiderFramework\Core\System::setEnviromentExecutionDirectoryAndConstraints();

        $autoloaderDirectory = 'Framework' . DIRECTORY_SEPARATOR .
                                'Modules' .  DIRECTORY_SEPARATOR .
                                'InsiderFramework' . DIRECTORY_SEPARATOR .
                                'Core' . DIRECTORY_SEPARATOR .
                                'Loaders';
        require_once(
            $autoloaderDirectory .
            DIRECTORY_SEPARATOR .
            'AutoLoader.php'
        );

        \Modules\InsiderFramework\Core\Loaders\AutoLoader::initializeAutoLoader();

        \Modules\InsiderFramework\Core\Loaders\ConfigLoader::loadFrameworkConfigVariables();
        
        \Modules\InsiderFramework\Core\Loaders\ModuleLoader::loadModulesFromJsonConfigFile();

        \Modules\InsiderFramework\Core\Error\ErrorMonitor::initialize();
        
        \Modules\InsiderFramework\Core\RoutingSystem\Bootstrap::initialize();

        // Modifying charset
        header('Content-type: text/html; charset=' . ENCODE);
        
        // Initializing global object variables in each
        // page. Leave them blank.
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'injectedHtml' => '',
                'injectedScripts' => '',
                'injectedCss' => '',
            ),
            'insiderFrameworkSystem'
        );

        // If the debug bar is active, start the "counter"
        if (DEBUG_BAR == true) {
            // Initializing debug timer
            $timer = new \Modules\InsiderFramework\Core\Debug();
            $timer->debugBar("count");
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
                'timer' => $timer
            ), 'insiderFrameworkSystem');
            unset($timer);
        }

        // Setting global POST and GET variables
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
            'POST' => \Modules\InsiderFramework\Core\Request::getPost(),
            'GET' => \Modules\InsiderFramework\Core\Request::getGet(),
            'SERVER' => \Modules\InsiderFramework\Core\Request::getRequest("SERVER")
        ), 'insiderFrameworkSystem');

        // UserAgent
        $session = \Modules\InsiderFramework\Core\Request::getRequest('session');
        if (isset($session['HTTP_USER_AGENT'])) {
            $UserAgent = $session['HTTP_USER_AGENT'];
        } else {
            $UserAgent = null;
        }
        unset($session);
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'UserAgent' => $UserAgent
            ),
            'insiderFrameworkSystem'
        );
        unset($UserAgent);

        // Starting session
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }

        // Creates the getallheaders function if it does not exist (if it`s running on
        // the terminal, for example)
        if (!function_exists('getallheaders')) {
            /**
             * Find all values that are in the global variable $_SERVER
             *
             * @author Marcello Costa
             *
             * @package Modules\InsiderFramework\Core\System
             *
             * @return array Array of headers
             */
            function getallheaders(): array
            {
                $headers = [];
                foreach ($_SERVER as $name => $value) {
                    if (substr($name, 0, 5) == 'HTTP_') {
                        $headers[str_replace(' ', '-', ucwords(strtolower(
                            str_replace('_', ' ', substr($name, 5))
                        )))] = $value;
                    }
                }
                return $headers;
            }
        }
        $headersRequest = getallheaders();
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'headersRequest' => $headersRequest
            ),
            'insiderFrameworkSystem'
        );

        // Flag that marks whether it is a request via token
        // If the consoleRequest does not exist (since it can come from the update console)
        if (!isset($consoleRequest)) {
            $consoleRequest = false;
        }

        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);
        if (isset($backtrace[1])) {
            if (isset($backtrace[1]['file']) && basename($backtrace[1]['file']) === 'console.php') {
                $consoleRequest = true;
            }
        }

        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'consoleRequest' => $consoleRequest
            ),
            'insiderFrameworkSystem'
        );

        unset($headersRequest);

        // Difficulty slightly "Session Hijacking"
        // If the User Agent exists
        if (array_key_exists('HTTP_USER_AGENT', \Modules\InsiderFramework\Core\Request::getRequest('session'))) {
            // If it is not a request for api
            if (!$consoleRequest) {
                // If the User-Agent header has changed and IE is not being used
                if (
                    (
                        \Modules\InsiderFramework\Core\Request::getRequest('session')['HTTP_USER_AGENT'] !=
                        md5(\Modules\InsiderFramework\Core\Request::getRequest('SERVER')['HTTP_USER_AGENT'])
                    )
                    &&
                    (
                        (strpos(
                            'msie',
                            strtolower(\Modules\InsiderFramework\Core\Request::getRequest('SERVER')['HTTP_USER_AGENT'])
                        ) === false)
                        &&
                        (strpos(
                            'trident',
                            strtolower(\Modules\InsiderFramework\Core\Request::getRequest('SERVER')['HTTP_USER_AGENT'])
                        ) === false)
                    )
                ) {
                    // Invalid access. The User-Agent header changed during the same session.
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "Invalid access. The User-Agent header changed during the same session",
                        "app/sys",
                        "ATTACK_DETECTED"
                    );
                }
            }
        } else {
            // If it is not a special request
            if (!$consoleRequest) {
                // Retrieving the requisition data
                $server = \Modules\InsiderFramework\Core\Request::getRequest('SERVER');

                // If the user agent exists
                if (isset($server['HTTP_USER_AGENT'])) {
                    // First user access, we will write in the session a md5 hash of the User-Agent header
                    \Modules\InsiderFramework\Core\Request::getRequest('session')['HTTP_USER_AGENT'] =
                    md5(\Modules\InsiderFramework\Core\Request::getRequest('SERVER')['HTTP_USER_AGENT']);
                } else {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        "There is no UserAgent in the requisition",
                        "app/sys",
                        "ATTACK_DETECTED"
                    );
                }
                unset($server);
            }
        }

        // Retrieving request data
        $server = \Modules\InsiderFramework\Core\Request::getRequest('SERVER');
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('SERVER' => $server), 'insiderFrameworkSystem');

        // If it is a special request
        if ($consoleRequest) {
            // If the URL contains the user's idsession cookie
            if (isset($server['QUERY_STRING']) && (strpos($server['QUERY_STRING'], 'cookieframeidsession') !== false)) {
                // Getting the value of the cookie
                preg_match("/cookieframeidsession=([^&]*)/", $server['QUERY_STRING'], $matches);

                // Setting the cookie value manually
                \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie("idsession", $matches[1]);
            }
        }
        unset($server);
        unset($consoleRequest);

        // Initializes global status of fatal error
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('fatalError' => false), 'insiderFrameworkSystem');

        // Initializes global variable of errors
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('registeredErrors' => []));

        // Initializes global return format value
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'responseFormat' => DEFAULT_RESPONSE_FORMAT
            ),
            'insiderFrameworkSystem'
        );
    }

    /**
    * Set the right execution directory for framework
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\System
    *
    * @return void
    */
    public static function setEnviromentExecutionDirectoryAndConstraints(): void
    {
        chdir('..' . DIRECTORY_SEPARATOR);

        // Framework installation directory (APP_ROOT and INSTALL_DIR)
        define('INSTALL_DIR', getcwd());
        define('APP_ROOT', INSTALL_DIR);
    }

    /**
     * Checks if the cpu load monitor is enable
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\System
     *
     * @return void
     */
    public static function checkCpuUsage(): void
    {
        $loadAVG = \Modules\InsiderFramework\Core\KernelSpace::getVariable('loadAVG', 'insiderFrameworkSystem');

        if ($loadAVG["max_use"] > 0) {
            $load = sys_getloadavg();

            switch ($loadAVG["time"]) {
                case 1:
                    $loadAVG["timefunc"] = 0;
                    break;

                case 5:
                    $loadAVG["timefunc"] = 1;
                    break;

                case 15:
                    $loadAVG["timefunc"] = 2;
                    break;

                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'Invalid load_avg check time: %' . $loadAVG["time"] . '%',
                        "app/sys"
                    );
                    break;
            }
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('loadAVG' => $loadAVG), 'insiderFrameworkSystem');

            if ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                if ($loadAVG['send_email'] == true) {
                    if (
                        !(\Modules\InsiderFramework\Core\Manipulation\Mail::sendMail(
                            MAILBOX,
                            MAILBOX,
                            MAILBOX_PASS,
                            "Load AVG - InsiderFramework",
                            "CPU usage alarm - " . REQUESTED_URL,
                            "CPU usage alarm - " . REQUESTED_URL . " - " . implode(",", $load),
                            MAILBOX_SMTP,
                            MAILBOX_SMTP_PORT,
                            MAILBOX_SMTP_AUTH,
                            MAILBOX_SMTP_SECURE
                        ))
                    ) {
                        error_log("It was not possible to send an error message via email to the default mailbox!", 0);
                    }
                }

                if (strpos($loadAVG['action'], 'throttle') !== false) {
                    $throttle = explode('-', $loadAVG['action']);
                    if (count($throttle) <= 1 || intval($throttle[1]) === 0) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "Invalid time interval in LOAD_AVG_ACTION setting for throttle",
                            "app/sys"
                        );
                    }
                    $loadAVG['action'] = 'throttle';
                    \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('loadAVG' => $loadAVG), 'insiderFrameworkSystem');
                }

                switch (strtolower(trim($loadAVG['action']))) {
                    case 'throttle':
                        $throttleTime = intval($throttle[1]);
                        while ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                            // Waiting
                            usleep($throttleTime);

                            // Getting the system load
                            $load = sys_getloadavg();
                        }
                        break;

                    case 'block-screen':
                        $KcRoute = new \Modules\InsiderFramework\Core\Route();
                        $KcRoute->RequestRoute("/error/loadAvg");
                        die();
                        break;

                    case 'deny':
                        die();
                        break;

                    default:
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "Invalid action '%" . $loadAVG['action'] . "%' in the LOAD_AVG_ACTION setting",
                            "app/sys"
                        );
                        break;
                }
            }
        }
    }
}