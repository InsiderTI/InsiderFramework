<?php

// Activate strict types
declare(strict_types=1);

namespace Modules\InsiderFramework\Core;

/**
 * Class for the framework bootstrap functions
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Bootstrap
 */
class Bootstrap
{
    /**
    * Set the right execution directory for framework
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
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
    * Require the autoload class file
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    protected static function requireAutoLoaderClass(): void
    {
        // Activates the output buffer. This is necessary in case of internal errors.
        // So the framework will not let you return something that breaks the return JSON
        // of the error.
        ob_start();
        \Modules\InsiderFramework\Core\Bootstrap::setEnviromentExecutionDirectoryAndConstraints();

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
    }

    /**
    * Require the debug bar counter
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initializeDebugBarCounter(): void
    {
        // If the debug bar is active, start the "counter"
        if (DEBUG_BAR == true) {
            // Initializing debug timer
            $timer = new \Modules\InsiderFramework\Core\Debug();
            $timer->debugBar("startCount");
            unset($timer);
        }
    }

    /**
    * Initialize global object page variables
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initializeGlobalObjectPageVariables(): void
    {
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
    }

    /**
    * Initialize HTTP global verbs variables
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initalizeGlobalHttpVerbsVariables(): void
    {
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array(
            'POST' => \Modules\InsiderFramework\Core\Request::getPost(),
            'GET' => \Modules\InsiderFramework\Core\Request::getGet(),
            'PUT' => \Modules\InsiderFramework\Core\Request::getPut(),
            'DELETE' => \Modules\InsiderFramework\Core\Request::getDelete(),
            'SERVER' => \Modules\InsiderFramework\Core\Request::getRequest("SERVER")
        ), 'insiderFrameworkSystem');
    }

    /**
    * Initialize UserAgent variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initalizeUserAgentVariable(): void
    {
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
    }

    /**
    * Initialize HeadeRequest variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initializeHeaderRequestVariable(): void
    {
        // Modifying charset
        header('Content-type: text/html; charset=' . ENCODE);
        // Creates the getallheaders function if it does not exist (if it`s running on
        // the terminal, for example)
        if (!function_exists('getallheaders')) {
            /**
             * Find all values that are in the global variable $_SERVER
             *
             * @author Marcello Costa
             *
             * @package Modules\InsiderFramework\Core\Bootstrap
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
    }

    /**
    * Initialize requestSource variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private static function initializeRequestSourceVariable(): void
    {
        // Flag that marks whether it is a request via token
        $backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS);
        $originFile = basename(end($backtrace)['file']);

        switch ($originFile) {
            case 'index.php':
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'requestSource' => 'http'
                    ),
                    'insiderFrameworkSystem'
                );
                break;

            case 'console.php':
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'requestSource' => 'console'
                    ),
                    'insiderFrameworkSystem'
                );
                break;

            case 'phpunit':
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'requestSource' => 'phpunit'
                    ),
                    'insiderFrameworkSystem'
                );
                break;

            default:
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'requestSource' => 'unknown'
                    ),
                    'insiderFrameworkSystem'
                );
                break;
        }
    }

    /**
    * Prevents session hijacking attack
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private function sessionHijackingProtection(): void
    {
        $requestSource = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'requestSource',
            'insiderFrameworkSystem'
        );

        if ($requestSource === 'http') {
            // Difficulty slightly "Session Hijacking"
            // If the User Agent exists
            if (array_key_exists('HTTP_USER_AGENT', \Modules\InsiderFramework\Core\Request::getRequest('session'))) {
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
            } else {
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
    }

    /**
    * Start PHP session
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private function startPhpSession(): void
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
    }

    /**
    * Initialize Server global variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private function initializeGlobalServerVariable(): void
    {
        // Retrieving request data
        $server = \Modules\InsiderFramework\Core\Request::getRequest('SERVER');
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('SERVER' => $server), 'insiderFrameworkSystem');
    }

    /**
    * Initialize errors global variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private function initializeErrorsVariables(): void
    {
        // Initializes global status of fatal error
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'fatalError' => false
            ),
            'insiderFrameworkSystem'
        );

        // Initializes global variable of errors
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'registeredErrors' => []
            )
        );
    }

    /**
    * Initialize response format global variable
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    private function initializeResponseFormatVariable(): void
    {
         // Initializes global return format value
         $responseFormat = \Modules\InsiderFramework\Core\Response::setCurrentResponseFormat(DEFAULT_RESPONSE_FORMAT);
    }

    /**
    * Initializes framework variables and enviroment classes
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Bootstrap
    *
    * @return void
    */
    public static function initializeFramework(): void
    {
        Bootstrap::requireAutoLoaderClass();

        \Modules\InsiderFramework\Core\Loaders\AutoLoader::initializeAutoLoader();

        \Modules\InsiderFramework\Core\Loaders\ConfigLoader::initializeConfigVariablesFromConfigFiles();
        
        \Modules\InsiderFramework\Core\Loaders\ModuleLoader::loadModulesFromJsonConfigFile();

        \Modules\InsiderFramework\Core\Error\ErrorMonitor::initialize();

        Bootstrap::initializeDebugBarCounter();

        Bootstrap::initializeGlobalObjectPageVariables();
        
        Bootstrap::initalizeGlobalHttpVerbsVariables();

        Bootstrap::initalizeUserAgentVariable();

        \Modules\InsiderFramework\Core\RoutingSystem\Bootstrap::initialize();

        Bootstrap::initializeHeaderRequestVariable();

        Bootstrap::startPhpSession();

        Bootstrap::initializeRequestSourceVariable();

        Bootstrap::sessionHijackingProtection();

        Bootstrap::initializeGlobalServerVariable();

        Bootstrap::initializeErrorsVariables();

        Bootstrap::initializeResponseFormatVariable();
    }
}
