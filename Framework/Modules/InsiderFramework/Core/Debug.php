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
     * @param string $action Action to be fired ("count" or "render")
     *
     * @return void
     */
    public function debugBar(string $action): void
    {
        switch ($action) {
                // If it's the beggining of counting
            case "count":
                // Setting the cookie who marks the start of the script
                // (if it's not already initialized)
                $startTest = \Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie('starttime');
                if ($startTest == '0' || $startTest === false) {
                    \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie('starttime', microtime());
                }
                break;

                // Stopping the counter and rendering
            case "render":
                // Recovering the info about the time of the request
                $starttime = floatval(\Modules\InsiderFramework\Core\Manipulation\Cookie::getCookie('starttime'));

                // Initializing the variable which marks the end of the script
                $end = floatval(microtime());

                // Resetting the counter
                \Modules\InsiderFramework\Core\Manipulation\Cookie::setCookie('starttime', 0);

                // Calculating how much time was spent and how much memory is used
                $elapsedTime = round(floatval($end) - floatval($starttime), 2);
                $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);

                // Maybe was not enough time to browser send the cookie
                if ($elapsedTime < 0) {
                    // Calculating how much time was spent and the memory usage starts in 0
                    $elapsedTime = round(floatval($end) - floatval(0), 2);
                    $memoryUsage = round(((memory_get_peak_usage(true) / 1024) / 1024), 2);
                }

                // Displaying an message in the page
                $msg = '<div id="debugbarinsiderframe">';
                $msg .= '<img src="' . REQUESTED_URL . '/favicon.png" id="debugbarinsiderframeimg"/> ' .
                    'Processing time: <span style="color:#2C89A0;">' . $elapsedTime . '</span>s / ' .
                    'Memory Usage: <span style="color:#FF0000;">' . $memoryUsage . 'Mb</span>';
                $msg .= '</div>';

                \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                    array(
                        'injectedHtml' => \Modules\InsiderFramework\Core\KernelSpace::getVariable(
                            'injectedHtml',
                            'insiderFrameworkSystem'
                        ) . $msg
                    ),
                    'insiderFrameworkSystem'
                );
                break;
        }
    }
}
