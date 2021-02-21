<?php
namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class ToleranceLoops {
  /**
   * ToleranceLoops config loader
   *
   * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\ToleranceLoops
   */
  public static function load(array $coreData): void {
    if (!isset($coreData['MAX_TOLERANCE_LOOPS'])) {
        \Modules\Insiderframework\Core\Error::primaryError(
            "The following information was not found in the configuration: 'MAX_TOLERANCE_LOOPS'"
        );
    }

    // /**
    //  * Maximum number of loops before generating a warning for the default mail box or writing to the log
    //  *
    //  * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\ToleranceLoops
    //  */
    define('MAX_TOLERANCE_LOOPS', $coreData['MAX_TOLERANCE_LOOPS']);
  }
}