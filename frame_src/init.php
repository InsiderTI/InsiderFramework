<?php
/**
  File that initializes the framework on each request.
  This file contains the logic that initializes the framework settings as well
  as the main classes that are used.
 
  @author Marcello Costa
 */

// Activate strict types
declare(strict_types=1);

// Activates the output buffer. This is necessary in case of internal errors.
// So the framework will not let you return something that breaks the return JSON
// of the error.
ob_start();

// Returning only one directory
chdir('..'.DIRECTORY_SEPARATOR);

/**
    Clear and restart the php buffer

    @author Marcello Costa

    @package Core

    @return  void  Without return
*/
function clearAndRestartBuffer(){
    while (ob_get_level() > 0) {
        ob_end_clean();
    }
    ob_start();
}

/**
    Function that allows you to trigger an error directly to the user
    
    @author Marcello Costa
    @package Core

    @param  string  $msg       Error message
    @param  int     $errorCode Error code
 
    @return  array  Returns the result
*/
function primaryError(string $msg, int $errorCode = 500) : array {
    global $kernelspace;
    if ($kernelspace === null){
        error_log($msg);
        clearAndRestartBuffer();
        http_response_code($errorCode);
        $msgToUser = [];
        $msgToUser['error'] = $msg;
        $output = json_encode($msgToUser);
        die($output);
    }
    
    $consoleRequest = $kernelspace->getVariable('consoleRequest','insiderFrameworkSystem');
    
    // If it's an error inside a terminal (console)
    if ($consoleRequest){
        die();
    }
    
    // Writing to the log
    error_log($msg);

    // Clear and restart the buffer
    clearAndRestartBuffer();
    
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

// Initializes environment variables
require_once('frame_src'.DIRECTORY_SEPARATOR.'config_loader.php');

// Include and init kernelSpace variable
require_once('frame_src'.DIRECTORY_SEPARATOR.'keyclasses'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'kernelspace.php');
global $kernelspace;
$kernelspace = new \KeyClass\KernelSpace();

global $i10n;
if (is_array($i10n)){
    $kernelspace->setVariable(array('i10n' => $i10n), 'insiderFrameworkSystem');
    unset($i10n);
}

// Loading global variables from config to kernelspace
global $injectedCss;
global $loadAVG;
global $databases;
global $packsLoaded;
global $guildsLoaded;
$kernelspace->setVariable(array('guildsLoaded' => $guildsLoaded), 'insiderFrameworkSystem');
$kernelspace->setVariable(array('packsLoaded' => $packsLoaded), 'insiderFrameworkSystem');
$kernelspace->setVariable(array('databases' => $databases), 'insiderFrameworkSystem');
$kernelspace->setVariable(array('injectedCss' => $injectedCss), 'insiderFrameworkSystem');
$kernelspace->setVariable(array('loadAVG' => $loadAVG), 'insiderFrameworkSystem');
unset($injectedCss, $loadAVG, $databases, $packsLoaded, $guildsLoaded);

// Modifying charset
header('Content-type: text/html; charset='.ENCODE);

// Initializing global object variables in each
// page. Leave them blank.
$kernelspace->setVariable(
    array(
        'injectedHtml' => '',
        'injectedScripts' => '',
        'injectedCss' => '',
    ), 'insiderFrameworkSystem'
);

// If the debug bar is active, start the "counter"
if (DEBUG_BAR == true) {
    // Adding the debug class
    require_once('frame_src'.DIRECTORY_SEPARATOR.'keyclasses'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'debug.php');

    // Initializing debug timer
    $timer = new KeyClass\Debug();
    $timer->debugBar("count");
    $kernelspace->setVariable(array(
       'timer' => $timer
    ), 'insiderFrameworkSystem');
    unset($timer);
}

// Includes classes that are not found
require_once('frame_src'.DIRECTORY_SEPARATOR.'auto_loader.php');

// Start the template interpreter
require_once('frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'Sagacious'.DIRECTORY_SEPARATOR.'loader.php');

// Setting global POST and GET variables
$kernelspace->setVariable(array(
  'POST' => \KeyClass\Security::getPost(),
  'GET' => \KeyClass\Security::getGet(),
  'SERVER' => \KeyClass\Security::getRequest("SERVER")
), 'insiderFrameworkSystem');

// UserAgent
$session = \KeyClass\Security::getRequest('session');
if (isset($session['HTTP_USER_AGENT'])) {
    $UserAgent=$session['HTTP_USER_AGENT'];
}
else {
    $UserAgent = null;
}
unset($session);
$kernelspace->setVariable(array('UserAgent' => $UserAgent), 'insiderFrameworkSystem');
unset($UserAgent);

// Starting session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Creates the getallheaders function if it does not exist (if it`s running on 
// the terminal, for example)
if (!function_exists('getallheaders')) {
    /**
        Find all values that are in the global variable $_SERVER

        @author Marcello Costa
        @package Core

        @return  array  Array of headers
    */
    function getallheaders() : array {
      $headers = [];
      foreach ($_SERVER as $name => $value) {
        if (substr($name, 0, 5) == 'HTTP_') {
            $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
        }
      }
      return $headers;
    }
}
$headersRequest = getallheaders();
$kernelspace->setVariable(array('headersRequest' => $headersRequest), 'insiderFrameworkSystem');

// Flag that marks whether it is a request via token
// If the consoleRequest does not exist (since it can come from the update console)
if (!isset($consoleRequest)) {
    $consoleRequest = false;
}

$backtrace = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 1);
if (basename($backtrace[0]['file']) === 'console.php'){
    $consoleRequest = true;
}

$kernelspace->setVariable(array('consoleRequest' => $consoleRequest), 'insiderFrameworkSystem');
unset($headersRequest);

// Difficulty slightly "Session Hijacking"
// If the User Agent exists
if (array_key_exists('HTTP_USER_AGENT', \KeyClass\Security::getRequest('session')))
{
    // If it is not a request for api
    if (!$consoleRequest) {
        // If the User-Agent header has changed and IE is not being used
        if ((\KeyClass\Security::getRequest('session')['HTTP_USER_AGENT'] != md5(\KeyClass\Security::getRequest('SERVER')['HTTP_USER_AGENT'])) &&
            ((strpos('msie', strtolower(\KeyClass\Security::getRequest('SERVER')['HTTP_USER_AGENT'])) === false) &&
             (strpos(('trident'), strtolower(\KeyClass\Security::getRequest('SERVER')['HTTP_USER_AGENT'])) === false)
            )
           )
        {
            // Invalid access. The User-Agent header changed during the same session.
            \KeyClass\Error::i10nErrorRegister("Invalid access. The User-Agent header changed during the same session", 'pack/sys', "ATTACK_DETECTED");
        }
    }
}

// If the User Agent does not exist
else
{
    // If it is not a special request
    if (!$consoleRequest) {
        // Retrieving the requisition data
        $server=\KeyClass\Security::getRequest('SERVER');

        // If the user agent exists
        if (isset($server['HTTP_USER_AGENT'])) {
            // First user access, we will write in the session a md5 hash of the User-Agent header
            \KeyClass\Security::getRequest('session')['HTTP_USER_AGENT'] = md5(\KeyClass\Security::getRequest('SERVER')['HTTP_USER_AGENT']);
        }

        // Error ! Attack detected
        else {
            \KeyClass\Error::i10nErrorRegister("There is no UserAgent in the requisition", 'pack/sys', "ATTACK_DETECTED");
        }
        unset($server);
    }
}

// Starts the error monitor
\KeyClass\FileTree::requireOnceFile('frame_src'.DIRECTORY_SEPARATOR.'error_monitor.php');

// Retrieving request data
$server=\KeyClass\Security::getRequest('SERVER');
$kernelspace->setVariable(array('SERVER' => $server), 'insiderFrameworkSystem');

// If it is a special request
if ($consoleRequest) {
    // If the URL contains the user's idsession cookie
    if (isset($server['QUERY_STRING']) && (strpos($server['QUERY_STRING'],'cookieframeidsession') !== false)) {
        // Getting the value of the cookie
        preg_match("/cookieframeidsession=([^&]*)/", $server['QUERY_STRING'], $matches);

        // Setting the cookie value manually
        \KeyClass\Security::setCookie("idsession", $matches[1]);
    }
}
unset($server);
unset($consoleRequest);

// Requesting modules (need not use class KC_FTree for this load)
$moduleLoader = [];

try {
    require_once('frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.'moduleLoader.php');
    
    if (!(empty($moduleLoader))) {
        foreach ($moduleLoader as $module) {
            $modulepath = 'frame_src'.DIRECTORY_SEPARATOR.'modules'.DIRECTORY_SEPARATOR.'php'.DIRECTORY_SEPARATOR.$module;
            
            if (!file_exists($modulepath)) {
                primaryError("Cannot found module file ('".$modulepath."' listed on moduleLoader.php). Did you forget to run command 'composer install' ?");
            }
            require_once($modulepath);
        }
    }
}
catch (\Exception $e) {
    primaryError($e->getMessage());
}
$kernelspace->setVariable(array('modulesLoaded' => $moduleLoader), 'insiderFrameworkSystem');
unset($moduleLoader);

unset($modulepath);
unset($module);

// Initializes global status of fatal error
$kernelspace->setVariable(array('fatalError' => false), 'insiderFrameworkSystem');

// Initializes global variable of errors
$kernelspace->setVariable(array('registeredErrors' => []));

// Initializes global return format value
$kernelspace->setVariable(array('responseFormat' => DEFAULT_RESPONSE_FORMAT), 'insiderFrameworkSystem');
