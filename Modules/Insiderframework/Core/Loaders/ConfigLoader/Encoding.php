<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Encoding {
   /**
    * Define ENCODE constant from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Encoding
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
  public static function load(array $coreData): void {
    if (!isset($coreData['ENCODE'])) {
      \Modules\Insiderframework\Core\Error::primaryError(
        "The following information was not found in the configuration: 'ENCODE'"
      );
    }

    /**
     * Default application encoding
     *
     * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
     */
    define('ENCODE', $coreData['ENCODE']);
  }
}