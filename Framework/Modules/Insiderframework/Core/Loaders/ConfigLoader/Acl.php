<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Acl {
   /**
    * Define ACL_DEFAULT_ENGINE constant from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Acl
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    public static function load(array $coreData): void {
        if (!isset($coreData['ACL_DEFAULT_ENGINE'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'ACL_DEFAULT_ENGINE'"
            );
        }

        /**
         * String value. ACL engine class
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Acl
         */
        define('ACL_DEFAULT_ENGINE', $coreData['ACL_DEFAULT_ENGINE']);
    }
}