<?php

namespace Modules\Insiderframework\Core\Loaders;

/**
 * Loader for configurations in framework
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Loaders\ConfigLoader
 */
class ConfigLoader
{
    /**
    * Returns data of config file
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Loaders\ConfigLoader
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
}