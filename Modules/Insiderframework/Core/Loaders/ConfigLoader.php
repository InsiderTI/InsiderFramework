<?php

namespace Modules\Insiderframework\Core\Loaders;

/**
 * Loader for configurations in framework
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
 */
class ConfigLoader
{
    /**
    * Returns data of config file
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
    *
    * @param string $filename Filename of JSON
    *
    * @return array Data inside file
    */
    public static function getConfigFileData(string $filename): array
    {
        $filepath = "Config" . DIRECTORY_SEPARATOR .
                    $filename . ".json";

        if (file_exists($filepath) && is_readable($filepath)) {
            $configData = \Modules\Insiderframework\Core\Json::getJSONDataFile($filepath);
            if (!is_array($configData)) {
                return [];
            }
            return $configData;
        }
        return [];
    }

    /**
    * Read "core" config JSON files from framework/config directory
    * These files are needed to load framework properly
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
    *
    * @return array Data of json config files
    */
    public static function getFrameworkConfigVariablesFromConfigFiles(): array
    {
        $configFiles = array(
            'core',
            'database',
            'mail',
            'repositories',
        );

        $configData = [];

        foreach ($configFiles as $configFile) {
            $dataConfiguration = ConfigLoader::getConfigFileData($configFile);
            if (count($dataConfiguration) === 0) {
                \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
                    "Could not read '$configFile' file of config directory"
                );
            }
            $configData = array_merge($configData, $dataConfiguration);
        }

        return $configData;
    }

    /**
    * Initialize configuration variables from config files
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
    *
    * @return array Data of json config files
    */
    public static function initializeConfigVariablesFromConfigFiles(): void
    {
        $coreData = ConfigLoader::getFrameworkConfigVariablesFromConfigFiles();
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\RequestedUrl::load();
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\RepositoriesList::load($coreData);
        
        // TO BE CONVERTED
        // if (!isset($coreData['LINGUAS'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'LINGUAS'"
        //     );
        // }

        // /**
        //  * Default application language
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('LINGUAS', $coreData['LINGUAS']);
        // \Modules\Insiderframework\Core\I10n::setCurrentLinguas(LINGUAS);

        // if (!isset($coreData['ENCODE'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'ENCODE'"
        //     );
        // }

        // /**
        //  * Default application encoding
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('ENCODE', $coreData['ENCODE']);

        // if (!isset($coreData['ENCRYPT_KEY'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'ENCRYPT_KEY'"
        //     );
        // }

        // /**
        //  * Encryption / global decryption key
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('ENCRYPT_KEY', $coreData['ENCRYPT_KEY']);

        // if (!isset($coreData['MAILBOX'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX'"
        //     );
        // }

        // /**
        //  * Email from default
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX', $coreData['MAILBOX']);

        // if (!isset($coreData['MAILBOX_PASS'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX_PASS'"
        //     );
        // }

        // /**
        //  * Default email password / admin
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX_PASS', $coreData['MAILBOX_PASS']);

        // if (!isset($coreData['MAILBOX_SMTP'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX_SMTP'"
        //     );
        // }

        // /**
        //  * Default email SMTP server
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX_SMTP', $coreData['MAILBOX_SMTP']);

        // if (!isset($coreData['MAILBOX_SMTP_AUTH'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX_SMTP_AUTH'"
        //     );
        // }

        // /**
        //  * Boolean value that defines whether SMTP has default email authentication or not
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX_SMTP_AUTH', $coreData['MAILBOX_SMTP_AUTH']);

        // if (!isset($coreData['MAILBOX_SMTP_SECURE'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX_SMTP_SECURE'"
        //     );
        // }

        // /**
        //  * Type of default email security (TLS or SSL)
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX_SMTP_SECURE', $coreData['MAILBOX_SMTP_SECURE']);

        // if (!isset($coreData['MAILBOX_SMTP_PORT'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'MAILBOX_SMTP_PORT'"
        //     );
        // }

        // /**
        //  * SMTP port of default
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAILBOX_SMTP_PORT', $coreData['MAILBOX_SMTP_PORT']);

        // if (!isset($coreData['DEFAULT_RESPONSE_FORMAT'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'DEFAULT_RESPONSE_FORMAT'"
        //     );
        // }

        // /**
        //  * Application default response format
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('DEFAULT_RESPONSE_FORMAT', $coreData['DEFAULT_RESPONSE_FORMAT']);

        // if (!isset($coreData['ACL_DEFAULT_ENGINE'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'ACL_DEFAULT_ENGINE'"
        //     );
        // }

        // /**
        //  * String value. ACL engine class
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('ACL_DEFAULT_ENGINE', $coreData['ACL_DEFAULT_ENGINE']);

        // /**
        //  * Maximum number of loops before generating a warning for the default mail box or writing to the log
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('MAX_TOLERANCE_LOOPS', $coreData['MAX_TOLERANCE_LOOPS']);
        // // -------------------------- DEBUG -------------------------- //

        // if (!isset($coreData['DEBUG'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'DEBUG'"
        //     );
        // }

        // /**
        //  * DEBUG mode of the framework. When enabled, for example, it displays errors
        //  * directly on the screen instead of sending via email.
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // $debug = $coreData['DEBUG'];

        // if (!isset($coreData['DEBUG_BAR'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'DEBUG_BAR'"
        //     );
        // }

        // /**
        //  * Debug bar. When enabled, it shows the memory counter used, framework runtime,
        //  * and other information.
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('DEBUG_BAR', $coreData['DEBUG_BAR']);

        // // If the debug bar is enabled, add the debug styles in the default css
        // if (DEBUG_BAR === true) {
        //     if ($debug === false) {
        //         \Modules\Insiderframework\Core\Error\ErrorHandler::errorRegister(
        //             'You cannot <b>enable DEBUG_BAR</b> core config without enable DEBUG. ' .
        //             'Automatically activating DEBUG.',
        //             'WARNING'
        //         );
        //         $debug = true;
        //     }
        //     define('DEBUG', $debug);

        //     global $injectedCss;
        //     $filename = "Web" . DIRECTORY_SEPARATOR .
        //                 "Apps" . DIRECTORY_SEPARATOR .
        //                 "Sys" . DIRECTORY_SEPARATOR .
        //                 "assets" . DIRECTORY_SEPARATOR .
        //                 "css" . DIRECTORY_SEPARATOR .
        //                 "debug.css";

        //     $cssDebugBar = file_get_contents($filename);
        //     $injectedCss = $injectedCss . "<style>" . $cssDebugBar . "</style>";
        //     unset($filename);
        //     unset($cssDebugBar);
        // } else {
        //     define('DEBUG', $debug);
        // }

        // if (!isset($coreData['LOAD_AVG_ACTION'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_ACTION'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_MAX_USE_CPU'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_MAX_USE_CPU'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_TIME'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_TIME'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_SEND_MAIL'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_SEND_MAIL'"
        //     );
        // }
        // /**
        //  * @var array $loadAVG Temporary global variable that defines the maximum
        //  * load margin of the cpu, how many minutes can this hold until an event
        //  * is triggered (by default, a message to the user).
        //  *
        //  * @see https://en.wikipedia.org/wiki/Load_%28computing%29
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // global $loadAVG;
        // $loadAVG = array(
        //     "action" => $coreData['LOAD_AVG_ACTION'], // Possible action to be taken if stipulated value is exceeded
        //     "max_use" => $coreData['LOAD_AVG_MAX_USE_CPU'], // Use 0 to deactivate. Example: 0.8 is 80% load
        //     "time" => $coreData['LOAD_AVG_TIME'], // Possible range values: 1, 5, and 15 (minutes)
        //     "send_email" => $coreData['LOAD_AVG_SEND_MAIL'] // Send email when you reach the limit
        // );
        // \Modules\Insiderframework\Core\KernelSpace::setVariable(
        //     array(
        //         'loadAVG' => $loadAVG
        //     ),
        //     'insiderFrameworkSystem'
        // );
        // unset($loadAVG);

        // /**
        //  * Email sending policy for errors
        //  *
        //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //  */
        // define('ERROR_MAIL_SENDING_POLICY', $coreData['ERROR_MAIL_SENDING_POLICY']);

        // // ---------- DATABASE SETTINGS --------------- //
        // if (!isset($coreData['DATABASES'])) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
        //         "The following information was not found in the configuration: 'DATABASES'"
        //     );
        // }
        // $databases = $coreData['DATABASES'];
        // \Modules\Insiderframework\Core\KernelSpace::setVariable(
        //     array(
        //         'databases' => $databases
        //     ),
        //     'insiderFrameworkSystem'
        // );

        // if (isset($coreData['DB_APP'])) {
        //     /**
        //      * Name of the application's default database
        //      *
        //      * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
        //      */
        //     define('BD_APP', $coreData['DB_APP']);
        // }
        // unset($coreData);

        // // Loading framework registry
        // $appLoaded = \Modules\Insiderframework\Core\Json::getJSONDataFile(
        //     INSTALL_DIR . DIRECTORY_SEPARATOR .
        //     "Framework" . DIRECTORY_SEPARATOR .
        //     "Registry" . DIRECTORY_SEPARATOR .
        //     "Sections" . DIRECTORY_SEPARATOR .
        //     "Apps.json"
        // );
        // if ($appLoaded == false) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::errorRegister(
        //         'File ' . INSTALL_DIR . DIRECTORY_SEPARATOR .
        //         "Framework" . DIRECTORY_SEPARATOR .
        //         "Registry" . DIRECTORY_SEPARATOR .
        //         "Sections" . DIRECTORY_SEPARATOR .
        //         "Apps.json" .
        //         ' not found'
        //     );
        // }

        // // Carregando as possíveis traduções dos app
        // foreach (array_keys($appLoaded) as $app) {
        //     $pathI10n = INSTALL_DIR . DIRECTORY_SEPARATOR . "Apps" . DIRECTORY_SEPARATOR .
        //     $app . DIRECTORY_SEPARATOR . "I10n";

        //     $dirTree = \Modules\Insiderframework\Core\FileTree::dirTree($pathI10n);
        //     foreach ($dirTree as $dT) {
        //         if (!is_dir($dT)) {
        //             \Modules\Insiderframework\Core\Manipulation\I10n::loadi10nFile("app/$app", $dT);
        //         }
        //     }
        // }
        // if (isset($app)) {
        //     unset($app);
        // }
        // if (isset($pathI10n)) {
        //     unset($pathI10n);
        // }
        // if (isset($dirTree)) {
        //     unset($dirTree);
        // }
        // if (isset($dT)) {
        //     unset($dT);
        // }

        // $guildsJsonPath = INSTALL_DIR . DIRECTORY_SEPARATOR .
        //                   "Framework" . DIRECTORY_SEPARATOR .
        //                   "Registry" . DIRECTORY_SEPARATOR .
        //                   "Sections" . DIRECTORY_SEPARATOR .
        //                   "Guilds.json";
                        
        // $guildsLoaded = \Modules\Insiderframework\Core\Json::getJSONDataFile(
        //     $guildsJsonPath
        // );
        // if ($guildsLoaded == false) {
        //     \Modules\Insiderframework\Core\Error\ErrorHandler::errorRegister(
        //         'File ' . $guildsJsonPath . ' not found'
        //     );
        // }
        // \Modules\Insiderframework\Core\KernelSpace::setVariable(
        //     array(
        //         'guildsLoaded' => $guildsLoaded
        //     ),
        //     'insiderFrameworkSystem'
        // );
    }
}