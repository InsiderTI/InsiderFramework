<?php

namespace Modules\Insiderframework\Core\Loaders\ConfigLoader;

class LoadAvg {
   /**
    * Define LoadAvg constants from coreData array
    *
    * @author Marcello Costa
    *
    * @package Modules\Insiderframework\Core\Loaders\ConfigLoader\LoadAvg
    *
    * @param array $coreData Core data configuration loaded from files
    *
    * @return void
    */
    public static function load(array $coreData): void {
        if (!isset($coreData['LOAD_AVG_ACTION'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_ACTION'"
            );
        }
        if (!isset($coreData['LOAD_AVG_MAX_USE_CPU'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_MAX_USE_CPU'"
            );
        }
        if (!isset($coreData['LOAD_AVG_TIME'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_TIME'"
            );
        }
        if (!isset($coreData['LOAD_AVG_SEND_MAIL'])) {
            \Modules\Insiderframework\Core\Error::primaryError(
                "The following information was not found in the configuration: 'LOAD_AVG_SEND_MAIL'"
            );
        }
        /**
         * @var array $loadAVG Temporary variable that defines the maximum
         * load margin of the cpu, how many minutes can this hold until an event
         * is triggered (by default, a message to the user).
         *
         * @see https://en.wikipedia.org/wiki/Load_%28computing%29
         *
         * @package Modules\Insiderframework\Core\Loaders\ConfigLoader
         */
        $loadAVG = array(
            "action" => $coreData['LOAD_AVG_ACTION'], // Possible action to be taken if stipulated value is exceeded
            "max_use" => $coreData['LOAD_AVG_MAX_USE_CPU'], // Use 0 to deactivate. Example: 0.8 is 80% load
            "time" => $coreData['LOAD_AVG_TIME'], // Possible range values: 1, 5, and 15 (minutes)
            "send_email" => $coreData['LOAD_AVG_SEND_MAIL'] // Send email when you reach the limit
        );
        \Modules\Insiderframework\Core\KernelSpace::setVariable(
            array(
                'loadAVG' => $loadAVG
            ),
            'insiderFrameworkSystem'
        );
        unset($loadAVG);
    }
}