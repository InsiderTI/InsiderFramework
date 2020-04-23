<?php

namespace Modules\InsiderFramework\Core\Loaders;

/**
 * Class responsible for modules listed in Jsn Config
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Loaders\ModuleLoader
 */
class ModuleLoader
{
    /**
    * Load modules listed in Json config file (moduleloader.json)
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Loaders\ModuleLoader
    *
    * @return void
    */
    public static function loadModulesFromJsonConfigFile(): void
    {
        $configModules = \Modules\InsiderFramework\Core\Loaders\ConfigLoader::getConfigData('moduleloader');
        if (count($configModules) === 0) {
            return;
        }

        \Modules\InsiderFramework\Core\Loaders\ModuleLoader::loadAdditionalModules($configModules);
    }

    /**
    * Additional modules listed in modulerLoader.php
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Loaders\ModuleLoader
    *
    * @param array $modules Modules listed in config file
    *
    * @return void
    */
    public static function loadAdditionalModules(array $modules): void
    {
        // Requesting modules (need not use class KC_FTree for this load)
        $moduleLoader = [];

        try {
            foreach ($modules as $moduleName => $module) {
                $modulePath = str_replace("/", DIRECTORY_SEPARATOR, $module);

                $modulepath = 'framework' . DIRECTORY_SEPARATOR .
                              'Modules' . DIRECTORY_SEPARATOR .
                              $modulePath;

                if (!file_exists($modulepath)) {
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError(
                        "Cannot found module " . $moduleName . " file ('" .
                        $modulepath . "' listed on moduleLoader.php)." .
                        " Did you forget to run command 'composer install' ?"
                    );
                }
                require_once($modulepath);
            }

            \Modules\InsiderFramework\Core\Manipulation\KernelSpace::setVariable(array('modulesLoaded' => $moduleLoader), 'insiderFrameworkSystem');
            unset($moduleLoader);

            unset($modulepath);
            unset($module);
        } catch (\Exception $e) {
            \Modules\InsiderFramework\Core\Error\ErrorHandler::primaryError($e->getMessage());
        }
    }
}