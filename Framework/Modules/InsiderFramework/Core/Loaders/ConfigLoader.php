<?php

namespace Modules\InsiderFramework\Core\Loaders;

/**
 * Loader for configurations in framework
 *
 * @author Marcello Costa
 * @package Modules\InsiderFramework\Loaders\ConfigLoader
 */
class ConfigLoader
{
    /**
    * Read "core" config JSON files from framework/config directory
    * These files are needed to load framework properly
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Loaders\ConfigLoader
    *
    * @return array Data of json config files
    */
    public static function loadFrameworkConfigVariables(): array
    {
        $coreConfigFiles = array(
            'core',
            'database',
            'mail',
            'repositories',
        );

        $coreData = [];

        foreach ($coreConfigFiles as $configFile) {
            $dataConfiguration = \Modules\InsiderFramework\Core\Loaders\ConfigLoader::getConfigData($configFile);
            if (count($dataConfiguration) === 0) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    "Could not read '$configFile' file of config directory"
                );
            }
            $coreData = array_merge($coreData, $dataConfiguration);
        }

        global $i10n;
        if (is_array($i10n)) {
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(array('i10n' => $i10n), 'insiderFrameworkSystem');
            unset($i10n);
        }

        // Loading global variables from config to kernelspace
        \Modules\InsiderFramework\Core\Loaders\ConfigLoader::
        registryJsonConfigurationToConstraintsAndVariablesInKernelSpace($coreData);

        return $coreData;
    }

    /**
    * Validates the repositories configuration
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Loaders\ConfigLoader
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    protected static function defineRepositoriesConstants(&$coreData)
    {
        if (
            !isset($coreData['REPOSITORIES']) ||
            !is_array($coreData['REPOSITORIES']) ||
            empty($coreData['REPOSITORIES'])
        ) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'REPOSITORIES'"
            );
        }

        $LOCAL_REPOSITORIES = [];
        $REMOTE_REPOSITORIES = [];
        foreach ($coreData['REPOSITORIES'] as $repo) {
            if (!isset($repo['DOMAIN'])) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    "The following information was not found in the repositories configuration: 'DOMAIN'"
                );
            }
            if (!isset($repo['TYPE'])) {
                \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                    "The following information was not found in the repositories configuration: 'TYPE'"
                );
            }
            switch (strtoupper(trim($repo['TYPE']))) {
                case 'REMOTE':
                    if (isset($REMOTE_REPOSITORIES[$repo['DOMAIN']])) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            "Duplicated entry for repository: " . $repo['DOMAIN']
                        );
                    }
                    $REMOTE_REPOSITORIES[$repo['DOMAIN']] = $repo;
                    break;

                case 'LOCAL':
                    if (isset($LOCAL_REPOSITORIES[$repo['DOMAIN']])) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                            "Duplicated entry for repository: " . $repo['DOMAIN']
                        );
                    }
                    $LOCAL_REPOSITORIES[$repo['DOMAIN']] = $repo;
                    break;
                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                        "Unknown type for repository: " . $repo['TYPE']
                    );
                    break;
            }
        }

        $rK = array_keys($REMOTE_REPOSITORIES);
        $lK = array_keys($LOCAL_REPOSITORIES);

        $final = array_merge($rK, $lK);
        $finalUnique = array_unique($final);

        if (count($final) !== count($finalUnique)) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "Duplicated domains has been founded on configuration " .
                "REPOSITORIES and DOMAIN. Please, review the configuration files"
            );
        }
        unset($rK);
        unset($lK);
        unset($final);
        unset($finalUnique);

        /**
         * Local repositories
         *
         * @package Core
         */
        define('LOCAL_REPOSITORIES', $LOCAL_REPOSITORIES);
        unset($LOCAL_REPOSITORIES);

        /**
         * Remote repositories
         *
         * @package Core
         */
        define('REMOTE_REPOSITORIES', $REMOTE_REPOSITORIES);
    }

    /**
    * Returns data of config file
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Loaders\ConfigLoader
    *
    * @param string $filename Filename of JSON
    *
    * @return array Data inside file
    */
    public static function getConfigData(string $filename): array
    {
        $filepath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                    "Framework" . DIRECTORY_SEPARATOR .
                    "Config" . DIRECTORY_SEPARATOR .
                    $filename . ".json";

        if (file_exists($filepath) && is_readable($filepath)) {
            $configData = \Modules\InsiderFramework\Core\Json::getJSONDataFile($filepath);
            if (!is_array($configData)) {
                return [];
            }
            return $configData;
        }
        return [];
    }

    /**
    * Registry json configuration to constraints and variables in kernelspace
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Loaders\ConfigLoader
    *
    * @param array $coreData Core data loaded from config json files
    *
    * @return void
    */
    public static function registryJsonConfigurationToConstraintsAndVariablesInKernelSpace(array &$coreData): void
    {
        /**
         * Domain used to request
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        $proto = 'http';
        if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
            $proto = $_SERVER["HTTP_X_FORWARDED_PROTO"];
        }
        if (!isset($_SERVER['SHELL']) && isset($_SERVER['HTTP_HOST'])) {
            define('REQUESTED_URL', $proto . "://" . $_SERVER['HTTP_HOST']);
        } else {
            define('REQUESTED_URL', $proto . "://localhost");
        }

        \Modules\InsiderFramework\Core\Loaders\ConfigLoader::defineRepositoriesConstants($coreData);

        if (!defined('REQUESTED_URL')) {
            define('REQUESTED_URL', $coreData['REPOSITORIES'][0]['DOMAIN']);
        }

        if (!isset($coreData['LINGUAS'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'LINGUAS'"
            );
        }

        /**
         * Default application language
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('LINGUAS', $coreData['LINGUAS']);

        if (!isset($coreData['ENCODE'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'ENCODE'"
            );
        }

        /**
         * Default application encoding
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('ENCODE', $coreData['ENCODE']);

        if (!isset($coreData['ENCRYPT_KEY'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'ENCRYPT_KEY'"
            );
        }

        /**
         * Encryption / global decryption key
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('ENCRYPT_KEY', $coreData['ENCRYPT_KEY']);

        if (!isset($coreData['MAILBOX'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX'"
            );
        }

        /**
         * Email from default
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX', $coreData['MAILBOX']);

        if (!isset($coreData['MAILBOX_PASS'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_PASS'"
            );
        }

        /**
         * Default email password / admin
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX_PASS', $coreData['MAILBOX_PASS']);

        if (!isset($coreData['MAILBOX_SMTP'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP'"
            );
        }

        /**
         * Default email SMTP server
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX_SMTP', $coreData['MAILBOX_SMTP']);

        if (!isset($coreData['MAILBOX_SMTP_AUTH'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_AUTH'"
            );
        }

        /**
         * Boolean value that defines whether SMTP has default email authentication or not
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX_SMTP_AUTH', $coreData['MAILBOX_SMTP_AUTH']);

        if (!isset($coreData['MAILBOX_SMTP_SECURE'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_SECURE'"
            );
        }

        /**
         * Type of default email security (TLS or SSL)
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX_SMTP_SECURE', $coreData['MAILBOX_SMTP_SECURE']);

        if (!isset($coreData['MAILBOX_SMTP_PORT'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'MAILBOX_SMTP_PORT'"
            );
        }

        /**
         * SMTP port of default
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAILBOX_SMTP_PORT', $coreData['MAILBOX_SMTP_PORT']);

        if (!isset($coreData['DEFAULT_RESPONSE_FORMAT'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'DEFAULT_RESPONSE_FORMAT'"
            );
        }

        /**
         * Application default response format
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('DEFAULT_RESPONSE_FORMAT', $coreData['DEFAULT_RESPONSE_FORMAT']);

        if (!isset($coreData['ACL_METHOD'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'ACL_METHOD'"
            );
        }

        /**
         * String value. "native" = ACL standard framework or "custom" = ACL custom method
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        if ($coreData['ACL_METHOD'] === "native" || $coreData['ACL_METHOD'] === "custom") {
            define('ACL_METHOD', $coreData['ACL_METHOD']);
        } else {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The configuration 'ACL_METHOD' has an invalid value " .
                $coreData['ACL_METHOD'] . ". Possible values are 'native' or 'custom'."
            );
        }

        /**
         * Maximum number of loops before generating a warning for the default mail box or writing to the log
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('MAX_TOLERANCE_LOOPS', $coreData['MAX_TOLERANCE_LOOPS']);
        // -------------------------- DEBUG -------------------------- //

        if (!isset($coreData['DEBUG'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'DEBUG'"
            );
        }

        /**
         * DEBUG mode of the framework. When enabled, for example, it displays errors
         * directly on the screen instead of sending via email.
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('DEBUG', $coreData['DEBUG']);

        if (!isset($coreData['DEBUG_BAR'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'DEBUG_BAR'"
            );
        }

        /**
         * Debug bar. When enabled, it shows the memory counter used, framework runtime,
         * and other information.
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('DEBUG_BAR', $coreData['DEBUG_BAR']);

        // If the debug bar is enabled, add the debug styles in the default css
        if (DEBUG_BAR === true) {
            global $injectedCss;
            $filename = "Web" . DIRECTORY_SEPARATOR . "Apps" . DIRECTORY_SEPARATOR . "sys" . DIRECTORY_SEPARATOR .
                        "assets" . DIRECTORY_SEPARATOR . "css" . DIRECTORY_SEPARATOR . "debug.css";
            $cssDebugBar = file_get_contents($filename);
            $injectedCss = $injectedCss . "<style>" . $cssDebugBar . "</style>";
            unset($filename);
            unset($cssDebugBar);
        }

        if (!isset($coreData['LOAD_AVG_ACTION'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_ACTION'"
            );
        }
        if (!isset($coreData['LOAD_AVG_MAX_USE_CPU'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_MAX_USE_CPU'"
            );
        }
        if (!isset($coreData['LOAD_AVG_TIME'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_TIME'"
            );
        }
        if (!isset($coreData['LOAD_AVG_SEND_MAIL'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_SEND_MAIL'"
            );
        }
        /**
         * @var array $loadAVG Temporary global variable that defines the maximum
         * load margin of the cpu, how many minutes can this hold until an event
         * is triggered (by default, a message to the user).
         *
         * @see https://en.wikipedia.org/wiki/Load_%28computing%29
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        global $loadAVG;
        $loadAVG = array(
            "action" => $coreData['LOAD_AVG_ACTION'], // Possible action to be taken if stipulated value is exceeded
            "max_use" => $coreData['LOAD_AVG_MAX_USE_CPU'], // Use 0 to deactivate. Example: 0.8 is 80% load
            "time" => $coreData['LOAD_AVG_TIME'], // Possible range values: 1, 5, and 15 (minutes)
            "send_email" => $coreData['LOAD_AVG_SEND_MAIL'] // Send email when you reach the limit
        );
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'loadAVG' => $loadAVG
            ),
            'insiderFrameworkSystem'
        );
        unset($loadAVG);

        /**
         * Email sending policy for errors
         *
         * @package Modules\InsiderFramework\Loaders\ConfigLoader
         */
        define('ERROR_MAIL_SENDING_POLICY', $coreData['ERROR_MAIL_SENDING_POLICY']);

        // ---------- DATABASE SETTINGS --------------- //
        if (!isset($coreData['DATABASES'])) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                "The following information was not found in the configuration: 'DATABASES'"
            );
        }
        $databases = $coreData['DATABASES'];
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'databases' => $databases
            ),
            'insiderFrameworkSystem'
        );

        if (isset($coreData['DB_APP'])) {
            /**
             * Name of the application's default database
             *
             * @package Modules\InsiderFramework\Loaders\ConfigLoader
             */
            define('BD_APP', $coreData['DB_APP']);
        }
        unset($coreData);

        // Loading framework registry
        $appLoaded = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Sections" . DIRECTORY_SEPARATOR .
            "Apps.json"
        );
        if ($appLoaded == false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'File ' . INSTALL_DIR . DIRECTORY_SEPARATOR .
                "Framework" . DIRECTORY_SEPARATOR .
                "Registry" . DIRECTORY_SEPARATOR .
                "Sections" . DIRECTORY_SEPARATOR .
                "Apps.json" .
                ' not found'
            );
        }

        // Carregando as possíveis traduções dos app
        foreach (array_keys($appLoaded) as $app) {
            $pathI10n = INSTALL_DIR . DIRECTORY_SEPARATOR . "Apps" . DIRECTORY_SEPARATOR .
            $app . DIRECTORY_SEPARATOR . "I10n";

            $dirTree = \Modules\InsiderFramework\Core\FileTree::dirTree($pathI10n);
            foreach ($dirTree as $dT) {
                if (!is_dir($dT)) {
                    \Modules\InsiderFramework\Core\Manipulation\I10n::loadi10nFile("app/$app", $dT);
                }
            }
        }
        if (isset($app)) {
            unset($app);
        }
        if (isset($pathI10n)) {
            unset($pathI10n);
        }
        if (isset($dirTree)) {
            unset($dirTree);
        }
        if (isset($dT)) {
            unset($dT);
        }

        $guildsJsonPath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                          "Framework" . DIRECTORY_SEPARATOR .
                          "Registry" . DIRECTORY_SEPARATOR .
                          "Sections" . DIRECTORY_SEPARATOR .
                          "Guilds.json";
                        
        $guildsLoaded = \Modules\InsiderFramework\Core\Json::getJSONDataFile(
            $guildsJsonPath
        );
        if ($guildsLoaded == false) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::ErrorRegister(
                'File ' . $guildsJsonPath . ' not found'
            );
        }
        \Modules\InsiderFramework\Core\KernelSpace::setVariable(
            array(
                'guildsLoaded' => $guildsLoaded
            ),
            'insiderFrameworkSystem'
        );
    }
}
