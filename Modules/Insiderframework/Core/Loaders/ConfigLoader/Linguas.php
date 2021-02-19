<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;
class Linguas(){
   /**
    * Define LINGUAS constant from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\Linguas
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
  public static function load(array $coreData): void {
    if (!isset($coreData['LINGUAS'])) {
        \Modules\Insiderframework\Core\Error\ErrorHandler::primaryError(
            "The following information was not found in the configuration: 'LINGUAS'"
        );
    }

    define('LINGUAS', $coreData['LINGUAS']);
    \Modules\Insiderframework\Core\I10n::setCurrentLinguas(LINGUAS);
  }
}