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
            function ($soughtitem): void {
                if (!defined('INSTALL_DIR')) {
                    define('INSTALL_DIR', '.');
                }

                $firstNamespaceClass = explode("\\", $soughtitem)[0];
        
                if ($firstNamespaceClass !== 'Apps') {
                    $filepath = str_replace(
                        "\\",
                        DIRECTORY_SEPARATOR,
                        INSTALL_DIR . DIRECTORY_SEPARATOR .
                        "Framework" . DIRECTORY_SEPARATOR .
                        $soughtitem . ".php"
                    );
                } else {
                    $filepath = str_replace(
                        "\\",
                        DIRECTORY_SEPARATOR,
                        INSTALL_DIR . DIRECTORY_SEPARATOR .
                        $soughtitem . ".php"
                    );
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
