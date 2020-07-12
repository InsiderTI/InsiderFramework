<?php

namespace Modules\InsiderFramework\Core;

/**
 * Module for debugging the code
 *
 * @author  Marcello Costa <marcello88costa@yahoo.com.br>
 * @link    https://www.insiderframework.com/documentation/modules#debug
 * @license http://www.apache.org/licenses/LICENSE-2.0  Apache License 2.0
 *
 * @package Module\InsiderFramework\Core\Debug
 */
class Debug
{
    /**
     * Shows the debugBar
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Debug
     *
     * @param string       $action Action to be fired:
     *                                "startCount",
     *                                "processToInjectedHtml",
     *                                "logWarningError"
     *
     * @param ErrorMessage $errorData Error message (optional)
     *
     * @return void
     */
    public function debugBar(
        string $action,
        ?\Modules\InsiderFramework\Core\Error\ErrorMessage $errorData = null
    ): void {
        switch ($action) {
            case "startCount":
                // Setting the cookie who marks the start of the script
                // (if it's not already initialized)
                
                $debugBarData = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'debugbar',
                    'insiderFrameworkSystem'
                );
                $startTime = false;
                if (isset($debugBarData['starttime'])) {
                    $startTime = $debugBarData['starttime'];
                }
                if ($startTime == 0 || $startTime === false) {
                    \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                        array(
                            'debugbar' => array(
                                'starttime' => microtime(true)
                            )
                        ),
                        'insiderFrameworkSystem'
                    );
                }
                break;

            // Stopping the counter and processing html
            case "processToInjectedHtml":
                // Recovering the info about the time of the request
                $debugBarData = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'debugbar',
                    'insiderFrameworkSystem'
                );

                $startTime = 0;
                if (isset($debugBarData['starttime'])) {
                    $startTime = $debugBarData['starttime'];
                }

                // Initializing the variable which marks the end of the script
                $end = microtime(true);

                // Calculating how much time was spent and how much memory is used
                $elapsedTime = round($end - $startTime, 3);
                
                $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);

                // Resetting the counter
                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'debugbar' => array(
                            'starttime' => 0,
                            'elapsedTime' => $elapsedTime,
                            'memoryUsage' => $memoryUsage
                        )
                    ),
                    'insiderFrameworkSystem'
                );

                if ($elapsedTime < 0) {
                    $elapsedTime = round(floatval($end) - floatval(0), 2);
                    $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);
                }

                $debugController = new \Apps\Sys\Controllers\DebugController();
                $debugBarHtmlCode = $debugController->debugBarRender();

                $injectedHtml = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                    'injectedHtml',
                    'insiderFrameworkSystem'
                );

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'injectedHtml' => $injectedHtml . $debugBarHtmlCode
                    ),
                    'insiderFrameworkSystem'
                );
                break;

            case "logWarningError":
                $warnings = $this->getWarnings();
                $warnings[] = $errorData;

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'warnings' => $warnings
                    ),
                    'insiderFrameworkSystem'
                );
                break;
        }
    }

    /**
    * Return warnings in KernelSpace
    *
    * @author Marcello Costa
    *
    * @package Modules\InsiderFramework\Core\Debug
    *
    * @return array Warnings in KernelSpace
    */
    public function getWarnings(): array
    {
        $warnings = \Modules\InsiderFramework\Core\KernelSpace::getVariable(
            'warnings',
            'insiderFrameworkSystem'
        );

        if (!is_array($warnings)) {
            $warnings = [];
        }

        return $warnings;
    }
}
