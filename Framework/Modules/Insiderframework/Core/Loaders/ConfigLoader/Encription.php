<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Encription {
   /**
    * Define ENCRYPT_KEY constant from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Encription
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
  public static function load(array $coreData): void {
    if (!isset($coreData['ENCRYPT_KEY'])) {
        \Modules\Insiderframework\Core\Error::primaryError(
            "The following information was not found in the configuration: 'ENCRYPT_KEY'"
        );
    }

    /**
     * Encryption / global decryption key
     *
     * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Encription
     */
    define('ENCRYPT_KEY', $coreData['ENCRYPT_KEY']);
  }
}