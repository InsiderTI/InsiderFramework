<?php

namespace Modules\InsiderFramework\Core\Loaders;

/**
 * Este arquivo carrega as classes, interfaces e etc do ambiente
 * dinamicamente
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
             * Inicializa as classes, interfaces e etc do ambiente
             *
             * @author Marcello Costa
             *
             * @package Modules\InsiderFramework\Core\Loaders\AutoLoader
             *
             * @param string $soughtitem Nome do item requisitado
             *
             * @return void
             */
            function ($soughtitem): void {
                if (!defined('INSTALL_DIR')) {
                    define('INSTALL_DIR', '.');
                }
        
                $filepath = str_replace(
                    "\\",
                    DIRECTORY_SEPARATOR,
                    INSTALL_DIR . DIRECTORY_SEPARATOR .
                    "framework" . DIRECTORY_SEPARATOR .
                    $soughtitem . ".php"
                );

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
