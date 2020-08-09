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
    public static function initializeAutoLoader()
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

    /**
    * Get file path of a class inside the Apps directory
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
    *
    * @param string $soughtitem Requested item name
    *
    * @return string File path of class
    */
    public static function getAppClassFilePath(string $soughtitem): string
    {
        return str_replace(
            "\\",
            DIRECTORY_SEPARATOR,
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            $soughtitem . ".php"
        );
    }

    /**
    * Get file path of a class inside the Framework directory
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
    *
    * @param string $soughtitem Requested item name
    *
    * @return string File path of class
    */
    public static function getFrameworkClassFilePath(string $soughtitem): string
    {
        return str_replace(
            "\\",
            DIRECTORY_SEPARATOR,
            INSTALL_DIR . DIRECTORY_SEPARATOR .
            "Framework" . DIRECTORY_SEPARATOR .
            $soughtitem . ".php"
        );
    }
}
