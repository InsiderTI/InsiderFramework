<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Debug {
   /**
    * Define debug constants from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Debug
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    public static function load(array $coreData): void {
        if (!isset($coreData['DEBUG'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'DEBUG'"
            );
        }

        /**
         * DEBUG mode of the framework. When enabled, for example, it displays errors
         * directly on the screen instead of sending via email.
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Debug
         */
        $debug = $coreData['DEBUG'];

        if (!isset($coreData['DEBUG_BAR'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'DEBUG_BAR'"
            );
        }

        /**
         * Debug bar. When enabled, it shows the memory counter used, framework runtime,
         * and other information.
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Debug
         */
        define('DEBUG_BAR', $coreData['DEBUG_BAR']);

        // If the debug bar is enabled, add the debug styles in the default css
        if (DEBUG_BAR === true) {
            if ($debug === false) {
                \Modules\Insiderframework\Core\Error::errorRegister(
                    'You cannot <b>enable DEBUG_BAR</b> core config without enable DEBUG. ' .
                    'Automatically activating DEBUG.',
                    'WARNING'
                );
                $debug = true;
            }
        }
        define('DEBUG', $debug);
    }
}