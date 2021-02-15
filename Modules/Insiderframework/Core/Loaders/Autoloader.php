<?php

namespace Modules\InsiderFramework\Core\Loaders;

/**
 * This file loads the classes, interfaces and etc. of the environment
 * dynamically
 *
 * @author Marcello Costa
 *
 * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
 */
class AutoLoader
{
    /**
    * Construct method to autoloader
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
    *
    * @return void
    */
    public static function initializeAutoLoader(): void
    {
        spl_autoload_register(
            /**
             * Initializes environment classes, interfaces, etc.
             *
             * @author Marcello Costa
             *
             * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
             *
             * @param string $soughtitem Requested item name
             *
             * @return void
             */
            function (string $soughtitem): void {
                if (!defined('INSTALL_DIR')) {
                    define('INSTALL_DIR', '.');
                }

                $firstNamespaceClass = explode("\\", $soughtitem)[0];
        
                if ($firstNamespaceClass !== 'Apps') {
                    $filepath = AutoLoader::getFrameworkClassFilePath($soughtitem);
                } else {
                    $filepath = AutoLoader::getAppClassFilePath($soughtitem);
                }

                if (
                    file_exists($filepath) &&
                    is_readable($filepath)
                ) {
                    require_once $filepath;
                }
            }
        );
    }
}