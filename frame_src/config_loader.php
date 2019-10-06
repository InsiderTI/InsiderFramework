<?php

/* This file loads the PHP side settings of the framework.

  @author Marcello Costa
  @package Core
 */

/**
  Framework installation directory (APP_ROOT)

  @package Core
 */
define('INSTALL_DIR', getcwd());
define('APP_ROOT', INSTALL_DIR);

// Loading the Filetree Class, JSON Read Class, I10n class and Code class
require_once(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "filetree.php");
require_once(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "json.php");
require_once(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "i10n.php");
require_once(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "keyclasses" . DIRECTORY_SEPARATOR . "php" . DIRECTORY_SEPARATOR . "code.php");


// Reading Configuration Files
$dataJSON = [];
$core = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "core.json");
if ($core === false) {
    primaryError("Could not read core file of system settings");
}
$dataJSON = array_merge($dataJSON, $core);
unset($core);

$database = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "database.json");
if ($database === false) {
    primaryError("Could not read database system configuration file");
}
$dataJSON = array_merge($dataJSON, $database);
unset($database);

$mail = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "mail.json");
if ($mail === false) {
    primaryError("Could not read mail file from system settings");
}
$dataJSON = array_merge($dataJSON, $mail);
unset($mail);

/**
  Domain used to request

  @package Core
 */
$proto = 'http';
if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])){
    $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"];
}
if (!isset($_SERVER['SHELL'])){
    define('REQUESTED_URL', $proto."://".$_SERVER['HTTP_HOST']);
}
else{
    define('REQUESTED_URL', $proto."://localhost");
}

$repositories = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "config" . DIRECTORY_SEPARATOR . "repositories.json");
if ($repositories === false) {
    primaryError("Could not read file system configuration repositories");
}
$dataJSON = array_merge($dataJSON, $repositories);
unset($repositories);

if (!isset($dataJSON['REPOSITORIES']) || !is_array($dataJSON['REPOSITORIES']) || empty($dataJSON['REPOSITORIES'])) {
    primaryError("The following information was not found in the configuration: 'REPOSITORIES'");
}

$LOCAL_REPOSITORIES = [];
$REMOTE_REPOSITORIES = [];
foreach($dataJSON['REPOSITORIES'] as $repo){
    if (!isset($repo['DOMAIN'])){
        primaryError("The following information was not found in the repositories configuration: 'DOMAIN'");
    }
    if (!isset($repo['TYPE'])){
        primaryError("The following information was not found in the repositories configuration: 'TYPE'");
    }
    switch (strtoupper(trim($repo['TYPE']))){
        case 'REMOTE':
            if (isset($REMOTE_REPOSITORIES[$repo['DOMAIN']])){
                primaryError("Duplicated entry for repository: ".$repo['DOMAIN']);
            }
            $REMOTE_REPOSITORIES[$repo['DOMAIN']]=$repo;
        break;
    
        case 'LOCAL':
            if (isset($LOCAL_REPOSITORIES[$repo['DOMAIN']])){
                primaryError("Duplicated entry for repository: ".$repo['DOMAIN']);
            }
            $LOCAL_REPOSITORIES[$repo['DOMAIN']]=$repo;
        break;
        default:
            primaryError("Unknown type for repository: ".$repo['TYPE']);
        break;
    }
}
unset($repo);

if (!defined('REQUESTED_URL')){
    define('REQUESTED_URL', $dataJSON['REPOSITORIES'][0]['DOMAIN']);

    if (isset($LOCAL_REPOSITORIES[0])){
        unset($LOCAL_REPOSITORIES[0]);
    }
}

$rK = array_keys($REMOTE_REPOSITORIES);
$lK = array_keys($LOCAL_REPOSITORIES);

$final = array_merge($rK, $lK);
$finalUnique = array_unique($final);

if (count($final) !== count($finalUnique)){
    primaryError("Duplicated domains has been founded on configuration REPOSITORIES and DOMAIN. Please, review the configuration files");
}
unset($rK);
unset($lK);
unset($final);
unset($finalUnique);

/**
  Local repositories

  @package Core
*/
define('LOCAL_REPOSITORIES', $LOCAL_REPOSITORIES);
unset($LOCAL_REPOSITORIES);

/**
  Remote repositories

  @package Core
*/
define('REMOTE_REPOSITORIES', $REMOTE_REPOSITORIES);

if (!isset($dataJSON['LINGUAS'])) {
    primaryError("The following information was not found in the configuration: 'LINGUAS'");
}

/**
  Default application language

  @package Core
 */
define('LINGUAS', $dataJSON['LINGUAS']);

if (!isset($dataJSON['ENCODE'])) {
    primaryError("The following information was not found in the configuration: 'ENCODE'");
}

/**
  Default application encoding

  @package Core
 */
define('ENCODE', $dataJSON['ENCODE']);

if (!isset($dataJSON['ENCRYPT_KEY'])) {
    primaryError("The following information was not found in the configuration: 'ENCRYPT_KEY'");
}

/**
  Encryption / global decryption key

  @package Core
 */
define('ENCRYPT_KEY', $dataJSON['ENCRYPT_KEY']);

if (!isset($dataJSON['MAILBOX'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX'");
}

/**
  Email from default

  @package Core
 */
define('MAILBOX', $dataJSON['MAILBOX']);

if (!isset($dataJSON['MAILBOX_PASS'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX_PASS'");
}

/**
  Default email password / admin

  @package Core
 */
define('MAILBOX_PASS', $dataJSON['MAILBOX_PASS']);

if (!isset($dataJSON['MAILBOX_SMTP'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX_SMTP'");
}

/**
  Default email SMTP server

  @package Core
 */
define('MAILBOX_SMTP', $dataJSON['MAILBOX_SMTP']);

if (!isset($dataJSON['MAILBOX_SMTP_AUTH'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX_SMTP_AUTH'");
}

/**
  Boolean value that defines whether SMTP has default email authentication or not

  @package Core
 */
define('MAILBOX_SMTP_AUTH', $dataJSON['MAILBOX_SMTP_AUTH']);

if (!isset($dataJSON['MAILBOX_SMTP_SECURE'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX_SMTP_SECURE'");
}

/**
  Type of default email security (TLS or SSL)

  @package Core
 */
define('MAILBOX_SMTP_SECURE', $dataJSON['MAILBOX_SMTP_SECURE']);

if (!isset($dataJSON['MAILBOX_SMTP_PORT'])) {
    primaryError("The following information was not found in the configuration: 'MAILBOX_SMTP_PORT'");
}

/**
  SMTP port of default

  @package Core
 */
define('MAILBOX_SMTP_PORT', $dataJSON['MAILBOX_SMTP_PORT']);

if (!isset($dataJSON['DEFAULT_RESPONSE_FORMAT'])) {
    primaryError("The following information was not found in the configuration: 'DEFAULT_RESPONSE_FORMAT'");
}

/**
  Application default response format

  @package Core
 */
define('DEFAULT_RESPONSE_FORMAT', $dataJSON['DEFAULT_RESPONSE_FORMAT']);

if (!isset($dataJSON['ACL_METHOD'])) {
    primaryError("The following information was not found in the configuration: 'ACL_METHOD'");
}

/**
  String value. "native" = ACL standard framework or "custom" = ACL custom method

  @package Core
 */

if ($dataJSON['ACL_METHOD'] === "native" || $dataJSON['ACL_METHOD'] === "custom") {
    define('ACL_METHOD', $dataJSON['ACL_METHOD']);
}
else {
    primaryError("The configuration 'ACL_METHOD' has an invalid value ".$dataJSON['ACL_METHOD'].". Possible values are 'native' or 'custom'.");
}

/**
  Maximum number of loops before generating a warning for the default mail box or writing to the log

  @package Core
 */
define('MAX_TOLERANCE_LOOPS', $dataJSON['MAX_TOLERANCE_LOOPS']);
// -------------------------- DEBUG -------------------------- //

if (!isset($dataJSON['DEBUG'])) {
    primaryError("The following information was not found in the configuration: 'DEBUG'");
}

/**
  DEBUG mode of the framework. When enabled, for example, it displays errors
  directly on the screen instead of sending via email.

  @package Core
 */
define('DEBUG', $dataJSON['DEBUG']);

if (!isset($dataJSON['DEBUG_BAR'])) {
    primaryError("The following information was not found in the configuration: 'DEBUG_BAR'");
}

/**
  Debug bar. When enabled, it shows the memory counter used, framework runtime,
  and other information.

  @package Core
 */
define('DEBUG_BAR', $dataJSON['DEBUG_BAR']);

// If the debug bar is enabled, add the debug styles in the default css
if (DEBUG_BAR === true) {
    global $injectedCss;
    $filename = "web" . DIRECTORY_SEPARATOR . "packs" . DIRECTORY_SEPARATOR . "sys" . DIRECTORY_SEPARATOR . "assets" . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "debug.css";
    $cssDebugBar = file_get_contents($filename);
    $injectedCss = $injectedCss . "<style>" . $cssDebugBar . "</style>";
    unset($filename);
    unset($cssDebugBar);
}

if (!isset($dataJSON['LOAD_AVG_ACTION'])) {
    primaryError("The following information was not found in the configuration: 'LOAD_AVG_ACTION'");
}
if (!isset($dataJSON['LOAD_AVG_MAX_USE_CPU'])) {
    primaryError("The following information was not found in the configuration: 'LOAD_AVG_MAX_USE_CPU'");
}
if (!isset($dataJSON['LOAD_AVG_TIME'])) {
    primaryError("The following information was not found in the configuration: 'LOAD_AVG_TIME'");
}
if (!isset($dataJSON['LOAD_AVG_SEND_MAIL'])) {
    primaryError("The following information was not found in the configuration: 'LOAD_AVG_SEND_MAIL'");
}
/**
  @var array Global variable that defines the maximum load margin of the cpu,
  how many minutes can this hold until an event is triggered (by default, 
  a message to the user).

  @see https://en.wikipedia.org/wiki/Load_%28computing%29

  @package Core
 */
global $loadAVG;
$loadAVG = array(
    "action" => $dataJSON['LOAD_AVG_ACTION'], // Possible action to be taken if stipulated value is exceeded
    "max_use" => $dataJSON['LOAD_AVG_MAX_USE_CPU'], // Use 0 to deactivate. Example: 0.8 is 80% load
    "time" => $dataJSON['LOAD_AVG_TIME'], // Possible range values: 1, 5, and 15 (minutes)
    "send_email" => $dataJSON['LOAD_AVG_SEND_MAIL'] // Send email when you reach the limit
);


/**
  Email sending policy for errors

  @package Core
 */
define('ERROR_MAIL_SENDING_POLICY', $dataJSON['ERROR_MAIL_SENDING_POLICY']);

// ---------- DATABASE SETTINGS --------------- //
//

if (!isset($dataJSON['DATABASES'])) {
    primaryError("The following information was not found in the configuration: 'DATABASES'");
}
global $databases;
$databases = $dataJSON['DATABASES'];

if (isset($dataJSON['DB_APP'])) {
    /**
      Name of the application's default database

      @package Core
     */
    define('BD_APP', $dataJSON['DB_APP']);
}
unset($dataJSON);

// Loading framework registry
global $packsLoaded;
$packsLoaded = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "packs.json");
if ($packsLoaded == false) {
    \KeyClass\Error::errorRegister('File ' . INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "packs.json" . ' not found');
}

// Carregando as possíveis traduções dos packs
foreach (array_keys($packsLoaded) as $pack) {
    $pathI10n = INSTALL_DIR . DIRECTORY_SEPARATOR . "packs" . DIRECTORY_SEPARATOR . $pack . DIRECTORY_SEPARATOR . "i10n";
    
    $dirTree = \KeyClass\FileTree::dirTree($pathI10n);
    foreach($dirTree as $dT){
        if (!is_dir($dT)){
            \KeyClass\I10n::loadi10nFile("pack/$pack", $dT);
        }
    }
}
if (isset($pack)){
    unset($pack);
}
if (isset($pathI10n)){
    unset($pathI10n);
}
if (isset($dirTree)){
    unset($dirTree);
}
if (isset($dT)){
    unset($dT);
}

global $guildsLoaded;
$guildsLoaded = \KeyClass\JSON::getJSONDataFile(INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "guilds.json");
if ($guildsLoaded == false) {
    \KeyClass\Error::errorRegister('File ' . INSTALL_DIR . DIRECTORY_SEPARATOR . "frame_src" . DIRECTORY_SEPARATOR . "registry" . DIRECTORY_SEPARATOR . "guilds.json" . ' not found');
}
