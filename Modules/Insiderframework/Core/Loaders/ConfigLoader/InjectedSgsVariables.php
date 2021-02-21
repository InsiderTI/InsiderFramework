<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;
use \Modules\Insiderframework\Core\KernelSpace;

class InjectedSgsVariables {
   /**
    * Define Sagacious variables constants from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\InjectedSgsVariables
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
  public static function load(array $coreData): void {
    $filename = "Web" . DIRECTORY_SEPARATOR .
                "Apps" . DIRECTORY_SEPARATOR .
                "Sys" . DIRECTORY_SEPARATOR .
                "assets" . DIRECTORY_SEPARATOR .
                "css" . DIRECTORY_SEPARATOR .
                "debug.css";

    $cssDebugBar = file_get_contents($filename);
    $injectedCss = "<style>" . $cssDebugBar . "</style>";

    KernelSpace::setVariable(array('injectedCss' => $injectedCss), 'sagacious');

    unset($filename);
    unset($cssDebugBar);
  }
}