<?php

namespace Modules\InsiderFramework\Core;

class CpuAvg
{
    /**
     * Checks if the cpu load monitor is enable
     *
     * @author Marcello Costa
     *
     * @package Modules\InsiderFramework\Core\Bootstrap
     *
     * @return void
     */
    public static function checkCpuUsage(): void
    {
        $loadAVG = \Modules\InsiderFramework\Core\KernelSpace::getVariable('loadAVG', 'insiderFrameworkSystem');

        if ($loadAVG["max_use"] > 0) {
            $load = sys_getloadavg();

            switch ($loadAVG["time"]) {
                case 1:
                    $loadAVG["timefunc"] = 0;
                    break;

                case 5:
                    $loadAVG["timefunc"] = 1;
                    break;

                case 15:
                    $loadAVG["timefunc"] = 2;
                    break;

                default:
                    \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                        'Invalid load_avg check time: %' . $loadAVG["time"] . '%',
                        "app/sys"
                    );
                    break;
            }
            \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                array(
                    'loadAVG' => $loadAVG
                ),
                'insiderFrameworkSystem'
            );

            if ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                if ($loadAVG['send_email'] == true) {
                    if (
                        !(\Modules\InsiderFramework\Core\Manipulation\Mail::sendMail(
                            MAILBOX,
                            MAILBOX,
                            MAILBOX_PASS,
                            "Load AVG - InsiderFramework",
                            "CPU usage alarm - " . REQUESTED_URL,
                            "CPU usage alarm - " . REQUESTED_URL . " - " . implode(",", $load),
                            MAILBOX_SMTP,
                            MAILBOX_SMTP_PORT,
                            MAILBOX_SMTP_AUTH,
                            MAILBOX_SMTP_SECURE
                        ))
                    ) {
                        error_log("It was not possible to send an error message via email to the default mailbox!", 0);
                    }
                }

                if (strpos($loadAVG['action'], 'throttle') !== false) {
                    $throttle = explode('-', $loadAVG['action']);
                    if (count($throttle) <= 1 || intval($throttle[1]) === 0) {
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "Invalid time interval in LOAD_AVG_ACTION setting for throttle",
                            "app/sys"
                        );
                    }
                    $loadAVG['action'] = 'throttle';
                    \Modules\InsiderFramework\Core\KernelSpace::setVariable(
                        array(
                            'loadAVG' => $loadAVG
                        ),
                        'insiderFrameworkSystem'
                    );
                }

                switch (strtolower(trim($loadAVG['action']))) {
                    case 'throttle':
                        $throttleTime = intval($throttle[1]);
                        while ($load[$loadAVG["timefunc"]] > $loadAVG["max_use"]) {
                            // Waiting
                            usleep($throttleTime);

                            // Getting the system load
                            $load = sys_getloadavg();
                        }
                        break;

                    case 'block-screen':
                        \Modules\InsiderFramework\Core\RoutingSystem\Request::requestRoute(
                            "/error/loadAvg"
                        );
                        die();
                        break;

                    case 'deny':
                        die();
                        break;

                    default:
                        \Modules\InsiderFramework\Core\Error\ErrorHandler::i10nErrorRegister(
                            "Invalid action '%" . $loadAVG['action'] . "%' in the LOAD_AVG_ACTION setting",
                            "app/sys"
                        );
                        break;
                }
            }
        }
    }
}
