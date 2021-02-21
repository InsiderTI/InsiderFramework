<?php

namespace Modules\Insiderframework\Core\Loaders;

use \Modules\Insiderframework\Core\KernelSpace;

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
                \Modules\Insiderframework\Core\Error::primaryError(
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
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Linguas::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Encoding::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Encription::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Mail::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\ResponseFormat::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Acl::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\ToleranceLoops::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Debug::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\InjectedSgsVariables::load($coreData);
        // TODO: TO BE CONVERTED

        // if (!isset($coreData['LOAD_AVG_ACTION'])) {
        //     \Modules\Insiderframework\Core\Error::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_ACTION'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_MAX_USE_CPU'])) {
        //     \Modules\Insiderframework\Core\Error::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_MAX_USE_CPU'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_TIME'])) {
        //     \Modules\Insiderframework\Core\Error::primaryError(
        //         "The following information was not found in the configuration: 'LOAD_AVG_TIME'"
        //     );
        // }
        // if (!isset($coreData['LOAD_AVG_SEND_MAIL'])) {
        //     \Modules\Insiderframework\Core\Error::primaryError(
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

        // // ---------- DATABASE SETTINGS --------------- //
        // if (!isset($coreData['DATABASES'])) {
        //     \Modules\Insiderframework\Core\Error::primaryError(
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
        //     \Modules\Insiderframework\Core\Error::errorRegister(
        //         'File ' . INSTALL_DIR . DIRECTORY_SEPARATOR .
        //         "Framework" . DIRECTORY_SEPARATOR .
        //         "Registry" . DIRECTORY_SEPARATOR .
        //         "Sections" . DIRECTORY_SEPARATOR .
        //         "Apps.json" .
        //         ' not found'
        //     );
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
        //     \Modules\Insiderframework\Core\Error::errorRegister(
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