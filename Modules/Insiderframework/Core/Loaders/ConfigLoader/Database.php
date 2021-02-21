<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Database {
   /**
    * Define database constants from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Database
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    public static function load(array $coreData): void {
        // ---------- DATABASE SETTINGS --------------- //
        if (!isset($coreData['DATABASES'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'DATABASES'"
            );
        }
        $databases = $coreData['DATABASES'];
        \Modules\Insiderframework\Core\KernelSpace::setVariable(
            array(
                'databases' => $databases
            ),
            'insiderFrameworkSystem'
        );

        if (isset($coreData['DB_APP'])) {
            /**
             * Name of the application's default database
             *
             * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
             */
            define('DB_APP', $coreData['DB_APP']);
        }
    }
}