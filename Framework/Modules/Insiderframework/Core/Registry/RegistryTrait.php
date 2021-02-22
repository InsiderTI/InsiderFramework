<?php

namespace Modules\Insiderframework\Core\Registry;

/**
 * Methods responsible for handle kernelspace
 *
 * @author Marcello Costa
 *
 * @package Modules\Insiderframework\Core\Registry\RegistryTrait
 */
trait RegistryTrait {
    /**
     * Get list of all installed modules
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Registry\RegistryTrait
     *
     * @return array List of all installed modules
     */
    public static function getListOfInstalledModules(): array {
        $modulesList = \Modules\Insiderframework\Core\Json::getJSONDataFile(
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            "Registry" . DIRECTORY_SEPARATOR .
            "Sections" . DIRECTORY_SEPARATOR .
            "Modules.json"
        );

        return $modulesList;
    }

    /**
     * Get module info from JSON
     *
     * @author Marcello Costa
     *
     * @package Modules\Insiderframework\Core\Registry\RegistryTrait
     *
     * @param string $moduleName Module name (optional)
     *
     * @return array Info of one or all module
     */
    public static function getModuleInfo(string $moduleName = null): array {
        $listOfModules = RegistryTrait::getListOfInstalledModules();

        if ($moduleName !== null && !in_array($moduleName, $listOfModules)){
            \Modules\Insiderframework\Core\Error::errorRegister(
                'Cannot find module ' . $moduleName
            );
        }

        $modules = [];
        if ($moduleName !== NULL){
            $modules[] = $moduleName;
        } else {
            // TODO
            // Get all control files
            // Must be implemented: Text::extractString
            // Must be implemented: FileTree::fillArrayWithFileNodes
        }

        $controlPath = INSTALL_DIR . "Framework" . DIRECTORY_SEPARATOR .
                       "Registry" . DIRECTORY_SEPARATOR .
                       "Controls" . DIRECTORY_SEPARATOR .
                       $moduleName . DIRECTORY_SEPARATOR .
                       "control.json";

        $controlData = \Modules\Insiderframework\Core\Json::getJSONDataFile($controlPath);

        if (!$controlData) {
            var_dump($controlPath);
            die();
            \Modules\Insiderframework\Core\Error::errorRegister(
                "Cannot load file contents %" . $controlPath . "%"
            );
        }

        return $controlData;
    }
}