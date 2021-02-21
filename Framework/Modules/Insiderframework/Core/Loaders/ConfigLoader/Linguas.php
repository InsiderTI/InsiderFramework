<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class Linguas {
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
        \Modules\Insiderframework\Core\Error::primaryError(
            "The following information was not found in the configuration: 'LINGUAS'"
        );
    }

    define('LINGUAS', $coreData['LINGUAS']);
    \Modules\Insiderframework\Core\I10n::setCurrentLinguas(LINGUAS);
    Linguas::loadI10nFromApps($coreData);
  }

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
  protected static function loadI10nFromApps(array $coreData): void {
    // foreach (array_keys($appLoaded) as $app) {
    //     $pathI10n = INSTALL_DIR . DIRECTORY_SEPARATOR . "Apps" . DIRECTORY_SEPARATOR .
    //     $app . DIRECTORY_SEPARATOR . "I10n";

    //     $dirTree = \Modules\Insiderframework\Core\FileTree::dirTree($pathI10n);
    //     foreach ($dirTree as $dT) {
    //         if (!is_dir($dT)) {
    //             \Modules\Insiderframework\Core\Manipulation\I10n::loadi10nFile("app/$app", $dT);
    //         }
    //     }
    // }
    // if (isset($app)) {
    //     unset($app);
    // }
    // if (isset($pathI10n)) {
    //     unset($pathI10n);
    // }
    // if (isset($dirTree)) {
    //     unset($dirTree);
    // }
    // if (isset($dT)) {
    //     unset($dT);
    // }
  }
}