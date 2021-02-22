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
        $filepath = INSTALL_DIR . DIRECTORY_SEPARATOR .
                    "Config" . DIRECTORY_SEPARATOR .
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
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\LoadAvg::load($coreData);
        \Modules\Insiderframework\Core\Loaders\ConfigLoader\Database::load($coreData);

        // TODO: Load coreData to KernelSpace and load all loaders from registry
        unset($coreData);
    }
}